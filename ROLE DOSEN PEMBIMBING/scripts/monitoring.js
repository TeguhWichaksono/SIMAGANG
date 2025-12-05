/**
 * monitoring.js
 * JavaScript untuk Monitoring & Validasi Logbook Mahasiswa
 * 
 * Features:
 * - Switch tabs
 * - Filter logbook
 * - Load detail logbook (AJAX)
 * - Submit validasi (AJAX)
 * - Modal management
 * - Image viewer
 */

// ========================================
// GLOBAL VARIABLES
// ========================================
let currentLogbookData = null;

// ========================================
// TAB SWITCHING
// ========================================
function switchTab(tabName) {
    // Remove active class from all tabs
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(btn => btn.classList.remove('active'));
    tabContents.forEach(content => content.classList.remove('active'));
    
    // Add active class to selected tab
    document.getElementById(`tab-btn-${tabName}`).classList.add('active');
    document.getElementById(`tab-${tabName}`).classList.add('active');
}

// ========================================
// FILTER LOGBOOK (CLIENT-SIDE)
// ========================================
function applyFilter() {
    const filterMahasiswa = document.getElementById('filterMahasiswa').value;
    const filterStatus = document.getElementById('filterStatus').value;
    const filterTanggalMulai = document.getElementById('filterTanggalMulai').value;
    const filterTanggalSelesai = document.getElementById('filterTanggalSelesai').value;
    
    const table = document.getElementById('tableRiwayat');
    const rows = table.querySelectorAll('tbody tr');
    
    let visibleCount = 0;
    
    rows.forEach((row, index) => {
        const mahasiswaId = row.getAttribute('data-mahasiswa');
        const status = row.getAttribute('data-status');
        const tanggal = row.getAttribute('data-tanggal');
        
        let showRow = true;
        
        // Filter Mahasiswa
        if (filterMahasiswa && mahasiswaId !== filterMahasiswa) {
            showRow = false;
        }
        
        // Filter Status
        if (filterStatus && status !== filterStatus) {
            showRow = false;
        }
        
        // Filter Tanggal Mulai
        if (filterTanggalMulai && tanggal < filterTanggalMulai) {
            showRow = false;
        }
        
        // Filter Tanggal Selesai
        if (filterTanggalSelesai && tanggal > filterTanggalSelesai) {
            showRow = false;
        }
        
        // Show/Hide row
        if (showRow) {
            row.style.display = '';
            visibleCount++;
            // Update nomor urut
            row.querySelector('td:first-child').textContent = visibleCount;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show empty state if no results
    const tableWrapper = table.closest('.table-wrapper');
    let emptyState = tableWrapper.nextElementSibling;
    
    if (visibleCount === 0) {
        if (!emptyState || !emptyState.classList.contains('empty-state')) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.style.marginTop = '20px';
            emptyState.innerHTML = `
                <i class="fas fa-search"></i>
                <h3>Tidak Ada Hasil</h3>
                <p>Tidak ada logbook yang sesuai dengan filter Anda</p>
            `;
            tableWrapper.parentNode.insertBefore(emptyState, tableWrapper.nextSibling);
        }
        tableWrapper.style.display = 'none';
        emptyState.style.display = 'block';
    } else {
        tableWrapper.style.display = 'block';
        if (emptyState && emptyState.classList.contains('empty-state')) {
            emptyState.style.display = 'none';
        }
    }
}

// ========================================
// LIHAT DETAIL LOGBOOK (AJAX)
// ========================================
function lihatDetailLogbook(idLogbook) {
    const modal = document.getElementById('modalDetailLogbook');
    const modalBody = document.getElementById('detailLogbookContent');
    const modalFooter = document.getElementById('detailLogbookFooter');
    const subtitle = document.getElementById('detailSubtitle');
    
    // Show modal with loading
    modal.classList.add('show');
    modalBody.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Memuat data...</p>
        </div>
    `;
    
    // AJAX Request
    fetch(`pages/get_detail_logbook.php?id_logbook=${idLogbook}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentLogbookData = data.data;
                renderDetailLogbook(data.data);
            } else {
                modalBody.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <h3>Error</h3>
                        <p>${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Terjadi Kesalahan</h3>
                    <p>Gagal memuat data logbook. Silakan coba lagi.</p>
                </div>
            `;
        });
}

// ========================================
// RENDER DETAIL LOGBOOK
// ========================================
function renderDetailLogbook(data) {
    const logbook = data.logbook;
    const kegiatan = data.kegiatan;
    
    const modalBody = document.getElementById('detailLogbookContent');
    const modalFooter = document.getElementById('detailLogbookFooter');
    const subtitle = document.getElementById('detailSubtitle');
    
    // Update subtitle
    subtitle.innerHTML = `
        <i class="fas fa-user"></i>
        ${logbook.nama_mahasiswa} - ${logbook.nim}
    `;
    
    // Status badge styling
    const statusClass = {
        'pending': 'pending',
        'disetujui': 'disetujui',
        'ditolak': 'ditolak'
    };
    
    const statusIcon = {
        'pending': 'fa-clock',
        'disetujui': 'fa-check-circle',
        'ditolak': 'fa-times-circle'
    };
    
    const statusText = {
        'pending': 'Pending',
        'disetujui': 'Disetujui',
        'ditolak': 'Ditolak'
    };
    
    // Build HTML
    let html = `
        <!-- INFO ABSENSI -->
        <div class="detail-section">
            <h4><i class="fas fa-camera"></i> Informasi Absensi</h4>
            
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="info-card-content">
                        <small>Tanggal</small>
                        <div>${logbook.tanggal_formatted}</div>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-card-content">
                        <small>Jam Absensi</small>
                        <div>${logbook.jam_formatted}</div>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-card-content">
                        <small>Lokasi</small>
                        <div>${logbook.lokasi_absensi || '-'}</div>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="info-card-content">
                        <small>Tempat Magang</small>
                        <div>${logbook.nama_mitra || '-'}</div>
                    </div>
                </div>
            </div>
            
            ${logbook.foto_absensi ? `
                <div class="photo-container">
                    <img src="uploads/${logbook.foto_absensi}" 
                         alt="Foto Absensi" 
                         onclick="openImageModal(this.src)"
                         style="cursor: pointer;">
                </div>
            ` : '<p class="text-muted">Tidak ada foto absensi</p>'}
        </div>
        
        <!-- DETAIL KEGIATAN -->
        <div class="detail-section">
            <h4><i class="fas fa-tasks"></i> Detail Kegiatan (${kegiatan.length})</h4>
            
            ${kegiatan.length > 0 ? `
                <div class="kegiatan-list">
                    ${kegiatan.map((k, index) => `
                        <div class="kegiatan-item">
                            <div class="kegiatan-header">
                                <span class="kegiatan-number">Kegiatan ${index + 1}</span>
                                <span class="kegiatan-time">
                                    <i class="fas fa-clock"></i>
                                    ${k.jam_mulai.substring(0, 5)} - ${k.jam_selesai.substring(0, 5)} WIB
                                </span>
                            </div>
                            <div class="kegiatan-desc">
                                ${k.deskripsi_kegiatan.replace(/\n/g, '<br>')}
                            </div>
                            ${k.foto_kegiatan ? `
                                <img src="uploads/${k.foto_kegiatan}" 
                                     alt="Foto Kegiatan" 
                                     class="kegiatan-foto"
                                     onclick="openImageModal(this.src)">
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            ` : '<p class="text-muted">Tidak ada detail kegiatan</p>'}
        </div>
        
        <!-- STATUS VALIDASI -->
        <div class="detail-section">
            <h4><i class="fas fa-clipboard-check"></i> Status Validasi</h4>
            
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <span class="status-badge ${statusClass[logbook.status_validasi]}">
                    <i class="fas ${statusIcon[logbook.status_validasi]}"></i>
                    ${statusText[logbook.status_validasi]}
                </span>
            </div>
            
            ${logbook.catatan_dosen ? `
                <div style="background: var(--gray-50); padding: 14px; border-radius: 8px; border-left: 4px solid var(--gray-400);">
                    <strong style="display: block; font-size: 13px; color: var(--gray-700); margin-bottom: 8px;">
                        Catatan Dosen:
                    </strong>
                    <p style="font-size: 14px; line-height: 1.6; color: var(--gray-600); margin: 0;">
                        ${logbook.catatan_dosen}
                    </p>
                </div>
            ` : '<p class="text-muted">Tidak ada catatan</p>'}
        </div>
    `;
    
    modalBody.innerHTML = html;
    
    // Update footer buttons
    if (logbook.status_validasi === 'pending') {
        modalFooter.innerHTML = `
            <button class="btn btn-secondary" onclick="closeModal('modalDetailLogbook')">
                Tutup
            </button>
            <button class="btn btn-danger" onclick="closeModal('modalDetailLogbook'); validasiLogbook(${logbook.id_logbook}, 'ditolak')">
                <i class="fas fa-times"></i>
                Tolak
            </button>
            <button class="btn btn-success" onclick="closeModal('modalDetailLogbook'); validasiLogbook(${logbook.id_logbook}, 'disetujui')">
                <i class="fas fa-check"></i>
                Setujui
            </button>
        `;
    } else {
        modalFooter.innerHTML = `
            <button class="btn btn-secondary" onclick="closeModal('modalDetailLogbook')">
                Tutup
            </button>
        `;
    }
}

// ========================================
// VALIDASI LOGBOOK (SHOW MODAL)
// ========================================
function validasiLogbook(idLogbook, status) {
    const modal = document.getElementById('modalValidasi');
    const title = document.getElementById('validasiTitle');
    const btnSubmit = document.getElementById('btnSubmitValidasi');
    
    // Set hidden inputs
    document.getElementById('validasiIdLogbook').value = idLogbook;
    document.getElementById('validasiStatus').value = status;
    document.getElementById('validasiCatatan').value = '';
    
    // Update title dan button style
    if (status === 'disetujui') {
        title.textContent = 'Setujui Logbook';
        btnSubmit.className = 'btn btn-success';
        btnSubmit.innerHTML = '<i class="fas fa-check"></i> Setujui';
    } else {
        title.textContent = 'Tolak Logbook';
        btnSubmit.className = 'btn btn-danger';
        btnSubmit.innerHTML = '<i class="fas fa-times"></i> Tolak';
    }
    
    // Show modal
    modal.classList.add('show');
}

// ========================================
// SUBMIT VALIDASI (AJAX)
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const formValidasi = document.getElementById('formValidasi');
    
    if (formValidasi) {
        formValidasi.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const idLogbook = document.getElementById('validasiIdLogbook').value;
            const status = document.getElementById('validasiStatus').value;
            const catatan = document.getElementById('validasiCatatan').value;
            const btnSubmit = document.getElementById('btnSubmitValidasi');
            
            // Disable button
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('id_logbook', idLogbook);
            formData.append('status', status);
            formData.append('catatan', catatan);
            
            // AJAX Request
            fetch('pages/update_validasi_logbook.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success - Show notification
                    showNotification('success', data.message);
                    
                    // Close modal
                    closeModal('modalValidasi');
                    
                    // Reload page after 1 second
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    // Error
                    showNotification('error', data.message);
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = status === 'disetujui' 
                        ? '<i class="fas fa-check"></i> Setujui'
                        : '<i class="fas fa-times"></i> Tolak';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Terjadi kesalahan. Silakan coba lagi.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = status === 'disetujui' 
                    ? '<i class="fas fa-check"></i> Setujui'
                    : '<i class="fas fa-times"></i> Tolak';
            });
        });
    }
});

// ========================================
// MODAL MANAGEMENT
// ========================================
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal-overlay.show');
        modals.forEach(modal => modal.classList.remove('show'));
    }
});

// ========================================
// IMAGE VIEWER
// ========================================
function openImageModal(src) {
    const modal = document.getElementById('modalImage');
    const img = document.getElementById('modalImageContent');
    
    img.src = src;
    modal.classList.add('show');
}

// ========================================
// NOTIFICATION SYSTEM
// ========================================
function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background: ${type === 'success' ? 'var(--success-color)' : 'var(--danger-color)'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideInRight 0.3s ease;
    `;
    
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

// Format date to Indonesian
function formatDateIndo(dateString) {
    const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    const date = new Date(dateString);
    const namaHari = hari[date.getDay()];
    const tanggal = date.getDate();
    const namaBulan = bulan[date.getMonth()];
    const tahun = date.getFullYear();
    
    return `${namaHari}, ${tanggal} ${namaBulan} ${tahun}`;
}

// ========================================
// INITIALIZE
// ========================================
console.log('Monitoring Logbook System Loaded ✓');