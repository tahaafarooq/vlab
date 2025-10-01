<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = isset($_POST['name']) ? $_POST['name'] : '';
$uploader = isset($_POST['uploader']) ? $_POST['uploader'] : '';
//PATCHING FILE UPLOAD BY SETTING UPLOAD FOLDER TO /tmp/ (TEMPORARY)
//$uploadDir = __DIR__ . '/uploads/';
$uploadDir = '/tmp/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploaded = $_FILES['image'] ?? null;
$filename = '';

// PATCHING FILE UPLOAD WITH ALLOWLIST
// adding an allowlist
$allowed_ext = ['jpg', 'jpeg', 'png'];

if ($uploaded && $uploaded['error'] === UPLOAD_ERR_OK) {
    $origName = $uploaded['name'];
    // fetch the extension
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    // checking if the image uploaded is part of allow list
    if (!in_array($ext, $allowed_ext, true)) {
        die('Upload DENIED!');
    }
    $fileName = basename($origName);
    $target = $uploadDir . $fileName;

    if (move_uploaded_file($uploaded['tmp_name'], $target)) {
        $filename = basename($target);
    }
}

$db = get_db();

$sql = "INSERT INTO cars (name, filename, uploader) VALUES ('" . $name . "', '" . $filename . "', '" . $uploader . "')";
$db->exec($sql);

header('Location: index.php');
exit;
