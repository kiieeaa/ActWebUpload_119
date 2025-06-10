<?php
// Atur batas file dan waktu eksekusi jika perlu
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '55M');
ini_set('max_execution_time', '300');

$target_dir = "uploads/";

// Buat direktori 'uploads' jika belum ada
if (!is_dir($target_dir)) {
    // mkdir akan membuat folder, 0755 adalah izin akses standar
    if (!mkdir($target_dir, 0755, true)) {
        upload_error("Failed to create the 'uploads' directory. Please check permissions.");
    }
}

// Fungsi untuk menampilkan pesan error dengan tampilan yang rapi
function upload_error($message) {
    http_response_code(400); // Set response code ke Bad Request
    echo '<!DOCTYPE html><html lang="en"><head><title>Upload Error</title><style>body{font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;background-color:#f0f2f5;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}.message{background:white;padding:2rem 3rem;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);text-align:center;}h3{color:#d93025;}a{color:#1877f2;text-decoration:none;font-weight:600;display:inline-block;margin-top:1.5rem;}</style></head><body>';
    echo '<div class="message"><h3>Upload Failed!</h3><p>' . htmlspecialchars($message) . '</p><a href="index.html">← Go Back and Try Again</a></div>';
    echo '</body></html>';
    exit(); // Hentikan eksekusi skrip
}

// Cek apakah form telah disubmit
if (isset($_POST["submit"])) {
    // Cek apakah ada file yang diunggah
    if (!isset($_FILES["fileToUpload"]) || $_FILES["fileToUpload"]["error"] == UPLOAD_ERR_NO_FILE) {
        upload_error("No file was selected for upload.");
    }
    
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    
    // Cek apakah file sudah ada
    if (file_exists($target_file)) {
        upload_error("Sorry, a file with that name already exists.");
    }

    // Cek ukuran file (contoh: 50MB)
    if ($_FILES["fileToUpload"]["size"] > 50 * 1024 * 1024) { // 50 MB
        upload_error("Sorry, your file is too large. The limit is 50MB.");
    }

    /*
    // OPSIONAL: Aktifkan jika Anda hanya ingin mengizinkan tipe file tertentu
    $allowed_types = ["jpg", "jpeg", "png", "gif", "webp", "pdf", "mp4", "mov"];
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (!in_array($fileType, $allowed_types)) {
        upload_error("Sorry, only image files (JPG, PNG, GIF), PDFs, and videos (MP4, MOV) are allowed.");
    }
    */

    // Coba pindahkan file yang diunggah
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        // Jika berhasil, alihkan ke halaman galeri
        header("Location: list.php?upload=success");
        exit();
    } else {
        // Tangani error yang mungkin terjadi saat pemindahan
        upload_error("Sorry, there was an unknown error uploading your file. Please check server logs or folder permissions.");
    }
} else {
    // Alihkan jika skrip diakses langsung tanpa submit form
    header("Location: index.html");
    exit();
}
?>