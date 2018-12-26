<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = sprintf(
    'mysql:host=%s;dbname=%s',
    getenv('TEST_DB_HOST'),
    getenv('TEST_DB_NAME'),
);
$db['username'] = getenv('TEST_DB_USER');
$db['password'] = getenv('TEST_DB_PASS');

return $db;
