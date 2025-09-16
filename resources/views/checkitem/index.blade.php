@extends('layouts.app')
@include('checkitem.detail')

@section('content')
    <div class="section-header">
        <h1>Data Indikator Checksheet</h1>
        <div class="ml-auto">
            <a href="{{ route('checkitem.create') }}" class="btn btn-primary mr-2">
                <i class="fa fa-plus"></i> Tambah
            </a>
            <div class="form-group mb-0 d-inline-block">
                <select id="type_filter" class="form-control" style="min-width: 120px;">
                    <option value="machine">Machine</option>
                    <option value="part">Part</option>
                    <option value="all" selected>All</option>
                </select>
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
                                    <th id="code_header">Machine Code</th>
                                    <th id="name_header">Machine Name</th>
                                    <th>Type</th>
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
                info: true
            });
            loadCheckItemData();
        });

        function loadCheckItemData() {
            let type = $('#type_filter').val();
            
            // Update header kolom berdasarkan filter
            updateTableHeaders(type);
            
            $.ajax({
                url: "/checkitem/get-data",
                type: "GET",
                dataType: 'JSON',
                data: { type: type },
                success: function(response) {
                    let counter = 1;
                    $('#table_id').DataTable().clear();
                    
                    $.each(response.data, function(key, value) {
                        let typeClass = value.type === 'machine' ? 'badge-primary' : 'badge-success';
                        let typeText = value.type.charAt(0).toUpperCase() + value.type.slice(1);
                        
                        // Hide type column jika bukan filter 'all'
                        let typeColumn = response.type === 'all' ? 
                            `<td><span class="badge ${typeClass}">${typeText}</span></td>` : 
                            '<td style="display: none;"></td>';
                        
                        let itemRow = `
                            <tr class="item-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                <td>${value.code}</td>
                                <td>${value.name}</td>
                                ${typeColumn}
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-info btn-sm" 
                                                id="button_detail" 
                                                data-type="${value.type}" 
                                                data-id="${value.id}"
                                                title="Detail">
                                            <i class="fas fa-eye"></i> 
                                        </button>
                                        <button type="button" 
                                                class="btn btn-warning btn-sm" 
                                                id="button_edit" 
                                                data-type="${value.type}" 
                                                data-id="${value.id}"
                                                title="Edit">
                                            <i class="fas fa-edit"></i> 
                                        </button>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm" 
                                                id="button_delete" 
                                                data-type="${value.type}" 
                                                data-id="${value.id}"
                                                data-name="${value.name}"
                                                title="Delete">
                                            <i class="fas fa-trash"></i> 
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#table_id').DataTable().row.add($(itemRow)).draw(false);
                    });
                    
                    // Show/hide type column berdasarkan filter
                    if (response.type === 'all') {
                        $('#table_id thead th:nth-child(4)').show();
                        $('#table_id tbody td:nth-child(4)').show();
                    } else {
                        $('#table_id thead th:nth-child(4)').hide();
                        $('#table_id tbody td:nth-child(4)').hide();
                    }
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

        function updateTableHeaders(type) {
            if (type === 'machine') {
                $('#code_header').text('Machine Code');
                $('#name_header').text('Machine Name');
            } else if (type === 'part') {
                $('#code_header').text('Part Code');
                $('#name_header').text('Part Name');
            } else {
                $('#code_header').text('Code');
                $('#name_header').text('Name');
            }
        }

        // Event listener untuk filter change
        $('#type_filter').on('change', function() {
            loadCheckItemData();
        });
    </script>

    <!-- Show Modal Edit -->
    <script>
        $('body').on('click', '#button_edit', function() {
            let type = $(this).data('type');
            let id = $(this).data('id');

            // Redirect ke halaman edit
            window.location.href = `/checkitem/edit/${type}/${id}`;
        });
    </script>

    <!-- Delete Data -->
    <script>
        $('body').on('click', '#button_delete', function() {
            let type = $(this).data('type');
            let id = $(this).data('id');
            let name = $(this).data('name');
            let token = $("meta[name='csrf-token']").attr("content");

            Swal.fire({
                title: 'Apakah Kamu Yakin?',
                text: `Ingin menghapus semua check items untuk "${name}"? Data yang dihapus tidak dapat dikembalikan!`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'TIDAK',
                confirmButtonText: 'YA, HAPUS!',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/checkitem/${type}/${id}`,
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
                            
                            loadCheckItemData();
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

    <!-- Show Modal Detail -->
    <script>
        $('body').on('click', '#button_detail', function() {
            let type = $(this).data('type');
            let id = $(this).data('id');

            $.ajax({
                url: `/checkitem/detail/${type}/${id}`,
                type: "GET",
                dataType: 'JSON',
                beforeSend: function() {
                    // Show loading
                    $('#modal_detail_body').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                    $('#modal_detail').modal('show');
                },
                success: function(response) {
                    let data = response.data;
                    let checkItemsHtml = '';
                    
                    if (data.check_items && data.check_items.length > 0) {
                        $.each(data.check_items, function(key, item) {
                            let standardsHtml = '';
                            if (item.standards && item.standards.length > 0) {
                                $.each(item.standards, function(index, standard) {
                                    standardsHtml += `<li class="text-dark">${standard}</li>`;
                                });
                            } else {
                                standardsHtml = '<li class="text-dark"><em>Belum ada standar</em></li>';
                            }
                            
                            checkItemsHtml += `
                                <div class="mb-3">
                                    <h6 class="text-primary"><i class="fas fa-wrench"></i> ${item.item_name}</h6>
                                    <ul class="ml-3">
                                        ${standardsHtml}
                                    </ul>
                                </div>
                            `;
                        });
                    } else {
                        checkItemsHtml = '<p class="text-dark"><em>Belum ada check item</em></p>';
                    }

                    let modalContent = `
                        
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        ${checkItemsHtml}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#modal_detail_title').html(` Detail Indikator ${data.type}: ${data.code}`);
                    $('#modal_detail_body').html(modalContent);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading detail:', error);
                    $('#modal_detail_body').html('<div class="alert alert-danger">Gagal memuat detail data!</div>');
                }
            });
        });
    </script>
@endsection