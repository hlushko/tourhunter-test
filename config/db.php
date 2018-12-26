<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf('mysql:host=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_NAME')),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASS'),
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
