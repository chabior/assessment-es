1. Installation
    - run `composer install`
    - create `.env` file based on `.env.dist` file
    - fill `.env` file with database connection credentials
    - run `bin/console doctrine:migrations:migrate` command to populate database

2. Usage
    - start php server by calling `php -S 127.0.0.1:8000 -t public`
    - enter `http://127.0.0.1:8000/`
    - for the first visit player is auto registered
    - to clean env and start over call `bin/console app:clear` command
    - to run tests call `vendor/phpunit/phpunit/phpunit --no-configuration App\Tests\Model\PlayerTest tests/src/Model/PlayerTest.php`

3. Description     
    Assessment is modeled using EventSourcing and CQRS ideas.       

4. Todo
    - add custom exception classes
    - refactor tests
    - move deposits to database
