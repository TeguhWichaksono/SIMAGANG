<?php
class Sidebar {
    public static function render($activePage = '') {
        ?>
        <div class="sidebar">
            <div class="logo">
                <svg width="100" height="40" viewBox="0 0 100 40">
                    <path d="M12,15 L30,15 L24,27 L12,27 Z" fill="#4270F4" />
                    <text x="38" y="27" fill="#262A39" font-size="20" font-weight="bold">
                        SMG
                    </text>
                </svg>
            </div>

            <div class="nav-menu">
                <a href="dashboard.php" class="nav-item <?= $activePage == 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i><span>Dashboard</span>
                </a>
                <a href="akun.php" class="nav-item <?= $activePage == 'akun' ? 'active' : '' ?>">
                    <i class="fas fa-user-circle"></i><span>Akun</span>
                </a>
                <a href="manajemen_user.php" class="nav-item <?= $activePage == 'manajemen_user' ? 'active' : '' ?>">
                    <i class="fas fa-users-gear"></i><span>Manajemen User</span>
                </a>
                <a href="laporan_sistem.php" class="nav-item <?= $activePage == 'laporan_sistem' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i><span>Laporan Sistem</span>
                </a>
                <a href="notifikasi.php" class="nav-item <?= $activePage == 'notifikasi' ? 'active' : '' ?>">
                    <i class="fas fa-bell"></i><span>Notifikasi</span>
                </a>
                <a href="help_center.php" class="nav-item <?= $activePage == 'help_center' ? 'active' : '' ?>">
                    <i class="fas fa-life-ring"></i><span>Help Center</span>
                </a>
            </div>

            <div class="premium-box">
                <h3>Buku Panduan</h3>
                <button class="premium-btn">Selengkapnya</button>
            </div>
        </div>
        <?php
    }
}
?>
