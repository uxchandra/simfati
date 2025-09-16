@extends('layouts.app')

{{-- Include modal detail --}}
@include('general-checkup.detail')

@section('content')
    <div class="section-header">
        <h1>Data General Checkup</h1>
        <div class="ml-auto">
            <a href="{{ route('general-checkup.create') }}" class="btn btn-primary mr-2">
                <i class="fa fa-plus"></i> Tambah Checkup
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form id="filter_form" class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label for="status_filter" class="form-label">Status</label>
                                <select class="form-control" id="status_filter" name="status">
                                    <option value="all">Semua Status</option>
                                    <option value="good">Good</option>
                                    <option value="problem">Problem</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label for="shift_filter" class="form-label">Shift</label>
                                <select class="form-control" id="shift_filter" name="shift">
                                    <option value="all">Semua Shift</option>
                                    <option value="morning">Morning</option>
                                    <option value="afternoon">Afternoon</option>
                                    <option value="night">Night</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-info btn-block" id="apply_filter">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="display" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    {{-- <th>Checkup Code</th> --}}
                                    <th>Tanggal</th>
                                    <th>Item Code</th>                                    
                                    <th>Item Name</th>
                                    <th>Type</th>
                                    <th>PIC</th>
                                    <th>Shift</th>
                                    <th>Status</th>
                                    {{-- <th>Notes</th> --}}
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Datatables Jquery -->
    <script>
        $(document).ready(function() {
            $('#table_id').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                order: [[5, 'desc']] // Sort by date descending
            });
            
            // Load all data initially (no date filter)
            loadCheckupData();
        });

        function loadCheckupData() {
            let params = {};
            
            // Only add parameters if they have values
            if ($('#start_date').val()) {
                params.start_date = $('#start_date').val();
            }
            if ($('#end_date').val()) {
                params.end_date = $('#end_date').val();
            }
            if ($('#status_filter').val() && $('#status_filter').val() !== 'all') {
                params.status = $('#status_filter').val();
            }
            if ($('#shift_filter').val() && $('#shift_filter').val() !== 'all') {
                params.shift = $('#shift_filter').val();
            }

            $.ajax({
                url: "/general-checkup/get-data",
                type: "GET",
                dataType: 'JSON',
                data: params,
                success: function(response) {
                    let counter = 1;
                    $('#table_id').DataTable().clear();
                    
                    $.each(response.data, function(key, value) {
                        let statusClass = getStatusClass(value.overall_status);
                        let typeClass = value.item_type === 'machine' ? 'badge-primary' : 'badge-success';
                        
                        let itemRow = `
                            <tr class="checkup-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                {{-- <td>${value.checkup_code}</td> --}}
                                <td>${value.checkup_date}</td>
                                <td>${value.item_code}</td>
                                <td>${value.item_name}</td>
                                <td><span class="badge ${typeClass}">${value.item_type.charAt(0).toUpperCase() + value.item_type.slice(1)}</span></td>
                                <td>${value.inspector}</td>
                                <td>${value.shift}</td>
                                <td><span class="badge ${statusClass}">${value.overall_status.charAt(0).toUpperCase() + value.overall_status.slice(1)}</span></td>
                                {{-- <td title="${value.notes}">${value.notes}</td> --}}
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-info btn-sm btn-detail" 
                                                data-id="${value.id}"
                                                title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-warning btn-sm btn-edit" 
                                                data-id="${value.id}"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm btn-delete" 
                                                data-id="${value.id}"
                                                data-code="${value.checkup_code}"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#table_id').DataTable().row.add($(itemRow)).draw(false);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error loading data:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data!'
                    });
                }
            });
        }

        function getStatusClass(status) {
            switch(status) {
                case 'good': return 'badge-success';
                case 'not_good': return 'badge-danger';
                case 'problem': return 'badge-danger';
                case 'critical': return 'badge-danger';
                default: return 'badge-secondary';
            }
        }

        // Event listener untuk filter
        $('#apply_filter').on('click', function() {
            loadCheckupData();
        });

        // Auto apply filter when date changes
        $('#start_date, #end_date').on('change', function() {
            if ($('#start_date').val() && $('#end_date').val()) {
                loadCheckupData();
            }
        });
    </script>

    <!-- Show Modal Detail -->
    <script>
        $('body').on('click', '.btn-detail', function() {
            let id = $(this).data('id');

            $.ajax({
                url: `/general-checkup/detail/${id}`,
                type: "GET",
                dataType: 'JSON',
                beforeSend: function() {
                    $('#modal_detail_body').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                    $('#modal_detail').modal('show');
                },
                success: function(response) {
                    let data = response.data;
                    let detailsHtml = '';
                    
                    if (data.details && data.details.length > 0) {
                        $.each(data.details, function(key, detail) {
                            let standardsHtml = '';
                            if (detail.standards && detail.standards.length > 0) {
                                $.each(detail.standards, function(index, standard) {
                                    let resultClass = standard.result === 'OK' ? 'text-success' : 'text-danger';
                                    standardsHtml += `
                                        <tr>
                                            <td>${standard.standard_name}</td>
                                            <td><span class="${resultClass}"><strong>${standard.result}</strong></span></td>
                                            <td>${standard.notes || '-'}</td>
                                        </tr>
                                    `;
                                });
                            } else {
                                standardsHtml = '<tr><td colspan="3" class="text-muted text-center"><em>Belum ada standard</em></td></tr>';
                            }
                            
                            let statusClass = getStatusClass(detail.item_status.toLowerCase().replace(' ', '_'));
                            
                            detailsHtml += `
                                <div class="mb-4">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-wrench"></i> ${detail.check_item_name}
                                        <span class="badge ${statusClass} ml-2">${detail.item_status}</span>
                                    </h6>
                                    
                                    ${detail.maintenance_notes ? `
                                        <div class="alert alert-info">
                                            <strong>Maintenance Notes:</strong> ${detail.maintenance_notes}
                                        </div>
                                    ` : ''}
                                    
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Standard</th>
                                                <th width="80">Result</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${standardsHtml}
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        });
                    } else {
                        detailsHtml = '<p class="text-muted"><em>Belum ada detail checkup</em></p>';
                    }

                    // Photos section
                    let photosHtml = '';
                    if (data.photos && data.photos.length > 0) {
                        photosHtml = '<div class="row">';
                        $.each(data.photos, function(key, photo) {
                            photosHtml += `
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <img src="${photo.photo_url}" 
                                             class="card-img-top photo-thumbnail" 
                                             style="height: 200px; object-fit: cover; cursor: pointer;" 
                                             alt="Checkup Photo"
                                             data-photo-url="${photo.photo_url}"
                                             data-photo-desc="${photo.photo_description}"
                                             data-photo-date="${photo.uploaded_at}">
                                        <div class="card-body p-2">
                                            <p class="card-text small">${photo.photo_description}</p>
                                            <small class="text-muted">${photo.uploaded_at}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        photosHtml += '</div>';
                    } else {
                        photosHtml = '<p class="text-muted"><em>Tidak ada foto dokumentasi</em></p>';
                    }

                    let modalContent = `
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Checkup Code:</strong> ${data.checkup_code}</p>
                                                <p><strong>Type:</strong> ${data.item_type}</p>
                                                <p><strong>Item:</strong> ${data.item_code} - ${data.item_name}</p>
                                                <p><strong>Tanggal:</strong> ${data.checkup_date}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Inspector:</strong> ${data.inspector}</p>
                                                <p><strong>Shift:</strong> ${data.shift}</p>
                                                <p><strong>Status:</strong> <span class="badge ${getStatusClass(data.overall_status.toLowerCase())}">${data.overall_status}</span></p>
                                                <p><strong>Created by:</strong> ${data.created_by} <small>(${data.created_at})</small></p>
                                            </div>
                                        </div>
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6><i class="fas fa-list-check"></i> Detail Checkup</h6>
                                    </div>
                                    <div class="card-body">
                                        ${detailsHtml}
                                    </div>
                                </div>
                            </div>
                        </div>

                        ${data.notes ? `<div class="alert alert-info"><strong>Notes:</strong> ${data.notes}</div>` : ''}
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-0">
                                    <div class="card-header">
                                        <h6><i class="fas fa-camera"></i> Foto Dokumentasi</h6>
                                    </div>
                                    <div class="card-body">
                                        ${photosHtml}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#modal_detail_title').html(`</i> Informasi Checkup`);
                    $('#modal_detail_body').html(modalContent);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading detail:', error);
                    $('#modal_detail_body').html('<div class="alert alert-danger">Gagal memuat detail data!</div>');
                }
            });
        });
    </script>

    <!-- Edit & Delete Actions -->
    <script>
        // Edit button
        $('body').on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            window.location.href = `/general-checkup/${id}/edit`;
        });

        // Delete button
        $('body').on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            let code = $(this).data('code');
            let token = $("meta[name='csrf-token']").attr("content");

            Swal.fire({
                title: 'Apakah Kamu Yakin?',
                text: `Ingin menghapus checkup "${code}"? Data yang dihapus tidak dapat dikembalikan!`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'TIDAK',
                confirmButtonText: 'YA, HAPUS!',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/general-checkup/${id}`,
                        type: "DELETE",
                        cache: false,
                        data: {
                            "_token": token
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Menghapus...',
                                text: 'Sedang menghapus data',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                willOpen: () => {
                                    Swal.showLoading()
                                }
                            });
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                showConfirmButton: true,
                                timer: 3000
                            });
                            
                            loadCheckupData();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data!'
                            });
                        }
                    });
                }
            });
        });

        // Photo click handler for SweetAlert2 popup
        $('body').on('click', '.photo-thumbnail', function() {
            let photoUrl = $(this).data('photo-url');
            let photoDesc = $(this).data('photo-desc');
            let photoDate = $(this).data('photo-date');
            
            Swal.fire({
                title: 'Foto Dokumentasi',
                html: `
                    <div class="text-center">
                        <img src="${photoUrl}" class="img-fluid" style="max-width: 100%; max-height: 70vh; object-fit: contain;" alt="Checkup Photo">
                        <div class="mt-3">
                            <p class="text-muted small">${photoDesc}</p>
                            <small class="text-muted">${photoDate}</small>
                        </div>
                    </div>
                `,
                showCloseButton: true,
                showConfirmButton: false,
                width: 'auto',
                padding: '20px',
                customClass: {
                    popup: 'swal2-photo-popup'
                }
            });
        });
    </script>

    <style>
        .swal2-photo-popup {
            max-width: 90vw !important;
        }
        .photo-thumbnail:hover {
            opacity: 0.8;
            transform: scale(1.02);
            transition: all 0.2s ease;
        }
    </style>
@endsection