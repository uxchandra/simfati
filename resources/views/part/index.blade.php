@extends('layouts.app')

@include('part.create')
@include('part.edit')

@section('content')
    <div class="section-header">
        <h1>Data Part</h1>
        <div class="ml-auto">
            <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_part"><i class="fa fa-plus"></i>
                Part</a>
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
                                    <th>Part Code</th>
                                    <th>Part Name</th>
                                    <th>Machine</th>
                                    <th>Model</th>
                                    <th>Process</th>
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
            loadPartData();
        });

        function loadPartData() {
            $.ajax({
                url: "/part/get-data",
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
                            case 'obsolete':
                                badgeClass = 'badge badge-warning';
                                break;
                            default:
                                badgeClass = 'badge badge-primary';
                        }
                        
                        let partRow = `
                            <tr class="part-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                <td>${value.part_code}</td>
                                <td>${value.part_name}</td>
                                <td>${value.machine ? value.machine.machine_name : '-'}</td>
                                <td>${value.model}</td>
                                <td>${value.process}</td>
                                <td><span class="${badgeClass}">${value.status.charAt(0).toUpperCase() + value.status.slice(1)}</span></td>
                                <td>
                                    <div class="d-flex">
                                        <a href="javascript:void(0)" id="button_edit_part" data-id="${value.id}" class="btn btn-icon btn-warning btn-lg mr-2"><i class="far fa-edit"></i></a>
                                        <a href="javascript:void(0)" id="button_hapus_part" data-id="${value.id}" class="btn btn-icon btn-danger btn-lg"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#table_id').DataTable().row.add($(partRow)).draw(false);
                    });
                }
            });
        }
    </script>

    <!-- Show Modal Tambah Part -->
    <script>
        $('body').on('click', '#button_tambah_part', function() {
            $('#modal_tambah_part').modal('show');
        });

        $('#store').click(function(e) {
            e.preventDefault();

            let part_code = $('#part_code').val();
            let part_name = $('#part_name').val();
            let part_type = $('#part_type').val();
            let machine_id = $('#machine_id').val();
            let model = $('#model').val();
            let process = $('#process').val();
            let customer = $('#customer').val();
            let status = $('#status').val();
            let token = $("meta[name='csrf-token']").attr("content");

            let formData = new FormData();
            formData.append('part_code', part_code);
            formData.append('part_name', part_name);
            formData.append('part_type', part_type);
            formData.append('machine_id', machine_id);
            formData.append('model', model);
            formData.append('process', process);
            formData.append('customer', customer);
            formData.append('status', status);
            formData.append('_token', token);

            $.ajax({
                url: '/part',
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

                    loadPartData();
                    
                    // Reset form
                    $('#part_code').val('');
                    $('#part_name').val('');
                    $('#part_type').val('');
                    $('#machine_id').val('');
                    $('#model').val('');
                    $('#process').val('');
                    $('#customer').val('');
                    $('#status').val('');
                    $('#modal_tambah_part').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON) {
                        // Clear previous alerts
                        $('.alert-danger').addClass('d-none').removeClass('d-block');
                        
                        // Show specific field errors
                        $.each(error.responseJSON, function(field, messages) {
                            if (messages && messages[0]) {
                                $(`#alert-${field}`).removeClass('d-none').addClass('d-block').html(messages[0]);
                            }
                        });
                    }
                }
            });
        });
    </script>

    <!-- Edit Data Part -->
    <script>
        //Show modal edit
        $('body').on('click', '#button_edit_part', function() {
            let part_id = $(this).data('id');

            $.ajax({
                url: `/part/${part_id}/edit`,
                type: "GET",
                cache: false,
                success: function(response) {
                    $('#part_id').val(response.data.id);
                    $('#edit_part_code').val(response.data.part_code);
                    $('#edit_part_name').val(response.data.part_name);
                    $('#edit_part_type').val(response.data.part_type);
                    $('#edit_machine_id').val(response.data.machine_id);
                    $('#edit_model').val(response.data.model);
                    $('#edit_process').val(response.data.process);
                    $('#edit_customer').val(response.data.customer);
                    $('#edit_status').val(response.data.status);

                    $('#modal_edit_part').modal('show');
                }
            });
        });

        // Proses Update Data
        $('#update').click(function(e) {
            e.preventDefault();

            let part_id = $('#part_id').val();
            let part_code = $('#edit_part_code').val();
            let part_name = $('#edit_part_name').val();
            let part_type = $('#edit_part_type').val();
            let machine_id = $('#edit_machine_id').val();
            let model = $('#edit_model').val();
            let process = $('#edit_process').val();
            let customer = $('#edit_customer').val();
            let status = $('#edit_status').val();
            let token = $("meta[name='csrf-token']").attr('content');

            let formData = new FormData();
            formData.append('part_code', part_code);
            formData.append('part_name', part_name);
            formData.append('part_type', part_type);
            formData.append('machine_id', machine_id);
            formData.append('model', model);
            formData.append('process', process);
            formData.append('customer', customer);
            formData.append('status', status);
            formData.append('_token', token);
            formData.append('_method', 'PUT');

            $.ajax({
                url: `/part/${part_id}`,
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

                    loadPartData(); // Reload the data instead of manual update
                    $('#modal_edit_part').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON) {
                        // Clear previous alerts
                        $('.alert-danger').addClass('d-none').removeClass('d-block');
                        
                        // Show specific field errors
                        $.each(error.responseJSON, function(field, messages) {
                            if (messages && messages[0]) {
                                $(`#alert-edit-${field}`).removeClass('d-none').addClass('d-block').html(messages[0]);
                            }
                        });
                    }
                }
            });
        });
    </script>

    <!-- Hapus Data Part -->
    <script>
        $('body').on('click', '#button_hapus_part', function() {
            let part_id = $(this).data('id');
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
                        url: `/part/${part_id}`,
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
                            
                            loadPartData();
                        }
                    })
                }
            });
        });
    </script>
@endsection