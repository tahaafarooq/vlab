<?php
$dbFile = __DIR__ . '/data/cars.db';
if (!file_exists(dirname($dbFile))) {
    mkdir(dirname($dbFile), 0777, true);
}
function get_db() {
    global $dbFile;
    $db = new SQLite3($dbFile);
    return $db;
}