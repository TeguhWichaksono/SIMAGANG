<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SI MAGANG | Manajemen Informatika</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    * {margin: 0; padding: 0; box-sizing: border-box; font-family: "Plus Jakarta Sans", sans-serif;}
    html {scroll-behavior: smooth;}
    body {background: #f0f4f8; color: #1e293b; overflow-x: hidden;}
    
    /* Floating Shapes Background */
    .shape {position: absolute; border-radius: 50%; opacity: 0.1; pointer-events: none;}
    .shape1 {width: 300px; height: 300px; background: linear-gradient(135deg, #0ea5e9, #06b6d4); top: 10%; right: 5%; animation: float 20s infinite;}
    .shape2 {width: 200px; height: 200px; background: linear-gradient(135deg, #3b82f6, #2563eb); bottom: 20%; left: 10%; animation: float 15s infinite reverse;}
    .shape3 {width: 150px; height: 150px; background: linear-gradient(135deg, #0284c7, #0369a1); top: 60%; right: 15%; animation: float 25s infinite;}
    @keyframes float {0%, 100% {transform: translateY(0px) rotate(0deg);} 50% {transform: translateY(-30px) rotate(180deg);}}
    
    /* Header */
    header {background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); box-shadow: 0 4px 30px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000;}
    nav {max-width: 1400px; margin: auto; display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 3rem;}
    .logo {display: flex; align-items: center; font-weight: 800; font-size: 28px; color: #0284c7;}
    .logo i {margin-right: 12px; font-size: 36px; background: linear-gradient(135deg, #0ea5e9, #0284c7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;}
    .nav-links {display: flex; gap: 3rem; align-items: center;}
    .nav-links a {text-decoration: none; color: #475569; font-weight: 600; font-size: 15px; transition: all 0.3s; position: relative;}
    .nav-links a::before {content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 3px; background: linear-gradient(90deg, #0ea5e9, #0284c7); transition: width 0.3s; border-radius: 10px;}
    .nav-links a:hover {color: #0284c7;}
    .nav-links a:hover::before {width: 100%;}
    .btn-login {padding: 0.9rem 2.5rem; border-radius: 15px; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; text-decoration: none; font-weight: 700; transition: all 0.3s; box-shadow: 0 8px 25px rgba(14, 165, 233, 0.35);}
    .btn-login:hover {transform: translateY(-2px); box-shadow: 0 12px 35px rgba(14, 165, 233, 0.45);}
    
    /* Hero */
    .hero {min-height: 100vh; display: flex; align-items: center; padding: 0 3rem; background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); position: relative; overflow: hidden;}
    .hero-grid {max-width: 1400px; margin: auto; display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 6rem; align-items: center; position: relative; z-index: 2;}
    .hero-text h1 {font-size: 4.5rem; font-weight: 800; line-height: 1.15; margin-bottom: 2rem; color: #0f172a;}
    .hero-text h1 .gradient-text {background: linear-gradient(135deg, #0ea5e9, #0284c7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;}
    .hero-text p {font-size: 1.25rem; color: #475569; line-height: 1.9; margin-bottom: 3rem;}
    .hero-stats {display: flex; gap: 3rem; margin-bottom: 3rem;}
    .stat-box {text-align: center;}
    .stat-box .number {font-size: 2.5rem; font-weight: 800; color: #0284c7; display: block; line-height: 1;}
    .stat-box .label {font-size: 0.95rem; color: #64748b; margin-top: 0.5rem;}
    .hero-buttons {display: flex; gap: 1.5rem; flex-wrap: wrap;}
    .btn {padding: 1.2rem 3rem; border-radius: 15px; text-decoration: none; font-weight: 700; transition: all 0.3s; display: inline-flex; align-items: center; gap: 12px; font-size: 16px;}
    .btn-primary {background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; box-shadow: 0 10px 35px rgba(14, 165, 233, 0.35);}
    .btn-primary:hover {transform: translateY(-3px); box-shadow: 0 15px 45px rgba(14, 165, 233, 0.45);}
    .btn-outline {background: transparent; color: #0284c7; border: 3px solid #0284c7;}
    .btn-outline:hover {background: #0284c7; color: #fff;}
    .hero-visual {position: relative;}
    .hero-visual img {width: 100%; border-radius: 30px; box-shadow: 0 30px 70px rgba(14, 165, 233, 0.25);}
    .floating-card {position: absolute; background: #fff; padding: 1.5rem; border-radius: 20px; box-shadow: 0 15px 50px rgba(0,0,0,0.1); animation: floatCard 4s infinite;}
    .floating-card.card1 {top: 10%; right: -10%; width: 180px;}
    .floating-card.card2 {bottom: 15%; left: -10%; width: 200px;}
    @keyframes floatCard {0%, 100% {transform: translateY(0px);} 50% {transform: translateY(-15px);}}
    .floating-card i {font-size: 2rem; color: #0284c7; margin-bottom: 0.5rem;}
    .floating-card h4 {font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 0.3rem;}
    .floating-card p {font-size: 0.85rem; color: #64748b;}
    
    /* Features */
    .features {padding: 10rem 3rem; background: #fff; position: relative;}
    .container {max-width: 1400px; margin: auto;}
    .section-title {text-align: center; margin-bottom: 6rem;}
    .section-title .label {display: inline-block; padding: 0.7rem 2rem; background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #0369a1; border-radius: 50px; font-weight: 700; font-size: 14px; margin-bottom: 1.5rem;}
    .section-title h2 {font-size: 3.8rem; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem;}
    .section-title p {font-size: 1.25rem; color: #64748b; max-width: 750px; margin: auto;}
    .features-grid {display: grid; grid-template-columns: repeat(2, 1fr); gap: 3rem;}
    .feature-box {background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 3.5rem; border-radius: 30px; transition: all 0.4s; border: 3px solid transparent; position: relative; overflow: hidden;}
    .feature-box::before {content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(14, 165, 233, 0.05), rgba(2, 132, 199, 0.05)); opacity: 0; transition: opacity 0.4s;}
    .feature-box:hover {transform: translateY(-10px); border-color: #0ea5e9; box-shadow: 0 25px 60px rgba(14, 165, 233, 0.2);}
    .feature-box:hover::before {opacity: 1;}
    .feature-icon {width: 90px; height: 90px; background: linear-gradient(135deg, #0ea5e9, #0284c7); border-radius: 25px; display: flex; align-items: center; justify-content: center; margin-bottom: 2rem; font-size: 2.5rem; color: #fff; box-shadow: 0 15px 40px rgba(14, 165, 233, 0.4); position: relative; z-index: 1;}
    .feature-box h3 {font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-bottom: 1.2rem; position: relative; z-index: 1;}
    .feature-box p {font-size: 1.05rem; color: #475569; line-height: 1.8; position: relative; z-index: 1;}
    
    /* Power BI */
    .powerbi {padding: 10rem 3rem; background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); position: relative;}
    .powerbi-box {margin-top: 4rem; background: #fff; padding: 2.5rem; border-radius: 35px; box-shadow: 0 30px 80px rgba(14, 165, 233, 0.15); border: 4px solid rgba(14, 165, 233, 0.1);}
    .powerbi-box iframe {width: 100%; height: 800px; border: none; border-radius: 25px;}
    
    /* Timeline */
    .timeline {padding: 10rem 3rem; background: #fff;}
    .timeline-grid {display: grid; grid-template-columns: repeat(2, 1fr); gap: 3rem; margin-top: 5rem;}
    .timeline-card {background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 3rem; border-radius: 30px; border-left: 6px solid #0ea5e9; transition: all 0.4s; position: relative;}
    .timeline-card::before {content: attr(data-number); position: absolute; top: -20px; left: 30px; width: 60px; height: 60px; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; box-shadow: 0 10px 30px rgba(14, 165, 233, 0.4);}
    .timeline-card:hover {transform: translateX(10px); box-shadow: 0 20px 50px rgba(14, 165, 233, 0.2);}
    .timeline-card h3 {font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem; margin-top: 1.5rem;}
    .timeline-card p {font-size: 1.05rem; color: #475569; line-height: 1.8;}
    
    /* CTA */
    .cta {padding: 10rem 3rem; background: linear-gradient(135deg, #0369a1, #0284c7, #0ea5e9); position: relative; overflow: hidden;}
    .cta::before, .cta::after {content: ''; position: absolute; border-radius: 50%;}
    .cta::before {width: 600px; height: 600px; background: radial-gradient(circle, rgba(255,255,255,0.15), transparent); top: -200px; right: -200px;}
    .cta::after {width: 400px; height: 400px; background: radial-gradient(circle, rgba(255,255,255,0.1), transparent); bottom: -100px; left: -100px;}
    .cta-content {max-width: 900px; margin: auto; text-align: center; position: relative; z-index: 1;}
    .cta-content h2 {font-size: 4rem; font-weight: 800; color: #fff; margin-bottom: 2rem; line-height: 1.2;}
    .cta-content p {font-size: 1.35rem; color: rgba(255,255,255,0.95); margin-bottom: 3rem; line-height: 1.9;}
    .cta .btn-primary {background: #fff; color: #0284c7; box-shadow: 0 15px 50px rgba(0,0,0,0.2);}
    .cta .btn-primary:hover {transform: translateY(-3px) scale(1.05); box-shadow: 0 20px 60px rgba(0,0,0,0.3);}
    
    /* Footer */
    footer {background: #0f172a; color: #fff; padding: 3rem 3rem 2rem; text-align: center;}
    .footer-simple {max-width: 1200px; margin: auto;}
    .footer-copyright {font-size: 1.1rem; margin-bottom: 1.5rem; color: #94a3b8; font-weight: 600;}
    .footer-contact {font-size: 1.05rem; margin-bottom: 1rem; color: #cbd5e1;}
    .footer-contact a {color: #0ea5e9; text-decoration: none; font-weight: 600; transition: all 0.3s;}
    .footer-contact a:hover {color: #38bdf8; text-decoration: underline;}
    .footer-social {font-size: 1.05rem; color: #cbd5e1;}
    .footer-social a {color: #0ea5e9; text-decoration: none; font-weight: 600; transition: all 0.3s;}
    .footer-social a:hover {color: #38bdf8; text-decoration: underline;}
    
    .menu-toggle {display: none; flex-direction: column; cursor: pointer; gap: 7px;}
    .menu-toggle span {width: 30px; height: 4px; background: #0284c7; border-radius: 5px; transition: all 0.3s;}
    
    @media (max-width: 1024px) {
      .hero-grid {grid-template-columns: 1fr; text-align: center;}
      .hero-text h1 {font-size: 3.5rem;}
      .hero-stats {justify-content: center;}
      .hero-buttons {justify-content: center;}
      .features-grid, .timeline-grid {grid-template-columns: 1fr;}
      .floating-card {display: none;}
    }
    
    @media (max-width: 768px) {
      nav {padding: 1.2rem 1.5rem;}
      .nav-links {display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; flex-direction: column; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1);}
      .nav-links.active {display: flex;}
      .menu-toggle {display: flex;}
      .hero {padding: 5rem 1.5rem;}
      .hero-text h1 {font-size: 2.8rem;}
      .section-title h2 {font-size: 2.5rem;}
      .features, .powerbi, .timeline, .cta {padding: 6rem 1.5rem;}
      .powerbi-box iframe {height: 500px;}
      footer {padding: 2rem 1.5rem;}
      .footer-copyright, .footer-contact, .footer-social {font-size: 0.95rem;}
    }
  </style>
</head>
<body>
  <header>
    <nav>
      <div class="logo"><i class="fas fa-graduation-cap"></i>SI MAGANG</div>
      <div class="menu-toggle" onclick="toggleMenu()"><span></span><span></span><span></span></div>
      <div class="nav-links" id="navLinks">
        <a href="#beranda">Beranda</a>
        <a href="#fitur">Fitur</a>
        <a href="#penyebaran">Penyebaran</a>
        <a href="#alur">Alur</a>
        <a href="#kontak">Kontak</a>
        <a href="Login/login.php" class="btn-login">Login</a>
      </div>
    </nav>
  </header>

  <section class="hero" id="beranda">
    <div class="shape shape1"></div>
    <div class="shape shape2"></div>
    <div class="shape shape3"></div>
    <div class="hero-grid">
      <div class="hero-text">
        <h1>Platform Magang <span class="gradient-text">Digital Terpadu</span> untuk Masa Depan</h1>
        <p>Transformasi digital dalam manajemen magang mahasiswa. Sistem terintegrasi untuk pengajuan, monitoring, pengajuan kelompok dengan teknologi terkini.</p>
        <div class="hero-stats">
          <div class="stat-box">
            <span class="number">85+</span>
            <span class="label">Mahasiswa Aktif</span>
          </div>
          <div class="stat-box">
            <span class="number">35+</span>
            <span class="label">Mitra Industri</span>
          </div>
        </div>
        <div class="hero-buttons">
          <a href="Login/login.php" class="btn btn-primary">Mulai Sekarang <i class="fas fa-arrow-right"></i></a>
          <a href="#fitur" class="btn btn-outline">Jelajahi Fitur</a>
        </div>
      </div>
      <div class="hero-visual">
       <img src="ROLE MAHASISWA/images/JTI.jpg" alt="Hero Image">

        <div class="floating-card card1">
          <i class="fas fa-check-circle"></i>
          <h4>100% Digital</h4>
          <p>Paperless system</p>
        </div>
        <div class="floating-card card2">
          <i class="fas fa-clock"></i>
          <h4>Real-time</h4>
          <p>Monitoring 24/7</p>
        </div>
      </div>
    </div>
  </section>

  <section class="features" id="fitur">
    <div class="container">
      <div class="section-title">
        <span class="label">Fitur Unggulan</span>
        <h2>Solusi Lengkap Manajemen Magang</h2>
        <p>Platform all-in-one yang dirancang khusus untuk memudahkan seluruh proses magang dari awal hingga akhir</p>
      </div>
      <div class="features-grid">
        <div class="feature-box">
          <div class="feature-icon"><i class="fas fa-user-graduate"></i></div>
          <h3>Portal Mahasiswa Interaktif</h3>
          <p>Dashboard lengkap dengan fitur pengajuan magang, logbook digital harian, upload dokumen, dan tracking progress real-time.</p>
        </div>
        <div class="feature-box">
          <div class="feature-icon"><i class="fas fa-chalkboard-teacher"></i></div>
          <h3>Panel Dosen Pembimbing</h3>
          <p>Sistem monitoring terpusat untuk memantau progress mahasiswa, memberikan feedback langsung, approve laporan kegiatan, dan melakukan evaluasi komprehensif.</p>
        </div>
        <div class="feature-box">
          <div class="feature-icon"><i class="fas fa-cogs"></i></div>
          <h3>Admin Management System</h3>
          <p>Control panel lengkap untuk mengelola data mahasiswa, user management, dan monitoring seluruh aktivitas sistem secara real-time.</p>
        </div>
        <div class="feature-box">
          <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
          <h3>Dashboard Koordinator Magang</h3>
          <p>Analytics dashboard untuk koordinasi antar bidang, laporan statistik lengkap, dan data visualization untuk pengambilan keputusan berbasis data.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="powerbi" id="penyebaran">
    <div class="container">
      <div class="section-title">
        <span class="label">Data & Analytics</span>
        <h2>Visualisasi Penyebaran Magang</h2>
        <p>Monitoring real-time sebaran lokasi magang dan statistik program magang di seluruh Indonesia dengan Power BI</p>
      </div>
      <div class="powerbi-box">
        <iframe title="Projek" src="https://app.powerbi.com/view?r=eyJrIjoiMjk0ODg2YzMtMGMxOC00Y2FiLTkyYmQtZDVkMzQwYjIxNWEzIiwidCI6ImE2OWUxOWU4LWYwYTQtNGU3Ny1iZmY2LTk1NjRjODgxOWIxNCJ9" frameborder="0" allowFullScreen="true"></iframe>
      </div>
    </div>
  </section>

  <section class="timeline" id="alur">
    <div class="container">
      <div class="section-title">
        <span class="label">Proses Magang</span>
        <h2>Alur Magang yang Sistematis</h2>
        <p>Empat tahap terstruktur untuk memastikan pengalaman magang yang optimal dan profesional</p>
      </div>
      <div class="timeline-grid">
        <div class="timeline-card" data-number="1">
          <h3>Pendaftaran & Seleksi</h3>
          <p>Mahasiswa mendaftar melalui portal digital, mengisi formulir lengkap, upload dokumen persyaratan, dan memilih mitra magang sesuai dengan minat dan kompetensi yang diinginkan.</p>
        </div>
        <div class="timeline-card" data-number="2">
          <h3>Verifikasi & Approval</h3>
          <p>Tim Koordinator bidang melakukan verifikasi dokumen secara menyeluruh, validasi persyaratan, dan memberikan approval untuk memulai program magang.</p>
        </div>
        <div class="timeline-card" data-number="3">
          <h3>Pelaksanaan & Monitoring</h3>
          <p>Mahasiswa menjalankan program magang dengan pemantauan berkala melalui logbook digital, dan evaluasi progress secara real-time oleh dosen pembimbing.</p>
        </div>
        <div class="timeline-card" data-number="4">
          <h3>Penyelesaian Program & Akses Riwayat</h3>
          <p>Mahasiswa menyelesaikan seluruh rangkaian kegiatan magang dan sistem otomatis menutup proses. Setelah itu, mahasiswa dapat mengakses riwayat lengkap mulai dari aktivitas, logbook, hingga status akhir sebagai dokumentasi digital.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="cta">
    <div class="cta-content">
      <h2>Wujudkan Pengalaman Magang Anda yang Lebih Profesional</h2>
      <p>Bergabunglah dengan ratusan mahasiswa yang telah merasakan kemudahan dan efisiensi mengelola magang melalui SI MAGANG. Login sekarang dan mulai perjalanan magang anda!</p>
      <a href="Login/login.php" class="btn btn-primary">Login Sekarang <i class="fas fa-rocket"></i></a>
    </div>
  </section>

  <footer id="kontak">
    <div class="footer-simple">
      <p class="footer-copyright">&copy; 2025 SI MAGANG | Politeknik Negeri Jember. Semua hak dilindungi.</p>
      <p class="footer-contact">
        Hubungi kami di 📞 <a href="tel:082140559526">0821-4055-9526</a> | ✉ <a href="mailto:support_simagang@gmail.com">support_simagang@gmail.com</a>
      </p>
      <p class="footer-social">
        📸 Instagram: <a href="https://instagram.com/simagang_jti.polije" target="_blank">@simagang_jti.polije</a>
      </p>
    </div>
  </footer>

  <script>
    function toggleMenu() {
      document.getElementById('navLinks').classList.toggle('active');
    }

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({behavior: 'smooth', block: 'start'});
          document.getElementById('navLinks').classList.remove('active');
        }
      });
    });
  </script>
</body>
</html>