.DEFAULT_GOAL=help
PLATFORM := $(shell uname)

help:  ## Display this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' Makefile \
	  | sort \
	  | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[0;32m%-30s\033[0m %s\n", $$1, $$2}'

cs-fix-dry: ## Dry-run php-cs-fixer
	php vendor/bin/php-cs-fixer fix --diff --config=.php_cs.dist.php --dry-run -vvv

cs-fix: ## Run php-cs-fixer
	php vendor/bin/php-cs-fixer fix --diff --config=.php_cs.dist.php -vvv
