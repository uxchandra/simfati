@extends('layouts.app')

@include('machine.create')
@include('machine.edit')

@section('content')
    <div class="section-header">
        <h1>Data Machine</h1>
        <div class="ml-auto">
            <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_machine"><i class="fa fa-plus"></i>
                Machine</a>
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
                                    <th>Machine Code</th>
                                    <th>Machine Name</th>
                                    <th>Section</th>
                                    <th>Status</th>
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
                url: "/machine/get-data",
                type: "GET",
                dataType: 'JSON',
                success: function(response) {
                    let counter = 1;
                    $('#table_id').DataTable().clear();
                    $.each(response.data, function(key, value) {
                        let badgeClass = '';
                        switch(value.status) {
                            case 'active':
                                badgeClass = 'badge badge-success';
                                break;
                            case 'inactive':
                                badgeClass = 'badge badge-secondary';
                                break;
                            case 'maintenance':
                                badgeClass = 'badge badge-warning';
                                break;
                            default:
                                badgeClass = 'badge badge-primary';
                        }
                        
                        let machineRow = `
                            <tr class="machine-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                <td>${value.machine_code}</td>
                                <td>${value.machine_name}</td>
                                <td>${value.section}</td>
                                <td><span class="${badgeClass}">${value.status.charAt(0).toUpperCase() + value.status.slice(1)}</span></td>
                                <td>
                                    <div class="d-flex">
                                        <a href="javascript:void(0)" id="button_edit_machine" data-id="${value.id}" class="btn btn-icon btn-warning btn-lg mr-2" style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;"><i class="far fa-edit"></i></a>
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
        $('body').on('click', '#button_tambah_machine', function() {
            $('#modal_tambah_machine').modal('show');
        });

        $('#store').click(function(e) {
            e.preventDefault();

            let machine_code = $('#machine_code').val();
            let machine_name = $('#machine_name').val();
            let section = $('#section').val();
            let status = $('#status').val();
            let token = $("meta[name='csrf-token']").attr("content");

            let formData = new FormData();
            formData.append('machine_code', machine_code);
            formData.append('machine_name', machine_name);
            formData.append('section', section);
            formData.append('status', status);
            formData.append('_token', token);

            $.ajax({
                url: '/machine',
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
                    
                    $('#machine_code').val('');
                    $('#machine_name').val('');
                    $('#section').val('');
                    $('#status').val('');
                    $('#modal_tambah_machine').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON) {
                        if (error.responseJSON.machine_code && error.responseJSON.machine_code[0]) {
                            $('#alert-machine_code').removeClass('d-none');
                            $('#alert-machine_code').addClass('d-block');
                            $('#alert-machine_code').html(error.responseJSON.machine_code[0]);
                        }
                        if (error.responseJSON.machine_name && error.responseJSON.machine_name[0]) {
                            $('#alert-machine_name').removeClass('d-none');
                            $('#alert-machine_name').addClass('d-block');
                            $('#alert-machine_name').html(error.responseJSON.machine_name[0]);
                        }
                        if (error.responseJSON.section && error.responseJSON.section[0]) {
                            $('#alert-section').removeClass('d-none');
                            $('#alert-section').addClass('d-block');
                            $('#alert-section').html(error.responseJSON.section[0]);
                        }
                        if (error.responseJSON.status && error.responseJSON.status[0]) {
                            $('#alert-status').removeClass('d-none');
                            $('#alert-status').addClass('d-block');
                            $('#alert-status').html(error.responseJSON.status[0]);
                        }
                    }
                }
            });
        });
    </script>

    <!-- Edit Data Machine -->
    <script>
        //Show modal edit
        $('body').on('click', '#button_edit_machine', function() {
            let machine_id = $(this).data('id');

            $.ajax({
                url: `/machine/${machine_id}/edit`,
                type: "GET",
                cache: false,
                success: function(response) {
                    $('#machine_id').val(response.data.id);
                    $('#edit_machine_code').val(response.data.machine_code);
                    $('#edit_machine_name').val(response.data.machine_name);
                    $('#edit_section').val(response.data.section);
                    $('#edit_status').val(response.data.status);

                    $('#modal_edit_machine').modal('show');
                }
            });
        });

        // Proses Update Data
        $('#update').click(function(e) {
            e.preventDefault();

            let machine_id = $('#machine_id').val();
            let machine_code = $('#edit_machine_code').val();
            let machine_name = $('#edit_machine_name').val();
            let section = $('#edit_section').val();
            let status = $('#edit_status').val();
            let token = $("meta[name='csrf-token']").attr('content');

            let formData = new FormData();
            formData.append('machine_code', machine_code);
            formData.append('machine_name', machine_name);
            formData.append('section', section);
            formData.append('status', status);
            formData.append('_token', token);
            formData.append('_method', 'PUT');

            $.ajax({
                url: `/machine/${machine_id}`,
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
                        showConfirmButton: true,
                        timer: 3000
                    });

                    let row = $(`#index_${response.data.id}`);
                    let rowData = row.find('td');
                    let badgeClass = '';
                    switch(response.data.status) {
                        case 'active':
                            badgeClass = 'badge badge-success';
                            break;
                        case 'inactive':
                            badgeClass = 'badge badge-secondary';
                            break;
                        case 'maintenance':
                            badgeClass = 'badge badge-warning';
                            break;
                        default:
                            badgeClass = 'badge badge-primary';
                    }
                    
                    rowData.eq(1).text(response.data.machine_code);
                    rowData.eq(2).text(response.data.machine_name);
                    rowData.eq(3).text(response.data.section);
                    rowData.eq(4).html(`<span class="${badgeClass}">${response.data.status.charAt(0).toUpperCase() + response.data.status.slice(1)}</span>`);

                    $('#modal_edit_machine').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON) {
                        if (error.responseJSON.machine_code && error.responseJSON.machine_code[0]) {
                            $('#alert-edit-machine_code').removeClass('d-none');
                            $('#alert-edit-machine_code').addClass('d-block');
                            $('#alert-edit-machine_code').html(error.responseJSON.machine_code[0]);
                        }
                        if (error.responseJSON.machine_name && error.responseJSON.machine_name[0]) {
                            $('#alert-edit-machine_name').removeClass('d-none');
                            $('#alert-edit-machine_name').addClass('d-block');
                            $('#alert-edit-machine_name').html(error.responseJSON.machine_name[0]);
                        }
                        if (error.responseJSON.section && error.responseJSON.section[0]) {
                            $('#alert-edit-section').removeClass('d-none');
                            $('#alert-edit-section').addClass('d-block');
                            $('#alert-edit-section').html(error.responseJSON.section[0]);
                        }
                        if (error.responseJSON.status && error.responseJSON.status[0]) {
                            $('#alert-edit-status').removeClass('d-none');
                            $('#alert-edit-status').addClass('d-block');
                            $('#alert-edit-status').html(error.responseJSON.status[0]);
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
                        url: `/machine/${machine_id}`,
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
                                showConfirmButton: true,
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