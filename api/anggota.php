<?php
require_once __DIR__ . '/bootstrap.php';

 $method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $search = $_GET['search'] ?? '';
            if ($search !== '') {
                $stmt = $pdo->prepare("SELECT * FROM anggota WHERE nama LIKE ? OR nim LIKE ? ORDER BY id_anggota ASC");
                $stmt->execute(["%$search%", "%$search%"]);
            } else {
                $stmt = $pdo->query("SELECT * FROM anggota ORDER BY id_anggota ASC");
            }
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['nim']) || empty($input['nama']) || empty($input['prodi'])) {
                echo json_encode(['success' => false, 'message' => 'Data wajib tidak lengkap']);
                exit;
            }
            $cek = $pdo->prepare("SELECT id_anggota FROM anggota WHERE nim = ?");
            $cek->execute([$input['nim']]);
            if ($cek->fetch()) {
                echo json_encode(['success' => false, 'message' => 'NIM sudah terdaftar']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO anggota (nim, nama, prodi, no_hp) VALUES (?, ?, ?, ?)");
            $stmt->execute([$input['nim'], $input['nama'], $input['prodi'], $input['no_hp'] ?? '']);
            echo json_encode(['success' => true, 'message' => 'Anggota berhasil ditambahkan']);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id_anggota'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE anggota SET nim=?, nama=?, prodi=?, no_hp=? WHERE id_anggota=?");
            $stmt->execute([$input['nim'], $input['nama'], $input['prodi'], $input['no_hp'] ?? '', $id]);
            echo json_encode(['success' => true, 'message' => 'Anggota berhasil diperbarui']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
                exit;
            }
            $cek = $pdo->prepare("SELECT COUNT(*) as cnt FROM peminjaman WHERE id_anggota = ?");
            $cek->execute([$id]);
            if ($cek->fetch()['cnt'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Tidak bisa hapus: anggota memiliki riwayat peminjaman']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM anggota WHERE id_anggota = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Anggota berhasil dihapus']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}