@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Maintenance Schedule</h1>
        <div class="ml-auto d-flex align-items-center">
            <div class="form-group mb-0 mr-3">
                <select class="form-control" id="type_filter" name="type">
                    <option value="all">Semua Type</option>
                    <option value="machine">Machine</option>
                    <option value="part">Part</option>
                </select>
            </div>
            <a href="{{ route('maintenance-schedule.create') }}" class="btn btn-primary">
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
                                    {{-- <th>Schedule Name</th> --}}
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Type</th>
                                    <th>Period (Days)</th>
                                    <th>Last Check</th>
                                    <th>Next Check</th>
                                    {{-- <th>Days Remaining</th> --}}
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
                order: [[8, 'asc']] // Sort by days remaining ascending (overdue first)
            });
            
            loadScheduleData();
        });

        function loadScheduleData() {
            let params = {
                type: $('#type_filter').val()
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
                        let typeClass = value.item_type === 'machine' ? 'badge-primary' : 'badge-success';
                        let daysRemainingDisplay = getDaysRemainingDisplay(value.days_remaining, value.status);
                        
                        let itemRow = `
                            <tr class="schedule-row" id="index_${value.id}">
                                <td>${counter++}</td>   
                                {{-- <td><strong>${value.schedule_name}</strong></td> --}}
                                <td>${value.item_code}</td>
                                <td>${value.item_name}</td>
                                <td><span class="badge ${typeClass}">${value.item_type.charAt(0).toUpperCase() + value.item_type.slice(1)}</span></td>
                                <td class="text-center">${value.period_days}</td>
                                <td>${value.last_check}</td>
                                <td>${value.next_check}</td>
                                {{-- <td class="text-center">${daysRemainingDisplay}</td> --}}
                                <td><span class="badge ${statusClass}">${value.status_label.text}</span></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-warning btn-sm btn-edit" 
                                                data-id="${value.id}"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm btn-delete" 
                                                data-id="${value.id}"
                                                data-name="${value.schedule_name}"
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
                case 'overdue': return 'badge-danger';
                case 'due_today': return 'badge-warning';
                case 'due_soon': return 'badge-info';
                case 'on_schedule': return 'badge-success';
                default: return 'badge-secondary';
            }
        }

        function getDaysRemainingDisplay(days, status) {
            if (status === 'overdue') {
                return `<span class="text-danger font-weight-bold">${Math.abs(days)} hari terlambat</span>`;
            } else if (status === 'due_today') {
                return `<span class="text-warning font-weight-bold">Hari ini</span>`;
            } else {
                return `${days} hari`;
            }
        }

        // Auto apply filter when type select changes
        $('#type_filter').on('change', function() {
            loadScheduleData();
        });
    </script>

    <!-- Edit & Delete Actions -->
    <script>
        // Edit button
        $('body').on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            window.location.href = `/maintenance-schedule/${id}/edit`;
        });

        // Delete button
        $('body').on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let token = $("meta[name='csrf-token']").attr("content");

            Swal.fire({
                title: 'Apakah Kamu Yakin?',
                text: `Ingin menghapus schedule "${name}"? Data yang dihapus tidak dapat dikembalikan!`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'TIDAK',
                confirmButtonText: 'YA, HAPUS!',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/maintenance-schedule/${id}`,
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