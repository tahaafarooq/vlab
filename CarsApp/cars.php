<?php

require_once 'db.php';
$db = get_db();

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (isset($_GET['img'])) {
    $requested = $_GET['img'];

    $filePath = __DIR__ . '/' . $requested;

    if (file_exists($filePath) && is_file($filePath)) {
        $finfoType = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $finfoType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $finfoType = mime_content_type($filePath);
        }

        if (!$finfoType) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $map = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png', 'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'txt' => 'text/plain', 'log' => 'text/plain',
            ];
            $finfoType = $map[$ext] ?? 'application/octet-stream';
        }
        header('Content-Type: ' . $finfoType);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo "<!doctype html><html><head><meta charset='utf-8'><title>File not found</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-gray-50'><div class='mx-auto max-w-3xl p-8'><div class='bg-white p-6 rounded-2xl shadow'><h2 class='text-xl font-semibold'>File not found</h2><p class='mt-2 text-gray-500'>Requested file: <code>" . htmlspecialchars($requested) . "</code> does not exist.</p><p class='mt-4'><a href='index.php' class='text-sky-600'>Back to gallery</a></p></div></div></body></html>";
        exit;
    }
}

$query = "SELECT id, name, filename, uploader FROM cars WHERE id = $id LIMIT 1";
$res = $db->query($query);
$row = $res ? $res->fetchArray(SQLITE3_ASSOC) : false;

if (!$row) {
    http_response_code(404);
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Not found</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-50">';
    echo '<div class="mx-auto max-w-3xl p-8"><div class="bg-white p-6 rounded-2xl shadow"> <h2 class="text-xl font-semibold">Car not found</h2><p class="mt-2 text-gray-500">No car matches that id. Try exploring the gallery.</p><p class="mt-4"><a href="index.php" class="text-sky-600">Back to gallery</a></p></div></div></body></html>';
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Car #<?php echo htmlspecialchars($row['id']); ?> — CarsApp</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .card-shadow { box-shadow: 0 6px 18px rgba(15,23,42,0.06); }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">
  <div class="mx-auto max-w-4xl px-4 py-10">
    <a href="index.php" class="inline-block mb-6 text-sm text-gray-500">← Back to gallery</a>

    <div class="bg-white rounded-2xl p-6 card-shadow">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <div>
          <?php if (!empty($row['filename']) && file_exists(__DIR__ . '/uploads/' . $row['filename'])): ?>
            <img src="cars.php?img=uploads/<?php echo rawurlencode($row['filename']); ?>" class="w-full rounded-lg object-contain max-h-[60vh]" alt="car image" />
          <?php else: ?>
            <div class="h-64 flex items-center justify-center rounded-lg bg-gray-100 text-gray-400">No image</div>
          <?php endif; ?>
        </div>

        <div>
          <h1 class="text-2xl font-bold mb-2">
            <?php echo $row['name']; ?>
          </h1>

          <p class="text-sm text-gray-500 mb-2">Uploaded by: <strong><?php echo $row['uploader']; ?></strong></p>
          <p class="text-sm text-gray-500 mb-4">Car ID: <?php echo htmlspecialchars($row['id']); ?></p>

          <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
            <h3 class="font-medium text-gray-700">Metadata</h3>
            <dl class="mt-2 text-sm text-gray-600">
              <div class="flex justify-between py-2">
                <dt>Filename</dt>
                <dd><?php echo htmlspecialchars($row['filename']); ?></dd>
              </div>
              <div class="flex justify-between py-2">
                <dt>Source</dt>
                <dd>uploads/</dd>
              </div>
            </dl>
          </div>

          <div class="mt-6">
            <?php if (!empty($row['filename'])): ?>
              <a href="cars.php?img=uploads/<?php echo rawurlencode($row['filename']); ?>" class="inline-block px-4 py-2 rounded-lg border hover:bg-gray-100">Open Image</a>
            <?php endif; ?>
            <a href="index.php" class="ml-3 inline-block px-4 py-2 rounded-lg bg-sky-600 text-white hover:bg-sky-700">Back</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
