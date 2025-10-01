<?php
require_once 'db.php';
$db = get_db();

// fetch all cars (no filtering)
$results = $db->query('SELECT id, name, filename, uploader FROM cars ORDER BY id DESC');
?>
<!doctype html>
<html class="h-full" lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>CarsApp</title>

  <!-- Tailwind CDN -->
  <script src="scripts/tailwind.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <style>
    .card-shadow { box-shadow: 0 6px 18px rgba(15,23,42,0.06); }
    .img-placeholder { height: 160px; display:flex; align-items:center; justify-content:center; background:#f3f4f6; color:#9ca3af }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 h-full">
  <header class="bg-white border-b">
    <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-cyan-400">CarsApp</div>
        <div class="text-sm text-gray-500">— upload. explore. showoff.</div>
      </div>
      <nav class="flex items-center gap-4 text-sm">
        <a href="index.php" class="px-3 py-1 rounded-md hover:bg-gray-100">Gallery</a>
      </nav>
    </div>
  </header>

  <main class="mx-auto max-w-6xl px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- left: upload -->
      <section class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl card-shadow">
          <h2 class="text-lg font-semibold">Upload your Car Image</h2>
          <p class="text-sm text-gray-500 mt-1 mb-4"></p>

          <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Car name</label>
              <input name="name" type="text" placeholder="e.g. Ferrari F8" class="mt-1 block w-full rounded-md border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-sky-400" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Uploader name</label>
              <input name="uploader" type="text" placeholder="Your name or team handle" class="mt-1 block w-full rounded-md border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-sky-400" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Image</label>
              <input id="fileInput" name="image" type="file" class="mt-1 block w-full text-sm text-gray-600" />
            </div>

            <div class="flex items-center gap-3">
              <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-sky-600 text-white hover:bg-sky-700">Upload</button>
              <button type="button" id="clearBtn" class="px-3 py-2 rounded-lg border text-sm">Clear</button>
            </div>
          </form>

        <div class="mt-6 bg-white p-4 rounded-2xl card-shadow">
          <h3 class="text-sm font-semibold text-gray-700">Quick preview</h3>
          <div id="previewBox" class="mt-3 rounded-md overflow-hidden border border-dashed border-gray-200 img-placeholder">
            <span class="text-xs">No file selected</span>
          </div>
        </div>
      </section>

      <!-- right: gallery -->
      <section class="lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold">Latest uploads</h2>
          <div class="text-sm text-gray-500">Grid view of uploaded cars</div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-5">
          <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)) : ?>
            <article class="bg-white rounded-2xl card-shadow overflow-hidden">
              <div class="relative">
                <?php if (!empty($row['filename']) && file_exists(__DIR__ . '/uploads/' . $row['filename'])): ?>
                   <!--Patching XSS vuln-->
                  <img src="uploads/<?php echo htmlspecialchars($row['filename'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full object-cover h-44" alt="car image" loading="lazy" />
                <?php else: ?>
                  <div class="img-placeholder h-44">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14M3 7a2 2 0 012-2h14l1 2M7 10h10M7 14h6" /></svg>
                  </div>
                <?php endif; ?>

                <div class="p-4">
                  <h3 class="text-lg font-semibold leading-snug">
                    <!--Patching XSS vuln-->
                    <?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                  </h3>
                  <!--Patching XSS vuln-->
                  <p class="text-xs text-gray-400 mt-1">ID: <?php echo $row['id']; ?> · <?php echo htmlspecialchars($row['filename']); ?></p>
                  <!--Patching XSS vuln-->
                  <p class="text-sm text-gray-500 mt-2">Uploaded by: <span class="font-medium"><?php echo htmlspecialchars($row['uploader'], ENT_QUOTES, 'UTF-8'); ?></span></p>

                  <div class="mt-3 flex items-center gap-2">
                    <a href="cars.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm">View</a>
                  </div>
                </div>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
      </section>
    </div>
  </main>

  <script>
    const fileInput = document.getElementById('fileInput');
    const previewBox = document.getElementById('previewBox');
    fileInput?.addEventListener('change', ev => {
      const f = ev.target.files?.[0];
      if (!f) {
        previewBox.innerHTML = '<span class="text-xs">No file selected</span>';
        return;
      }
      if (f.type.startsWith('image/')) {
        const url = URL.createObjectURL(f);
        previewBox.innerHTML = '<img src="' + url + '" class="w-full object-cover" style="height:160px" />';
      } else {
        previewBox.innerHTML = '<div class="p-4 text-sm text-gray-600">' + f.name + ' (' + Math.round(f.size/1024) + ' KB)</div>';
      }
    });

    document.getElementById('clearBtn')?.addEventListener('click', () => {
      document.getElementById('uploadForm').reset();
      previewBox.innerHTML = '<span class="text-xs">No file selected</span>';
    });
  </script>
</body>
</html>
