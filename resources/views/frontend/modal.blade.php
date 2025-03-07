<div class="modal_status">
    <div class="modal_window">
        {{-- <div class="title">Hasil Pencarian</div> --}}
        <div class="modal_content">
            <div class="info_row">
                <p class="text">Atas Nama</p>
                <p class="font" id="customer"></p>
            </div>
            <div class="info_row">
                <p class="text">Tanggal Transaksi</p>
                <p class="font" id="tgl_transaksi"></p>
            </div>
            <div class="info_row">
                <p class="text">Status</p>
                <p class="font" id="status_order"></p>
            </div>
        </div>
        <br />
        <button class="btn btn-danger btn-block" onclick="close_dlgs()">Close</button>
    </div>
</div>

<style>
    .modal_window>.title {
        font-size: 24px;
        /* Besarkan ukuran font */
        font-weight: bold;
        color: black;
    }

    .text {
        color: black !important;
        font-weight: bold;
    }

    .font {
        color: slategrey !important;
    }

    .modal_window {
        position: relative;
        width: 100%;
        padding: 20px;
        margin-top: 20px;
        background-color: white;
        border-radius: 5px;
        box-sizing: border-box;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
    }

    .modal_status {
        display: none;
        position: center;
        top: 0px;
        left: 0px;
        right: 0px;
        bottom: 0px;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 99999;
        border-radius: 5px;
    }

    .modal_content {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .info_row {
        display: flex;
        flex-direction: column;
    }
</style>