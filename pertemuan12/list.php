<?php
$upload_dir = 'uploads/';

// Logika untuk menghapus file
if (isset($_GET['delete'])) {
    $file_to_delete = basename($_GET['delete']);
    // Keamanan: pastikan tidak ada upaya path traversal
    if (strpos($file_to_delete, '/') === false && strpos($file_to_delete, '\\') === false) {
        $file_path_to_delete = $upload_dir . $file_to_delete;
        if (file_exists($file_path_to_delete)) {
            unlink($file_path_to_delete);
        }
    }
    // Alihkan kembali ke halaman list untuk refresh
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Files Gallery</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 2rem;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        h1 { color: #1c1e21; }
        .upload-btn { background-color: #42b72a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; }
        .file-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; }
        .file-card { background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; }
        .preview-box { height: 180px; background-color: #e9ebee; display: flex; justify-content: center; align-items: center; overflow: hidden; }
        .preview-box img, .preview-box video { width: 100%; height: 100%; object-fit: cover; }
        .pdf-render-container { width: 100%; height: 100%; overflow-y: auto; background-color: #f0f2f5; }
        .pdf-render-container canvas { display: block; width: 100% !important; height: auto !important; }
        .preview-box .icon { font-size: 60px; color: #8a8d91; }
        .file-info { padding: 1rem; text-align: center; border-top: 1px solid #e4e6eb; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .file-name { font-weight: 600; color: #1c1e21; word-break: break-all; min-height: 44px; margin-bottom: 1rem; }
        .action-links { display: flex; justify-content: space-around; align-items: center; }
        .file-link { font-size: 14px; text-decoration: none; font-weight: 600; padding: 6px 12px; border-radius: 4px; transition: background-color 0.2s; }
        .download-link { color: #1877f2; background-color: #e7f3ff; }
        .delete-link { color: #e02424; background-color: #feebe_b; }
        .no-files { grid-column: 1 / -1; text-align: center; padding: 3rem; background-color: #fff; border-radius: 8px; color: #606770; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Uploaded Files Gallery</h1>
        <a href="index.html" class="upload-btn">← Upload Another File</a>
    </div>

    <div class="file-grid">
        <?php
        if (!is_dir($upload_dir)) {
            echo '<div class="no-files"><h3>Directory Not Found</h3><p>The "uploads" directory does not exist. Please create it or upload a file to automatically create it.</p></div>';
        } else {
            // Urutkan file berdasarkan waktu modifikasi (terbaru dulu)
            $files = array_filter(scandir($upload_dir), function($file) use ($upload_dir) {
                return !is_dir($upload_dir . $file);
            });

            if (!empty($files)) {
                array_multisort(
                    array_map('filemtime', array_map(fn($f) => $upload_dir . $f, $files)),
                    SORT_DESC,
                    $files
                );

                foreach ($files as $file) {
                    $file_path = $upload_dir . $file;
                    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                    
                    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                    $video_extensions = ['mp4', 'webm', 'mov', 'ogg'];
                    
                    echo '<div class="file-card">';
                    echo '  <div class="preview-box">';

                    if (in_array($file_extension, $image_extensions)) {
                        echo '<img src="' . htmlspecialchars($file_path) . '" alt="' . htmlspecialchars($file) . '" loading="lazy">';
                    } elseif (in_array($file_extension, $video_extensions)) {
                        echo '<video muted loop onmouseover="this.play()" onmouseout="this.pause();"><source src="' . htmlspecialchars($file_path) . '"></video>';
                    } elseif ($file_extension === 'pdf') {
                        // Atribut data-pdf-src digunakan oleh JavaScript untuk menemukan file PDF
                        echo '<div class="pdf-render-container" data-pdf-src="' . htmlspecialchars($file_path) . '"></div>';
                    } else {
                        echo '<div class="icon">📄</div>'; // Ikon untuk file lain
                    }

                    echo '  </div>';
                    echo '  <div class="file-info">';
                    echo '      <div class="file-name">' . htmlspecialchars($file) . '</div>';
                    echo '      <div class="action-links">';
                    echo '          <a class="file-link download-link" href="' . htmlspecialchars($file_path) . '" download>Download</a>';
                    echo '          <a class="file-link delete-link" href="list.php?delete=' . rawurlencode($file) . '" onclick="return confirm(\'Are you sure you want to delete this file?\')">Delete</a>';
                    echo '      </div>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-files"><h2>No files uploaded yet.</h2><p>Use the upload page to add some files!</p></div>';
            }
        }
        ?>
    </div>
</div>
    <script>
        // Set worker path untuk PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        // Render pratinjau untuk semua elemen PDF di galeri
        document.querySelectorAll('div.pdf-render-container').forEach(container => {
            const pdfPath = container.dataset.pdfSrc;
            if (!pdfPath) return;

            const loadingTask = pdfjsLib.getDocument(pdfPath);
            loadingTask.promise.then(function(pdf) {
                // Hanya render halaman pertama untuk pratinjau di galeri
                return pdf.getPage(1); 
            }).then(function(page) {
                const desiredWidth = container.clientWidth;
                const viewport = page.getViewport({ scale: 1 });
                const scale = desiredWidth / viewport.width;
                const scaledViewport = page.getViewport({ scale: scale });
                
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.height = scaledViewport.height;
                canvas.width = scaledViewport.width;
                
                container.appendChild(canvas);
                
                page.render({
                    canvasContext: ctx,
                    viewport: scaledViewport
                });
            }).catch(err => {
                console.error('Error rendering PDF preview:', pdfPath, err);
                container.innerHTML = '<div class="icon">⚠</div>'; // Tampilkan ikon error jika gagal
            });
        });
    </script>

</body>
</html>