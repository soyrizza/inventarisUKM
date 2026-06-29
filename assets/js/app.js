// ============================================================
// DATA LAYER
// ============================================================
async function parseJSON(res) {
    const text = await res.text();
    try {
        return JSON.parse(text);
    } catch (e) {
        console.error('Invalid JSON dari server:', text.substring(0, 300));
        return null;
    }
}

async function apiGet(endpoint, params) {
    params = params || {};
    try {
        const query = new URLSearchParams(params).toString();
        const url = 'api/' + endpoint + '.php' + (query ? '?' + query : '');
        const res = await fetch(url);
        const json = await parseJSON(res);
        if (!json) return null;
        if (json.success) return json.data;
        console.error('API Error [' + endpoint + ']:', json.message);
        return null;
    } catch (e) {
        console.error('Network error [' + endpoint + ']:', e.message);
        return null;
    }
}

async function apiPost(endpoint, data) {
    try {
        const res = await fetch('api/' + endpoint + '.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await parseJSON(res);
        if (!json) return { success: false, message: 'Server tidak merespons dengan benar' };
        return json;
    } catch (e) {
        console.error('Network error [POST ' + endpoint + ']:', e.message);
        return { success: false, message: 'Gagal terhubung ke server' };
    }
}

async function apiPut(endpoint, data) {
    try {
        const res = await fetch('api/' + endpoint + '.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await parseJSON(res);
        if (!json) return { success: false, message: 'Server tidak merespons dengan benar' };
        return json;
    } catch (e) {
        console.error('Network error [PUT ' + endpoint + ']:', e.message);
        return { success: false, message: 'Gagal terhubung ke server' };
    }
}

async function apiDelete(endpoint, id) {
    try {
        const res = await fetch('api/' + endpoint + '.php?id=' + id, { method: 'DELETE' });
        const json = await parseJSON(res);
        if (!json) return { success: false, message: 'Server tidak merespons dengan benar' };
        return json;
    } catch (e) {
        console.error('Network error [DELETE ' + endpoint + ']:', e.message);
        return { success: false, message: 'Gagal terhubung ke server' };
    }
}

// ============================================================
// TOAST
// ============================================================
function showToast(message, type) {
    type = type || 'success';
    var container = document.getElementById('toastContainer');
    var toast = document.createElement('div');
    var icons = { success: 'lucide:check-circle', error: 'lucide:x-circle', warning: 'lucide:alert-triangle', info: 'lucide:info' };
    var colors = { success: 'text-green-400 border-green-400/20', error: 'text-red-400 border-red-400/20', warning: 'text-yellow-400 border-yellow-400/20', info: 'text-blue-400 border-blue-400/20' };
    toast.className = 'toast pointer-events-auto glass-card rounded-xl px-4 py-3 flex items-center gap-3 min-w-[280px] max-w-sm ' + (colors[type] || colors.info);
    toast.innerHTML = '<span class="iconify text-lg flex-shrink-0" data-icon="' + (icons[type] || icons.info) + '"></span><span class="text-sm text-neutral-200 flex-1">' + message + '</span>';
    container.appendChild(toast);
    setTimeout(function() { toast.classList.add('removing'); setTimeout(function() { toast.remove(); }, 300); }, 3000);
}

// ============================================================
// MODAL
// ============================================================
function openModal(id) {
    var m = document.getElementById(id);
    m.classList.remove('hidden');
    m.classList.add('flex');
    document.body.style.overflow = 'hidden';
    if (id === 'modalBarang') populateKategoriDropdown();
}

function closeModal(id) {
    var m = document.getElementById(id);
    m.classList.add('hidden');
    m.classList.remove('flex');
    document.body.style.overflow = '';
    if (id === 'modalBarang') {
        document.getElementById('formBarang').reset();
        document.getElementById('modalBarangTitle').textContent = 'Tambah Barang';
    }
    if (id === 'modalKategori') {
        document.getElementById('formKategori').reset();
        document.getElementById('modalKategoriTitle').textContent = 'Tambah Kategori';
    }
    if (id === 'modalAnggota') {
        document.getElementById('formAnggota').reset();
        document.getElementById('modalAnggotaTitle').textContent = 'Tambah Anggota';
    }
    if (id === 'modalUser') {
        document.getElementById('formUser').reset();
        document.getElementById('modalUserTitle').textContent = 'Tambah User';
        document.getElementById('inputPassword').required = true;
        document.getElementById('pwdHint').textContent = ' (wajib diisi)';
    }
}

// ============================================================
// SIDEBAR & NAVIGATION
// ============================================================
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

var pageTitles = {
    dashboard: 'Dashboard',
    barang: 'Data Barang',
    kategori: 'Kategori Barang',
    anggota: 'Data Anggota',
    peminjaman: 'Peminjaman Barang',
    pengembalian: 'Pengembalian Barang',
    laporan: 'Laporan Inventaris',
    users: 'Kelola User'
};

function showPage(page) {
    var forbidden = [];
    if (userRole === 'anggota') forbidden = ['kategori', 'anggota', 'users'];
    if (userRole === 'ketua') forbidden = ['users'];
    if (forbidden.indexOf(page) !== -1) {
        showToast('Anda tidak memiliki akses ke halaman ini', 'warning');
        return;
    }
    
    var sections = document.querySelectorAll('.page-section');
    for (var i = 0; i < sections.length; i++) {
        sections[i].classList.add('hidden');
    }
    var target = document.getElementById('page-' + page);
    if (target) {
        target.classList.remove('hidden');
        var anims = target.querySelectorAll('.animate-fadeInUp, .animate-slideInLeft');
        for (var j = 0; j < anims.length; j++) {
            anims[j].style.animation = 'none';
            anims[j].offsetHeight;
            anims[j].style.animation = '';
        }
    }
    var links = document.querySelectorAll('.sidebar-link');
    for (var k = 0; k < links.length; k++) {
        links[k].classList.remove('active');
        links[k].classList.add('text-neutral-400');
    }
    var activeLink = document.querySelector('.sidebar-link[data-page="' + page + '"]');
    if (activeLink) {
        activeLink.classList.add('active');
        activeLink.classList.remove('text-neutral-400');
    }
    document.getElementById('pageTitle').textContent = pageTitles[page] || page;
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
    refreshPage(page);
}

function refreshPage(page) {
    switch (page) {
        case 'dashboard': renderDashboard(); break;
        case 'barang': renderBarang(); break;
        case 'kategori': renderKategori(); break;
        case 'anggota': renderAnggota(); break;
        case 'peminjaman': renderPeminjaman(); populatePeminjamanDropdowns(); break;
        case 'pengembalian': renderPengembalian(); populatePengembalianDropdown(); break;
        case 'laporan': renderLaporan(); break;
        case 'users': renderUsers(); break;
    }
}

// ============================================================
// HELPERS
// ============================================================
function kondisiBadge(k) {
    var map = { 'Baik': 'badge-success', 'Rusak Ringan': 'badge-warning', 'Rusak Berat': 'badge-danger' };
    return '<span class="badge ' + (map[k] || 'badge-neutral') + '">' + k + '</span>';
}

function statusBadge(s) {
    var map = { 'dipinjam': 'badge-warning', 'selesai': 'badge-success', 'ditolak': 'badge-danger', 'menunggu': 'badge-info' };
    var labels = { 'dipinjam': 'Dipinjam', 'selesai': 'Selesai', 'ditolak': 'Ditolak', 'menunggu': 'Menunggu' };
    return '<span class="badge ' + (map[s] || 'badge-neutral') + '">' + (labels[s] || s) + '</span>';
}

// ============================================================
// DASHBOARD
// ============================================================
async function renderDashboard() {
    var d = await apiGet('dashboard');
    if (!d) {
        document.getElementById('statTotal').textContent = '!';
        document.getElementById('statTersedia').textContent = '!';
        document.getElementById('statDipinjam').textContent = '!';
        document.getElementById('statRusak').textContent = '!';
        return;
    }
    animateCounter('statTotal', d.total || 0);
    animateCounter('statTersedia', d.tersedia || 0);
    animateCounter('statDipinjam', d.dipinjam || 0);
    animateCounter('statRusak', d.rusak || 0);

    var tbody = document.getElementById('dashRecentBorrow');
    var empty = document.getElementById('dashRecentEmpty');
    var recent = d.recent || [];
    if (recent.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');
        var html = '';
        for (var i = 0; i < recent.length; i++) {
            var p = recent[i];
            var bList = (p.barang_list || []).join(', ');
            html += '<tr class="table-row"><td class="px-5 py-3 text-neutral-300">' + p.nama_anggota + '</td><td class="px-5 py-3 text-neutral-400 max-w-[150px] truncate">' + (bList || '-') + '</td><td class="px-5 py-3 text-neutral-500">' + p.tanggal_pinjam + '</td><td class="px-5 py-3">' + statusBadge(p.status) + '</td></tr>';
        }
        tbody.innerHTML = html;
    }

    var kStats = document.getElementById('dashKategoriStats');
    var kategori = d.kategori || [];
    var totalBarang = d.total_barang || 1;
    var kHtml = '';
    for (var j = 0; j < kategori.length; j++) {
        var k = kategori[j];
        var pct = Math.round((k.jumlah / totalBarang) * 100);
        kHtml += '<div><div class="flex justify-between text-xs mb-1"><span class="text-neutral-400">' + k.nama_kategori + '</span><span class="text-neutral-500">' + k.jumlah + ' item</span></div><div class="h-1.5 bg-white/5 rounded-full overflow-hidden"><div class="h-full bg-lime-400/60 rounded-full transition-all duration-700" style="width:' + pct + '%"></div></div></div>';
    }
    kStats.innerHTML = kHtml;
}

function animateCounter(id, target) {
    var el = document.getElementById(id);
    if (!el) return;
    target = Math.max(0, target);
    if (target === 0) { el.textContent = '0'; return; }
    var current = 0;
    var step = Math.max(1, Math.ceil(target / 30));
    var interval = setInterval(function() {
        current += step;
        if (current >= target) { current = target; clearInterval(interval); }
        el.textContent = current;
    }, 25);
}

// ============================================================
// BARANG CRUD
// ============================================================
async function renderBarang() {
    var search = (document.getElementById('searchBarang') || {}).value || '';
    var filterKat = (document.getElementById('filterKategoriBarang') || {}).value || '';
    var kategori = await apiGet('kategori');
    if (!kategori) return;
    var filterEl = document.getElementById('filterKategoriBarang');
    var currentVal = filterEl.value;
    var optHtml = '<option value="">Semua Kategori</option>';
    for (var i = 0; i < kategori.length; i++) {
        var sel = kategori[i].id_kategori == currentVal ? ' selected' : '';
        optHtml += '<option value="' + kategori[i].id_kategori + '"' + sel + '>' + kategori[i].nama_kategori + '</option>';
    }
    filterEl.innerHTML = optHtml;

    var barang = await apiGet('barang', { search: search, id_kategori: filterKat });
    if (!barang) return;
    var tbody = document.getElementById('tbodyBarang');
    var empty = document.getElementById('emptyBarang');
    if (barang.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');
        var html = '';
        for (var j = 0; j < barang.length; j++) {
            var b = barang[j];
            var stokClass = b.stok_tersedia > 0 ? 'text-green-400' : 'text-red-400';
            var nameEsc = b.nama_barang.replace(/'/g, "\\'");
            html += '<tr class="table-row">' +
                '<td class="px-5 py-3 text-xs font-mono text-lime-400/80">' + b.kode_barang + '</td>' +
                '<td class="px-5 py-3 text-neutral-200 font-medium">' + b.nama_barang + '</td>' +
                '<td class="px-5 py-3 text-neutral-400">' + (b.nama_kategori || '-') + '</td>' +
                '<td class="px-5 py-3 text-neutral-300 text-center">' + b.stok_total + '</td>' +
                '<td class="px-5 py-3 text-center"><span class="' + stokClass + ' font-medium">' + b.stok_tersedia + '</span></td>' +
                '<td class="px-5 py-3 text-center">' + kondisiBadge(b.kondisi) + '</td>' +
                '<td class="px-5 py-3 text-neutral-500 text-xs">' + b.lokasi + '</td>' +
                '<td class="px-5 py-3 text-center"><div class="flex items-center justify-center gap-1 crud-buttons">' +
                '<button onclick="editBarang(' + b.id_barang + ')" class="btn-warning btn-sm" title="Edit"><span class="iconify text-xs" data-icon="lucide:pencil"></span></button>' +
                '<button onclick="confirmHapus(\'barang\',' + b.id_barang + ',\'' + nameEsc + '\')" class="btn-danger btn-sm" title="Hapus"><span class="iconify text-xs" data-icon="lucide:trash-2"></span></button>' +
                '</div></td></tr>';
        }
        tbody.innerHTML = html;
    }
}

async function populateKategoriDropdown() {
    var kategori = await apiGet('kategori');
    if (!kategori) return;
    var sel = document.querySelector('#formBarang select[name="id_kategori"]');
    var val = sel.value;
    var html = '<option value="">Pilih...</option>';
    for (var i = 0; i < kategori.length; i++) {
        var s = kategori[i].id_kategori == val ? ' selected' : '';
        html += '<option value="' + kategori[i].id_kategori + '"' + s + '>' + kategori[i].nama_kategori + '</option>';
    }
    sel.innerHTML = html;
}

async function submitBarang(e) {
    e.preventDefault();
    var f = e.target;
    var id = f.id_barang.value;
    var data = {
        id_kategori: parseInt(f.id_kategori.value),
        nama_barang: f.nama_barang.value.trim(),
        kode_barang: f.kode_barang.value.trim(),
        stok_total: parseInt(f.stok_total.value),
        stok_tersedia: parseInt(f.stok_tersedia.value),
        kondisi: f.kondisi.value,
        lokasi: f.lokasi.value.trim(),
        keterangan: f.keterangan.value.trim()
    };
    var result;
    if (id) {
        data.id_barang = parseInt(id);
        result = await apiPut('barang', data);
    } else {
        result = await apiPost('barang', data);
    }
    if (result.success) {
        showToast(result.message, 'success');
        closeModal('modalBarang');
        renderBarang();
    } else {
        showToast(result.message, 'error');
    }
    return false;
}

async function editBarang(id) {
    var barang = await apiGet('barang');
    if (!barang) return;
    var b = null;
    for (var i = 0; i < barang.length; i++) {
        if (barang[i].id_barang === id) { b = barang[i]; break; }
    }
    if (!b) return;
    await populateKategoriDropdown();
    var f = document.getElementById('formBarang');
    f.id_barang.value = b.id_barang;
    f.nama_barang.value = b.nama_barang;
    f.kode_barang.value = b.kode_barang;
    f.id_kategori.value = b.id_kategori;
    f.stok_total.value = b.stok_total;
    f.stok_tersedia.value = b.stok_tersedia;
    f.kondisi.value = b.kondisi;
    f.lokasi.value = b.lokasi;
    f.keterangan.value = b.keterangan || '';
    document.getElementById('modalBarangTitle').textContent = 'Edit Barang';
    openModal('modalBarang');
}

// ============================================================
// KATEGORI CRUD
// ============================================================
async function renderKategori() {
    var kategori = await apiGet('kategori');
    if (!kategori) return;
    var tbody = document.getElementById('tbodyKategori');
    var empty = document.getElementById('emptyKategori');
    if (kategori.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');
        var html = '';
        for (var i = 0; i < kategori.length; i++) {
            var k = kategori[i];
            var nameEsc = k.nama_kategori.replace(/'/g, "\\'");
            html += '<tr class="table-row">' +
                '<td class="px-5 py-3 text-xs font-mono text-neutral-500">KTG-' + String(k.id_kategori).padStart(3, '0') + '</td>' +
                '<td class="px-5 py-3 text-neutral-200 font-medium">' + k.nama_kategori + '</td>' +
                '<td class="px-5 py-3 text-neutral-500 text-xs">' + (k.keterangan || '-') + '</td>' +
                '<td class="px-5 py-3 text-center"><span class="badge badge-info">' + (k.jumlah_barang || 0) + ' barang</span></td>' +
                '<td class="px-5 py-3 text-center"><div class="flex items-center justify-center gap-1 crud-buttons">' +
                '<button onclick="editKategori(' + k.id_kategori + ')" class="btn-warning btn-sm" title="Edit"><span class="iconify text-xs" data-icon="lucide:pencil"></span></button>' +
                '<button onclick="confirmHapus(\'kategori\',' + k.id_kategori + ',\'' + nameEsc + '\')" class="btn-danger btn-sm" title="Hapus"><span class="iconify text-xs" data-icon="lucide:trash-2"></span></button>' +
                '</div></td></tr>';
        }
        tbody.innerHTML = html;
    }
}

async function submitKategori(e) {
    e.preventDefault();
    var f = e.target;
    var id = f.id_kategori.value;
    var data = { nama_kategori: f.nama_kategori.value.trim(), keterangan: f.keterangan.value.trim() };
    var result;
    if (id) {
        data.id_kategori = parseInt(id);
        result = await apiPut('kategori', data);
    } else {
        result = await apiPost('kategori', data);
    }
    if (result.success) {
        showToast(result.message, 'success');
        closeModal('modalKategori');
        renderKategori();
    } else {
        showToast(result.message, 'error');
    }
    return false;
}

async function editKategori(id) {
    var kategori = await apiGet('kategori');
    if (!kategori) return;
    var k = null;
    for (var i = 0; i < kategori.length; i++) {
        if (kategori[i].id_kategori === id) { k = kategori[i]; break; }
    }
    if (!k) return;
    var f = document.getElementById('formKategori');
    f.id_kategori.value = k.id_kategori;
    f.nama_kategori.value = k.nama_kategori;
    f.keterangan.value = k.keterangan || '';
    document.getElementById('modalKategoriTitle').textContent = 'Edit Kategori';
    openModal('modalKategori');
}

// ============================================================
// ANGGOTA CRUD
// ============================================================
async function renderAnggota() {
    var search = (document.getElementById('searchAnggota') || {}).value || '';
    var anggota = await apiGet('anggota', { search: search });
    if (!anggota) return;
    var tbody = document.getElementById('tbodyAnggota');
    var empty = document.getElementById('emptyAnggota');
    if (anggota.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');
        var html = '';
        for (var i = 0; i < anggota.length; i++) {
            var a = anggota[i];
            var nameEsc = a.nama.replace(/'/g, "\\'");
            html += '<tr class="table-row">' +
                '<td class="px-5 py-3 text-xs font-mono text-lime-400/80">' + a.nim + '</td>' +
                '<td class="px-5 py-3 text-neutral-200 font-medium">' + a.nama + '</td>' +
                '<td class="px-5 py-3 text-neutral-400 text-xs">' + a.prodi + '</td>' +
                '<td class="px-5 py-3 text-neutral-500 text-xs">' + (a.no_hp || '-') + '</td>' +
                '<td class="px-5 py-3 text-center"><div class="flex items-center justify-center gap-1 crud-buttons">' +
                '<button onclick="editAnggota(' + a.id_anggota + ')" class="btn-warning btn-sm" title="Edit"><span class="iconify text-xs" data-icon="lucide:pencil"></span></button>' +
                '<button onclick="confirmHapus(\'anggota\',' + a.id_anggota + ',\'' + nameEsc + '\')" class="btn-danger btn-sm" title="Hapus"><span class="iconify text-xs" data-icon="lucide:trash-2"></span></button>' +
                '</div></td></tr>';
        }
        tbody.innerHTML = html;
    }
}

async function submitAnggota(e) {
    e.preventDefault();
    var f = e.target;
    var id = f.id_anggota.value;
    var data = {
        nim: f.nim.value.trim(),
        nama: f.nama.value.trim(),
        prodi: f.prodi.value.trim(),
        no_hp: f.no_hp.value.trim()
    };
    var result;
    if (id) {
        data.id_anggota = parseInt(id);
        result = await apiPut('anggota', data);
    } else {
        result = await apiPost('anggota', data);
    }
    if (result.success) {
        showToast(result.message, 'success');
        closeModal('modalAnggota');
        renderAnggota();
    } else {
        showToast(result.message, 'error');
    }
    return false;
}

async function editAnggota(id) {
    var anggota = await apiGet('anggota');
    if (!anggota) return;
    var a = null;
    for (var i = 0; i < anggota.length; i++) {
        if (anggota[i].id_anggota === id) { a = anggota[i]; break; }
    }
    if (!a) return;
    var f = document.getElementById('formAnggota');
    f.id_anggota.value = a.id_anggota;
    f.nim.value = a.nim;
    f.nama.value = a.nama;
    f.prodi.value = a.prodi;
    f.no_hp.value = a.no_hp || '';
    document.getElementById('modalAnggotaTitle').textContent = 'Edit Anggota';
    openModal('modalAnggota');
}

// ============================================================
// HAPUS (GENERIC)
// ============================================================
var hapusState = { type: '', id: 0 };

function confirmHapus(type, id, name) {
    hapusState = { type: type, id: id };
    document.getElementById('hapusMessage').textContent = 'Apakah Anda yakin ingin menghapus "' + name + '"? Tindakan ini tidak dapat dibatalkan.';
    openModal('modalHapus');
}

document.getElementById('btnConfirmHapus').addEventListener('click', async function() {
    var type = hapusState.type;
    var id = hapusState.id;
    var result = await apiDelete(type, id);
    if (result.success) {
        showToast('Data berhasil dihapus', 'success');
    } else {
        showToast(result.message || 'Gagal menghapus', 'error');
    }
    closeModal('modalHapus');
    refreshPage(getCurrentPage());
});

function getCurrentPage() {
    var active = document.querySelector('.sidebar-link.active');
    return active ? active.dataset.page : 'dashboard';
}

// ============================================================
// PEMINJAMAN
// ============================================================
async function populatePeminjamanDropdowns() {
    var anggota = await apiGet('anggota');
    var barang = await apiGet('barang');
    if (!anggota || !barang) return;

    var selAnggota = document.querySelector('#formPeminjaman select[name="id_anggota"]');
    var selBarang = document.querySelector('#formPeminjaman select[name="id_barang"]');

    var aHtml = '<option value="">Pilih anggota...</option>';
    for (var i = 0; i < anggota.length; i++) {
        aHtml += '<option value="' + anggota[i].id_anggota + '">' + anggota[i].nama + ' (' + anggota[i].nim + ')</option>';
    }
    selAnggota.innerHTML = aHtml;

    var available = [];
    for (var j = 0; j < barang.length; j++) {
        if (barang[j].stok_tersedia > 0) available.push(barang[j]);
    }
    var bHtml = '<option value="">Pilih barang...</option>';
    for (var k = 0; k < available.length; k++) {
        bHtml += '<option value="' + available[k].id_barang + '">' + available[k].nama_barang + ' [Tersedia: ' + available[k].stok_tersedia + ']</option>';
    }
    selBarang.innerHTML = bHtml;

    var today = new Date().toISOString().split('T')[0];
    var nextWeek = new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0];
    document.querySelector('#formPeminjaman input[name="tgl_pinjam"]').value = today;
    document.querySelector('#formPeminjaman input[name="tgl_kembali"]').value = nextWeek;
}

function updateStokInfo() {
    var sel = document.querySelector('#formPeminjaman select[name="id_barang"]');
    var info = document.getElementById('stokInfo');
    var id = parseInt(sel.value);
    if (!id) { info.textContent = ''; return; }
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        var match = opt.text.match(/Tersedia: (\d+)/);
        info.textContent = match ? 'Stok tersedia: ' + match[1] : '';
    }
}

async function renderPeminjaman() {
    var search = (document.getElementById('searchPeminjaman') || {}).value || '';
    var peminjaman = await apiGet('peminjaman', { search: search });
    if (!peminjaman) return;
    var tbody = document.getElementById('tbodyPeminjaman');
    var empty = document.getElementById('emptyPeminjaman');
    if (peminjaman.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');
        var html = '';
        for (var i = 0; i < peminjaman.length; i++) {
            var p = peminjaman[i];
            var bList = '';
            var totalJml = 0;
            var details = p.details || [];
            for (var j = 0; j < details.length; j++) {
                bList += details[j].nama_barang + ' (' + details[j].jumlah + ')';
                if (j < details.length - 1) bList += ', ';
                totalJml += details[j].jumlah;
            }
            html += '<tr class="table-row">' +
                '<td class="px-4 py-3 text-xs font-mono text-neutral-500">PM-' + String(p.id_peminjaman).padStart(4, '0') + '</td>' +
                '<td class="px-4 py-3 text-neutral-300">' + p.nama_anggota + '</td>' +
                '<td class="px-4 py-3 text-neutral-400 max-w-[180px] truncate text-xs">' + bList + '</td>' +
                '<td class="px-4 py-3 text-neutral-400 text-center">' + totalJml + '</td>' +
                '<td class="px-4 py-3 text-neutral-500 text-xs">' + p.tanggal_pinjam + '</td>' +
                '<td class="px-4 py-3">' + statusBadge(p.status) + '</td></tr>';
        }
        tbody.innerHTML = html;
    }
}

async function submitPeminjaman(e) {
    e.preventDefault();
    var f = e.target;
    var data = {
        id_anggota: parseInt(f.id_anggota.value),
        id_barang: parseInt(f.id_barang.value),
        jumlah: parseInt(f.jumlah.value),
        tgl_pinjam: f.tgl_pinjam.value,
        tgl_kembali: f.tgl_kembali.value,
        catatan: f.catatan.value.trim()
    };
    var result = await apiPost('peminjaman', data);
    if (result.success) {
        showToast(result.message, 'success');
        f.reset();
        populatePeminjamanDropdowns();
        renderPeminjaman();
    } else {
        showToast(result.message, 'error');
    }
    return false;
}

// ============================================================
// PENGEMBALIAN
// ============================================================
async function populatePengembalianDropdown() {
    var active = await apiGet('pengembalian');
    if (!active) return;
    var sel = document.querySelector('#formPengembalian select[name="id_peminjaman"]');
    var html = '<option value="">Pilih transaksi...</option>';
    for (var i = 0; i < active.length; i++) {
        var p = active[i];
        var bList = '';
        var details = p.details || [];
        for (var j = 0; j < details.length; j++) {
            bList += details[j].nama_barang;
            if (j < details.length - 1) bList += ', ';
        }
        html += '<option value="' + p.id_peminjaman + '">PM-' + String(p.id_peminjaman).padStart(4, '0') + ' — ' + p.nama_anggota + ' — ' + bList + '</option>';
    }
    sel.innerHTML = html;
    document.querySelector('#formPengembalian input[name="tgl_kembali"]').value = new Date().toISOString().split('T')[0];
    renderActiveBorrow();
}

function showPengembalianDetail() {
    var sel = document.querySelector('#formPengembalian select[name="id_peminjaman"]');
    var detail = document.getElementById('pengembalianDetail');
    var text = document.getElementById('detailPengembalianText');
    var id = parseInt(sel.value);
    if (!id) { detail.classList.add('hidden'); return; }
    var opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) { detail.classList.add('hidden'); return; }
    detail.classList.remove('hidden');
    var parts = opt.textContent.split('—');
    text.innerHTML = '<span class="text-neutral-500">•</span> ' + (parts[0] || '').trim() + '<br><span class="text-neutral-500">•</span> ' + (parts[1] || '').trim() + '<br><span class="text-neutral-500">•</span> ' + (parts[2] || '').trim();
}

async function renderActiveBorrow() {
    var active = await apiGet('pengembalian');
    if (!active) return;
    var tbody = document.getElementById('tbodyActiveBorrow');
    var empty = document.getElementById('emptyActiveBorrow');
    if (active.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');
        var html = '';
        for (var i = 0; i < active.length; i++) {
            var p = active[i];
            var bList = '';
            var totalJml = 0;
            var details = p.details || [];
            for (var j = 0; j < details.length; j++) {
                bList += details[j].nama_barang + ' (' + details[j].jumlah + ')';
                if (j < details.length - 1) bList += ', ';
                totalJml += details[j].jumlah;
            }
            var isLate = new Date() > new Date(p.tanggal_rencana_kembali);
            var lateClass = isLate ? 'text-red-400 font-medium' : 'text-neutral-500';
            var lateText = isLate ? ' (Terlambat!)' : '';
            html += '<tr class="table-row">' +
                '<td class="px-4 py-3 text-neutral-300">' + p.nama_anggota + '</td>' +
                '<td class="px-4 py-3 text-neutral-400 text-xs max-w-[180px] truncate">' + bList + '</td>' +
                '<td class="px-4 py-3 text-neutral-400 text-center">' + totalJml + '</td>' +
                '<td class="px-4 py-3 text-neutral-500 text-xs">' + p.tanggal_pinjam + '</td>' +
                '<td class="px-4 py-3 text-xs ' + lateClass + '">' + p.tanggal_rencana_kembali + lateText + '</td>' +
                '<td class="px-4 py-3">' + statusBadge(p.status) + '</td></tr>';
        }
        tbody.innerHTML = html;
    }
}

function renderPengembalian() { renderActiveBorrow(); }

async function submitPengembalian(e) {
    e.preventDefault();
    var f = e.target;
    var data = {
        id_peminjaman: parseInt(f.id_peminjaman.value),
        tgl_kembali: f.tgl_kembali.value,
        kondisi: f.kondisi.value,
        catatan: f.catatan.value.trim()
    };
    var result = await apiPost('pengembalian', data);
    if (result.success) {
        showToast(result.message, 'success');
        if (data.kondisi !== 'Baik') {
            showToast('Perhatian: Barang dikembalikan dalam kondisi "' + data.kondisi + '"', 'warning');
        }
        f.reset();
        document.getElementById('pengembalianDetail').classList.add('hidden');
        populatePengembalianDropdown();
    } else {
        showToast(result.message, 'error');
    }
    return false;
}

// ============================================================
// LAPORAN
// ============================================================
async function renderLaporan() {
    var d = await apiGet('laporan');
    if (!d) return;
    document.getElementById('lapTotalPinjam').textContent = d.total_pinjam || 0;
    document.getElementById('lapTotalKembali').textContent = d.total_kembali || 0;
    document.getElementById('lapTotalAktif').textContent = d.total_aktif || 0;

    var tbodyKat = document.getElementById('tbodyLapKategori');
    var kat = d.kategori || [];
    var kHtml = '';
    for (var i = 0; i < kat.length; i++) {
        var k = kat[i];
        var stokClass = k.tersedia > 0 ? 'text-green-400' : 'text-red-400';
        kHtml += '<tr class="table-row"><td class="px-5 py-3 text-neutral-200">' + k.nama_kategori + '</td><td class="px-5 py-3 text-center text-neutral-400">' + k.jenis + '</td><td class="px-5 py-3 text-center text-neutral-300 font-medium">' + k.total + '</td><td class="px-5 py-3 text-center"><span class="' + stokClass + ' font-medium">' + k.tersedia + '</span></td></tr>';
    }
    tbodyKat.innerHTML = kHtml;

    var riwayat = d.riwayat || [];
    var tbodyRiw = document.getElementById('tbodyLapRiwayat');
    var emptyRiw = document.getElementById('emptyLapRiwayat');
    if (riwayat.length === 0) {
        tbodyRiw.innerHTML = '';
        emptyRiw.classList.remove('hidden');
    } else {
        emptyRiw.classList.add('hidden');
        var rHtml = '';
        for (var j = 0; j < riwayat.length; j++) {
            var r = riwayat[j];
            var statusHtml = r.tipe === 'kembali' ? '<span class="badge badge-success">Dikembalikan</span>' : statusBadge(r.status);
            rHtml += '<tr class="table-row"><td class="px-4 py-3 text-neutral-300 text-xs">' + r.peminjam + '</td><td class="px-4 py-3 text-neutral-400 text-xs max-w-[160px] truncate">' + r.barang + '</td><td class="px-4 py-3">' + statusHtml + '</td></tr>';
        }
        tbodyRiw.innerHTML = rHtml;
    }
}

// ============================================================
// USER CRUD
// ============================================================
async function renderUsers() {
    var users = await apiGet('users');
    if (!users) return;
    var tbody = document.getElementById('tbodyUsers');
    var empty = document.getElementById('emptyUsers');
    if (users.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');
        var roleBadge = { admin: 'badge-success', anggota: 'badge-info', ketua: 'badge-warning' };
        var roleLabel = { admin: 'Admin', anggota: 'Anggota', ketua: 'Ketua' };
        var html = '';
        for (var i = 0; i < users.length; i++) {
            var u = users[i];
            var nameEsc = u.nama.replace(/'/g, "\\'");
            html += '<tr class="table-row">' +
                '<td class="px-5 py-3 text-xs font-mono text-neutral-500">' + u.id_user + '</td>' +
                '<td class="px-5 py-3 text-neutral-200 font-medium">' + u.nama + '</td>' +
                '<td class="px-5 py-3 text-neutral-300 font-mono text-xs">' + u.username + '</td>' +
                '<td class="px-5 py-3"><span class="badge ' + (roleBadge[u.role] || 'badge-neutral') + '">' + (roleLabel[u.role] || u.role) + '</span></td>' +
                '<td class="px-5 py-3 text-neutral-500 text-xs">' + u.created_at + '</td>' +
                '<td class="px-5 py-3 text-center"><div class="flex items-center justify-center gap-1 crud-buttons">' +
                '<button onclick="editUser(' + u.id_user + ')" class="btn-warning btn-sm" title="Edit"><span class="iconify text-xs" data-icon="lucide:pencil"></span></button>' +
                '<button onclick="confirmHapus(\'users\',' + u.id_user + ',\'' + nameEsc + '\')" class="btn-danger btn-sm" title="Hapus"><span class="iconify text-xs" data-icon="lucide:trash-2"></span></button>' +
                '</div></td></tr>';
        }
        tbody.innerHTML = html;
    }
}

async function submitUser(e) {
    e.preventDefault();
    var f = e.target;
    var id = f.id_user.value;
    var data = {
        nama: f.nama.value.trim(),
        username: f.username.value.trim(),
        role: f.role.value
    };
    if (f.password.value.trim() !== '') {
        data.password = f.password.value.trim();
    }
    var result;
    if (id) {
        data.id_user = parseInt(id);
        result = await apiPut('users', data);
    } else {
        if (!data.password) {
            showToast('Password wajib diisi untuk user baru', 'error');
            return false;
        }
        result = await apiPost('users', data);
    }
    if (result.success) {
        showToast(result.message, 'success');
        closeModal('modalUser');
        renderUsers();
    } else {
        showToast(result.message, 'error');
    }
    return false;
}

async function editUser(id) {
    var users = await apiGet('users');
    if (!users) return;
    var u = null;
    for (var i = 0; i < users.length; i++) {
        if (users[i].id_user === id) { u = users[i]; break; }
    }
    if (!u) return;
    var f = document.getElementById('formUser');
    f.id_user.value = u.id_user;
    f.nama.value = u.nama;
    f.username.value = u.username;
    f.role.value = u.role;
    f.password.value = '';
    document.getElementById('inputPassword').required = false;
    document.getElementById('pwdHint').textContent = ' (kosongkan jika tidak diubah)';
    document.getElementById('modalUserTitle').textContent = 'Edit User';
    openModal('modalUser');
}

// ============================================================
// KEYBOARD & INIT
// ============================================================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var modals = ['modalHapus', 'modalBarang', 'modalKategori', 'modalAnggota', 'modalUser'];
        for (var i = 0; i < modals.length; i++) {
            var m = document.getElementById(modals[i]);
            if (m && !m.classList.contains('hidden')) {
                closeModal(modals[i]);
                break;
            }
        }
    }
});

renderDashboard();