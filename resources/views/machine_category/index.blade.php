@extends('layouts.app')

@include('machine_category.create')
@include('machine_category.edit')

@section('content')
    <div class="section-header">
        <h1>Data Inventaris Category</h1>
        <div class="ml-auto">
            <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_category"><i class="fa fa-plus"></i>
                Category</a>
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
                                    <th>Name</th>
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
            loadMachineData();
        });

        function loadMachineData() {
            $.ajax({
                url: "/machine_category/get-data",
                type: "GET",
                dataType: 'JSON',
                success: function(response) {
                    let counter = 1;
                    $('#table_id').DataTable().clear();
                    $.each(response.data, function(key, value) {
                        
                        let machineRow = `
                            <tr class="machine-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                <td>${value.name}</td>
                                <td>
                                    <div class="d-flex">
                                        <a href="javascript:void(0)" id="button_edit_category" data-id="${value.id}" class="btn btn-icon btn-warning btn-lg mr-2" style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;"><i class="far fa-edit"></i></a>
                                        <a href="javascript:void(0)" id="button_hapus_machine" data-id="${value.id}" class="btn btn-icon btn-danger btn-lg" style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#table_id').DataTable().row.add($(machineRow)).draw(false);
                    });
                }
            });
        }
    </script>

    <!-- Show Modal Tambah Machine -->
    <script>
        $('body').on('click', '#button_tambah_category', function() {
            $('#modal_tambah_category').modal('show');
        });

        $('#store').click(function(e) {
            e.preventDefault();

            let name = $('#name').val();
            let token = $("meta[name='csrf-token']").attr("content");

            let formData = new FormData();
            formData.append('name', name);
            formData.append('_token', token);

            $.ajax({
                url: '/machine_category',
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

                    loadMachineData();
                    
                    $('#name').val('');
                    $('#modal_tambah_category').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON) {
                        if (error.responseJSON.name && error.responseJSON.name[0]) {
                            $('#alert-machine_name').removeClass('d-none');
                            $('#alert-machine_name').addClass('d-block');
                            $('#alert-machine_name').html(error.responseJSON.name[0]);
                        }
                    }
                }
            });
        });
    </script>

    <!-- Edit Data Machine -->
    <script>
        //Show modal edit
        $('body').on('click', '#button_edit_category', function() {
            let machine_id = $(this).data('id');

            $.ajax({
                url: `/machine_category/${machine_id}/edit`,
                type: "GET",
                cache: false,
                success: function(response) {
                    $('#category_id').val(response.data.id);
                    $('#edit_name').val(response.data.name);

                    $('#modal_edit_category').modal('show');
                }
            });
        });

        // Proses Update Data
        $('#update').click(function(e) {
            e.preventDefault();

            let category_id = $('#category_id').val();
            let name = $('#edit_name').val();
            let token = $("meta[name='csrf-token']").attr('content');

            let formData = new FormData();
            formData.append('name', name);
            formData.append('_token', token);
            formData.append('_method', 'PUT');

            $.ajax({
                url: `/machine_category/${category_id}`,
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
                        timer: 3000
                    });

                    let row = $(`#index_${response.data.id}`);
                    let rowData = row.find('td');
                    
                    rowData.eq(1).text(response.data.name);

                    $('#modal_edit_category').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON) {
                        if (error.responseJSON.name && error.responseJSON.name[0]) {
                            $('#alert-edit-machine_name').removeClass('d-none');
                            $('#alert-edit-machine_name').addClass('d-block');
                            $('#alert-edit-machine_name').html(error.responseJSON.name[0]);
                        }
                    }
                }
            });
        });
    </script>

    <!-- Hapus Data Machine -->
    <script>
        $('body').on('click', '#button_hapus_machine', function() {
            let machine_id = $(this).data('id');
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
                        url: `/machine_category/${machine_id}`,
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
                                timer: 3000
                            });
                            
                            loadMachineData();
                        }
                    })
                }
            });
        });
    </script>
@endsection