
all: test mago stan

test:
	vendor/bin/phpunit tests/

mago:
	mago analyze --minimum-report-level error

stan:
	vendor/bin/phpstan analyse src tests --memory-limit 1G --level 5