/**
 * ========================================
 * monitoring.js
 * JavaScript untuk Monitoring Logbook
 * UPDATED - Fixed AJAX URLs
 * ========================================
 */
// Tab Switching
function switchTab(tabName) {
    console.log('Switching to tab:', tabName);
    
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(btn => btn.classList.remove('active'));
    tabContents.forEach(content => content.classList.remove('active'));
    
    const selectedButton = document.getElementById(`tab-btn-${tabName}`);
    const selectedContent = document.getElementById(`tab-${tabName}`);
    
    if (selectedButton && selectedContent) {
        selectedButton.classList.add('active');
        selectedContent.classList.add('active');
    }
}

// Filter Logbook
function applyFilter() {
    console.log('Applying filters...');
    
    const filterMahasiswa = document.getElementById('filterMahasiswa').value;
    const filterStatus = document.getElementById('filterStatus').value;
    const filterTanggalMulai = document.getElementById('filterTanggalMulai').value;
    const filterTanggalSelesai = document.getElementById('filterTanggalSelesai').value;
    
    const table = document.getElementById('tableRiwayat');
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach((row) => {
        const mahasiswaId = row.getAttribute('data-mahasiswa');
        const status = row.getAttribute('data-status');
        const tanggal = row.getAttribute('data-tanggal');
        
        let showRow = true;
        
        if (filterMahasiswa && mahasiswaId !== filterMahasiswa) showRow = false;
        if (filterStatus && status !== filterStatus) showRow = false;
        if (filterTanggalMulai && tanggal < filterTanggalMulai) showRow = false;
        if (filterTanggalSelesai && tanggal > filterTanggalSelesai) showRow = false;
        
        if (showRow) {
            row.style.display = '';
            visibleCount++;
            row.querySelector('td:first-child').textContent = visibleCount;
        } else {
            row.style.display = 'none';
        }
    });
    
    showNotification('info', `Menampilkan ${visibleCount} hasil`);
}

// Lihat Detail Logbook
function lihatDetailLogbook(idLogbook) {
    console.log('Loading detail for logbook:', idLogbook);
    
    const modal = document.getElementById('modalDetailLogbook');
    const modalBody = document.getElementById('detailLogbookContent');
    
    if (!modal || !modalBody) {
        console.error('Modal elements not found');
        return;
    }
    
    modal.classList.add('show');
    modalBody.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Memuat data...</p>
        </div>
    `;
    
    // FIXED: URL ke ajax_handler.php
    const url = `ajax_handler.php?action=get_detail_logbook&id_logbook=${idLogbook}`;
    console.log('Fetching from:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            
            if (data.success) {
                renderDetailLogbook(data.data);
            } else {
                showError(modalBody, data.message || 'Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showError(modalBody, 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        });
}

// Render Detail Logbook
function renderDetailLogbook(data) {
    console.log('Rendering detail logbook...');
    
    const logbook = data.logbook;
    const kegiatan = data.kegiatan;
    
    const modalBody = document.getElementById('detailLogbookContent');
    const modalFooter = document.getElementById('detailLogbookFooter');
    const subtitle = document.getElementById('detailSubtitle');
    
    subtitle.innerHTML = `
        <i class="fas fa-user"></i>
        ${logbook.nama_mahasiswa} - ${logbook.nim}
    `;
    
    const statusConfig = {
        'pending': { class: 'pending', icon: 'fa-clock', text: 'Pending' },
        'disetujui': { class: 'disetujui', icon: 'fa-check-circle', text: 'Disetujui' },
        'ditolak': { class: 'ditolak', icon: 'fa-times-circle', text: 'Ditolak' }
    };
    
    const status = statusConfig[logbook.status_validasi];
    
    let html = `
        <div class="detail-section">
            <h4><i class="fas fa-camera"></i> Informasi Absensi</h4>
            
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-calendar"></i></div>
                    <div class="info-card-content">
                        <small>Tanggal</small>
                        <div>${logbook.tanggal_formatted}</div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-clock"></i></div>
                    <div class="info-card-content">
                        <small>Jam Absensi</small>
                        <div>${logbook.jam_formatted}</div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-card-content">
                        <small>Lokasi</small>
                        <div>${logbook.lokasi_absensi || '-'}</div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-building"></i></div>
                    <div class="info-card-content">
                        <small>Tempat Magang</small>
                        <div>${logbook.nama_mitra || '-'}</div>
                    </div>
                </div>
            </div>
            
            ${logbook.foto_absensi ? `
                <div class="photo-container">
                    <img src="../ROLE Mahasiswa/uploads/${logbook.foto_absensi}" 
                         alt="Foto Absensi" 
                         onclick="openImageModal(this.src)"
                         style="cursor: pointer;">
                </div>
            ` : '<p style="color: var(--gray-500); font-style: italic; text-align: center;">Tidak ada foto absensi</p>'}
        </div>
        
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
                                ${escapeHtml(k.deskripsi_kegiatan).replace(/\n/g, '<br>')}
                            </div>
                            ${k.foto_kegiatan ? `
                                <img src="../ROLE Mahasiswa/uploads/${k.foto_kegiatan}" 
                                     alt="Foto Kegiatan" 
                                     class="kegiatan-foto"
                                     onclick="openImageModal(this.src)">
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            ` : '<p style="color: var(--gray-500); font-style: italic; text-align: center;">Tidak ada detail kegiatan</p>'}
        </div>
        
        <div class="detail-section">
            <h4><i class="fas fa-clipboard-check"></i> Status Validasi</h4>
            
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <span class="status-badge ${status.class}">
                    <i class="fas ${status.icon}"></i>
                    ${status.text}
                </span>
            </div>
            
            ${logbook.catatan_dosen ? `
                <div style="background: var(--gray-50); padding: 14px; border-radius: 8px; border-left: 4px solid var(--gray-400);">
                    <strong style="display: block; font-size: 13px; color: var(--gray-700); margin-bottom: 8px;">
                        Catatan Dosen:
                    </strong>
                    <p style="font-size: 14px; line-height: 1.6; color: var(--gray-600); margin: 0;">
                        ${escapeHtml(logbook.catatan_dosen)}
                    </p>
                </div>
            ` : '<p style="color: var(--gray-500); font-style: italic; text-align: center;">Tidak ada catatan</p>'}
        </div>
    `;
    
    modalBody.innerHTML = html;
    
    if (logbook.status_validasi === 'pending') {
        modalFooter.innerHTML = `
            <button class="btn btn-secondary" onclick="closeModal('modalDetailLogbook')">Tutup</button>
            <button class="btn btn-danger" onclick="closeModal('modalDetailLogbook'); validasiLogbook(${logbook.id_logbook}, 'ditolak')">
                <i class="fas fa-times"></i> Tolak
            </button>
            <button class="btn btn-success" onclick="closeModal('modalDetailLogbook'); validasiLogbook(${logbook.id_logbook}, 'disetujui')">
                <i class="fas fa-check"></i> Setujui
            </button>
        `;
    } else {
        modalFooter.innerHTML = `
            <button class="btn btn-secondary" onclick="closeModal('modalDetailLogbook')">Tutup</button>
        `;
    }
}

// Validasi Logbook
function validasiLogbook(idLogbook, status) {
    console.log('Validating logbook:', idLogbook, status);
    
    const modal = document.getElementById('modalValidasi');
    const title = document.getElementById('validasiTitle');
    const btnSubmit = document.getElementById('btnSubmitValidasi');
    
    document.getElementById('validasiIdLogbook').value = idLogbook;
    document.getElementById('validasiStatus').value = status;
    document.getElementById('validasiCatatan').value = '';
    
    if (status === 'disetujui') {
        title.textContent = 'Setujui Logbook';
        btnSubmit.className = 'btn btn-success';
        btnSubmit.innerHTML = '<i class="fas fa-check"></i> Setujui';
    } else {
        title.textContent = 'Tolak Logbook';
        btnSubmit.className = 'btn btn-danger';
        btnSubmit.innerHTML = '<i class="fas fa-times"></i> Tolak';
    }
    
    modal.classList.add('show');
}

// Submit Validasi
document.addEventListener('DOMContentLoaded', function() {
    console.log('Monitoring JS initialized');
    
    const formValidasi = document.getElementById('formValidasi');
    
    if (formValidasi) {
        formValidasi.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Submitting validation...');
            
            const idLogbook = document.getElementById('validasiIdLogbook').value;
            const status = document.getElementById('validasiStatus').value;
            const catatan = document.getElementById('validasiCatatan').value;
            const btnSubmit = document.getElementById('btnSubmitValidasi');
            
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            
            const formData = new FormData();
            formData.append('id_logbook', idLogbook);
            formData.append('status', status);
            formData.append('catatan', catatan);
            
            // FIXED: URL ke ajax_handler.php
            const url = 'ajax_handler.php?action=update_validasi_logbook';
            console.log('Posting to:', url);
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    showNotification('success', data.message);
                    closeModal('modalValidasi');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('error', data.message || 'Gagal memvalidasi');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = status === 'disetujui' 
                        ? '<i class="fas fa-check"></i> Setujui'
                        : '<i class="fas fa-times"></i> Tolak';
                }
            })
            .catch(error => {
                console.error('Submit error:', error);
                showNotification('error', 'Terjadi kesalahan. Silakan coba lagi.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = status === 'disetujui' 
                    ? '<i class="fas fa-check"></i> Setujui'
                    : '<i class="fas fa-times"></i> Tolak';
            });
        });
    }
});

// Modal Management
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('show');
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
    }
});

// Image Viewer
function openImageModal(src) {
    const modal = document.getElementById('modalImage');
    const img = document.getElementById('modalImageContent');
    if (modal && img) {
        img.src = src;
        modal.classList.add('show');
    }
}

// Utility Functions
function showError(container, message) {
    container.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-exclamation-circle"></i>
            <h3>Error</h3>
            <p>${message}</p>
        </div>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(type, message) {
    const colors = {
        'success': '#2dbf78',
        'error': '#f36c6c',
        'info': '#4270f4'
    };
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'info': 'fa-info-circle'
    };
    
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background: ${colors[type] || colors.info};
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
        <i class="fas ${icons[type] || icons.info}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    if (!document.getElementById('notification-style')) {
        const style = document.createElement('style');
        style.id = 'notification-style';
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    setTimeout(() => {
        notification.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

console.log('✓ Monitoring Logbook System Loaded');