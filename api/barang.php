<?php
require_once __DIR__ . '/bootstrap.php';

 $method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {

        // ---- READ ----
        case 'GET':
            $search = $_GET['search'] ?? '';
            $id_kategori = $_GET['id_kategori'] ?? '';

            $sql = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE 1=1";
            $params = [];

            if ($search !== '') {
                $sql .= " AND (b.nama_barang LIKE ? OR b.kode_barang LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            if ($id_kategori !== '') {
                $sql .= " AND b.id_kategori = ?";
                $params[] = $id_kategori;
            }

            $sql .= " ORDER BY b.id_barang ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        // ---- CREATE ----
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['nama_barang']) || empty($input['kode_barang']) || !isset($input['stok_total'])) {
                echo json_encode(['success' => false, 'message' => 'Data wajib tidak lengkap']);
                exit;
            }
            if ($input['stok_tersedia'] > $input['stok_total']) {
                echo json_encode(['success' => false, 'message' => 'Stok tersedia tidak boleh melebihi stok total']);
                exit;
            }
            // Cek kode duplikat
            $cek = $pdo->prepare("SELECT id_barang FROM barang WHERE kode_barang = ?");
            $cek->execute([$input['kode_barang']]);
            if ($cek->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Kode barang sudah digunakan']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO barang (id_kategori, nama_barang, kode_barang, stok_total, stok_tersedia, kondisi, lokasi, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $input['id_kategori'], $input['nama_barang'], $input['kode_barang'],
                $input['stok_total'], $input['stok_tersedia'], $input['kondisi'],
                $input['lokasi'], $input['keterangan'] ?? ''
            ]);
            echo json_encode(['success' => true, 'message' => 'Barang berhasil ditambahkan', 'id' => $pdo->lastInsertId()]);
            break;

        // ---- UPDATE ----
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id_barang'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
                exit;
            }
            if ($input['stok_tersedia'] > $input['stok_total']) {
                echo json_encode(['success' => false, 'message' => 'Stok tersedia tidak boleh melebihi stok total']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE barang SET id_kategori=?, nama_barang=?, kode_barang=?, stok_total=?, stok_tersedia=?, kondisi=?, lokasi=?, keterangan=? WHERE id_barang=?");
            $stmt->execute([
                $input['id_kategori'], $input['nama_barang'], $input['kode_barang'],
                $input['stok_total'], $input['stok_tersedia'], $input['kondisi'],
                $input['lokasi'], $input['keterangan'] ?? '', $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Barang berhasil diperbarui']);
            break;

        // ---- DELETE ----
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
                exit;
            }
            // Cek apakah barang sedang dipinjam
            $cek = $pdo->prepare("SELECT COUNT(*) as cnt FROM detail_peminjaman dp JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman WHERE dp.id_barang = ? AND p.status = 'dipinjam'");
            $cek->execute([$id]);
            if ($cek->fetch()['cnt'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Tidak bisa hapus: barang sedang dipinjam']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM barang WHERE id_barang = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Barang berhasil dihapus']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}