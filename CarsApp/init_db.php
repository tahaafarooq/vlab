<?php
require_once 'db.php';
$db = get_db();

$db->exec('CREATE TABLE IF NOT EXISTS cars (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    filename TEXT,
    uploader TEXT
);');

$db->exec("INSERT INTO cars (name, filename, uploader) VALUES ('Lambo Aventador', 'sample1.jpg', 'alice');");
$db->exec("INSERT INTO cars (name, filename, uploader) VALUES ('Jaguar XJ', 'sample2.jpg', 'bob');");

echo "DB initialized at data/cars.db\n";
