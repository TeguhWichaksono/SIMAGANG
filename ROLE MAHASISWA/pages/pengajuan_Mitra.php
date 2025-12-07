<?php
include_once '../Koneksi/koneksi.php';

$id_user = $_SESSION['id'];
$is_locked = false;
$lock_message = "";
$show_lanjut_upload = false;
$lock_reason = ""; // Tambahan untuk tracking alasan kunci

// 1. Ambil Data Mahasiswa & Status Magang
$q_mhs = "SELECT id_mahasiswa, status_magang FROM mahasiswa WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $q_mhs);
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data_mhs = mysqli_fetch_assoc($res);
$id_mahasiswa = $data_mhs['id_mahasiswa'] ?? 0;
$status_magang = $data_mhs['status_magang'] ?? 'pra-magang';

// 2. Cek Status Magang (Aktif/Selesai = KUNCI)
if ($status_magang == 'magang_aktif' || $status_magang == 'selesai') {
    $is_locked = true;
    $lock_reason = "status_magang";
    $lock_message = "Formulir terkunci karena status magang Anda: <strong>" . strtoupper(str_replace('_', ' ', $status_magang)) . "</strong>.";
}

// 3. LOGIKA PENGAMAN UTAMA - Cek Status Pengajuan Mitra
$disable_tambah_baru = false; // Flag untuk disable tombol "Tambah Mitra Baru"

if (!$is_locked) {
    // Deteksi kolom status (sama seperti di status_pengajuan_mitra.php)
    $status_column = 'status_pengajuan';
    $check_columns = "SHOW COLUMNS FROM pengajuan_mitra LIKE 'status%'";
    $result_check = mysqli_query($conn, $check_columns);
    $columns_found = [];
    while ($col = mysqli_fetch_assoc($result_check)) {
        $columns_found[] = $col['Field'];
    }
    
    if (in_array('status_pengajuan', $columns_found)) {
        $status_column = 'status_pengajuan';
    } elseif (in_array('status', $columns_found)) {
        $status_column = 'status';
    }

    // Cek apakah ada pengajuan yang MENUNGGU atau DISETUJUI
    $q_cek_status = "SELECT id_pengajuan, nama_perusahaan, {$status_column} as status_aktual 
                     FROM pengajuan_mitra 
                     WHERE id_mahasiswa = ? 
                     ORDER BY tanggal_pengajuan DESC 
                     LIMIT 1";
    
    $stmt2 = mysqli_prepare($conn, $q_cek_status);
    mysqli_stmt_bind_param($stmt2, 'i', $id_mahasiswa);
    mysqli_stmt_execute($stmt2);
    $res2 = mysqli_stmt_get_result($stmt2);
    
    if ($row_status = mysqli_fetch_assoc($res2)) {
        $status_raw = strtolower(trim($row_status['status_aktual']));
        $nama_perusahaan = htmlspecialchars($row_status['nama_perusahaan']);
        
        // KUNCI TOTAL jika status MENUNGGU
        if ($status_raw === 'menunggu' || $status_raw === 'pending') {
            $is_locked = true;
            $lock_reason = "menunggu_approval";
            $lock_message = "
                <strong>⏳ Pengajuan Mitra Sedang Diproses</strong><br>
                Anda memiliki pengajuan mitra \"<strong>{$nama_perusahaan}</strong>\" yang masih <strong>MENUNGGU PERSETUJUAN</strong> dari Koordinator Bidang Magang.<br><br>
                <i class=\"fas fa-info-circle\"></i> Anda tidak dapat mengajukan mitra baru sampai pengajuan ini <strong>DITOLAK</strong> oleh Koordinator.
            ";
        }
        
        // HANYA DISABLE TAMBAH BARU jika status DISETUJUI/DITERIMA
        elseif ($status_raw === 'diterima' || $status_raw === 'disetujui' || $status_raw === 'approved') {
            $disable_tambah_baru = true; // Form tetap buka, tapi tombol "Tambah Baru" disabled
            $lock_reason = "sudah_disetujui";
            $show_lanjut_upload = true;
            $lock_message = "
                <strong>✅ Mitra \"<strong>{$nama_perusahaan}</strong>\" Sudah Disetujui</strong><br><br>
                <i class=\"fas fa-info-circle\"></i> Anda <strong>tidak dapat menambah mitra baru</strong> karena sudah ada mitra yang disetujui.<br>
                <i class=\"fas fa-check-circle\"></i> Anda masih bisa <strong>memilih mitra lain dari daftar rekomendasi</strong> atau <strong>lanjut upload dokumen</strong>.
            ";
        }
        
        // TIDAK DIKUNCI jika status DITOLAK (boleh mengajukan lagi)
        // Status ditolak tidak perlu di-handle karena $is_locked tetap false
    }
    mysqli_stmt_close($stmt2);
}
?>

<link rel="stylesheet" href="styles/pengajuanMitra.css">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<div class="form-container">
  <h2>Pemilihan Mitra Magang</h2>

  <?php if ($is_locked): ?>
      <div style="background-color: <?= $lock_reason === 'sudah_disetujui' ? '#e8f5e9' : '#fff3cd' ?>; 
                  color: <?= $lock_reason === 'sudah_disetujui' ? '#2e7d32' : '#856404' ?>; 
                  padding: 20px; 
                  border-radius: 8px; 
                  margin-bottom: 20px; 
                  border: 2px solid <?= $lock_reason === 'sudah_disetujui' ? '#c8e6c9' : '#ffeaa7' ?>;">
          <?= $lock_message ?>
      </div>
      
      <!-- Tombol Lihat Status untuk Pengajuan yang Menunggu -->
      <?php if ($lock_reason === 'menunggu_approval'): ?>
          <div style="text-align: center; margin: 20px 0;">
              <a href="index.php?page=status_pengajuan_mitra" style="text-decoration:none;">
                  <button type="button" style="background:#17a2b8; border:none; padding:12px 24px; border-radius:5px; color:white; cursor:pointer; font-weight:600;">
                    <i class="fas fa-history"></i> Lihat Status Pengajuan
                  </button>
              </a>
          </div>
      <?php endif; ?>
  <?php elseif ($disable_tambah_baru): ?>
      <!-- Info Box untuk Mitra yang Sudah Disetujui (Form Tetap Buka) -->
      <div style="background-color: #e8f5e9; 
                  color: #2e7d32; 
                  padding: 20px; 
                  border-radius: 8px; 
                  margin-bottom: 20px; 
                  border: 2px solid #c8e6c9;">
          <?= $lock_message ?>
      </div>
  <?php endif; ?>

  <form id="formMitra">
    <p>Pilih mitra dari daftar rekomendasi kampus atau tambahkan mitra baru jika belum tersedia.</p>

    <!-- FIELD FULL WIDTH -->
    <div class="form-group full-width">
      <label for="namaMitra">Nama Mitra</label>
      <input type="text" 
             id="namaMitra" 
             name="namaMitra" 
             placeholder="Pilih mitra dari daftar rekomendasi" 
             required 
             readonly
             <?= $is_locked ? 'disabled' : '' ?>>
    </div>

    <div class="form-group full-width">
      <label for="alamatMitra">Alamat Instansi</label>
      <textarea id="alamatMitra" 
                name="alamatMitra" 
                placeholder="Alamat akan terisi otomatis" 
                required 
                readonly
                <?= $is_locked ? 'disabled' : '' ?>></textarea>
    </div>

    <div class="form-group full-width">
      <label for="bidangMitra">Bidang Usaha</label>
      <input type="text" 
             id="bidangMitra" 
             name="bidangMitra" 
             placeholder="Bidang akan terisi otomatis" 
             required 
             readonly
             <?= $is_locked ? 'disabled' : '' ?>>
    </div>

    <div class="form-group full-width">
      <label for="kontakMitra">Kontak (No HP / Email)</label>
      <input type="text" 
             id="kontakMitra" 
             name="kontakMitra" 
             placeholder="Kontak akan terisi otomatis" 
             required 
             readonly
             <?= $is_locked ? 'disabled' : '' ?>>
    </div>

    <!-- BUTTON ACTIONS -->
    <div class="form-actions">
      <?php if (!$is_locked): ?>
          <button type="button" id="btnRekomendasi">
            <i class="fas fa-list"></i> Lihat Rekomendasi Mitra
          </button>
          
          <?php if (!$disable_tambah_baru): ?>
          <button type="button" id="btnTambahBaru">
            <i class="fas fa-plus"></i> Tambahkan Mitra Baru
          </button>
          <?php else: ?>
          <button type="button" id="btnTambahBaru" disabled style="opacity: 0.5; cursor: not-allowed;" title="Tidak dapat menambah mitra baru karena sudah ada mitra yang disetujui">
            <i class="fas fa-ban"></i> Tambah Mitra Baru (Dinonaktifkan)
          </button>
          <?php endif; ?>
          
          <button type="button" id="btnSimpanMitra" style="display:none;">
            <i class="fas fa-save"></i> Simpan Mitra
          </button>
          <button type="button" id="btnBatalTambah" style="display:none;">
            <i class="fas fa-times"></i> Batal
          </button>
      <?php else: ?>
          <a href="index.php?page=status_pengajuan_mitra" style="text-decoration:none;">
              <button type="button" style="background:#17a2b8; border:none; padding:10px 15px; border-radius:5px; color:white; cursor:pointer;">
                <i class="fas fa-history"></i> Lihat Status Pengajuan
              </button>
          </a>
      <?php endif; ?>
    </div>

    <div class="form-actions" style="margin-top:20px; border-top:1px solid #eee; padding-top:20px;">
      <?php if (!$is_locked && !$disable_tambah_baru): ?>
          <button type="submit" id="lanjutDokumen">
            <i class="fas fa-arrow-right"></i> Lanjut ke Upload Dokumen
          </button>
      <?php elseif ($disable_tambah_baru || $show_lanjut_upload): ?>
          <button type="submit" id="lanjutDokumen">
            <i class="fas fa-arrow-right"></i> Lanjut ke Upload Dokumen
          </button>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- ================= POP-UP REKOMENDASI ================= -->
<div class="popup" id="popupRekom">
  <div class="popup-content">
    <span class="close" id="closePopup">&times;</span>
    <h3>Daftar Rekomendasi Mitra Kampus</h3>

    <div class="filter-container">
      <select id="filterBidang">
        <option value="">Semua Bidang</option>
      </select>
      <input type="text" id="searchMitra" placeholder="Cari nama mitra...">
    </div>

    <table id="tabelRekomMitra">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Alamat</th>
          <th>Bidang</th>
          <th>Pilih</th>
        </tr>
      </thead>
      <tbody id="bodyMitra">
        <tr>
          <td colspan="4" style="text-align:center;">Loading data...</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script>
  // ==========================================
  // VARIABLE GLOBAL & PENGAMAN
  // ==========================================
  let mitraData = [];
  const isFormLocked = <?= $is_locked ? 'true' : 'false' ?>;
  const disableTambahBaru = <?= $disable_tambah_baru ? 'true' : 'false' ?>;
  const lockReason = "<?= $lock_reason ?>";

  // PENGAMAN: Jika form terkunci, disable semua interaksi
  if (isFormLocked) {
    console.log('🔒 Form terkunci. Alasan:', lockReason);
  }
  
  if (disableTambahBaru) {
    console.log('⚠️ Tombol Tambah Mitra Baru dinonaktifkan (sudah ada mitra disetujui)');
  }

  // ==========================================
  // FUNGSI LOAD DATA DARI DATABASE
  // ==========================================
  function loadMitraFromDatabase() {
    fetch('pages/getMitra.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          mitraData = data.data;
          loadDropdown();
          loadTable();
        } else {
          alert('Gagal memuat data mitra: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memuat data mitra');
      });
  }

  // Load data hanya jika form tidak terkunci
  if (!isFormLocked) {
    loadMitraFromDatabase();
  }

  // ==========================================
  // FUNGSI LOAD DROPDOWN FILTER BIDANG
  // ==========================================
  function loadDropdown() {
    let bidangList = [...new Set(mitraData.map(m => m.bidang))];
    const selectBidang = document.getElementById("filterBidang");
    selectBidang.innerHTML = '<option value="">Semua Bidang</option>';

    bidangList.forEach(bid => {
      let opt = document.createElement("option");
      opt.value = bid;
      opt.textContent = bid;
      selectBidang.appendChild(opt);
    });
  }

  // ==========================================
  // FUNGSI LOAD TABLE MITRA
  // ==========================================
  function loadTable(filterText = "", filterBidang = "") {
    const tbody = document.getElementById("bodyMitra");
    tbody.innerHTML = "";

    const filteredData = mitraData
      .filter(m => m.nama.toLowerCase().includes(filterText.toLowerCase()))
      .filter(m => filterBidang === "" ? true : m.bidang === filterBidang);

    if (filteredData.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Tidak ada data ditemukan</td></tr>';
      return;
    }

    filteredData.forEach(m => {
      tbody.innerHTML += `
      <tr>
        <td>${m.nama}</td>
        <td>${m.alamat}</td>
        <td>${m.bidang}</td>
        <td><button class="btnPilih" onclick="pilihMitra('${escapeHtml(m.nama)}','${escapeHtml(m.alamat)}','${escapeHtml(m.bidang)}','${escapeHtml(m.kontak)}', ${m.id})">Pilih</button></td>
      </tr>
    `;
    });
  }

  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
  }

  // ==========================================
  // EVENT LISTENER SEARCH & FILTER
  // ==========================================
  document.getElementById("searchMitra").addEventListener("keyup", e => {
    loadTable(e.target.value, document.getElementById("filterBidang").value);
  });

  document.getElementById("filterBidang").addEventListener("change", e => {
    loadTable(document.getElementById("searchMitra").value, e.target.value);
  });

  // ==========================================
  // OPEN & CLOSE POPUP
  // ==========================================
  const btnRekom = document.getElementById("btnRekomendasi");
  if (btnRekom) {
    btnRekom.onclick = () => {
      if (isFormLocked) {
        alert('⚠️ Form terkunci! Anda tidak dapat memilih mitra saat ini.');
        return;
      }
      document.getElementById("popupRekom").style.display = "block";
      loadMitraFromDatabase();
    };
  }

  document.getElementById("closePopup").onclick = () => {
    document.getElementById("popupRekom").style.display = "none";
  };

  window.onclick = (event) => {
    const popup = document.getElementById("popupRekom");
    if (event.target == popup) {
      popup.style.display = "none";
    }
  };

  // ==========================================
  // PILIH MITRA DARI POPUP
  // ==========================================
  function pilihMitra(nama, alamat, bidang, kontak, idMitra) {
    if (isFormLocked) {
      alert('⚠️ Form terkunci! Anda tidak dapat memilih mitra saat ini.');
      return;
    }

    console.log('Mitra dipilih:', {nama, alamat, bidang, kontak, idMitra});

    document.getElementById("namaMitra").value = nama;
    document.getElementById("alamatMitra").value = alamat;
    document.getElementById("bidangMitra").value = bidang;
    document.getElementById("kontakMitra").value = kontak;
    document.getElementById("namaMitra").setAttribute('data-id-mitra', idMitra);

    let hiddenInput = document.getElementById("hidden_id_mitra");
    if (!hiddenInput) {
      hiddenInput = document.createElement("input");
      hiddenInput.type = "hidden";
      hiddenInput.id = "hidden_id_mitra";
      hiddenInput.name = "id_mitra_hidden";
      document.getElementById("formMitra").appendChild(hiddenInput);
    }
    hiddenInput.value = idMitra;

    document.getElementById("popupRekom").style.display = "none";
    setFormReadonly(true);
    alert("Mitra berhasil dipilih: " + nama);
  }

  // ==========================================
  // FUNGSI ENABLE/DISABLE FORM
  // ==========================================
  function setFormReadonly(readonly) {
    document.getElementById("namaMitra").readOnly = readonly;
    document.getElementById("alamatMitra").readOnly = readonly;
    document.getElementById("bidangMitra").readOnly = readonly;
    document.getElementById("kontakMitra").readOnly = readonly;

    if (readonly) {
      document.getElementById("btnRekomendasi").style.display = "inline-block";
      document.getElementById("btnTambahBaru").style.display = "inline-block";
      document.getElementById("btnSimpanMitra").style.display = "none";
      document.getElementById("btnBatalTambah").style.display = "none";
    } else {
      document.getElementById("btnRekomendasi").style.display = "none";
      document.getElementById("btnTambahBaru").style.display = "none";
      document.getElementById("btnSimpanMitra").style.display = "inline-block";
      document.getElementById("btnBatalTambah").style.display = "inline-block";
    }
  }

  // ==========================================
  // TOMBOL TAMBAH MITRA BARU
  // ==========================================
  const btnTambah = document.getElementById("btnTambahBaru");
  if (btnTambah) {
    btnTambah.onclick = () => {
      if (isFormLocked) {
        alert('⚠️ Form terkunci! Anda tidak dapat menambah mitra baru saat ini.');
        return;
      }
      
      if (disableTambahBaru) {
        alert('⚠️ Tidak dapat menambah mitra baru!\n\nAnda sudah memiliki mitra yang disetujui oleh Koordinator Bidang Magang.\n\nSilakan pilih mitra dari daftar rekomendasi atau lanjut ke upload dokumen.');
        return;
      }

      document.getElementById("formMitra").reset();
      setFormReadonly(false);

      document.getElementById("namaMitra").placeholder = "Masukkan nama mitra";
      document.getElementById("alamatMitra").placeholder = "Masukkan alamat instansi";
      document.getElementById("bidangMitra").placeholder = "Masukkan bidang usaha";
      document.getElementById("kontakMitra").placeholder = "Masukkan kontak";
      document.getElementById("namaMitra").focus();
    };
  }

  // ==========================================
  // TOMBOL BATAL
  // ==========================================
  const btnBatal = document.getElementById("btnBatalTambah");
  if (btnBatal) {
    btnBatal.onclick = () => {
      document.getElementById("formMitra").reset();
      setFormReadonly(true);

      document.getElementById("namaMitra").placeholder = "Pilih mitra dari daftar rekomendasi";
      document.getElementById("alamatMitra").placeholder = "Alamat akan terisi otomatis";
      document.getElementById("bidangMitra").placeholder = "Bidang akan terisi otomatis";
      document.getElementById("kontakMitra").placeholder = "Kontak akan terisi otomatis";
    };
  }

  // ==========================================
  // TOMBOL SIMPAN MITRA BARU
  // ==========================================
  document.getElementById("btnSimpanMitra").onclick = () => {
    if (isFormLocked) {
      alert('⚠️ Form terkunci! Anda tidak dapat menyimpan mitra baru saat ini.');
      return;
    }
    
    if (disableTambahBaru) {
      alert('⚠️ Tidak dapat menambah mitra baru!\n\nAnda sudah memiliki mitra yang disetujui oleh Koordinator Bidang Magang.');
      return;
    }

    const nama = document.getElementById("namaMitra").value.trim();
    const alamat = document.getElementById("alamatMitra").value.trim();
    const bidang = document.getElementById("bidangMitra").value.trim();
    const kontak = document.getElementById("kontakMitra").value.trim();

    if (!nama || !alamat || !bidang || !kontak) {
      alert("Semua field harus diisi!");
      return;
    }

    const btnSimpan = document.getElementById("btnSimpanMitra");
    const originalText = btnSimpan.innerHTML;
    btnSimpan.disabled = true;
    btnSimpan.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    fetch('pages/simpanMitra.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({nama, alamat, bidang, kontak})
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(
            "✅ Pengajuan mitra baru berhasil dikirim!\n\n" +
            "⏳ Status: MENUNGGU PERSETUJUAN\n\n" +
            "Anda akan diarahkan ke halaman Status Mitra untuk melihat perkembangan pengajuan."
          );
          
          setTimeout(() => {
            window.location.href = "index.php?page=status_pengajuan_mitra";
          }, 500);
          
        } else {
          alert("Gagal menyimpan mitra: " + data.message);
          btnSimpan.disabled = false;
          btnSimpan.innerHTML = originalText;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert("Terjadi kesalahan saat menyimpan mitra");
        btnSimpan.disabled = false;
        btnSimpan.innerHTML = originalText;
      });
  };

  // ==========================================
  // HANDLE SUBMIT FORM
  // ==========================================
  const formMitra = document.getElementById("formMitra");

  if (formMitra) {
    formMitra.addEventListener("submit", function(e) {
      e.preventDefault();

      // PENGAMAN UTAMA
      if (isFormLocked) {
        alert('⚠️ Form terkunci! Anda tidak dapat melanjutkan saat ini.\n\nAlasan: ' + 
          (lockReason === 'menunggu_approval' ? 'Pengajuan mitra sedang menunggu persetujuan' :
           lockReason === 'sudah_disetujui' ? 'Mitra sudah disetujui' :
           'Status magang tidak mengizinkan'));
        return;
      }

      const namaMitra = document.getElementById("namaMitra").value.trim();

      if (!namaMitra) {
        alert("Silakan pilih mitra terlebih dahulu!");
        return;
      }

      const mitraStatus = document.getElementById("namaMitra").getAttribute('data-mitra-status');
      const idMitra = document.getElementById("namaMitra").getAttribute('data-id-mitra');
      const idPengajuan = document.getElementById("namaMitra").getAttribute('data-id-pengajuan');

      let dataToSend = {
        nama_mitra: namaMitra,
        alamat_mitra: document.getElementById("alamatMitra").value.trim(),
        bidang_mitra: document.getElementById("bidangMitra").value.trim(),
        kontak_mitra: document.getElementById("kontakMitra").value.trim()
      };

      if (mitraStatus === 'pending') {
        dataToSend.mitra_status = 'pending';
        dataToSend.id_pengajuan_mitra = parseInt(idPengajuan);
        dataToSend.id_mitra = 0;
        
        if (!confirm(
          "⚠️ PERHATIAN!\n\n" +
          "Mitra yang Anda pilih masih dalam status PENDING (menunggu persetujuan Korbid).\n\n" +
          "Anda tetap bisa melanjutkan upload dokumen, tapi pengajuan magang Anda " +
          "baru akan diproses setelah mitra disetujui.\n\n" +
          "Lanjutkan?"
        )) {
          return;
        }
        
      } else {
        if (!idMitra || idMitra === 'null' || idMitra === '') {
          alert("ID Mitra tidak valid! Silakan pilih mitra kembali.");
          return;
        }
        dataToSend.id_mitra = parseInt(idMitra);
        dataToSend.mitra_status = 'approved';
      }

      console.log('📤 Sending data:', dataToSend);

      const btnSubmit = document.getElementById("lanjutDokumen");
      if (btnSubmit) {
        const originalText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
      }

      fetch('pages/save_mitra_session.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(dataToSend)
        })
        .then(response => {
          console.log('📥 Response status:', response.status);
          if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
          }
          return response.json();
        })
        .then(data => {
          console.log('📥 Response data:', data);

          if (data.success) {
            console.log('✅ Data berhasil disimpan!');
            
            setTimeout(() => {
              if (mitraStatus === 'pending') {
                alert(
                  "✅ Data mitra (pending) berhasil disimpan!\n\n" +
                  "⚠️ Catatan: Mitra Anda masih menunggu approval dari Korbid.\n\n" +
                  "Melanjutkan ke halaman upload dokumen..."
                );
              } else {
                alert("Data mitra berhasil disimpan! Melanjutkan ke halaman upload dokumen...");
              }
              
              window.location.href = "index.php?page=berkas_Magang";
            }, 500);

          } else {
            throw new Error(data.message || 'Gagal menyimpan data');
          }
        })
        .catch(error => {
          console.error('❌ Error:', error);
          alert("Terjadi kesalahan: " + error.message);

          if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
          }
        });
    });
  }
</script>