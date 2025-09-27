<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = isset($_POST['name']) ? $_POST['name'] : '';
$uploader = isset($_POST['uploader']) ? $_POST['uploader'] : '';
$uploadDir = __DIR__ . '/uploads/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploaded = $_FILES['image'] ?? null;
$filename = '';

if ($uploaded && $uploaded['error'] === UPLOAD_ERR_OK) {
    $origName = basename($uploaded['name']);
    $target = $uploadDir . $origName;

    if (move_uploaded_file($uploaded['tmp_name'], $target)) {
        $filename = $origName;
    }
}

$db = get_db();

$sql = "INSERT INTO cars (name, filename, uploader) VALUES ('" . $name . "', '" . $filename . "', '" . $uploader . "')";
$db->exec($sql);

header('Location: index.php');
exit;
