<?php
// pages/laporan_Sistem.php
// Halaman Monitoring Log Aktivitas User

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Hanya Admin yang boleh akses
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit;
}

include '../Koneksi/koneksi.php';

// ==========================================
// 1. LOGIC EXPORT TO CSV
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'export') {
    // Bersihkan buffer output agar file tidak corrupt
    ob_end_clean(); 
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="laporan_sistem_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    
    // Header Kolom CSV
    fputcsv($output, array('No', 'Waktu', 'Nama User', 'Role', 'Aktivitas'));

    // Query semua data (tanpa limit pagination)
    $query_export = "SELECT l.waktu, l.aktivitas, u.nama, u.role 
                     FROM log_aktivitas l 
                     JOIN users u ON l.id_user = u.id 
                     ORDER BY l.waktu DESC";
    $result_export = mysqli_query($conn, $query_export);

    $no = 1;
    while ($row = mysqli_fetch_assoc($result_export)) {
        fputcsv($output, array(
            $no++, 
            $row['waktu'], 
            $row['nama'], 
            $row['role'], 
            $row['aktivitas']
        ));
    }
    fclose($output);
    exit;
}

// ==========================================
// 2. LOGIC FILTER & PAGING
// ==========================================
$limit = 10; // Jumlah baris per halaman
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$start = ($page - 1) * $limit;

// Filter Search & Waktu
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_waktu = isset($_GET['waktu']) ? $_GET['waktu'] : 'all';

$where_clause = " WHERE 1=1 ";

// Logic Filter Search
if (!empty($search)) {
    $where_clause .= " AND (u.nama LIKE '%$search%' OR l.aktivitas LIKE '%$search%') ";
}

// Logic Filter Waktu
if ($filter_waktu == 'today') {
    $where_clause .= " AND DATE(l.waktu) = CURDATE() ";
} elseif ($filter_waktu == 'month') {
    $where_clause .= " AND MONTH(l.waktu) = MONTH(CURDATE()) AND YEAR(l.waktu) = YEAR(CURDATE()) ";
}

// Hitung Total Data (untuk pagination)
$query_count = "SELECT COUNT(*) as total FROM log_aktivitas l JOIN users u ON l.id_user = u.id $where_clause";
$result_count = mysqli_query($conn, $query_count);
$total_data = mysqli_fetch_assoc($result_count)['total'];
$total_pages = ceil($total_data / $limit);

// Query Data Utama
$query_main = "SELECT l.*, u.nama, u.role, u.foto_profil 
               FROM log_aktivitas l 
               JOIN users u ON l.id_user = u.id 
               $where_clause 
               ORDER BY l.waktu DESC 
               LIMIT $start, $limit";
$result_main = mysqli_query($conn, $query_main);
?>

<link rel="stylesheet" href="styles/laporan_Sistem.css?v=<?= time(); ?>">

<div class="laporan-wrapper">
    
    <div class="laporan-header">
        <div class="header-title">
            <h2><i class="fas fa-history"></i> Laporan Aktivitas Sistem</h2>
            <p>Memantau jejak audit dan aktivitas pengguna</p>
        </div>
        <div class="header-actions">
            <a href="index.php?page=laporan_Sistem&action=export" class="btn-export">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>

    <div class="filter-bar">
        <form method="GET" action="index.php" class="filter-form">
            <input type="hidden" name="page" value="laporan_Sistem">
            
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari user atau aktivitas..." value="<?= htmlspecialchars($search) ?>">
            </div>

            <select name="waktu" onchange="this.form.submit()" class="select-filter">
                <option value="all" <?= $filter_waktu == 'all' ? 'selected' : '' ?>>Semua Waktu</option>
                <option value="today" <?= $filter_waktu == 'today' ? 'selected' : '' ?>>Hari Ini</option>
                <option value="month" <?= $filter_waktu == 'month' ? 'selected' : '' ?>>Bulan Ini</option>
            </select>
        </form>
    </div>

    <div class="table-container">
        <table class="log-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Waktu</th>
                    <th width="25%">Pengguna</th>
                    <th width="40%">Aktivitas</th>
                    <th width="10%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result_main) > 0): ?>
                    <?php 
                    $no = $start + 1;
                    while ($row = mysqli_fetch_assoc($result_main)): 
                        // Deteksi tipe aktivitas untuk styling
                        $status_icon = 'fa-info-circle';
                        $status_class = 'info';
                        
                        if (stripos($row['aktivitas'], 'hapus') !== false || stripos($row['aktivitas'], 'delete') !== false) {
                            $status_icon = 'fa-trash';
                            $status_class = 'danger';
                        } elseif (stripos($row['aktivitas'], 'tambah') !== false || stripos($row['aktivitas'], 'create') !== false) {
                            $status_icon = 'fa-plus-circle';
                            $status_class = 'success';
                        } elseif (stripos($row['aktivitas'], 'edit') !== false || stripos($row['aktivitas'], 'update') !== false) {
                            $status_icon = 'fa-pen';
                            $status_class = 'warning';
                        }
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div class="time-cell">
                                <span class="date-part"><?= date('d M Y', strtotime($row['waktu'])) ?></span>
                                <span class="time-part"><?= date('H:i', strtotime($row['waktu'])) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar-small">
                                    <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                </div>
                                <div class="user-info">
                                    <span class="user-name"><?= htmlspecialchars($row['nama']) ?></span>
                                    <span class="user-role role-<?= strtolower(str_replace(' ', '-', $row['role'])) ?>">
                                        <?= htmlspecialchars($row['role']) ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="activity-text">
                                <?= htmlspecialchars($row['aktivitas']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?= $status_class ?>">
                                <i class="fas <?= $status_icon ?>"></i>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" alt="Empty" width="60">
                            <p>Tidak ada data log aktivitas ditemukan.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <p>Menampilkan <?= mysqli_num_rows($result_main) ?> dari <?= $total_data ?> data</p>
        <div class="pagination-buttons">
            <?php if ($page > 1): ?>
                <a href="index.php?page=laporan_Sistem&halaman=<?= $page - 1 ?>&search=<?= $search ?>&waktu=<?= $filter_waktu ?>" class="page-btn prev">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="index.php?page=laporan_Sistem&halaman=<?= $i ?>&search=<?= $search ?>&waktu=<?= $filter_waktu ?>" class="page-btn <?= ($i == $page) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="index.php?page=laporan_Sistem&halaman=<?= $page + 1 ?>&search=<?= $search ?>&waktu=<?= $filter_waktu ?>" class="page-btn next">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>