<?php
require_once __DIR__ . '/bootstrap.php';

 $method = $_SERVER['REQUEST_METHOD'];

// Hanya admin yang boleh akses (cek via session)
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

try {
    switch ($method) {

        case 'GET':
            $stmt = $pdo->query("SELECT id_user, nama, username, role, created_at FROM users ORDER BY id_user ASC");
            $data = $stmt->fetchAll();
            // Format tanggal
            foreach ($data as &$u) {
                $u['created_at'] = date('d M Y', strtotime($u['created_at']));
            }
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['nama']) || empty($input['username']) || empty($input['password']) || empty($input['role'])) {
                echo json_encode(['success' => false, 'message' => 'Data wajib tidak lengkap']);
                exit;
            }
            // Cek username duplikat
            $cek = $pdo->prepare("SELECT id_user FROM users WHERE username = ?");
            $cek->execute([$input['username']]);
            if ($cek->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
                exit;
            }
            $hash = password_hash($input['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$input['nama'], $input['username'], $hash, $input['role']]);
            echo json_encode(['success' => true, 'message' => 'User berhasil ditambahkan']);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id_user'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
                exit;
            }
            // Jika password diisi, hash baru. Jika kosong, pertahankan yang lama.
            if (!empty($input['password'])) {
                $hash = password_hash($input['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, password=?, role=? WHERE id_user=?");
                $stmt->execute([$input['nama'], $input['username'], $hash, $input['role'], $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id_user=?");
                $stmt->execute([$input['nama'], $input['username'], $input['role'], $id]);
            }
            echo json_encode(['success' => true, 'message' => 'User berhasil diperbarui']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
                exit;
            }
            // Cegah hapus diri sendiri
            if ($id == $_SESSION['user']['id_user']) {
                echo json_encode(['success' => false, 'message' => 'Tidak bisa menghapus akun yang sedang digunakan']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}