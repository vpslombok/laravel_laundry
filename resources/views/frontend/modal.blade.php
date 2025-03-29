<div class="modal_status">
    <div class="modal_window">
        <div class="close_btn" onclick="close_dlgs()">
            <button type="button" class="btn-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <h4 class="modal_header">Detail Pesanan</h4>
        <div class="modal_content">
            <div class="customer_info">
                <div class="info_card">
                    <div class="info_row">
                        <span class="text">Nomor Resi</span>
                        <span class="font" id="no_resi">-</span>
                    </div>
                    <div class="info_row">
                        <span class="text">Nama Pelanggan</span>
                        <span class="font" id="customer">-</span>
                    </div>
                    <div class="info_row">
                        <span class="text">Estimasi Selesai</span>
                        <span class="font" id="estimasi_selesai">-</span>
                    </div>
                    <div class="info_row status">
                        <span class="text">Status</span>
                        <span class="status_badge" id="status_order">-</span>
                    </div>
                </div>
            </div>

            <div class="timeline_section">
                <h3 class="timeline_title">Alur Proses</h3>
                <div class="timeline">
                    <div class="timeline_step" data-status="received">
                        <div class="step_icon">1</div>
                        <div class="step_content">
                            <h4>Cucian Diterima Oleh <span id="user">-</span></h4>
                            <h4 class="step_date" id="tgl_transaksi">-</h4>
                        </div>
                    </div>
                    <div class="timeline_step" data-status="washing">
                        <div class="step_icon">2</div>
                        <div class="step_content">
                            <h4>Proses Pencucian</h4>
                        </div>
                    </div>
                    <div class="timeline_step" data-status="ready">
                        <div class="step_icon">3</div>
                        <div class="step_content">
                            <h4>Siap Diambil</h4>
                        </div>
                    </div>
                    <div class="timeline_step" data-status="completed">
                        <div class="step_icon">4</div>
                        <div class="step_content">
                            <div class="step_header">
                                <h4>Sudah Diambil</h4>
                                <h4 class="step_date" id="tgl_ambil">-</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal_footer">
            <button class="btn btn_primary" onclick="close_dlgs()">Tutup</button>
        </div>
    </div>
</div>

<style>
    .modal_status {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 99999;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        animation: fadeIn 0.3s ease;
    }

    .modal_window {
        position: relative;
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        overflow-y: auto;
        margin: auto;
        animation: slideUp 0.3s ease;
    }

    .close_btn {
        position: absolute;
        right: 16px;
        top: 16px;
        padding: 0;
        background: none;
        border: none;
        cursor: pointer;
        z-index: 10;
    }

    .btn-close {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: transparent;
        border: none;
        padding: 0;
        transition: all 0.2s ease;
    }
    .modal_header {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 20px 0;
        margin-top: auto;
        text-align: center;
        padding: 0 20px; /* Menambahkan padding agar posisi tidak terlalu memepet samping */
    }

    .btn-close:hover {
        background-color: rgba(231, 76, 60, 0.1);
    }

    .btn-close svg {
        width: 20px;
        height: 20px;
        color: #7f8c8d;
        transition: color 0.2s ease;
        transform: translateY(-5px);
    }

    .btn-close:hover svg {
        color: #e74c3c;
    }

    .step_header {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .step_date {
        margin-top: 4px;
        color: #666;
        font-size: 13px;
        font-style: italic;
        padding-left: 2px;
        line-height: 1.4;
    }

    .step_date {
        animation: fadeIn 0.5s ease;
    }

    .title {
        font-size: 22px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .modal_content {
        padding: 20px;
    }

    .info_card {
        background: #f9f9f9;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .info_row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .info_row:last-child {
        margin-bottom: 0;
    }

    .info_row.status {
        align-items: center;
    }

    .text {
        color: #34495e !important;
        font-weight: 500;
        font-size: 15px;
    }

    .font {
        color: #7f8c8d !important;
        font-size: 15px;
    }

    .status_badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        background: #3498db;
        color: white;
    }

    .timeline_section {
        margin-top: 20px;
    }

    .timeline_title {
        font-size: 18px;
        color: #2c3e50;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline_step {
        position: relative;
        margin-bottom: 25px;
        display: flex;
    }

    .timeline_step:last-child {
        margin-bottom: 0;
    }

    .step_icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e0e0e0;
        color: #95a5a6;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        margin-right: 15px;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .step_icon.completed {
        background: #2ecc71;
        color: white;
    }

    .step_icon.active {
        background: #3498db;
        color: white;
        animation: pulse 1.5s infinite;
    }

    .step_content {
        flex-grow: 1;
    }

    .step_content h4 {
        margin: 0 0 5px 0;
        font-size: 16px;
        color: #2c3e50;
    }

    .step_content p {
        margin: 0;
        font-size: 14px;
        color: #7f8c8d;
    }

    .modal_footer {
        padding: 15px 20px;
        border-top: 1px solid #f0f0f0;
        text-align: right;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn_primary {
        background: #3498db;
        color: white;
    }

    .btn_primary:hover {
        background: #2980b9;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(52, 152, 219, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
        }
    }

    @media (max-width: 480px) {
        .modal_window {
            max-width: 100%;
            border-radius: 0;
        }

        .info_row {
            flex-direction: column;
        }

        .text,
        .font {
            margin-bottom: 5px;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Reset storage
        localStorage.removeItem('modalOpen');
        sessionStorage.removeItem('modalOpen');

        // 2. Hapus parameter URL
        if (window.location.search.includes('modal=open')) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // 3. Force hide modal
        const modal = document.querySelector('.modal_status');
        if (modal) {
            modal.style.display = 'none';
        }
    });

    function close_dlgs() {
        const modal = document.querySelector('.modal_status');
        if (modal) {
            modal.style.display = 'none';
        }
    }
</script>