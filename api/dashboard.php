<?php
require_once __DIR__ . '/bootstrap.php';

try {
    // Statistik utama
    $total = $pdo->query("SELECT COALESCE(SUM(stok_total), 0) as val FROM barang")->fetch()['val'];
    $tersedia = $pdo->query("SELECT COALESCE(SUM(stok_tersedia), 0) as val FROM barang")->fetch()['val'];
    $dipinjam = $total - $tersedia;
    $rusak = $pdo->query("SELECT COUNT(*) as val FROM barang WHERE kondisi != 'Baik'")->fetch()['val'];

    // Peminjaman terbaru (5 terakhir)
    $stmt = $pdo->query("
        SELECT p.id_peminjaman, p.tanggal_pinjam, p.status,
               a.nama as nama_anggota
        FROM peminjaman p
        JOIN anggota a ON p.id_anggota = a.id_anggota
        ORDER BY p.id_peminjaman DESC
        LIMIT 5
    ");
    $recent = $stmt->fetchAll();

    // Tambahkan detail barang ke setiap peminjaman
    foreach ($recent as &$r) {
        $s = $pdo->prepare("
            SELECT b.nama_barang
            FROM detail_peminjaman dp
            JOIN barang b ON dp.id_barang = b.id_barang
            WHERE dp.id_peminjaman = ?
        ");
        $s->execute([$r['id_peminjaman']]);
        $r['barang_list'] = array_column($s->fetchAll(), 'nama_barang');
    }

    // Ringkasan kategori
    $kategori = $pdo->query("
        SELECT k.id_kategori, k.nama_kategori,
               COUNT(b.id_barang) as jumlah
        FROM kategori k
        LEFT JOIN barang b ON b.id_kategori = k.id_kategori
        GROUP BY k.id_kategori, k.nama_kategori
    ")->fetchAll();

    $totalBarang = $pdo->query("SELECT COUNT(*) as val FROM barang")->fetch()['val'];

    echo json_encode([
        'success' => true,
        'data' => [
            'total'      => (int)$total,
            'tersedia'   => (int)$tersedia,
            'dipinjam'   => (int)$dipinjam,
            'rusak'      => (int)$rusak,
            'recent'     => $recent,
            'kategori'   => $kategori,
            'total_barang' => (int)$totalBarang
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}