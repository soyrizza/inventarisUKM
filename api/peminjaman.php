<?php
require_once __DIR__ . '/bootstrap.php';

 $method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {

        case 'GET':
            $search = $_GET['search'] ?? '';
            $sql = "
                SELECT p.id_peminjaman, p.id_anggota, p.tanggal_pinjam,
                       p.tanggal_rencana_kembali, p.status, p.catatan,
                       a.nama as nama_anggota, a.nim
                FROM peminjaman p
                JOIN anggota a ON p.id_anggota = a.id_anggota
                WHERE 1=1
            ";
            $params = [];
            if ($search !== '') {
                $sql .= " AND (a.nama LIKE ? OR a.nim LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            $sql .= " ORDER BY p.id_peminjaman DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $list = $stmt->fetchAll();

            // Tambahkan detail barang untuk setiap peminjaman
            foreach ($list as &$p) {
                $s = $pdo->prepare("
                    SELECT dp.id_barang, dp.jumlah, b.nama_barang, b.kode_barang
                    FROM detail_peminjaman dp
                    JOIN barang b ON dp.id_barang = b.id_barang
                    WHERE dp.id_peminjaman = ?
                ");
                $s->execute([$p['id_peminjaman']]);
                $p['details'] = $s->fetchAll();
            }

            echo json_encode(['success' => true, 'data' => $list]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            // Validasi
            if (empty($input['id_anggota']) || empty($input['id_barang']) || empty($input['jumlah'])
                || empty($input['tgl_pinjam']) || empty($input['tgl_kembali'])) {
                echo json_encode(['success' => false, 'message' => 'Data wajib tidak lengkap']);
                exit;
            }
            if ($input['tgl_kembali'] <= $input['tgl_pinjam']) {
                echo json_encode(['success' => false, 'message' => 'Tanggal rencana kembali harus setelah tanggal pinjam']);
                exit;
            }

            $idBarang = (int)$input['id_barang'];
            $jumlah = (int)$input['jumlah'];

            // Cek stok
            $cek = $pdo->prepare("SELECT stok_tersedia, stok_total FROM barang WHERE id_barang = ?");
            $cek->execute([$idBarang]);
            $barang = $cek->fetch();
            if (!$barang) {
                echo json_encode(['success' => false, 'message' => 'Barang tidak ditemukan']);
                exit;
            }
            if ($barang['stok_tersedia'] < $jumlah) {
                echo json_encode(['success' => false, 'message' => "Stok tidak mencukupi! Tersedia: {$barang['stok_tersedia']}"]);
                exit;
            }

            $pdo->beginTransaction();

            try {
                // Insert peminjaman
                $stmt = $pdo->prepare("INSERT INTO peminjaman (id_anggota, tanggal_pinjam, tanggal_rencana_kembali, status, catatan) VALUES (?, ?, ?, 'dipinjam', ?)");
                $stmt->execute([
                    $input['id_anggota'], $input['tgl_pinjam'],
                    $input['tgl_kembali'], $input['catatan'] ?? ''
                ]);
                $idPeminjaman = $pdo->lastInsertId();

                // Insert detail
                $stmt2 = $pdo->prepare("INSERT INTO detail_peminjaman (id_peminjaman, id_barang, jumlah) VALUES (?, ?, ?)");
                $stmt2->execute([$idPeminjaman, $idBarang, $jumlah]);

                // Kurangi stok tersedia
                $stmt3 = $pdo->prepare("UPDATE barang SET stok_tersedia = stok_tersedia - ? WHERE id_barang = ?");
                $stmt3->execute([$jumlah, $idBarang]);

                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Peminjaman berhasil dicatat']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}