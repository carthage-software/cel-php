list:
    @just --list

install:
    composer install

fmt:
    ./vendor/bin/mago --config config/mago.toml fmt

lint:
    ./vendor/bin/mago --config config/mago.toml lint --sort

fix:
    ./vendor/bin/mago --config config/mago.toml lint --fix --unsafe
    ./vendor/bin/mago --config config/mago.toml fmt

analyze:
    ./vendor/bin/mago --config config/mago.toml analyze --sort

typos:
    typos -c config/typos.toml

bench:
    php -dmemory_limit=-1 vendor/bin/phpbench run --config=config/phpbench.json

test:
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/phpunit -c config/phpunit.xml.dist

mutation:
    php -dmemory_limit=-1 vendor/bin/infection --configuration=config/infection.json.dist

verify:
    typos -c config/typos.toml
    ./vendor/bin/mago --config config/mago.toml fmt --check
    ./vendor/bin/mago --config config/mago.toml lint
    ./vendor/bin/mago --config config/mago.toml analyze
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/phpunit -c config/phpunit.xml.dist
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/infection --configuration=config/infection.json.dist

continuous-integration:
    typos -c config/typos.toml
    ./vendor/bin/mago --config config/mago.toml fmt --check
    ./vendor/bin/mago --config config/mago.toml lint --reporting-format=github
    ./vendor/bin/mago --config config/mago.toml analyze --reporting-format=github
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/phpunit -c config/phpunit.xml.dist
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/infection --configuration=config/infection.json.dist
    XDEBUG_MODE=coverage php -dmemory_limit=-1 vendor/bin/php-coveralls -x var/clover.xml -o var/coveralls-upload.json -v
