<?php
// public/php/config.php

return [
    'host' => getenv('MYSQL_SERVER'),
    'user' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'database' => getenv('MYSQL_DATABASE')
]; 