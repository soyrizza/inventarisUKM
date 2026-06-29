<?php
require_once __DIR__ . '/bootstrap.php';

try {
    // Total transaksi
    $totalPinjam = $pdo->query("SELECT COUNT(*) as val FROM peminjaman")->fetch()['val'];
    $totalKembali = $pdo->query("SELECT COUNT(*) as val FROM pengembalian")->fetch()['val'];
    $totalAktif = $pdo->query("SELECT COUNT(*) as val FROM peminjaman WHERE status = 'dipinjam'")->fetch()['val'];

    // Stok per kategori
    $kategori = $pdo->query("
        SELECT k.id_kategori, k.nama_kategori,
               COUNT(b.id_barang) as jenis,
               COALESCE(SUM(b.stok_total), 0) as total,
               COALESCE(SUM(b.stok_tersedia), 0) as tersedia
        FROM kategori k
        LEFT JOIN barang b ON b.id_kategori = k.id_kategori
        GROUP BY k.id_kategori, k.nama_kategori
    ")->fetchAll();

    // Riwayat gabungan peminjaman + pengembalian
    $riwayat = [];

    $pinjamAll = $pdo->query("
        SELECT p.id_peminjaman, p.tanggal_pinjam as tanggal,
               a.nama as peminjam,
               GROUP_CONCAT(b.nama_barang SEPARATOR ', ') as barang,
               'pinjam' as tipe, p.status
        FROM peminjaman p
        JOIN anggota a ON p.id_anggota = a.id_anggota
        LEFT JOIN detail_peminjaman dp ON dp.id_peminjaman = p.id_peminjaman
        LEFT JOIN barang b ON dp.id_barang = b.id_barang
        GROUP BY p.id_peminjaman, a.nama, p.tanggal_pinjam, p.status
    ")->fetchAll();

    foreach ($pinjamAll as $p) {
        $riwayat[] = [
            'peminjam' => $p['peminjam'],
            'barang'   => $p['barang'],
            'status'   => $p['status'],
            'tanggal'  => $p['tanggal'],
            'tipe'     => 'pinjam'
        ];
    }

    $kembaliAll = $pdo->query("
        SELECT pg.tanggal_kembali as tanggal,
               a.nama as peminjam,
               GROUP_CONCAT(b.nama_barang SEPARATOR ', ') as barang,
               pg.kondisi_kembali as kondisi
        FROM pengembalian pg
        JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
        JOIN anggota a ON p.id_anggota = a.id_anggota
        LEFT JOIN detail_peminjaman dp ON dp.id_peminjaman = p.id_peminjaman
        LEFT JOIN barang b ON dp.id_barang = b.id_barang
        GROUP BY pg.id_pengembalian, a.nama, pg.tanggal_kembali, pg.kondisi_kembali
    ")->fetchAll();

    foreach ($kembaliAll as $k) {
        $riwayat[] = [
            'peminjam' => $k['peminjam'],
            'barang'   => $k['barang'] . ' [' . $k['kondisi'] . ']',
            'status'   => 'selesai',
            'tanggal'  => $k['tanggal'],
            'tipe'     => 'kembali'
        ];
    }

    // Sort by tanggal descending
    usort($riwayat, function($a, $b) {
        return strcmp($b['tanggal'], $a['tanggal']);
    });

    echo json_encode([
        'success' => true,
        'data' => [
            'total_pinjam'  => (int)$totalPinjam,
            'total_kembali' => (int)$totalKembali,
            'total_aktif'   => (int)$totalAktif,
            'kategori'      => $kategori,
            'riwayat'       => $riwayat
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}