<?php
// ============================================================
// Bootstrap API — Memastikan output JSON selalu bersih
// Dipanggil oleh semua file di folder api/
// ============================================================
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Coba koneksi database, tangkap error di sini
try {
    require_once __DIR__ . '/../config/database.php';
    ob_end_clean(); // Bersihkan buffer (biasanya kosong jika koneksi berhasil)
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal: ' . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}

// Safety net: jika ada fatal error, tetap keluarin JSON bersih
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $error['message']
        ]);
    }
});