<?php
require_once __DIR__ . '/bootstrap.php';

 $method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {

        case 'GET':
            // Ambil daftar peminjaman yang statusnya 'dipinjam'
            $stmt = $pdo->query("
                SELECT p.id_peminjaman, p.id_anggota, p.tanggal_pinjam,
                       p.tanggal_rencana_kembali, p.status, p.catatan,
                       a.nama as nama_anggota, a.nim
                FROM peminjaman p
                JOIN anggota a ON p.id_anggota = a.id_anggota
                WHERE p.status = 'dipinjam'
                ORDER BY p.id_peminjaman DESC
            ");
            $list = $stmt->fetchAll();

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

            if (empty($input['id_peminjaman']) || empty($input['tgl_kembali']) || empty($input['kondisi'])) {
                echo json_encode(['success' => false, 'message' => 'Data wajib tidak lengkap']);
                exit;
            }

            $idPeminjaman = (int)$input['id_peminjaman'];
            $kondisi = $input['kondisi'];

            // Ambil detail peminjaman
            $stmt = $pdo->prepare("SELECT * FROM peminjaman WHERE id_peminjaman = ? AND status = 'dipinjam'");
            $stmt->execute([$idPeminjaman]);
            $pinjam = $stmt->fetch();
            if (!$pinjam) {
                echo json_encode(['success' => false, 'message' => 'Data peminjaman tidak ditemukan atau sudah selesai']);
                exit;
            }

            // Ambil detail barang
            $stmt2 = $pdo->prepare("
                SELECT dp.id_barang, dp.jumlah, b.nama_barang
                FROM detail_peminjaman dp
                JOIN barang b ON dp.id_barang = b.id_barang
                WHERE dp.id_peminjaman = ?
            ");
            $stmt2->execute([$idPeminjaman]);
            $details = $stmt2->fetchAll();

            $pdo->beginTransaction();

            try {
                foreach ($details as $d) {
                    $idBarang = $d['id_barang'];
                    $jumlah = $d['jumlah'];

                    if ($kondisi === 'Baik') {
                        // Kembalikan stok tersedia
                        $upd = $pdo->prepare("UPDATE barang SET stok_tersedia = stok_tersedia + ? WHERE id_barang = ?");
                        $upd->execute([$jumlah, $idBarang]);
                    } elseif ($kondisi === 'Hilang') {
                        // Kurangi stok total
                        $upd = $pdo->prepare("UPDATE barang SET stok_total = GREATEST(stok_total - ?, 0) WHERE id_barang = ?");
                        $upd->execute([$jumlah, $idBarang]);
                    } else {
                        // Rusak Ringan / Rusak Berat: kembalikan stok tapi update kondisi
                        $upd = $pdo->prepare("UPDATE barang SET stok_tersedia = stok_tersedia + ?, kondisi = ? WHERE id_barang = ?");
                        $upd->execute([$jumlah, $kondisi, $idBarang]);
                    }
                }

                // Update status peminjaman
                $upd2 = $pdo->prepare("UPDATE peminjaman SET status = 'selesai' WHERE id_peminjaman = ?");
                $upd2->execute([$idPeminjaman]);

                // Insert pengembalian
                $ins = $pdo->prepare("INSERT INTO pengembalian (id_peminjaman, tanggal_kembali, kondisi_kembali, denda, catatan) VALUES (?, ?, ?, 0.00, ?)");
                $ins->execute([$idPeminjaman, $input['tgl_kembali'], $kondisi, $input['catatan'] ?? '']);

                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Pengembalian berhasil diproses']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}