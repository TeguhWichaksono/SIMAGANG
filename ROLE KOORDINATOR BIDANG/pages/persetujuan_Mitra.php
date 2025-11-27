<?php
// SIMULASI DATA BACK-END
// ganti ini dengan query database yang benar.

// 1. Data Mitra Aktif (Tabel Utama)
// Asumsikan data ini diambil dari DB
$data_mitra_aktif = [
    ['no' => 1, 'nama' => 'PT Jember Abadi', 'bidang' => 'IT dan Software', 'alamat' => 'Jl. Letjen Panjaitan No.1', 'kontak' => '0812-3456-7890', 'status' => 'Aktif'],
    ['no' => 2, 'nama' => 'CV Sentosa', 'bidang' => 'Desain Grafis', 'alamat' => 'Jl. Raya Jember No.8', 'kontak' => '0896-1234-5678', 'status' => 'Aktif'],
    ['no' => 3, 'nama' => 'Koperasi Sejahtera', 'bidang' => 'IT dan Software', 'alamat' => 'Jl. Sudirman No.5', 'kontak' => '0812-9876-5432', 'status' => 'Aktif'],
];

// 2. Data Pengajuan Mitra (Tabel Persetujuan)
// Coba ubah nilai ini menjadi array kosong "[]" untuk menguji fitur "naik otomatis"
$data_pengajuan_mitra = [
    [
        'no' => 1, 'nama' => 'PT Jember Abadi', 'bidang' => 'IT dan Software', 'kontak' => '0812-3456-7890', 'status' => 'Pending',
        'diajukan' => 'Septiya Qorrata Ayun',
        'anggota' => [
            'Septiya Qorrata Ayun (E31241242)',
            'Rizky Ramadhan (E31241243)',
            'Putri Alifia (E31241250)',
        ]
    ],
    [
        'no' => 2, 'nama' => 'CV Sentosa', 'bidang' => 'Desain Grafis', 'kontak' => '0896-1234-5678', 'status' => 'Pending',
        'diajukan' => 'Naila Fadhilah',
        'anggota' => [
            'Naila Fadhilah (E31241244)',
            'Rendi Saputra (E31241255)',
            'Devi Anggraini (E31241260)',
        ]
    ],
];
$jumlah_pengajuan = count($data_pengajuan_mitra);
?>

<link rel="stylesheet" href="styles/persetujuan_Mitra.css?v=<?=time()?>">

<?php if ($jumlah_pengajuan > 0): ?>
<div class="content-section card-pengajuan mb-4">
    <h3><i class="fas fa-check-circle"></i> Persetujuan Mitra</h3>
    <p>Daftar Mitra yang Diajukan Mahasiswa</p>
    
    <div class="search-bar-data">
        <input type="text" id="searchPengajuan" placeholder="Cari Mitra atau Mahasiswa..." />
    </div>

    <table id="tabelPengajuan" class="table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Mitra / Instansi</th>
                <th>Bidang</th>
                <th>Diajukan oleh Mahasiswa</th>
                <th>Anggota Kelompok</th>
                <th>Kontak Mitra</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_pengajuan_mitra as $row): ?>
            <tr>
                <td><?= $row['no'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['bidang'] ?></td>
                <td><?= $row['diajukan'] ?></td>
                <td>
                    <ul class="anggota-list">
                        <?php foreach ($row['anggota'] as $anggota): ?>
                            <li><?= $anggota ?></li>
                        <?php endforeach; ?>
                    </ul>
                </td>
                <td><?= $row['kontak'] ?></td>
                <td class="status-pending"><?= $row['status'] ?></td>
                <td>
                    <button class="btn-action btn-acc">ACC</button>
                    <button class="btn-action btn-tolak">Tolak</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="content-section card-mitra">
    <h3><i class="fas fa-building"></i> Daftar Mitra / Perusahaan</h3>
    
    <div class="header-mitra-aktif">
        <button class="add-btn">
            <i class="fas fa-plus"></i> Tambah Mitra
        </button>
        <div class="search-bar-data search-mitra-aktif">
            <input type="text" id="searchMitraAktif" placeholder="Cari Mitra..." />
        </div>
    </div>
    
    <table id="tabelMitraAktif" class="table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Mitra</th>
                <th>Bidang</th>
                <th>Alamat</th>
                <th>Kontak (WA)</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_mitra_aktif as $row): ?>
            <tr>
                <td><?= $row['no'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['bidang'] ?></td>
                <td><?= $row['alamat'] ?></td>
                <td><?= $row['kontak'] ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <button class="btn-view">Lihat</button>
                    <button class="action-btn delete-btn"><i class="fas fa-trash"></i></button>
                    <button class="action-btn edit-btn"><i class="fas fa-pen"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>

document.getElementById("searchMitraAktif").addEventListener("keyup", function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll("#tabelMitraAktif tbody tr").forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
    });
});

document.querySelectorAll("#tabelMitraAktif .btn-view").forEach((btn) => {
    btn.addEventListener("click", () => {
        const mitraName = btn.closest("tr").cells[1].textContent;
        alert(`Detail Mitra: ${mitraName}`);
    });
});

<?php if ($jumlah_pengajuan > 0): ?>
    document.getElementById("searchPengajuan").addEventListener("keyup", function () {
        let value = this.value.toLowerCase();
        document.querySelectorAll("#tabelPengajuan tbody tr").forEach((row) => {
            row.style.display = row.textContent.toLowerCase().includes(value) ? "" : "none";
        });
    });

    document.querySelectorAll("#tabelPengajuan tbody tr").forEach((row) => {
        const btnAcc = row.querySelector(".btn-acc");
        const btnTolak = row.querySelector(".btn-tolak");
        const statusCell = row.cells[6]; 

        btnAcc.addEventListener("click", () => {
            statusCell.textContent = "Disetujui";
            statusCell.className = "status-disetujui";
            alert(`Mitra "${row.cells[1].textContent}" telah disetujui!`);
        });

        btnTolak.addEventListener("click", () => {
            statusCell.textContent = "Ditolak";
            statusCell.className = "status-ditolak";
            alert(`Mitra "${row.cells[1].textContent}" telah ditolak!`);
        });
    });
<?php endif; ?>
</script>