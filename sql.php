<?php

use App\Database\Database;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$sql = file_get_contents(__DIR__ . '/database.sql');

$db = new Database();
$db->query($sql);

echo "SQL executed successfully.";
