<?php
require_once __DIR__ . '/bootstrap.php';

 $method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT * FROM kategori ORDER BY id_kategori ASC");
            $data = $stmt->fetchAll();
            // Tambahkan jumlah barang per kategori
            foreach ($data as &$k) {
                $s = $pdo->prepare("SELECT COUNT(*) as cnt FROM barang WHERE id_kategori = ?");
                $s->execute([$k['id_kategori']]);
                $k['jumlah_barang'] = (int)$s->fetch()['cnt'];
            }
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['nama_kategori'])) {
                echo json_encode(['success' => false, 'message' => 'Nama kategori wajib diisi']);
                exit;
            }
            $cek = $pdo->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ?");
            $cek->execute([$input['nama_kategori']]);
            if ($cek->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Nama kategori sudah ada']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori, keterangan) VALUES (?, ?)");
            $stmt->execute([$input['nama_kategori'], $input['keterangan'] ?? '']);
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil ditambahkan']);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id_kategori'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori=?, keterangan=? WHERE id_kategori=?");
            $stmt->execute([$input['nama_kategori'], $input['keterangan'] ?? '', $id]);
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil diperbarui']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
                exit;
            }
            $cek = $pdo->prepare("SELECT COUNT(*) as cnt FROM barang WHERE id_kategori = ?");
            $cek->execute([$id]);
            if ($cek->fetch()['cnt'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Tidak bisa hapus: kategori masih memiliki barang']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil dihapus']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}