<?php
class Header {
    public static function render($title = "Dashboard", $username = "Admin Default", $nim = "E000000") {
        ?>
        <div class="header">
            <div class="welcome-section">
                <p class="greeting">Selamat Datang, <?= htmlspecialchars($nim . ' ' . $username) ?> </p>
                <h1 class="welcome-title"><?= htmlspecialchars($title) ?></h1>
            </div>

            <div class="header-right">
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <div class="notification-indicator"></div>
                </div>

                <div class="user-profile">
                    <img src="../assets/images/tyakk.png" alt="User" class="profile-pic" />
                </div>

                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Mencari" />
                </div>
            </div>
        </div>
        <?php
    }
}
?>
