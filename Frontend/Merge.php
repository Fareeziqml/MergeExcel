<?php
// Database connection (optional)
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
    body {
      background: linear-gradient(to bottom right, #e8f5e9, #f0fff4);
      font-family: "Inter", sans-serif;
    }
    .btn-green {
      background: linear-gradient(to right, #007b3e, #00a854);
      color: white;
      font-weight: 600;
      transition: all 0.2s ease-in-out;
    }
    .btn-green:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 20px rgba(0,123,62,0.4);
    }
    .excel-card {
      border: 1px solid #c8e6c9;
      box-shadow: 0 4px 12px rgba(33,115,70,0.1);
      backdrop-filter: blur(10px);
      background: rgba(255,255,255,0.8);
    }
    .file-item {
      background: #f6fff7;
      border: 1px solid #d4edda;
      padding: 8px 12px;
      margin: 4px 0;
      border-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: move;
    }
    /* Popup overlay blur effect */
    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255,255,255,0.6);
      backdrop-filter: blur(8px);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 50;
      opacity: 0;
      transition: opacity 0.4s ease;
    }
    .popup-overlay.active {
      display: flex;
      opacity: 1;
    }
    .popup-box {
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      text-align: center;
      animation: fadeInUp 0.5s ease;
      width: 300px;
    }
    @keyframes fadeInUp {
      from { transform: translateY(30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
  </style>
</head>

<body class="text-gray-800 font-[Inter]">

<header class="bg-white/70 backdrop-blur-md shadow-md sticky top-0 z-10 border-b border-green-100">
  <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-3xl font-extrabold text-green-700">💚 ILoveExcel</h1>
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

<main class="max-w-3xl mx-auto excel-card p-8 rounded-2xl space-y-6 mt-8">
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

    <div class="flex gap-3">
      <button type="submit" class="btn-green px-6 py-3 rounded-lg flex-1">Merge & Download</button>
      <button type="button" id="resetBtn" class="bg-gray-200 hover:bg-gray-300 px-6 py-3 rounded-lg text-gray-700 font-semibold flex-1">Reset</button>
    </div>
  </form>
</main>

<!-- Popup Overlay -->
<div id="popupOverlay" class="popup-overlay">
  <div class="popup-box">
    <h3 class="text-xl font-semibold text-green-700 mb-3">Merging Files...</h3>
    <div class="w-full bg-gray-200 h-3 rounded-full mb-3">
      <div id="progressBar" class="bg-green-500 h-3 rounded-full w-0 transition-all duration-500"></div>
    </div>
    <p id="progressText" class="text-sm text-gray-600 mb-4">Preparing files...</p>
    <button id="cancelBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold">Cancel</button>
  </div>
</div>

<footer class="mt-12 text-center text-gray-500">
  © 2025 <span class="font-semibold text-green-700">ILoveExcel</span> — Made with 💚
</footer>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
lucide.createIcons();

// --- File preview + reorder ---
document.getElementById('fileInput').addEventListener('change', function(e) {
  const list = document.getElementById('fileList');
  list.innerHTML = '';
  Array.from(e.target.files).forEach((file, i) => {
    const li = document.createElement('li');
    li.className = 'file-item';
    li.setAttribute('data-index', i);
    li.innerHTML = `<span>${file.name}</span><i data-lucide="move" class="drag-handle w-5 h-5"></i>`;
    list.appendChild(li);
  });
  lucide.createIcons();
  new Sortable(list, { animation: 150 });
});

// --- Reset button ---
document.getElementById('resetBtn').addEventListener('click', () => {
  document.getElementById('fileInput').value = '';
  document.getElementById('fileList').innerHTML = '';
  document.getElementById('fileOrder').value = '';
});

// --- Progress Popup & Cancel ---
const popup = document.getElementById('popupOverlay');
const progressBar = document.getElementById('progressBar');
const progressText = document.getElementById('progressText');
const cancelBtn = document.getElementById('cancelBtn');
let xhr, fakeProgress = 0, fakeInterval;

document.getElementById('mergeForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const order = Array.from(document.querySelectorAll('#fileList li')).map(li => li.getAttribute('data-index'));
  document.getElementById('fileOrder').value = order.join(',');

  const formData = new FormData(this);
  popup.classList.add('active');

  xhr = new XMLHttpRequest();
  xhr.open('POST', this.action, true);
  xhr.responseType = 'blob';

  // Actual upload progress
  xhr.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      const percent = Math.round((e.loaded / e.total) * 50); // upload = first 50%
      progressBar.style.width = percent + '%';
      progressText.textContent = `Uploading files... ${percent}%`;
    }
  };

  // Simulate merging process progress
  xhr.onloadstart = function() {
    fakeProgress = 50;
    fakeInterval = setInterval(() => {
      if (fakeProgress < 95) {
        fakeProgress += Math.random() * 2;
        progressBar.style.width = fakeProgress + '%';
        progressText.textContent = `Merging files... ${Math.round(fakeProgress)}%`;
      }
    }, 400);
  };

  xhr.onload = function() {
    clearInterval(fakeInterval);
    progressBar.style.width = '100%';
    progressText.textContent = "✅ Merge complete! Downloading...";

    if (xhr.status === 200) {
      const blob = xhr.response;
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = formData.get('customName') + '.xlsx';
      link.click();
      URL.revokeObjectURL(link.href);

      setTimeout(() => popup.classList.remove('active'), 2000);
    } else {
      progressText.textContent = "❌ Failed to merge files.";
    }
  };

  xhr.onerror = function() {
    clearInterval(fakeInterval);
    progressText.textContent = "❌ Error occurred during merge.";
  };

  cancelBtn.onclick = function() {
    xhr.abort();
    clearInterval(fakeInterval);
    progressText.textContent = "⛔ Merge canceled.";
    progressBar.style.width = '0%';
    setTimeout(() => popup.classList.remove('active'), 1000);
  };

  xhr.send(formData);
});
</script>

</body>
</html>
