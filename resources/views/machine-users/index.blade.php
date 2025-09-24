@extends('layouts.app')

@include('machine-users.create')
@include('machine-users.edit')

@section('content')
    <div class="section-header">
        <h1>PIC Machines</h1>
        <div class="ml-auto">
            <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_pic"><i class="fa fa-plus"></i>
                Assign PIC</a>
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
                                    <th>Department</th>
                                    <th>Machine</th>
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
                paging: true,
                order: [[0, 'asc']]
            });
            loadPicData();
        });

        function loadPicData() {
            $.ajax({
                url: "/machine-users",
                type: "GET",
                dataType: 'JSON',
                success: function(response) {
                    let counter = 1;
                    $('#table_id').DataTable().clear();
                    $.each(response.data, function(key, value) {
                        
                        // Format machines list
                        let machinesList = '';
                        let machineCount = 0;
                        if (value.machines && value.machines.length > 0) {
                            machineCount = value.machines.length;
                            let machineNames = value.machines.map(machine => 
                                `<span class="badge badge-info mr-1 mb-1">${machine.machine_code || 'N/A'}</span>`
                            ).join('');
                            machinesList = machineNames;
                        } else {
                            machinesList = '<span class="text-muted">Belum ada mesin</span>';
                        }
                        
                        let picRow = `
                            <tr class="pic-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                <td>${value.name}</td>
                                <td><span class="badge badge-success">${value.department ? value.department.kode : '-'}</span></td>
                                <td style="max-width: 300px;">
                                    ${machinesList}
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <a href="javascript:void(0)" id="button_edit_pic" data-id="${value.id}" class="btn btn-icon btn-warning btn-sm mr-2" title="Edit PIC"><i class="far fa-edit"></i></a>
                                        <a href="javascript:void(0)" id="button_detach_all" data-id="${value.id}" class="btn btn-icon btn-danger btn-sm" title="Hapus Semua Mesin"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#table_id').DataTable().row.add($(picRow)).draw(false);
                    });
                }
            });
        }
    </script>

    <!-- Show Modal Tambah PIC -->
    <script>
        $('body').on('click', '#button_tambah_pic', function() {
            $('#modal_tambah_pic').modal('show');
        });

        $('#store').click(function(e) {
            e.preventDefault();

            let user_id = $('#user_id').val();
            let machine_ids = $('#machine_ids').val(); // multiple select
            let token = $("meta[name='csrf-token']").attr("content");

            let formData = new FormData();
            formData.append('user_id', user_id);
            if (machine_ids && machine_ids.length > 0) {
                machine_ids.forEach(function(machine_id) {
                    formData.append('machine_ids[]', machine_id);
                });
            }
            formData.append('_token', token);

            $.ajax({
                url: '/machine-users',
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

                    loadPicData();
                    
                    // Reset form
                    $('#user_id').val('').trigger('change');
                    $('#machine_ids').val([]).trigger('change');
                    $('#modal_tambah_pic').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON && error.responseJSON.errors) {
                        // Clear previous alerts
                        $('.alert-danger').addClass('d-none').removeClass('d-block');
                        
                        // Show specific field errors
                        $.each(error.responseJSON.errors, function(field, messages) {
                            if (messages && messages[0]) {
                                $(`#alert-${field}`).removeClass('d-none').addClass('d-block').html(messages[0]);
                            }
                        });
                    }
                }
            });
        });
    </script>

    <!-- Edit Data PIC -->
    <script>
        //Show modal edit
        $('body').on('click', '#button_edit_pic', function() {
            let user_id = $(this).data('id');

            $.ajax({
                url: `/machine-users/${user_id}/edit`,
                type: "GET",
                cache: false,
                success: function(response) {
                    $('#edit_user_id').val(response.data.id);
                    $('#edit_user_name').text(response.data.name);
                    
                    // Set selected machines
                    let selectedMachines = response.data.machines.map(machine => machine.id);
                    $('#edit_machine_ids').val(selectedMachines).trigger('change');

                    $('#modal_edit_pic').modal('show');
                }
            });
        });

        // Proses Update Data
        $('#update').click(function(e) {
            e.preventDefault();

            let user_id = $('#edit_user_id').val();
            let machine_ids = $('#edit_machine_ids').val();
            let token = $("meta[name='csrf-token']").attr('content');

            let formData = new FormData();
            if (machine_ids && machine_ids.length > 0) {
                machine_ids.forEach(function(machine_id) {
                    formData.append('machine_ids[]', machine_id);
                });
            }
            formData.append('_token', token);
            formData.append('_method', 'PUT');

            $.ajax({
                url: `/machine-users/${user_id}`,
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

                    loadPicData();
                    $('#modal_edit_pic').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON && error.responseJSON.errors) {
                        // Clear previous alerts
                        $('.alert-danger').addClass('d-none').removeClass('d-block');
                        
                        // Show specific field errors
                        $.each(error.responseJSON.errors, function(field, messages) {
                            if (messages && messages[0]) {
                                $(`#alert-edit-${field}`).removeClass('d-none').addClass('d-block').html(messages[0]);
                            }
                        });
                    }
                }
            });
        });
    </script>

    <!-- Hapus Semua Mesin dari User -->
    <script>
        $('body').on('click', '#button_detach_all', function() {
            let user_id = $(this).data('id');
            let token = $("meta[name='csrf-token']").attr("content");

            Swal.fire({
                title: 'Apakah Kamu Yakin?',
                text: "Akan menghapus semua mesin dari user ini!",
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'TIDAK',
                confirmButtonText: 'YA, HAPUS!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/machine-users/${user_id}`,
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
                            
                            loadPicData();
                        }
                    })
                }
            });
        });
    </script>

    <!-- View Detail Machines -->
    <script>
        $('body').on('click', '#button_view_machines', function() {
            let user_id = $(this).data('id');
            
            $.ajax({
                url: `/machine-users/machines/user/${user_id}`,
                type: "GET",
                cache: false,
                success: function(response) {
                    let machineList = '';
                    if (response && response.length > 0) {
                        response.forEach(function(machine, index) {
                            machineList += `
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>${machine.name || machine.kode}</strong>
                                        <br>
                                        <small class="text-muted">${machine.description || 'No description'}</small>
                                    </div>
                                    <span class="badge badge-primary">${machine.status || 'Active'}</span>
                                </div>
                            `;
                        });
                    } else {
                        machineList = '<p class="text-center text-muted">Tidak ada mesin yang dikelola</p>';
                    }
                    
                    Swal.fire({
                        title: 'Mesin yang Dikelola',
                        html: machineList,
                        showCloseButton: true,
                        focusConfirm: false,
                        confirmButtonText: 'Tutup'
                    });
                }
            });
        });
    </script>
@endsection