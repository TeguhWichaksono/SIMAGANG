/**
 * daftar_Bimbingan.js
 * JavaScript untuk Daftar Mahasiswa Bimbingan
 * 
 * Features:
 * - Search & filter mahasiswa
 * - View detail mahasiswa
 * - Export to Excel
 * - Modal management
 */

// ========================================
// FILTER & SEARCH
// ========================================
console.log('Mahasiswa data:', mahasiswa);
console.log('Foto profil path:', mahasiswa.foto_profil);
console.log('Full URL:', `../ROLE Mahasiswa/uploads/${mahasiswa.foto_profil}`);
function applyFilters() {
    const searchValue = document.getElementById('searchMahasiswa').value.toLowerCase();
    const filterKelompok = document.getElementById('filterKelompok').value;
    const filterStatus = document.getElementById('filterStatus').value;
    const filterMitra = document.getElementById('filterMitra').value;
    
    const table = document.getElementById('tableMahasiswa');
    const rows = table.querySelectorAll('tbody tr');
    
    let visibleCount = 0;
    
    rows.forEach((row, index) => {
        const nama = row.getAttribute('data-nama');
        const nim = row.getAttribute('data-nim');
        const kelompok = row.getAttribute('data-kelompok');
        const status = row.getAttribute('data-status');
        const mitra = row.getAttribute('data-mitra');
        
        let showRow = true;
        
        // Search filter (nama atau NIM)
        if (searchValue && !nama.includes(searchValue) && !nim.includes(searchValue)) {
            showRow = false;
        }
        
        // Kelompok filter
        if (filterKelompok && kelompok !== filterKelompok) {
            showRow = false;
        }
        
        // Status filter
        if (filterStatus && status !== filterStatus) {
            showRow = false;
        }
        
        // Mitra filter
        if (filterMitra && mitra !== filterMitra) {
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
    
    // Update showing count
    document.getElementById('showingCount').textContent = visibleCount;
    
    // Show empty state if no results
    const tableWrapper = table.closest('.table-wrapper');
    const paginationInfo = document.querySelector('.pagination-info');
    let emptyState = tableWrapper.nextElementSibling;
    
    if (emptyState && emptyState.classList.contains('pagination-info')) {
        emptyState = emptyState.nextElementSibling;
    }
    
    if (visibleCount === 0) {
        if (!emptyState || !emptyState.classList.contains('empty-state')) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.style.marginTop = '20px';
            emptyState.innerHTML = `
                <i class="fas fa-search"></i>
                <h3>Tidak Ada Hasil</h3>
                <p>Tidak ada mahasiswa yang sesuai dengan filter Anda</p>
            `;
            if (paginationInfo) {
                paginationInfo.parentNode.insertBefore(emptyState, paginationInfo.nextSibling);
            } else {
                tableWrapper.parentNode.appendChild(emptyState);
            }
        }
        tableWrapper.style.display = 'none';
        if (paginationInfo) paginationInfo.style.display = 'none';
        emptyState.style.display = 'block';
    } else {
        tableWrapper.style.display = 'block';
        if (paginationInfo) paginationInfo.style.display = 'block';
        if (emptyState && emptyState.classList.contains('empty-state')) {
            emptyState.style.display = 'none';
        }
    }
}

// ========================================
// RESET FILTERS
// ========================================
function resetFilters() {
    document.getElementById('searchMahasiswa').value = '';
    document.getElementById('filterKelompok').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterMitra').value = '';
    applyFilters();
}

// ========================================
// LIHAT DETAIL MAHASISWA
// ========================================
function lihatDetailMahasiswa(idMahasiswa) {
    const modal = document.getElementById('modalDetailMahasiswa');
    const modalBody = document.getElementById('detailMahasiswaContent');
    const subtitle = document.getElementById('detailMahasiswaSubtitle');
    
    // Show modal with loading
    modal.classList.add('show');
    modalBody.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Memuat data...</p>
        </div>
    `;
    
    // 👇 FIXED: URL ke ajax_handler.php
    const url = `ajax_handler.php?action=get_detail_mahasiswa_bimbingan&id_mahasiswa=${idMahasiswa}`;
    console.log('Fetching from:', url);
    
    // AJAX Request
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
                renderDetailMahasiswa(data.data);
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
                    <p>Gagal memuat data mahasiswa. Silakan coba lagi.</p>
                </div>
            `;
        });
}

// ========================================
// RENDER DETAIL MAHASISWA
// ========================================
function renderDetailMahasiswa(data) {
    const mahasiswa = data.mahasiswa;
    const logbook = data.logbook;
    
    const modalBody = document.getElementById('detailMahasiswaContent');
    const subtitle = document.getElementById('detailMahasiswaSubtitle');
    
    // Update subtitle
    subtitle.innerHTML = `
        <i class="fas fa-user"></i>
        ${mahasiswa.nim} - ${mahasiswa.prodi}
    `;
    
    // Status badge
    const statusMap = {
        'pra-magang': { class: 'warning', text: 'Pra-Magang', icon: 'fa-hourglass-start' },
        'magang_aktif': { class: 'success', text: 'Magang Aktif', icon: 'fa-briefcase' },
        'selesai': { class: 'info', text: 'Selesai', icon: 'fa-check-circle' }
    };
    
    const status = statusMap[mahasiswa.status_magang];
    
    // Build HTML
    let html = `
        <div style="display: flex; flex-direction: column; gap: 30px;">
            
            <!-- Profile Section -->
            <div style="display: flex; align-items: center; gap: 20px; padding: 20px; background: var(--gray-50); border-radius: 12px;">
                ${mahasiswa.foto_profil ? `
                    <img src="../ROLE Mahasiswa/uploads/${mahasiswa.foto_profil}" 
                         alt="${mahasiswa.nama_mahasiswa}"
                         style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                ` : `
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: 700;">
                        ${mahasiswa.nama_mahasiswa.substring(0, 2).toUpperCase()}
                    </div>
                `}
                <div style="flex: 1;">
                    <h3 style="font-size: 22px; font-weight: 700; color: var(--gray-800); margin-bottom: 8px;">
                        ${mahasiswa.nama_mahasiswa}
                        ${mahasiswa.peran_kelompok === 'ketua' ? '<span style="background: #fbbf24; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;"><i class="fas fa-crown"></i> Ketua</span>' : ''}
                    </h3>
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 6px; color: var(--gray-600);">
                            <i class="fas fa-id-card"></i>
                            <span>${mahasiswa.nim}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; color: var(--gray-600);">
                            <i class="fas fa-graduation-cap"></i>
                            <span>${mahasiswa.prodi}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; color: var(--gray-600);">
                            <i class="fas fa-calendar"></i>
                            <span>Angkatan ${mahasiswa.angkatan}</span>
                        </div>
                    </div>
                </div>
                <span class="status-badge ${status.class}" style="height: fit-content;">
                    <i class="fas ${status.icon}"></i>
                    ${status.text}
                </span>
            </div>
            
            <!-- Info Magang -->
            <div>
                <h4 style="font-size: 16px; font-weight: 700; color: var(--gray-800); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-briefcase" style="color: var(--primary-color);"></i>
                    Informasi Magang
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                    <div style="padding: 12px; background: var(--gray-50); border-radius: 8px;">
                        <small style="color: var(--gray-500); font-size: 12px;">Kelompok</small>
                        <div style="font-weight: 600; color: var(--gray-800); margin-top: 4px;">
                            <i class="fas fa-users" style="color: var(--primary-color); margin-right: 6px;"></i>
                            ${mahasiswa.nama_kelompok || '-'}
                        </div>
                    </div>
                    <div style="padding: 12px; background: var(--gray-50); border-radius: 8px;">
                        <small style="color: var(--gray-500); font-size: 12px;">Tempat Magang</small>
                        <div style="font-weight: 600; color: var(--gray-800); margin-top: 4px;">
                            ${mahasiswa.nama_mitra || '-'}
                        </div>
                    </div>
                    <div style="padding: 12px; background: var(--gray-50); border-radius: 8px;">
                        <small style="color: var(--gray-500); font-size: 12px;">Bidang</small>
                        <div style="font-weight: 600; color: var(--gray-800); margin-top: 4px;">
                            ${mahasiswa.bidang_mitra || '-'}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Kontak -->
            <div>
                <h4 style="font-size: 16px; font-weight: 700; color: var(--gray-800); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-address-book" style="color: var(--primary-color);"></i>
                    Informasi Kontak
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                    <div style="padding: 12px; background: var(--gray-50); border-radius: 8px;">
                        <small style="color: var(--gray-500); font-size: 12px;">Email</small>
                        <div style="font-weight: 600; color: var(--gray-800); margin-top: 4px;">
                            <a href="mailto:${mahasiswa.email}" style="color: var(--primary-color); text-decoration: none;">
                                <i class="fas fa-envelope" style="margin-right: 6px;"></i>
                                ${mahasiswa.email}
                            </a>
                        </div>
                    </div>
                    <div style="padding: 12px; background: var(--gray-50); border-radius: 8px;">
                        <small style="color: var(--gray-500); font-size: 12px;">WhatsApp</small>
                        <div style="font-weight: 600; color: var(--gray-800); margin-top: 4px;">
                            ${mahasiswa.kontak ? `
                                <a href="https://wa.me/${mahasiswa.kontak.replace(/[^0-9]/g, '')}" target="_blank" style="color: #25d366; text-decoration: none;">
                                    <i class="fab fa-whatsapp" style="margin-right: 6px;"></i>
                                    ${mahasiswa.kontak}
                                </a>
                            ` : '-'}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress Logbook -->
            <div>
                <h4 style="font-size: 16px; font-weight: 700; color: var(--gray-800); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-chart-line" style="color: var(--primary-color);"></i>
                    Progress Logbook
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div style="padding: 16px; background: var(--yellow-50); border-radius: 8px; text-align: center; border: 2px solid #fef3c7;">
                        <div style="font-size: 28px; font-weight: 700; color: var(--warning-color);">${logbook.pending}</div>
                        <small style="color: #92400e; font-weight: 600;"><i class="fas fa-clock"></i> Pending</small>
                    </div>
                    <div style="padding: 16px; background: var(--green-50); border-radius: 8px; text-align: center; border: 2px solid #dcfce7;">
                        <div style="font-size: 28px; font-weight: 700; color: var(--success-color);">${logbook.disetujui}</div>
                        <small style="color: #065f46; font-weight: 600;"><i class="fas fa-check-circle"></i> Disetujui</small>
                    </div>
                    <div style="padding: 16px; background: var(--red-50); border-radius: 8px; text-align: center; border: 2px solid #fee2e2;">
                        <div style="font-size: 28px; font-weight: 700; color: var(--danger-color);">${logbook.ditolak}</div>
                        <small style="color: #991b1b; font-weight: 600;"><i class="fas fa-times-circle"></i> Ditolak</small>
                    </div>
                    <div style="padding: 16px; background: var(--blue-50); border-radius: 8px; text-align: center; border: 2px solid #dbeafe;">
                        <div style="font-size: 28px; font-weight: 700; color: var(--primary-color);">${logbook.total}</div>
                        <small style="color: #1e40af; font-weight: 600;"><i class="fas fa-book"></i> Total</small>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div style="display: flex; gap: 12px; padding-top: 20px; border-top: 1px solid var(--gray-200);">
                <button onclick="window.location.href='index.php?page=monitoring'" 
                        style="flex: 1; padding: 12px; background: var(--primary-color); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-eye"></i>
                    Lihat Semua Logbook
                </button>
                ${mahasiswa.email ? `
                    <button onclick="window.location.href='mailto:${mahasiswa.email}'" 
                            style="flex: 1; padding: 12px; background: var(--gray-600); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fas fa-envelope"></i>
                        Kirim Email
                    </button>
                ` : ''}
            </div>
        </div>
    `;
    
    modalBody.innerHTML = html;
}

// ========================================
// EXPORT TO EXCEL
// ========================================
function exportToExcel() {
    // Simple client-side export using table data
    const table = document.getElementById('tableMahasiswa');
    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
    
    if (rows.length === 0) {
        alert('Tidak ada data untuk diekspor');
        return;
    }
    
    // Create CSV content
    let csv = 'No,Nama,NIM,Prodi,Kelompok,Tempat Magang,Status,Total Logbook,Pending,Disetujui,Ditolak\n';
    
    rows.forEach((row, index) => {
        const cells = row.querySelectorAll('td');
        const nama = cells[1].querySelector('.mahasiswa-details h4').textContent;
        const nim = cells[1].querySelector('.mahasiswa-details p').textContent;
        const prodi = cells[1].querySelector('.mahasiswa-details small').textContent;
        const kelompok = cells[2].textContent.trim().replace(/\s+/g, ' ');
        const mitra = cells[3].querySelector('strong')?.textContent || '-';
        const status = cells[4].textContent.trim();
        
        const progressItems = cells[5].querySelectorAll('.progress-item span');
        const pending = progressItems[0]?.textContent.replace(' Pending', '') || '0';
        const disetujui = progressItems[1]?.textContent.replace(' Disetujui', '') || '0';
        const ditolak = progressItems[2]?.textContent.replace(' Ditolak', '') || '0';
        const total = cells[5].querySelector('.progress-summary strong')?.textContent.replace(' logbook', '') || '0';
        
        csv += `${index + 1},"${nama}","${nim}","${prodi}","${kelompok}","${mitra}","${status}","${total}","${pending}","${disetujui}","${ditolak}"\n`;
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `Daftar_Bimbingan_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification('success', 'Data berhasil diekspor ke Excel');
}

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
// NOTIFICATION SYSTEM
// ========================================
function showNotification(type, message) {
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
    
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    setTimeout(() => {
        notification.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ========================================
// INITIALIZE
// ========================================
console.log('Daftar Bimbingan System Loaded ✓');