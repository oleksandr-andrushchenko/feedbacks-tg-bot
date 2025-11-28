include .env
export

DC = docker-compose
BE_FUNCTION_CONTAINER = $(DOCKER_NAME)-be-function
RDBMS_CONTAINER = $(DOCKER_NAME)-rdbms
DYNAMODB_CONTAINER = $(DOCKER_NAME)-dynamodb

.PHONY: help
help: ## Show this help
	@echo "Available commands:"
	@awk -F '## ' '/^[a-zA-Z0-9_-]+:.*##/ { \
		split($$1, a, ":"); \
		printf "  \033[36m%-20s\033[0m %s\n", a[1], $$2 \
	}' $(MAKEFILE_LIST) | sort

.PHONY: ngrok-tunnel
ngrok-tunnel: ## Start ngrok tunnel for backend function
	ngrok http --host-header=rewrite http://localhost:$(BE_FUNCTION_PORT)

.PHONY: start
start: ## Build and start all Docker containers
	docker compose up -d --build --force-recreate

.PHONY: stop
stop: ## Stop and remove all Docker containers
	docker compose down --remove-orphans

.PHONY: restart
restart: stop start ## Restart all Docker containers and show status
	docker compose ps -a

.PHONY: composer-install
composer-install: ## Run composer install inside be-function container
	docker exec -it $(BE_FUNCTION_CONTAINER) composer install

.PHONY: warmup-cache
warmup-cache: ## Warm up Symfony cache inside be-function container
	docker exec -it $(BE_FUNCTION_CONTAINER) php bin/console cache:warmup

.PHONY: clear-cache
clear-cache: ## Clear cache inside be-function
	docker exec -it $(BE_FUNCTION_CONTAINER) php bin/console cache:clear

.PHONY: import-bots
import-bots: ## Import Telegram bots from CSV file
	docker exec -it $(BE_FUNCTION_CONTAINER) php bin/console telegram:bot:import telegram_bots.csv

.PHONY: sync-bot-webhook
sync-bot-webhook: ## Synchronize Telegram bot webhook
	docker exec -it $(BE_FUNCTION_CONTAINER) php bin/console telegram:bot:webhook:sync wild_s_local_bot

.PHONY: be-function-logs
be-function-logs: ## View be-function function logs
	docker logs $(BE_FUNCTION_CONTAINER) -f

.PHONY: login
login: ## Open shell inside be-function container
	docker exec -it $(BE_FUNCTION_CONTAINER) bash

.PHONY: search
search: ## Search for a Telegram user by name
	docker exec -it $(BE_FUNCTION_CONTAINER) php bin/console telegram:bot:search "Андрущенко Олександр" person_name --country=ua

.PHONY: logs
logs: ## Tail Symfony development logs
	docker exec -it $(BE_FUNCTION_CONTAINER) tail -f var/log/dev.log

.PHONY: rdbms-logs
rdbms-logs: ## View database (MySQL) container logs
	docker logs $(RDBMS_CONTAINER) -f

.PHONY: rdbms-login
rdbms-login: ## Open MySQL shell inside database container
	docker exec -it $(RDBMS_CONTAINER) mysql -uapp -p1111 -A app

.PHONY: generate-migration
generate-migration: ## Generate a new Doctrine migration file
	docker exec -it $(BE_FUNCTION_CONTAINER) php bin/console doctrine:migrations:diff

.PHONY: run-migrations
run-migrations: ## Execute pending Doctrine migrations
	docker exec -it $(BE_FUNCTION_CONTAINER) php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing

.PHONY: create-local-dynamodb
create-local-dynamodb: ## Create local DynamoDB table
	@echo "🚀 Creating local DynamoDB table $(DYNAMODB_TABLE)..."
	@if AWS_ACCESS_KEY_ID=$(AWS_ACCESS_KEY_ID) AWS_SECRET_ACCESS_KEY=$(AWS_SECRET_ACCESS_KEY) \
		aws dynamodb describe-table \
		--region "$(AWS_REGION)" \
		--table-name "$(DYNAMODB_TABLE)" \
		--endpoint-url "http://localhost:$(DYNAMODB_PORT)" > /dev/null 2>&1; then \
		echo "⚠️ Table $(DYNAMODB_TABLE) already exists, skipping creation."; \
	else \
		echo "🧩 Extracting DynamoDB schema from CloudFormation..."; \
		docker exec -it $(BE_FUNCTION_CONTAINER) php bin/console dynamodb:schema:extract > /tmp/dynamodb_schema.json; \
		if [ ! -s /tmp/dynamodb_schema.json ]; then echo '❌ Failed to generate valid DynamoDB schema JSON'; exit 1; fi; \
		echo "📄 Generated schema:"; \
		cat /tmp/dynamodb_schema.json | jq .; \
		AWS_ACCESS_KEY_ID=$(AWS_ACCESS_KEY_ID) AWS_SECRET_ACCESS_KEY=$(AWS_SECRET_ACCESS_KEY) \
		aws dynamodb create-table \
			--region "$(AWS_REGION)" \
			--cli-input-json file:///tmp/dynamodb_schema.json \
			--table-name "$(DYNAMODB_TABLE)" \
			--endpoint-url http://localhost:$(DYNAMODB_PORT) \
			--no-cli-pager; \
		rm -f /tmp/dynamodb_schema.json; \
		echo "✅ DynamoDB table $(DYNAMODB_TABLE) initialized in local DynamoDB"; \
	fi

.PHONY: fetch-local-dynamodb
fetch-local-dynamodb: ## Fetch 100 records from local DynamoDB
	@echo "📦 Fetching 100 records from $(DYNAMODB_TABLE)..."
	AWS_ACCESS_KEY_ID=$(AWS_ACCESS_KEY_ID) AWS_SECRET_ACCESS_KEY=$(AWS_SECRET_ACCESS_KEY) \
	aws dynamodb scan \
		--table-name "$(DYNAMODB_TABLE)" \
		--limit 100 \
		--endpoint-url "http://localhost:$(DYNAMODB_PORT)" \
		--region "$(AWS_REGION)" \
		--no-cli-pager \
		--output json

.PHONY: drop-local-dynamodb
drop-local-dynamodb: ## Drop DynamoDB table in local DynamoDB
	@echo "🗑️ Dropping local DynamoDB table $(DYNAMODB_TABLE)..."
	@if AWS_ACCESS_KEY_ID=$(AWS_ACCESS_KEY_ID) AWS_SECRET_ACCESS_KEY=$(AWS_SECRET_ACCESS_KEY) \
		aws dynamodb describe-table \
		--region "$(AWS_REGION)" \
		--table-name "$(DYNAMODB_TABLE)" \
		--endpoint-url "http://localhost:$(DYNAMODB_PORT)" > /dev/null 2>&1; then \
		AWS_ACCESS_KEY_ID=$(AWS_ACCESS_KEY_ID) AWS_SECRET_ACCESS_KEY=$(AWS_SECRET_ACCESS_KEY) \
		aws dynamodb delete-table \
			--region "$(AWS_REGION)" \
			--table-name "$(DYNAMODB_TABLE)" \
			--endpoint-url http://localhost:$(DYNAMODB_PORT) \
			--no-cli-pager; \
		echo "✅ Table $(DYNAMODB_TABLE) deleted from local DynamoDB"; \
	else \
		echo "⚠️ Table $(DYNAMODB_TABLE) does not exist, skipping deletion."; \
	fi

.PHONY: recreate-local-dynamodb
recreate-local-dynamodb: drop-local-dynamodb create-local-dynamodb ## Recreate DynamoDB table in local DynamoDB