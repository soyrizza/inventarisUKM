<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$currentUser = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventaris UKM - Manajemen Inventaris</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

<script>
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        heading: ['-apple-system', 'BlinkMacSystemFont', 'SF Pro Display', 'Helvetica Neue', 'sans-serif'],
        body: ['-apple-system', 'BlinkMacSystemFont', 'SF Pro Text', 'Helvetica Neue', 'sans-serif'],
      }
    }
  }
}
</script>

<!-- Custom CSS dipisah ke file terpisah -->
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-h-screen overflow-x-hidden grid-bg role-<?php echo $currentUser['role']; ?>">

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-[100] flex flex-col gap-3 pointer-events-none"></div>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar fixed top-0 left-0 h-full w-64 bg-[#050505] border-r border-white/5 flex flex-col z-50">
  <div class="p-5 border-b border-white/5">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-lime-400/10 border border-lime-400/20 flex items-center justify-center">
        <span class="iconify text-lime-400 text-lg" data-icon="lucide:package"></span>
      </div>
      <div>
        <h1 class="text-sm font-heading font-semibold text-neutral-100 tracking-tight">Inventaris UKM</h1>
        <p class="text-[10px] text-neutral-500 font-medium tracking-wide uppercase">Management System</p>
      </div>
    </div>
  </div>
  <nav class="flex-1 p-3 space-y-1 overflow-y-auto scrollbar-thin">
    <button onclick="showPage('dashboard')" data-page="dashboard" class="sidebar-link active w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-left">
      <span class="iconify text-base" data-icon="lucide:layout-dashboard"></span> Dashboard
    </button>
    <button onclick="showPage('barang')" data-page="barang" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-neutral-400 text-left">
      <span class="iconify text-base" data-icon="lucide:box"></span> Data Barang
    </button>
        <?php if ($currentUser['role'] !== 'anggota'): ?>
        <button onclick="showPage('kategori')" data-page="kategori" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-neutral-400 text-left">
          <span class="iconify text-base" data-icon="lucide:tags"></span> Kategori
        </button>
        <?php endif; ?>
     <?php if ($currentUser['role'] !== 'anggota'): ?>
    <button onclick="showPage('anggota')" data-page="anggota" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-neutral-400 text-left">
      <span class="iconify text-base" data-icon="lucide:users"></span> Data Anggota
    </button>
    <?php endif; ?>
        <?php if ($currentUser['role'] === 'admin'): ?>
        <div class="pt-3 pb-1 px-3">
            <p class="text-[10px] font-medium text-neutral-600 uppercase tracking-widest">Pengaturan</p>
        </div>
        <button onclick="showPage('users')" data-page="users" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-neutral-400 text-left">
            <span class="iconify text-base" data-icon="lucide:shield"></span> Kelola User
        </button>
        <?php endif; ?>
    <div class="pt-3 pb-1 px-3">
      <p class="text-[10px] font-medium text-neutral-600 uppercase tracking-widest">Transaksi</p>
    </div>
    <button onclick="showPage('peminjaman')" data-page="peminjaman" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-neutral-400 text-left">
      <span class="iconify text-base" data-icon="lucide:log-out"></span> Peminjaman
    </button>
    <button onclick="showPage('pengembalian')" data-page="pengembalian" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-neutral-400 text-left">
      <span class="iconify text-base" data-icon="lucide:log-in"></span> Pengembalian
    </button>
    <div class="pt-3 pb-1 px-3">
      <p class="text-[10px] font-medium text-neutral-600 uppercase tracking-widest">Laporan</p>
    </div>
    <button onclick="showPage('laporan')" data-page="laporan" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-neutral-400 text-left">
      <span class="iconify text-base" data-icon="lucide:file-bar-chart"></span> Laporan Inventaris
    </button>
  </nav>
  <div class="p-4 border-t border-white/5">
    <div class="flex items-center gap-2 text-[10px] text-neutral-600">
      <span class="iconify" data-icon="lucide:info"></span>
      <span>UKM Inventory v1.0 — 2025</span>
    </div>
  </div>
</aside>

<!-- Main Content -->
<main class="lg:ml-64 min-h-screen grid-bg">
  <header class="sticky top-0 z-30 bg-[#020202]/80 backdrop-blur-xl border-b border-white/5">
    <div class="flex items-center justify-between px-4 lg:px-6 h-14">
      <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-white/5 transition-colors">
          <span class="iconify text-lg text-neutral-400" data-icon="lucide:menu"></span>
        </button>
        <h2 id="pageTitle" class="text-sm font-semibold text-neutral-200">Dashboard</h2>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative">
          <span class="iconify text-neutral-500 text-lg" data-icon="lucide:bell"></span>
          <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-lime-400 rounded-full pulse-dot"></span>
        </div>
        <div class="flex items-center gap-2">
  <div class="hidden sm:flex flex-col items-end">
    <span class="text-xs text-neutral-200 font-medium"><?php echo htmlspecialchars($currentUser['nama']); ?></span>
    <span class="text-[10px] text-neutral-500 uppercase"><?php echo htmlspecialchars($currentUser['role']); ?></span>
  </div>
        <div class="w-8 h-8 rounded-full bg-lime-400/10 border border-lime-400/20 flex items-center justify-center">
            <span class="text-xs font-semibold text-lime-400"><?php echo strtoupper(substr($currentUser['nama'], 0, 2)); ?></span>
        </div>
        <a href="logout.php" class="p-2 rounded-lg hover:bg-red-400/10 transition-colors group" title="Logout">
            <span class="iconify text-neutral-500 group-hover:text-red-400 text-lg transition-colors" data-icon="lucide:log-out"></span>
        </a>
        </div>
      </div>
    </div>
  </header>

  <div class="p-4 lg:p-6">

    <!-- ===== DASHBOARD ===== -->
    <section id="page-dashboard" class="page-section">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card glass-card glass-card-hover rounded-xl p-5 transition-all duration-300 cursor-default animate-fadeInUp opacity-0" style="--accent-color:#A3E635">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-lime-400/10 flex items-center justify-center"><span class="iconify text-lime-400 text-xl" data-icon="lucide:package"></span></div>
            <span class="badge badge-success"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Aktif</span>
          </div>
          <p class="text-2xl font-heading font-semibold text-neutral-100" id="statTotal">0</p>
          <p class="text-xs text-neutral-500 mt-1">Total Barang</p>
        </div>
        <div class="stat-card glass-card glass-card-hover rounded-xl p-5 transition-all duration-300 cursor-default animate-fadeInUp opacity-0 delay-100" style="--accent-color:#4ade80">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-green-400/10 flex items-center justify-center"><span class="iconify text-green-400 text-xl" data-icon="lucide:check-circle"></span></div>
            <span class="badge badge-success">Tersedia</span>
          </div>
          <p class="text-2xl font-heading font-semibold text-neutral-100" id="statTersedia">0</p>
          <p class="text-xs text-neutral-500 mt-1">Barang Tersedia</p>
        </div>
        <div class="stat-card glass-card glass-card-hover rounded-xl p-5 transition-all duration-300 cursor-default animate-fadeInUp opacity-0 delay-200" style="--accent-color:#fbbf24">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-yellow-400/10 flex items-center justify-center"><span class="iconify text-yellow-400 text-xl" data-icon="lucide:arrow-up-right"></span></div>
            <span class="badge badge-warning">Dipinjam</span>
          </div>
          <p class="text-2xl font-heading font-semibold text-neutral-100" id="statDipinjam">0</p>
          <p class="text-xs text-neutral-500 mt-1">Sedang Dipinjam</p>
        </div>
        <div class="stat-card glass-card glass-card-hover rounded-xl p-5 transition-all duration-300 cursor-default animate-fadeInUp opacity-0 delay-300" style="--accent-color:#f87171">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-red-400/10 flex items-center justify-center"><span class="iconify text-red-400 text-xl" data-icon="lucide:alert-triangle"></span></div>
            <span class="badge badge-danger">Rusak</span>
          </div>
          <p class="text-2xl font-heading font-semibold text-neutral-100" id="statRusak">0</p>
          <p class="text-xs text-neutral-500 mt-1">Barang Rusak</p>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 glass-card rounded-xl overflow-hidden animate-fadeInUp opacity-0 delay-400">
          <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
            <h3 class="text-sm font-heading font-semibold text-neutral-200">Peminjaman Terbaru</h3>
            <button onclick="showPage('peminjaman')" class="text-xs text-lime-400 hover:text-lime-300 transition-colors">Lihat Semua →</button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead><tr class="text-left text-xs text-neutral-500 uppercase tracking-wider border-b border-white/5">
                <th class="px-5 py-3">Peminjam</th><th class="px-5 py-3">Barang</th><th class="px-5 py-3">Tgl Pinjam</th><th class="px-5 py-3">Status</th>
              </tr></thead>
              <tbody id="dashRecentBorrow"></tbody>
            </table>
          </div>
          <div id="dashRecentEmpty" class="hidden px-5 py-10 text-center">
            <span class="iconify text-3xl text-neutral-700 mx-auto mb-2" data-icon="lucide:inbox"></span>
            <p class="text-xs text-neutral-600">Belum ada data peminjaman</p>
          </div>
        </div>
        <div class="space-y-4 animate-fadeInUp opacity-0 delay-500">
          <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-heading font-semibold text-neutral-200 mb-4">Ringkasan Kategori</h3>
            <div id="dashKategoriStats" class="space-y-3"></div>
          </div>
          <div class="glass-card rounded-xl p-5">
            <h3 class="text-sm font-heading font-semibold text-neutral-200 mb-3">Aksi Cepat</h3>
            <div class="space-y-2">
              <button onclick="showPage('peminjaman')" class="w-full btn-primary text-xs py-2.5 rounded-lg flex items-center justify-center gap-2"><span class="iconify" data-icon="lucide:plus"></span> Tambah Peminjaman</button>
              <button onclick="showPage('pengembalian')" class="w-full btn-secondary text-xs py-2.5 rounded-lg flex items-center justify-center gap-2"><span class="iconify" data-icon="lucide:undo-2"></span> Proses Pengembalian</button>
              <button onclick="showPage('laporan')" class="w-full btn-secondary text-xs py-2.5 rounded-lg flex items-center justify-center gap-2"><span class="iconify" data-icon="lucide:file-text"></span> Lihat Laporan</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== DATA BARANG ===== -->
    <section id="page-barang" class="page-section hidden">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
        <div><h3 class="text-lg font-heading font-semibold text-neutral-100">Data Barang</h3><p class="text-xs text-neutral-500 mt-0.5">Kelola seluruh inventaris barang UKM</p></div>
             <button onclick="openModal('modalBarang')" class="add-button btn-primary text-xs px-4 py-2.5 rounded-lg flex items-center gap-2">
          <span class="iconify" data-icon="lucide:plus"></span> Tambah Barang</button>
      </div>
      <div class="glass-card rounded-xl p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500" data-icon="lucide:search"></span>
            <input type="text" id="searchBarang" placeholder="Cari nama atau kode barang..." class="input-field w-full pl-9 pr-4 py-2.5 rounded-lg text-sm" oninput="renderBarang()">
          </div>
          <select id="filterKategoriBarang" class="input-field px-4 py-2.5 rounded-lg text-sm min-w-[160px]" onchange="renderBarang()"><option value="">Semua Kategori</option></select>
        </div>
      </div>
      <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead><tr class="text-left text-xs text-neutral-500 uppercase tracking-wider border-b border-white/5">
              <th class="px-5 py-3">Kode</th><th class="px-5 py-3">Nama Barang</th><th class="px-5 py-3">Kategori</th><th class="px-5 py-3">Stok</th><th class="px-5 py-3">Tersedia</th><th class="px-5 py-3">Kondisi</th><th class="px-5 py-3">Lokasi</th><th class="px-5 py-3 text-center">Aksi</th>
            </tr></thead>
            <tbody id="tbodyBarang"></tbody>
          </table>
        </div>
        <div id="emptyBarang" class="hidden px-5 py-12 text-center">
          <span class="iconify text-4xl text-neutral-700 mx-auto mb-3" data-icon="lucide:package-open"></span>
          <p class="text-sm text-neutral-500">Belum ada data barang</p><p class="text-xs text-neutral-600 mt-1">Klik "Tambah Barang" untuk menambahkan</p>
        </div>
      </div>
    </section>

    <!-- ===== KATEGORI ===== -->
    <section id="page-kategori" class="page-section hidden">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
        <div><h3 class="text-lg font-heading font-semibold text-neutral-100">Kategori Barang</h3><p class="text-xs text-neutral-500 mt-0.5">Kelompokkan barang berdasarkan jenis</p></div>
             <button onclick="openModal('modalKategori')" class="add-button btn-primary text-xs px-4 py-2.5 rounded-lg flex items-center gap-2">
          <span class="iconify" data-icon="lucide:plus"></span> Tambah Kategori</button>
      </div>
      <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead><tr class="text-left text-xs text-neutral-500 uppercase tracking-wider border-b border-white/5">
              <th class="px-5 py-3">ID</th><th class="px-5 py-3">Nama Kategori</th><th class="px-5 py-3">Keterangan</th><th class="px-5 py-3">Jumlah Barang</th><th class="px-5 py-3 text-center">Aksi</th>
            </tr></thead>
            <tbody id="tbodyKategori"></tbody>
          </table>
        </div>
        <div id="emptyKategori" class="hidden px-5 py-12 text-center">
          <span class="iconify text-4xl text-neutral-700 mx-auto mb-3" data-icon="lucide:tags"></span>
          <p class="text-sm text-neutral-500">Belum ada kategori</p>
        </div>
      </div>
    </section>

    <!-- ===== DATA ANGGOTA ===== -->
    <section id="page-anggota" class="page-section hidden">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
        <div><h3 class="text-lg font-heading font-semibold text-neutral-100">Data Anggota</h3><p class="text-xs text-neutral-500 mt-0.5">Daftar anggota UKM yang berhak meminjam</p></div>
        <button onclick="openModal('modalAnggota')" class="add-button btn-primary text-xs px-4 py-2.5 rounded-lg flex items-center gap-2">
          <span class="iconify" data-icon="lucide:plus"></span> Tambah Anggota</button>
      </div>
      <div class="glass-card rounded-xl p-4 mb-4">
        <div class="relative">
          <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500" data-icon="lucide:search"></span>
          <input type="text" id="searchAnggota" placeholder="Cari nama atau NIM anggota..." class="input-field w-full pl-9 pr-4 py-2.5 rounded-lg text-sm" oninput="renderAnggota()">
        </div>
      </div>
      <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead><tr class="text-left text-xs text-neutral-500 uppercase tracking-wider border-b border-white/5">
              <th class="px-5 py-3">NIM</th><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Prodi</th><th class="px-5 py-3">No. HP</th><th class="px-5 py-3 text-center">Aksi</th>
            </tr></thead>
            <tbody id="tbodyAnggota"></tbody>
          </table>
        </div>
        <div id="emptyAnggota" class="hidden px-5 py-12 text-center">
          <span class="iconify text-4xl text-neutral-700 mx-auto mb-3" data-icon="lucide:users"></span>
          <p class="text-sm text-neutral-500">Belum ada data anggota</p>
        </div>
      </div>
    </section>

    <!-- ===== PEMINJAMAN ===== -->
    <section id="page-peminjaman" class="page-section hidden">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
        <div><h3 class="text-lg font-heading font-semibold text-neutral-100">Peminjaman Barang</h3><p class="text-xs text-neutral-500 mt-0.5">Catat transaksi peminjaman barang UKM</p></div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-1">
          <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-heading font-semibold text-neutral-200 mb-4 flex items-center gap-2"><span class="iconify text-lime-400" data-icon="lucide:file-plus"></span> Form Peminjaman</h4>
            <form id="formPeminjaman" onsubmit="return submitPeminjaman(event)" class="space-y-3">
              <div><label class="text-xs text-neutral-400 mb-1 block">Anggota Peminjam *</label><select name="id_anggota" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm"><option value="">Pilih anggota...</option></select></div>
              <div><label class="text-xs text-neutral-400 mb-1 block">Barang *</label><select name="id_barang" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" onchange="updateStokInfo()"><option value="">Pilih barang...</option></select><p id="stokInfo" class="text-[10px] text-neutral-600 mt-1"></p></div>
              <div><label class="text-xs text-neutral-400 mb-1 block">Jumlah *</label><input type="number" name="jumlah" min="1" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="Jumlah barang"></div>
              <div><label class="text-xs text-neutral-400 mb-1 block">Tanggal Pinjam *</label><input type="date" name="tgl_pinjam" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm"></div>
              <div><label class="text-xs text-neutral-400 mb-1 block">Rencana Kembali *</label><input type="date" name="tgl_kembali" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm"></div>
              <div><label class="text-xs text-neutral-400 mb-1 block">Catatan</label><textarea name="catatan" rows="2" class="input-field w-full px-3 py-2.5 rounded-lg text-sm resize-none" placeholder="Catatan tambahan..."></textarea></div>
              <button type="submit" class="btn-primary w-full py-2.5 rounded-lg text-xs flex items-center justify-center gap-2"><span class="iconify" data-icon="lucide:save"></span> Simpan Peminjaman</button>
            </form>
          </div>
        </div>
        <div class="lg:col-span-2">
          <div class="glass-card rounded-xl p-4 mb-4">
            <div class="relative"><span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500" data-icon="lucide:search"></span><input type="text" id="searchPeminjaman" placeholder="Cari peminjaman..." class="input-field w-full pl-9 pr-4 py-2.5 rounded-lg text-sm" oninput="renderPeminjaman()"></div>
          </div>
          <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-neutral-500 uppercase tracking-wider border-b border-white/5">
                  <th class="px-4 py-3">ID</th><th class="px-4 py-3">Peminjam</th><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Jml</th><th class="px-4 py-3">Tgl Pinjam</th><th class="px-4 py-3">Status</th>
                </tr></thead>
                <tbody id="tbodyPeminjaman"></tbody>
              </table>
            </div>
            <div id="emptyPeminjaman" class="hidden px-5 py-12 text-center">
              <span class="iconify text-4xl text-neutral-700 mx-auto mb-3" data-icon="lucide:log-out"></span>
              <p class="text-sm text-neutral-500">Belum ada data peminjaman</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== PENGEMBALIAN ===== -->
    <section id="page-pengembalian" class="page-section hidden">
      <div class="mb-5"><h3 class="text-lg font-heading font-semibold text-neutral-100">Pengembalian Barang</h3><p class="text-xs text-neutral-500 mt-0.5">Proses pengembalian barang yang dipinjam</p></div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-1">
          <div class="glass-card rounded-xl p-5">
            <h4 class="text-sm font-heading font-semibold text-neutral-200 mb-4 flex items-center gap-2"><span class="iconify text-lime-400" data-icon="lucide:undo-2"></span> Form Pengembalian</h4>
            <form id="formPengembalian" onsubmit="return submitPengembalian(event)" class="space-y-3">
              <div><label class="text-xs text-neutral-400 mb-1 block">Pilih Peminjaman *</label><select name="id_peminjaman" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" onchange="showPengembalianDetail()"><option value="">Pilih transaksi...</option></select></div>
              <div id="pengembalianDetail" class="hidden space-y-2 p-3 rounded-lg bg-white/[0.02] border border-white/5">
                <p class="text-xs text-neutral-400">Detail Peminjaman:</p>
                <p id="detailPengembalianText" class="text-xs text-neutral-300"></p>
              </div>
              <div><label class="text-xs text-neutral-400 mb-1 block">Tanggal Kembali *</label><input type="date" name="tgl_kembali" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm"></div>
              <div><label class="text-xs text-neutral-400 mb-1 block">Kondisi Barang Saat Dikembalikan *</label>
                <select name="kondisi" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm">
                  <option value="">Pilih kondisi...</option><option value="Baik">Baik</option><option value="Rusak Ringan">Rusak Ringan</option><option value="Rusak Berat">Rusak Berat</option><option value="Hilang">Hilang</option>
                </select>
              </div>
              <div><label class="text-xs text-neutral-400 mb-1 block">Catatan</label><textarea name="catatan" rows="2" class="input-field w-full px-3 py-2.5 rounded-lg text-sm resize-none" placeholder="Catatan pengembalian..."></textarea></div>
              <button type="submit" class="btn-primary w-full py-2.5 rounded-lg text-xs flex items-center justify-center gap-2"><span class="iconify" data-icon="lucide:check-circle"></span> Proses Pengembalian</button>
            </form>
          </div>
        </div>
        <div class="lg:col-span-2">
          <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/5"><h4 class="text-sm font-heading font-semibold text-neutral-200 flex items-center gap-2"><span class="iconify text-yellow-400" data-icon="lucide:clock"></span> Daftar Barang Sedang Dipinjam</h4></div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-neutral-500 uppercase tracking-wider border-b border-white/5">
                  <th class="px-4 py-3">Peminjam</th><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Jml</th><th class="px-4 py-3">Tgl Pinjam</th><th class="px-4 py-3">Batas Kembali</th><th class="px-4 py-3">Status</th>
                </tr></thead>
                <tbody id="tbodyActiveBorrow"></tbody>
              </table>
            </div>
            <div id="emptyActiveBorrow" class="hidden px-5 py-12 text-center">
              <span class="iconify text-4xl text-neutral-700 mx-auto mb-3" data-icon="lucide:check-check"></span>
              <p class="text-sm text-neutral-500">Tidak ada barang yang sedang dipinjam</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== LAPORAN ===== -->
    <section id="page-laporan" class="page-section hidden">
      <div class="mb-5"><h3 class="text-lg font-heading font-semibold text-neutral-100">Laporan Inventaris</h3><p class="text-xs text-neutral-500 mt-0.5">Ringkasan data barang, peminjaman, dan pengembalian</p></div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="glass-card rounded-xl p-5 text-center"><p class="text-3xl font-heading font-semibold text-lime-400" id="lapTotalPinjam">0</p><p class="text-xs text-neutral-500 mt-1">Total Transaksi Peminjaman</p></div>
        <div class="glass-card rounded-xl p-5 text-center"><p class="text-3xl font-heading font-semibold text-green-400" id="lapTotalKembali">0</p><p class="text-xs text-neutral-500 mt-1">Total Pengembalian</p></div>
        <div class="glass-card rounded-xl p-5 text-center"><p class="text-3xl font-heading font-semibold text-yellow-400" id="lapTotalAktif">0</p><p class="text-xs text-neutral-500 mt-1">Peminjaman Aktif</p></div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="glass-card rounded-xl overflow-hidden">
          <div class="px-5 py-4 border-b border-white/5"><h4 class="text-sm font-heading font-semibold text-neutral-200">Stok per Kategori</h4></div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead><tr class="text-left text-xs text-neutral-500 uppercase tracking-wider border-b border-white/5">
                <th class="px-5 py-3">Kategori</th><th class="px-5 py-3 text-center">Jenis</th><th class="px-5 py-3 text-center">Total</th><th class="px-5 py-3 text-center">Tersedia</th>
              </tr></thead>
              <tbody id="tbodyLapKategori"></tbody>
            </table>
          </div>
        </div>
        <div class="glass-card rounded-xl overflow-hidden">
          <div class="px-5 py-4 border-b border-white/5"><h4 class="text-sm font-heading font-semibold text-neutral-200">Riwayat Peminjaman & Pengembalian</h4></div>
          <div class="overflow-x-auto max-h-[400px] overflow-y-auto scrollbar-thin">
            <table class="w-full text-sm">
              <thead class="sticky top-0 bg-neutral-900/95 backdrop-blur"><tr class="text-left text-xs text-neutral-500 uppercase tracking-wider border-b border-white/5">
                <th class="px-4 py-3">Peminjam</th><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Status</th>
              </tr></thead>
              <tbody id="tbodyLapRiwayat"></tbody>
            </table>
          </div>
          <div id="emptyLapRiwayat" class="hidden px-5 py-10 text-center"><p class="text-xs text-neutral-600">Belum ada riwayat</p></div>
        </div>
      </div>
    </section>


    <!-- ===== KELOLA USER ===== -->
    <section id="page-users" class="page-section hidden">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
        <div>
          <h3 class="text-lg font-semibold text-neutral-100">Kelola User</h3>
          <p class="text-xs text-neutral-400 mt-0.5">Tambah, ubah, atau hapus akun pengguna sistem</p>
        </div>
        <button onclick="openModal('modalUser')" class="btn-primary text-xs px-4 py-2.5 rounded-lg flex items-center gap-2">
          <span class="iconify" data-icon="lucide:user-plus"></span> Tambah User
        </button>
      </div>
      <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-xs uppercase tracking-wider border-b border-white/6">
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">Nama</th>
                <th class="px-5 py-3">Username</th>
                <th class="px-5 py-3">Role</th>
                <th class="px-5 py-3">Dibuat</th>
                <th class="px-5 py-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="tbodyUsers"></tbody>
          </table>
        </div>
        <div id="emptyUsers" class="hidden px-5 py-12 text-center">
          <span class="iconify text-4xl text-neutral-700 mx-auto mb-3" data-icon="lucide:users"></span>
          <p class="text-sm text-neutral-400">Belum ada user</p>
        </div>
      </div>
    </section>


  </div>
</main>

<!-- ===== MODAL BARANG ===== -->
<div id="modalBarang" class="fixed inset-0 z-[80] hidden items-center justify-center p-4">
  <div class="modal-overlay absolute inset-0" onclick="closeModal('modalBarang')"></div>
  <div class="modal-content relative glass-card rounded-2xl w-full max-w-lg p-6 border border-white/10">
    <div class="flex items-center justify-between mb-5">
      <h3 id="modalBarangTitle" class="text-base font-heading font-semibold text-neutral-100">Tambah Barang</h3>
      <button onclick="closeModal('modalBarang')" class="p-1 rounded-lg hover:bg-white/5 transition-colors"><span class="iconify text-neutral-400" data-icon="lucide:x"></span></button>
    </div>
    <form id="formBarang" onsubmit="return submitBarang(event)" class="space-y-3">
      <input type="hidden" name="id_barang" value="">
      <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2"><label class="text-xs text-neutral-400 mb-1 block">Nama Barang *</label><input type="text" name="nama_barang" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="Nama barang"></div>
        <div><label class="text-xs text-neutral-400 mb-1 block">Kode Barang *</label><input type="text" name="kode_barang" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="BRG-001"></div>
        <div><label class="text-xs text-neutral-400 mb-1 block">Kategori *</label><select name="id_kategori" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm"><option value="">Pilih...</option></select></div>
        <div><label class="text-xs text-neutral-400 mb-1 block">Stok Total *</label><input type="number" name="stok_total" min="0" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="0"></div>
        <div><label class="text-xs text-neutral-400 mb-1 block">Stok Tersedia *</label><input type="number" name="stok_tersedia" min="0" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="0"></div>
        <div><label class="text-xs text-neutral-400 mb-1 block">Kondisi *</label><select name="kondisi" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm"><option value="Baik">Baik</option><option value="Rusak Ringan">Rusak Ringan</option><option value="Rusak Berat">Rusak Berat</option></select></div>
        <div><label class="text-xs text-neutral-400 mb-1 block">Lokasi *</label><input type="text" name="lokasi" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="Ruang sekretariat"></div>
        <div class="col-span-2"><label class="text-xs text-neutral-400 mb-1 block">Keterangan</label><textarea name="keterangan" rows="2" class="input-field w-full px-3 py-2.5 rounded-lg text-sm resize-none" placeholder="Catatan tambahan..."></textarea></div>
      </div>
      <div class="flex gap-2 pt-2">
        <button type="button" onclick="closeModal('modalBarang')" class="btn-secondary flex-1 py-2.5 rounded-lg text-xs">Batal</button>
        <button type="submit" class="btn-primary flex-1 py-2.5 rounded-lg text-xs">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODAL KATEGORI ===== -->
<div id="modalKategori" class="fixed inset-0 z-[80] hidden items-center justify-center p-4">
  <div class="modal-overlay absolute inset-0" onclick="closeModal('modalKategori')"></div>
  <div class="modal-content relative glass-card rounded-2xl w-full max-w-md p-6 border border-white/10">
    <div class="flex items-center justify-between mb-5">
      <h3 id="modalKategoriTitle" class="text-base font-heading font-semibold text-neutral-100">Tambah Kategori</h3>
      <button onclick="closeModal('modalKategori')" class="p-1 rounded-lg hover:bg-white/5 transition-colors"><span class="iconify text-neutral-400" data-icon="lucide:x"></span></button>
    </div>
    <form id="formKategori" onsubmit="return submitKategori(event)" class="space-y-3">
      <input type="hidden" name="id_kategori" value="">
      <div><label class="text-xs text-neutral-400 mb-1 block">Nama Kategori *</label><input type="text" name="nama_kategori" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="Nama kategori"></div>
      <div><label class="text-xs text-neutral-400 mb-1 block">Keterangan</label><textarea name="keterangan" rows="2" class="input-field w-full px-3 py-2.5 rounded-lg text-sm resize-none" placeholder="Keterangan kategori..."></textarea></div>
      <div class="flex gap-2 pt-2">
        <button type="button" onclick="closeModal('modalKategori')" class="btn-secondary flex-1 py-2.5 rounded-lg text-xs">Batal</button>
        <button type="submit" class="btn-primary flex-1 py-2.5 rounded-lg text-xs">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODAL ANGGOTA ===== -->
<div id="modalAnggota" class="fixed inset-0 z-[80] hidden items-center justify-center p-4">
  <div class="modal-overlay absolute inset-0" onclick="closeModal('modalAnggota')"></div>
  <div class="modal-content relative glass-card rounded-2xl w-full max-w-md p-6 border border-white/10">
    <div class="flex items-center justify-between mb-5">
      <h3 id="modalAnggotaTitle" class="text-base font-heading font-semibold text-neutral-100">Tambah Anggota</h3>
      <button onclick="closeModal('modalAnggota')" class="p-1 rounded-lg hover:bg-white/5 transition-colors"><span class="iconify text-neutral-400" data-icon="lucide:x"></span></button>
    </div>
    <form id="formAnggota" onsubmit="return submitAnggota(event)" class="space-y-3">
      <input type="hidden" name="id_anggota" value="">
      <div><label class="text-xs text-neutral-400 mb-1 block">NIM *</label><input type="text" name="nim" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="202401xxxx"></div>
      <div><label class="text-xs text-neutral-400 mb-1 block">Nama Lengkap *</label><input type="text" name="nama" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="Nama lengkap"></div>
      <div><label class="text-xs text-neutral-400 mb-1 block">Prodi *</label><input type="text" name="prodi" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="Teknik Informatika"></div>
      <div><label class="text-xs text-neutral-400 mb-1 block">No. HP</label><input type="text" name="no_hp" class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="08xxxxxxxxxx"></div>
      <div class="flex gap-2 pt-2">
        <button type="button" onclick="closeModal('modalAnggota')" class="btn-secondary flex-1 py-2.5 rounded-lg text-xs">Batal</button>
        <button type="submit" class="btn-primary flex-1 py-2.5 rounded-lg text-xs">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODAL HAPUS ===== -->
<div id="modalHapus" class="fixed inset-0 z-[90] hidden items-center justify-center p-4">
  <div class="modal-overlay absolute inset-0" onclick="closeModal('modalHapus')"></div>
  <div class="modal-content relative glass-card rounded-2xl w-full max-w-sm p-6 border border-red-500/20 text-center">
    <div class="w-14 h-14 rounded-full bg-red-400/10 flex items-center justify-center mx-auto mb-4"><span class="iconify text-red-400 text-2xl" data-icon="lucide:trash-2"></span></div>
    <h3 class="text-base font-heading font-semibold text-neutral-100 mb-2">Konfirmasi Hapus</h3>
    <p id="hapusMessage" class="text-sm text-neutral-400 mb-5">Apakah Anda yakin ingin menghapus data ini?</p>
    <div class="flex gap-2">
      <button onclick="closeModal('modalHapus')" class="btn-secondary flex-1 py-2.5 rounded-lg text-xs">Batal</button>
      <button id="btnConfirmHapus" class="btn-danger flex-1 py-2.5 rounded-lg text-xs">Hapus</button>
    </div>
  </div>
</div>


<!-- ===== MODAL USER ===== -->
<div id="modalUser" class="fixed inset-0 z-[80] hidden items-center justify-center p-4">
  <div class="modal-overlay absolute inset-0" onclick="closeModal('modalUser')"></div>
  <div class="modal-content relative glass-card rounded-2xl w-full max-w-md p-6 border border-white/10">
    <div class="flex items-center justify-between mb-5">
      <h3 id="modalUserTitle" class="text-base font-semibold text-neutral-100">Tambah User</h3>
      <button onclick="closeModal('modalUser')" class="p-1 rounded-lg hover:bg-white/5 transition-colors">
        <span class="iconify text-neutral-400" data-icon="lucide:x"></span>
      </button>
    </div>
    <form id="formUser" onsubmit="return submitUser(event)" class="space-y-3">
      <input type="hidden" name="id_user" value="">
      <div>
        <label class="text-xs mb-1 block" style="color:#d4d4d4">Nama Lengkap *</label>
        <input type="text" name="nama" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="Nama lengkap">
      </div>
      <div>
        <label class="text-xs mb-1 block" style="color:#d4d4d4">Username *</label>
        <input type="text" name="username" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="Username untuk login">
      </div>
      <div>
        <label class="text-xs mb-1 block" style="color:#d4d4d4">Password *<span id="pwdHint" class="text-neutral-500 font-normal"> (wajib diisi)</span></label>
        <input type="password" name="password" id="inputPassword" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm" placeholder="Password">
      </div>
      <div>
        <label class="text-xs mb-1 block" style="color:#d4d4d4">Role *</label>
        <select name="role" required class="input-field w-full px-3 py-2.5 rounded-lg text-sm">
          <option value="admin">Admin</option>
          <option value="anggota">Anggota</option>
          <option value="ketua">Ketua</option>
        </select>
      </div>
      <div class="flex gap-2 pt-2">
        <button type="button" onclick="closeModal('modalUser')" class="btn-secondary flex-1 py-2.5 rounded-lg text-xs">Batal</button>
        <button type="submit" class="btn-primary flex-1 py-2.5 rounded-lg text-xs">Simpan</button>
      </div>
    </form>
  </div>
</div>


<script>var userRole = '<?php echo $currentUser['role']; ?>';</script>
<!-- JavaScript dipisah ke file terpisah -->
<script src="assets/js/app.js"></script>

</body>
</html>