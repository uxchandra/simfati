@extends('layouts.app')

{{-- Pass variabel $roles ke file yang di-include --}}
@include('user.create')
@include('user.edit')

@section('content')
    <div class="section-header">
        <h1>Data User</h1>
        <div class="ml-auto">
            <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_user"><i class="fa fa-plus"></i>
                User</a>
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
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Created At</th>
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
            loadUserData();
        });

        function loadUserData() {
            $.ajax({
                url: "/user/get-data",
                type: "GET",
                dataType: 'JSON',
                success: function(response) {
                    let counter = 1;
                    $('#table_id').DataTable().clear();
                    $.each(response.data, function(key, value) {
                        let createdAt = new Date(value.created_at).toLocaleDateString('id-ID');
                        
                        let userRow = `
                            <tr class="user-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                <td>${value.name}</td>
                                <td>${value.username}</td>
                                <td><span class="badge badge-primary">${value.role ? value.role.role : '-'}</span></td>
                                <td>${createdAt}</td>
                                <td>
                                    <div class="d-flex">
                                        <a href="javascript:void(0)" id="button_edit_user" data-id="${value.id}" class="btn btn-icon btn-warning btn-lg mr-2"><i class="far fa-edit"></i></a>
                                        <a href="javascript:void(0)" id="button_hapus_user" data-id="${value.id}" class="btn btn-icon btn-danger btn-lg"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        `;
                        $('#table_id').DataTable().row.add($(userRow)).draw(false);
                    });
                }
            });
        }
    </script>

    <!-- Show Modal Tambah User -->
    <script>
        $('body').on('click', '#button_tambah_user', function() {
            $('#modal_tambah_user').modal('show');
        });

        $('#store').click(function(e) {
            e.preventDefault();

            let name = $('#name').val();
            let username = $('#username').val();
            let password = $('#password').val();
            let password_confirmation = $('#password_confirmation').val();
            let role_id = $('#role_id').val();
            let token = $("meta[name='csrf-token']").attr("content");

            let formData = new FormData();
            formData.append('name', name);
            formData.append('username', username);
            formData.append('password', password);
            formData.append('password_confirmation', password_confirmation);
            formData.append('role_id', role_id);
            formData.append('_token', token);

            $.ajax({
                url: '/user',
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

                    loadUserData();
                    
                    // Reset form
                    $('#name').val('');
                    $('#username').val('');
                    $('#password').val('');
                    $('#password_confirmation').val('');
                    $('#role_id').val('');
                    $('#modal_tambah_user').modal('hide');
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

    <!-- Edit Data User -->
    <script>
        //Show modal edit
        $('body').on('click', '#button_edit_user', function() {
            let user_id = $(this).data('id');

            $.ajax({
                url: `/user/${user_id}/edit`,
                type: "GET",
                cache: false,
                success: function(response) {
                    $('#user_id').val(response.data.id);
                    $('#edit_name').val(response.data.name);
                    $('#edit_username').val(response.data.username);
                    $('#edit_role_id').val(response.data.role_id);

                    $('#modal_edit_user').modal('show');
                }
            });
        });

        // Proses Update Data
        $('#update').click(function(e) {
            e.preventDefault();

            let user_id = $('#user_id').val();
            let name = $('#edit_name').val();
            let username = $('#edit_username').val();
            let password = $('#edit_password').val();
            let password_confirmation = $('#edit_password_confirmation').val();
            let role_id = $('#edit_role_id').val();
            let token = $("meta[name='csrf-token']").attr('content');

            let formData = new FormData();
            formData.append('name', name);
            formData.append('username', username);
            if (password) {
                formData.append('password', password);
                formData.append('password_confirmation', password_confirmation);
            }
            formData.append('role_id', role_id);
            formData.append('_token', token);
            formData.append('_method', 'PUT');

            $.ajax({
                url: `/user/${user_id}`,
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

                    loadUserData();
                    $('#modal_edit_user').modal('hide');
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

    <!-- Hapus Data User -->
    <script>
        $('body').on('click', '#button_hapus_user', function() {
            let user_id = $(this).data('id');
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
                        url: `/user/${user_id}`,
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
                            
                            loadUserData();
                        }
                    })
                }
            });
        });
    </script>
@endsection