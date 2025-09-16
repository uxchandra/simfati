@extends('layouts.app')

@include('repair-request.detail')

@section('content')
    <div class="section-header">
        <h1>Request Perbaikan</h1>
        <div class="ml-auto d-flex align-items-center">
            <div class="form-group mb-0 mr-3">
                <select class="form-control" id="status_filter" name="status">
                    <option value="all">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="form-group mb-0 mr-3">
                <select class="form-control" id="type_filter" name="type">
                    <option value="all">Semua Type</option>
                    <option value="machine">Machine</option>
                    <option value="part">Part</option>
                </select>
            </div>
            <a href="{{ route('repair-request.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Request Perbaikan
            </a>
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
                                    <th width="50">Code</th>
                                    <th>Date</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Problem</th>
                                    <th>User</th>
                                    <th>Status</th>
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
                order: [[7, 'desc']] // Sort by date descending
            });
            
            loadRequestData();
        });

        function loadRequestData() {
            let params = {
                status: $('#status_filter').val(),
                type: $('#type_filter').val()
            };

            $.ajax({
                url: "/repair-request/get-data",
                type: "GET",
                dataType: 'JSON',
                data: params,
                success: function(response) {
                    let counter = 1;
                    $('#table_id').DataTable().clear();
                    
                    $.each(response.data, function(key, value) {
                        let statusClass = getStatusClass(value.status);
                        let typeClass = value.item_type === 'machine' ? 'badge-primary' : 'badge-success';
                        let photosDisplay = getPhotosDisplay(value.has_photos, value.photos_count);
                        
                        let itemRow = `
                            <tr class="request-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                <td><strong>${value.request_code}</strong></td>
                                <td>${value.requested_at}</td>
                                <td>${value.item_code}</td>
                                <td>${value.item_name}</td>
                                <td title="${value.full_problem_description}">${value.problem_description}</td>
                                <td>${value.requested_by}</td>
                                <td><span class="badge ${statusClass}">${value.status_label.text}</span></td>
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
                                                title="Edit Status">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm btn-delete" 
                                                data-id="${value.id}"
                                                data-code="${value.request_code}"
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
                case 'pending': return 'badge-warning';
                case 'in_progress': return 'badge-info';
                case 'completed': return 'badge-success';
                case 'cancelled': return 'badge-danger';
                default: return 'badge-secondary';
            }
        }

        function getPhotosDisplay(hasPhotos, photosCount) {
            if (hasPhotos) {
                return `<span class="text-success" title="${photosCount} foto"><i class="fas fa-camera"></i> ${photosCount}</span>`;
            } else {
                return `<span class="text-muted" title="Tidak ada foto"><i class="fas fa-camera"></i> -</span>`;
            }
        }

        // Auto apply filter when select changes
        $('#status_filter, #type_filter').on('change', function() {
            loadRequestData();
        });
    </script>

    <!-- Show Modal Detail -->
    <script>
        $('body').on('click', '.btn-detail', function() {
            let id = $(this).data('id');

            $.ajax({
                url: `/repair-request/detail/${id}`,
                type: "GET",
                dataType: 'JSON',
                beforeSend: function() {
                    $('#modal_detail_body').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                    $('#modal_detail').modal('show');
                },
                success: function(response) {
                    let data = response.data;
                    
                    // Photos section
                    let photosHtml = '';
                    if (data.photos && data.photos.length > 0) {
                        photosHtml = '<div class="row">';
                        $.each(data.photos, function(key, photo) {
                            photosHtml += `
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <img src="${photo.photo_url}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Problem Photo">
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
                                                <p><strong>Request Code:</strong> ${data.request_code}</p>
                                                <p><strong>Type:</strong> ${data.item_type.charAt(0).toUpperCase() + data.item_type.slice(1)}</p>
                                                <p><strong>Item:</strong> ${data.item_code} - ${data.item_name}</p>
                                                <p><strong>Status:</strong> <span class="badge ${getStatusClass(data.status)}">${data.status_label.text}</span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Requested By:</strong> ${data.requested_by}</p>
                                                <p><strong>Request Date:</strong> ${data.requested_at}</p>
                                            </div>
                                        </div>
                                        <div class="alert alert-info">
                                            <strong>Problem Description:</strong><br>
                                            ${data.problem_description}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
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

                    $('#modal_detail_title').html(`Detail Request: ${data.request_code}`);
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
            window.location.href = `/repair-request/${id}/edit`;
        });

        // Delete button
        $('body').on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            let code = $(this).data('code');
            let token = $("meta[name='csrf-token']").attr("content");

            Swal.fire({
                title: 'Apakah Kamu Yakin?',
                text: `Ingin menghapus request "${code}"? Data yang dihapus tidak dapat dikembalikan!`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'TIDAK',
                confirmButtonText: 'YA, HAPUS!',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/repair-request/${id}`,
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
                            
                            loadRequestData();
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
    </script>
@endsection