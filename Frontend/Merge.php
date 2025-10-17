<?php
// Database connection (optional, kept for consistency)
$servername = "localhost";
$username = "root";
$password = "";
$database = "excel_db";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Merge Excel — ILoveExcel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body { background: linear-gradient(to bottom right, #e8f5e9, #f0fff4); font-family: "Inter", sans-serif; }
    .btn-green { background: linear-gradient(to right, #007b3e, #00a854); color: white; font-weight: 600; transition: 0.2s; }
    .btn-green:hover { transform: scale(1.05); box-shadow: 0 6px 20px rgba(0,123,62,0.4); }
    .excel-card { border: 1px solid #c8e6c9; box-shadow: 0 4px 12px rgba(33,115,70,0.1); backdrop-filter: blur(10px); background: rgba(255,255,255,0.8); }
    .file-list { list-style: none; padding: 0; }
    .file-item { background: #f6fff7; border: 1px solid #d4edda; padding: 8px 12px; margin: 4px 0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; cursor: move; }
    .drag-handle { cursor: grab; color: #008a4a; }
  </style>
</head>

<body class="bg-gradient-to-b from-green-50 via-white to-green-100 text-gray-800 font-[Inter]">

<header class="bg-white/70 backdrop-blur-md shadow-md sticky top-0 z-10 border-b border-green-100">
  <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-3xl font-extrabold gradient-text">💚 ILoveExcel</h1>
    <nav class="space-x-5 text-sm font-semibold flex flex-wrap justify-center">
      <a href="index.html" class="hover:text-green-700 transition">Home</a>
      <a href="Merge.php" class="text-green-700 underline underline-offset-4">Merge</a>
      <a href="split.html" class="hover:text-green-700 transition">Split</a>
      <a href="convert.html" class="hover:text-green-700 transition">Convert</a>
      <a href="clean.html" class="hover:text-green-700 transition">Clean</a>
      <a href="combine-analyze.html" class="hover:text-green-700 transition">Combine + Analyze</a>
      <a href="EditExcel.html" class="hover:text-green-700 transition">Edit Excel</a>
    </nav>
  </div>
</header>

<main class="max-w-3xl mx-auto excel-card p-8 rounded-2xl space-y-6">
  <div class="flex items-center gap-3 mb-4">
    <i data-lucide="file-plus" class="w-10 h-10 text-green-600"></i>
    <h2 class="text-2xl font-bold text-green-800">Merge Excel/CSV Files</h2>
  </div>

  <p class="text-gray-700 mb-4">Upload multiple Excel or CSV files, arrange their order, and merge them into one sheet.</p>

  <form id="mergeForm" class="space-y-4" enctype="multipart/form-data" method="POST" action="merge_process.php">
    <div>
      <label class="block font-semibold text-green-800 mb-1">Select Excel/CSV files:</label>
      <input type="file" id="fileInput" name="files[]" multiple required class="block w-full border border-green-200 rounded-lg p-2" />
    </div>

    <ul id="fileList" class="file-list"></ul>
    <input type="hidden" name="fileOrder" id="fileOrder" />

    <div>
      <label class="block font-semibold text-green-800 mb-1">Sheet Name:</label>
      <input type="text" name="sheetName" placeholder="Merged" class="block w-full border border-green-200 rounded-lg p-2" />
    </div>

    <div>
      <label class="block font-semibold text-green-800 mb-1">Custom File Name:</label>
      <input type="text" name="customName" placeholder="NEW_EMPLOYEE" class="block w-full border border-green-200 rounded-lg p-2" />
    </div>

    <button type="submit" class="btn-green px-6 py-3 rounded-lg w-full">Merge & Download</button>
  </form>
</main>

<footer class="mt-12 text-center text-gray-500">
  © 2025 <span class="font-semibold text-green-700">ILoveExcel</span> — Made with 💚
</footer>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
lucide.createIcons();

// Show uploaded files and enable drag order
document.getElementById('fileInput').addEventListener('change', function(e) {
  const list = document.getElementById('fileList');
  list.innerHTML = '';
  Array.from(e.target.files).forEach((file, i) => {
    const li = document.createElement('li');
    li.className = 'file-item';
    li.setAttribute('data-index', i);
    li.innerHTML = `
      <span>${file.name}</span>
      <i data-lucide="move" class="drag-handle w-5 h-5"></i>
    `;
    list.appendChild(li);
  });
  lucide.createIcons();
  new Sortable(list, { animation: 150 });
});

document.getElementById('mergeForm').addEventListener('submit', function(e) {
  const order = Array.from(document.querySelectorAll('#fileList li')).map(li => li.getAttribute('data-index'));
  document.getElementById('fileOrder').value = order.join(',');
});
</script>

</body>
</html>
