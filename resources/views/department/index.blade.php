@extends('layouts.app')

@include('department.create')
@include('department.edit')

@section('content')
    <div class="section-header">
        <h1>Data Department</h1>
        <div class="ml-auto">
            <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_department"><i class="fa fa-plus"></i>
                Department</a>
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
                                    <th>Kode</th>
                                    <th>Nama Department</th>
                                    <th>Opsi</th>
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
                paging: true
            });
            $.ajax({
                url: "/department/get-data",
                type: "GET",
                dataType: 'JSON',
                success: function(response) {
                    let counter = 1;
                    $('#table_id').DataTable().clear();
                    $.each(response.data, function(key, value) {
                        let department = `
                <tr class="barang-row" id="index_${value.id}">
                    <td>${counter++}</td>   
                    <td>${value.kode}</td>
                    <td>${value.nama}</td>
                    <td>
                        <div class="d-flex">
                            <a href="javascript:void(0)" id="button_edit_nama" data-id="${value.id}" class="btn btn-icon btn-warning btn-lg mr-2"><i class="far fa-edit"></i></a>
                            <a href="javascript:void(0)" id="button_hapus_nama" data-id="${value.id}" class="btn btn-icon btn-danger btn-lg"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
            `;
                        $('#table_id').DataTable().row.add($(department)).draw(false);
                    });
                }
            });
        });
    </script>

    <!-- Show Modal Tambah Department -->
    <script>
        $('body').on('click', '#button_tambah_department', function() {
            $('#modal_tambah_department').modal('show');
        });

        $('#store').click(function(e) {
            e.preventDefault();

            let kode = $('#kode').val();
            let nama = $('#nama').val();
            let token = $("meta[name='csrf-token']").attr("content");

            let formData = new FormData();
            formData.append('kode', kode);
            formData.append('nama', nama);
            formData.append('_token', token);

            $.ajax({
                url: '/department',
                type: "POST",
                cache: false,
                data: formData,
                contentType: false,
                processData: false,

                success: function(response) {
                    Swal.fire({
                        type: 'success',
                        icon: 'success',
                        title: `${response.message}`,
                        showConfirmButton: false,
                        timer: 2000
                    });

                    $.ajax({
                        url: '/department/get-data',
                        type: "GET",
                        cache: false,
                        success: function(response) {
                            $('#table_id').DataTable().clear();
                            let counter = 1;
                            $.each(response.data, function(key, value) {
                                let department = `
                                <tr class="barang-row" id="index_${value.id}">
                                    <td>${counter++}</td>   
                                    <td>${value.kode}</td>
                                    <td>${value.nama}</td>
                                    <td>
                                        <a href="javascript:void(0)" id="button_edit_nama" data-id="${value.id}" class="btn btn-icon btn-warning btn-lg mb-2"><i class="far fa-edit"></i> </a>
                                        <a href="javascript:void(0)" id="button_hapus_nama" data-id="${value.id}" class="btn btn-icon btn-danger btn-lg mb-2"><i class="fas fa-trash"></i> </a>
                                    </td>
                                </tr>
                             `;
                                $('#table_id').DataTable().row.add($(department)).draw(false);
                            });

                            $('#kode').val('');
                            $('#nama').val('');
                            $('#modal_tambah_department').modal('hide');

                            let table = $('#table_id').DataTable();
                            table.draw(); // Memperbarui DataTables
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    })
                },

                error: function(error) {
                    if (error.responseJSON) {
                        if (error.responseJSON.kode) {
                            $('#alert-kode').removeClass('d-none').addClass('d-block');
                            $('#alert-kode').html(error.responseJSON.kode[0]);
                        }
                        if (error.responseJSON.nama) {
                            $('#alert-nama').removeClass('d-none').addClass('d-block');
                            $('#alert-nama').html(error.responseJSON.nama[0]);
                        }
                    }
                }
            });
        });
    </script>

    <!-- Edit Data Department -->
    <script>
        // Show modal edit
        $('body').on('click', '#button_edit_nama', function() {
            let department_id = $(this).data('id');

            $.ajax({
                url: `/department/${department_id}/edit`,
                type: "GET",
                cache: false,
                success: function(response) {
                    $('#department_id').val(response.data.id);
                    $('#edit_kode').val(response.data.kode);
                    $('#edit_nama').val(response.data.nama);

                    $('#modal_edit_nama').modal('show');
                }
            });
        });

        // Proses Update Data
        $('#update').click(function(e) {
            e.preventDefault();

            let department_id = $('#department_id').val();
            let kode = $('#edit_kode').val();
            let nama = $('#edit_nama').val();
            let token = $("meta[name='csrf-token']").attr('content');

            let formData = new FormData();
            formData.append('kode', kode);
            formData.append('nama', nama);
            formData.append('_token', token);
            formData.append('_method', 'PUT');

            $.ajax({
                url: `/department/${department_id}`,
                type: "POST",
                cache: false,
                data: formData,
                contentType: false,
                processData: false,

                success: function(response) {
                    Swal.fire({
                        type: 'success',
                        icon: 'success',
                        title: `${response.message}`,
                        showConfirmButton: false,
                        timer: 2000
                    });

                    let row = $(`#index_${response.data.id}`);
                    let rowData = row.find('td');
                    rowData.eq(1).text(response.data.kode);
                    rowData.eq(2).text(response.data.nama);

                    $('#modal_edit_nama').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON) {
                        if (error.responseJSON.kode) {
                            $('#alert-kode').removeClass('d-none').addClass('d-block');
                            $('#alert-kode').html(error.responseJSON.kode[0]);
                        }
                        if (error.responseJSON.nama) {
                            $('#alert-nama').removeClass('d-none').addClass('d-block');
                            $('#alert-nama').html(error.responseJSON.nama[0]);
                        }
                    }
                }
            });
        });
    </script>

    <!-- Hapus Data Department -->
    <script>
        $('body').on('click', '#button_hapus_nama', function() {
            let department_id = $(this).data('id');
            let token = $("meta[name='csrf-token']").attr("content");

            Swal.fire({
                title: 'Apakah Kamu Yakin?',
                text: "ingin menghapus data ini !",
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'TIDAK',
                confirmButtonText: 'YA, HAPUS!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/department/${department_id}`,
                        type: "DELETE",
                        cache: false,
                        data: {
                            "_token": token
                        },
                        success: function(response) {
                            Swal.fire({
                                type: 'success',
                                icon: 'success',
                                title: `${response.message}`,
                                showConfirmButton: false,
                                timer: 2000
                            });
                            $('#table_id').DataTable().clear().draw();

                            $.ajax({
                                url: "/department/get-data",
                                type: "GET",
                                dataType: 'JSON',
                                success: function(response) {
                                    let counter = 1;
                                    $('#table_id').DataTable().clear();
                                    $.each(response.data, function(key, value) {
                                        let department = `
                                        <tr class="barang-row" id="index_${value.id}">
                                            <td>${counter++}</td>   
                                            <td>${value.kode}</td>
                                            <td>${value.nama}</td>
                                            <td>
                                                <a href="javascript:void(0)" id="button_edit_nama" data-id="${value.id}" class="btn btn-icon btn-warning btn-lg mb-2"><i class="far fa-edit"></i> </a>
                                                <a href="javascript:void(0)" id="button_hapus_nama" data-id="${value.id}" class="btn btn-icon btn-danger btn-lg mb-2"><i class="fas fa-trash"></i> </a>
                                            </td>
                                        </tr>
                                    `;
                                        $('#table_id').DataTable().row.add($(department)).draw(false);
                                    });
                                }
                            });
                        }
                    })
                }
            });
        });
    </script>
@endsection