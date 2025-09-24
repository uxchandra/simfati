@extends('layouts.app')

@include('maintenance-schedule.create')
@include('maintenance-schedule.edit')

@section('content')
    <div class="section-header">
        <h1>Maintenance Schedule</h1>
        <div class="ml-auto d-flex align-items-center">
            <div class="form-group mb-0 mr-3">
                <select class="form-control" id="pic_filter" name="pic">
                    <option value="all">Semua PIC</option>
                </select>
            </div>
            <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_schedule">
                <i class="fa fa-plus"></i> Tambah Schedule
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
                                    <th>Machine Code</th>
                                    <th>Machine Name</th>
                                    <th>Period (Days)</th>
                                    <th>Last Check</th>
                                    <th>Next Check</th>                                 
                                    <th>PIC</th>
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
                order: [[7, 'asc']] // Sort by status ascending (overdue first)
            });
            
            loadScheduleData();
            loadUsers(); // Load users saat halaman dimuat
            loadPicFilter(); // Load PIC filter
        });

        function loadScheduleData() {
            let params = {
                pic_id: $('#pic_filter').val()
            };

            $.ajax({
                url: "/maintenance-schedule/get-data",
                type: "GET",
                dataType: 'JSON',
                data: params,
                success: function(response) {
                    let counter = 1;
                    $('#table_id').DataTable().clear();
                    
                    $.each(response.data, function(key, value) {
                        let statusClass = getStatusClass(value.status);
                        let daysRemainingDisplay = getDaysRemainingDisplay(value.days_remaining, value.status);
                        
                        let nextCheckClass = getNextCheckClass(value.status);
                        let itemRow = `
                            <tr class="schedule-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                <td>${value.item_code}</td>
                                <td>${value.item_name}</td>
                                <td class="text-center">${value.period_days}</td>
                                <td>${value.last_check}</td>
                                <td><span class="badge ${nextCheckClass}">${value.next_check}</span></td>
                                <td>${value.pic_name}</td>
                                <td><span class="badge ${statusClass}">${value.status_label.text}</span></td>
                                <td>
                                    <div class="d-flex">
                                        <a href="javascript:void(0)" id="button_edit_schedule" data-id="${value.id}" class="btn btn-icon btn-warning btn-lg mr-2" style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;"><i class="far fa-edit"></i></a>
                                        <a href="javascript:void(0)" id="button_hapus_schedule" data-id="${value.id}" data-name="${value.schedule_name}" class="btn btn-icon btn-danger btn-lg" style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-trash"></i></a>
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
                case 'overdue': return 'badge-danger';
                case 'due_today': return 'badge-warning';
                case 'due_soon': return 'badge-info';
                case 'on_schedule': return 'badge-success';
                case 'no_checkup': return 'badge-secondary';
                default: return 'badge-secondary';
            }
        }
        
        function getNextCheckClass(status) {
            switch(status) {
                case 'overdue': return 'badge-danger';
                case 'due_today': return 'badge-warning';
                case 'due_soon': return 'badge-info';
                case 'on_schedule': return 'badge-info';
                case 'no_checkup': return 'badge-light';
                default: return 'badge-light';
            }
        }

        function getDaysRemainingDisplay(days, status) {
            if (status === 'overdue') {
                return `<span class="text-danger font-weight-bold">${Math.abs(days)} hari terlambat</span>`;
            } else if (status === 'due_today') {
                return `<span class="text-warning font-weight-bold">Hari ini</span>`;
            } else if (days === null) {
                return `<span class="text-muted">Belum ada checkup</span>`;
            } else {
                return `${days} hari`;
            }
        }

        // Auto apply filter when PIC select changes
        $('#pic_filter').on('change', function() {
            loadScheduleData();
        });

        // Function untuk load PIC filter
        function loadPicFilter() {
            $.ajax({
                url: "{{ route('maintenance-schedule.getUsers') }}",
                type: "GET",
                dataType: 'JSON',
                success: function(response) {
                    let picOptions = '<option value="all">Semua PIC</option>';
                    $.each(response.data, function(key, user) {
                        picOptions += `<option value="${user.id}">${user.name}</option>`;
                    });
                    $('#pic_filter').html(picOptions);
                }
            });
        }

        // Function untuk load users
        function loadUsers(callback) {
            $.ajax({
                url: "{{ route('maintenance-schedule.getUsers') }}",
                type: "GET",
                dataType: 'JSON',
                success: function(response) {
                    let userOptions = '<option value="">Pilih PIC</option>';
                    $.each(response.data, function(key, user) {
                        userOptions += `<option value="${user.id}">${user.name}</option>`;
                    });
                    $('#user_id').html(userOptions);
                    $('#edit_user_id').html(userOptions);
                    
                    // Execute callback if provided
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            });
        }

        // Function untuk load available machines
        function loadAvailableMachines(targetElement, selectedId = null) {
            $.ajax({
                url: '/maintenance-schedule/get-available-machines',
                type: 'GET',
                dataType: 'JSON',
                success: function(response) {
                    let options = '<option value="">Pilih Mesin</option>';
                    $.each(response.data, function(key, machine) {
                        let selected = selectedId && machine.id == selectedId ? 'selected' : '';
                        options += `<option value="${machine.id}" ${selected}>${machine.machine_name}</option>`;
                    });
                    $(targetElement).html(options);
                },
                error: function(xhr) {
                    console.error('Error loading machines:', xhr);
                    $(targetElement).html('<option value="">Gagal memuat data mesin</option>');
                }
            });
        }

        // Function untuk check machine availability
        function checkMachineAvailability(machineId, excludeId = null) {
            return $.ajax({
                url: '/maintenance-schedule/check-machine',
                type: 'GET',
                data: {
                    machine_id: machineId,
                    exclude_id: excludeId
                },
                dataType: 'JSON'
            });
        }
    </script>

    <!-- Show Modal Tambah Schedule -->
    <script>
        $('body').on('click', '#button_tambah_schedule', function() {
            // Reset form
            $('#machine_id').html('<option value="">Pilih Mesin</option>');
            $('#period_days').val('');
            $('#start_date').val('');
            $('#user_id').val('');
            
            // Reset error alerts
            $('.alert-danger').removeClass('d-block').addClass('d-none');
            
            // Load users dan available machines ketika modal dibuka
            loadUsers();
            loadAvailableMachines('#machine_id');
            
            $('#modal_tambah_schedule').modal('show');
        });

        // Store Schedule
        $('#store_schedule').click(function(e) {
            e.preventDefault();

            let machine_id = $('#machine_id').val();
            let period_days = $('#period_days').val();
            let start_date = $('#start_date').val();
            let user_id = $('#user_id').val();
            let token = $("meta[name='csrf-token']").attr("content");

            // Check if machine already has schedule
            if (machine_id) {
                checkMachineAvailability(machine_id).done(function(response) {
                    if (response.has_schedule) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian!',
                            text: 'Mesin ini sudah memiliki schedule!'
                        });
                        return;
                    }
                    
                    // Proceed with saving if machine is available
                    saveSchedule();
                });
            } else {
                saveSchedule();
            }

            function saveSchedule() {
                let formData = new FormData();
                formData.append('machine_id', machine_id);
                formData.append('period_days', period_days);
                formData.append('start_date', start_date);
                formData.append('user_id', user_id);
                formData.append('_token', token);

                $.ajax({
                    url: '/maintenance-schedule',
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

                        loadScheduleData();
                        
                        // Reset form
                        $('#machine_id').html('<option value="">Pilih Mesin</option>');
                        $('#period_days').val('');
                        $('#start_date').val('');
                        $('#user_id').val('');
                        $('#modal_tambah_schedule').modal('hide');
                    },

                    error: function(error) {
                        if (error.responseJSON) {
                            // Reset all alerts first
                            $('.alert-danger').removeClass('d-block').addClass('d-none');
                            
                            if (error.responseJSON.machine_id && error.responseJSON.machine_id[0]) {
                                $('#alert-machine_id').removeClass('d-none').addClass('d-block').html(error.responseJSON.machine_id[0]);
                            }
                            if (error.responseJSON.period_days && error.responseJSON.period_days[0]) {
                                $('#alert-period_days').removeClass('d-none').addClass('d-block').html(error.responseJSON.period_days[0]);
                            }
                            if (error.responseJSON.start_date && error.responseJSON.start_date[0]) {
                                $('#alert-start_date').removeClass('d-none').addClass('d-block').html(error.responseJSON.start_date[0]);
                            }
                            if (error.responseJSON.user_id && error.responseJSON.user_id[0]) {
                                $('#alert-user_id').removeClass('d-none').addClass('d-block').html(error.responseJSON.user_id[0]);
                            }
                            
                            // Handle duplicate machine error
                            if (error.responseJSON.message && error.responseJSON.message.includes('sudah memiliki schedule')) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Perhatian!',
                                    text: error.responseJSON.message
                                });
                            }
                        }
                    }
                });
            }
        });
    </script>

    <!-- Edit Data Schedule -->
    <script>
        //Show modal edit
        $('body').on('click', '#button_edit_schedule', function() {
            let schedule_id = $(this).data('id');

            // Load users sebelum menampilkan modal edit
            loadUsers();

            $.ajax({
                url: `/maintenance-schedule/${schedule_id}/edit`,
                type: "GET",
                cache: false,
                success: function(response) {
                    $('#edit_schedule_id').val(schedule_id);
                    $('#edit_period_days').val(response.period_days);
                    $('#edit_start_date').val(response.start_date);
                    
                    // Load all machines and set selected machine
                    $.ajax({
                        url: '/maintenance-schedule/get-available-machines',
                        type: 'GET',
                        dataType: 'JSON',
                        success: function(machineResponse) {
                            let options = '<option value="">Pilih Mesin</option>';
                            $.each(machineResponse.data, function(key, machine) {
                                let selected = machine.id == response.machine_id ? 'selected' : '';
                                options += `<option value="${machine.id}" ${selected}>${machine.machine_name}</option>`;
                            });
                            
                            // Add current machine if not in available list
                            let currentMachineInList = machineResponse.data.some(m => m.id == response.machine_id);
                            if (!currentMachineInList) {
                                // Get current machine info from the schedule data
                                let currentScheduleRow = $(`#index_${schedule_id}`);
                                if (currentScheduleRow.length) {
                                    let machineName = currentScheduleRow.find('td').eq(2).text(); // machine name column
                                    options += `<option value="${response.machine_id}" selected>${machineName}</option>`;
                                }
                            }
                            
                            $('#edit_machine_id').html(options);
                        }
                    });
                    
                    // Load users dan set selected user
                    loadUsers(function() {
                        $('#edit_user_id').val(response.user_id);
                    });
                    
                    // Reset error alerts
                    $('.alert-danger').removeClass('d-block').addClass('d-none');

                    $('#modal_edit_schedule').modal('show');
                }
            });
        });

        // Update Schedule
        $('#update_schedule').click(function(e) {
            e.preventDefault();

            let schedule_id = $('#edit_schedule_id').val();
            let machine_id = $('#edit_machine_id').val();
            let period_days = $('#edit_period_days').val();
            let start_date = $('#edit_start_date').val();
            let user_id = $('#edit_user_id').val();
            let token = $("meta[name='csrf-token']").attr('content');

            let formData = new FormData();
            formData.append('machine_id', machine_id);
            formData.append('period_days', period_days);
            formData.append('start_date', start_date);
            formData.append('user_id', user_id);
            formData.append('_token', token);
            formData.append('_method', 'PUT');

            $.ajax({
                url: `/maintenance-schedule/${schedule_id}`,
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

                    loadScheduleData(); // Reload seluruh data
                    $('#modal_edit_schedule').modal('hide');
                },

                error: function(error) {
                    if (error.responseJSON) {
                        // Reset all alerts first
                        $('.alert-danger').removeClass('d-block').addClass('d-none');
                        
                        if (error.responseJSON.machine_id && error.responseJSON.machine_id[0]) {
                            $('#alert-edit-machine_id').removeClass('d-none').addClass('d-block').html(error.responseJSON.machine_id[0]);
                        }
                        if (error.responseJSON.period_days && error.responseJSON.period_days[0]) {
                            $('#alert-edit-period_days').removeClass('d-none').addClass('d-block').html(error.responseJSON.period_days[0]);
                        }
                        if (error.responseJSON.start_date && error.responseJSON.start_date[0]) {
                            $('#alert-edit-start_date').removeClass('d-none').addClass('d-block').html(error.responseJSON.start_date[0]);
                        }
                        if (error.responseJSON.user_id && error.responseJSON.user_id[0]) {
                            $('#alert-edit-user_id').removeClass('d-none').addClass('d-block').html(error.responseJSON.user_id[0]);
                        }
                        
                        // Handle duplicate machine error
                        if (error.responseJSON.message && error.responseJSON.message.includes('sudah memiliki schedule')) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian!',
                                text: error.responseJSON.message
                            });
                        }
                    }
                }
            });
        });
    </script>

    <!-- Hapus Data Schedule -->
    <script>
        $('body').on('click', '#button_hapus_schedule', function() {
            let schedule_id = $(this).data('id');
            let schedule_name = $(this).data('name');
            let token = $("meta[name='csrf-token']").attr("content");

            Swal.fire({
                title: 'Apakah Kamu Yakin?',
                text: `Ingin menghapus schedule "${schedule_name}"? Data yang dihapus tidak dapat dikembalikan!`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'TIDAK',
                confirmButtonText: 'YA, HAPUS!',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/maintenance-schedule/${schedule_id}`,
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
                            
                            loadScheduleData();
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