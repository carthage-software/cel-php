list:
    @just --list

install:
    composer install

fmt:
    ./vendor/bin/mago --config config/mago.toml fmt

lint:
    ./vendor/bin/mago --config config/mago.toml lint

fix:
    ./vendor/bin/mago --config config/mago.toml lint --fix --unsafe

analyze:
    ./vendor/bin/mago --config config/mago.toml analyze

typos:
    typos -c config/typos.toml

test:
    ./vendor/bin/phpunit -c config/phpunit.xml.dist

mutation:
    ./vendor/bin/infection --configuration=config/infection.json.dist

verify:
    typos -c config/typos.toml
    ./vendor/bin/mago --config config/mago.toml fmt --check
    ./vendor/bin/mago --config config/mago.toml lint
    ./vendor/bin/mago --config config/mago.toml analyze
    ./vendor/bin/phpunit -c config/phpunit.xml.dist
    ./vendor/bin/infection --configuration=config/infection.json.dist

continuous-integration:
    typos -c config/typos.toml
    ./vendor/bin/mago --config config/mago.toml fmt --check
    ./vendor/bin/mago --config config/mago.toml lint --reporting-format=github
    ./vendor/bin/mago --config config/mago.toml analyze --reporting-format=github
    ./vendor/bin/phpunit -c config/phpunit.xml.dist
    ./vendor/bin/infection --configuration=config/infection.json.dist
    ./vendor/bin/php-coveralls -x var/clover.xml -o var/coveralls-upload.json -v
