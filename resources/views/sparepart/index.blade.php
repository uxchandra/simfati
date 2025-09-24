@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Data Sparepart</h1>
    {{-- <div class="ml-auto">
        <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_part"><i class="fa fa-plus"></i>
            sparepart</a>
    </div> --}}
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- DataTable Controls -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <label class="mr-2 mb-0">Show</label>
                                <select class="form-control form-control-sm w-auto mr-2" id="per-page-select">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                </select>
                                <label class="mb-0">entries</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <div class="input-group w-50">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="search-input" placeholder="Cari barang...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Data -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="sparepart-table">
                            <thead>
                                <tr>
                                    <th style="color: #212121; background-color: white;">No</th>
                                    <th style="color: #212121; background-color: white;">Kode</th>
                                    <th style="color: #212121; background-color: white;">Nama Barang</th>
                                    <th style="color: #212121; background-color: white;">Stok</th>
                                    <th style="color: #212121; background-color: white;">UOM</th>
                                    <th style="color: #212121; background-color: white;">Harga</th>
                                </tr>
                            </thead>
                            <tbody id="barang-tbody">
                                <!-- Data akan dimuat via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let autoRefreshInterval;
    let isAutoRefreshEnabled = true;
    let refreshIntervalSeconds = 10; // 30 detik
    
    // Load data saat pertama kali
    loadTableData();
    
    // Start auto refresh
    startAutoRefresh();
    
    // Search functionality
    $('#search-input').on('keyup', function() {
        let query = $(this).val();
        
        if (query.length > 2 || query.length === 0) {
            if (query.length === 0) {
                loadTableData(); // Load all data
            } else {
                searchBarang(query);
            }
        }
    });
    
    // Per page change
    $('#per-page-select').on('change', function() {
        loadTableData();
    });
    
    function loadTableData() {
        let perPage = $('#per-page-select').val();
        
        $.ajax({
            url: '{{ route("sparepart.data") }}',
            method: 'GET',
            data: { 
                per_page: perPage,
                _t: new Date().getTime() // Prevent cache
            },
            success: function(response) {
                if (response.success) {
                    updateTable(response.data);
                }
            },
            error: function(xhr) {
                console.error('Error loading data:', xhr);
            }
        });
    }
    
    function searchBarang(query) {
        $.ajax({
            url: '{{ route("sparepart.search") }}',
            method: 'GET',
            data: { 
                q: query,
                _t: new Date().getTime()
            },
            success: function(response) {
                if (response.success) {
                    updateTable(response.data);
                }
            },
            error: function() {
                console.error('Error searching data');
            }
        });
    }
    
    function updateTable(data) {
        let tbody = $('#barang-tbody');
        tbody.empty();
        
        if (data.length > 0) {
            data.forEach(function(barang, index) {
                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${barang.kode || '-'}</td>
                        <td>${barang.nama_barang || '-'}</td>
                        <td>
                            <span class="badge ${barang.stok > 0 ? 'badge-success' : 'badge-danger'}">
                                ${barang.stok || 0}
                            </span>
                        </td>
                        <td><span class="badge badge-info">${barang.uom}</span></td>
                        <td>Rp ${new Intl.NumberFormat('id-ID').format(barang.price || 0)}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        } else {
            tbody.append(`
                <tr>
                    <td colspan="10" class="text-center text-muted">
                        <i class="fas fa-inbox"></i> Tidak ada data ditemukan
                    </td>
                </tr>
            `);
        }
    }
    
    function startAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
        
        autoRefreshInterval = setInterval(function() {
            if (isAutoRefreshEnabled) {
                // Hanya auto refresh jika tidak sedang search
                if ($('#search-input').val().length === 0) {
                    loadTableData();
                }
            }
        }, refreshIntervalSeconds * 1000);
    }
    
    // Global functions (remove unused ones)
    // Auto refresh functions removed - keeping functions clean and minimal
});
</script>
@endpush
@endsection