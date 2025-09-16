@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Tambah Maintenance Schedule</h1>
        <div class="ml-auto">
            <a href="{{ route('maintenance-schedule.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-calendar-plus"></i> Form Tambah Schedule</h6>
                </div>
                <div class="card-body">
                    <form id="schedule_form">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="type" name="type" required>
                                        <option value="">Pilih Type</option>
                                        <option value="machine">Machine</option>
                                        <option value="part">Part</option>
                                    </select>
                                    <div class="invalid-feedback" id="error-type"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_id">Item <span class="text-danger">*</span></label>
                                    <select class="form-control" id="item_id" name="item_id" required disabled>
                                        <option value="">Pilih Item</option>
                                    </select>
                                    <div class="invalid-feedback" id="error-item_id"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period_days">Periode (Hari) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="period_days" name="period_days" 
                                           min="1" placeholder="Contoh: 7 (untuk mingguan), 30 (untuk bulanan)" required>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Berapa hari sekali maintenance dilakukan
                                    </small>
                                    <div class="invalid-feedback" id="error-period_days"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Tanggal mulai berlakunya schedule ini
                                    </small>
                                    <div class="invalid-feedback" id="error-start_date"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Preview -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info" id="schedule_preview" style="display: none;">
                                    <h6><i class="fas fa-info-circle"></i> Preview Schedule:</h6>
                                    <div id="preview_content"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="submit_btn">
                                    <i class="fas fa-save"></i> Simpan Schedule
                                </button>
                                <button type="button" class="btn btn-secondary btn-lg px-5 ml-2" onclick="window.location.href='{{ route('maintenance-schedule.index') }}'">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .preview-item {
            margin-bottom: 8px;
        }
        
        .preview-label {
            font-weight: 600;
            color: #495057;
        }
        
        .preview-value {
            color: #007bff;
        }
        
        .card-header h6 {
            margin-bottom: 0;
            color: #495057;
        }
        
        .alert-info {
            border-left: 4px solid #007bff;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Set default start date to today
            $('#start_date').val(new Date().toISOString().split('T')[0]);

            // Event listeners
            $('#type').on('change', function() {
                loadItemOptions();
                updatePreview();
            });

            $('#item_id').on('change', function() {
                updatePreview();
            });

            $('#period_days, #start_date').on('input change', function() {
                updatePreview();
            });

            $('#schedule_form').on('submit', function(e) {
                e.preventDefault();
                submitForm();
            });
        });

        function loadItemOptions() {
            let type = $('#type').val();
            let itemSelect = $('#item_id');
            
            if (!type) {
                itemSelect.prop('disabled', true).html('<option value="">Pilih Item</option>');
                return;
            }

            $.ajax({
                url: '/maintenance-schedule/get-options',
                type: 'GET',
                data: { type: type },
                beforeSend: function() {
                    itemSelect.html('<option value="">Loading...</option>');
                },
                success: function(response) {
                    let options = '<option value="">Pilih Item</option>';
                    
                    $.each(response.data, function(key, item) {
                        if (type === 'machine') {
                            options += `<option value="${item.id}">${item.machine_code} - ${item.machine_name}</option>`;
                        } else {
                            options += `<option value="${item.id}">${item.part_code} - ${item.part_name}</option>`;
                        }
                    });
                    
                    itemSelect.html(options).prop('disabled', false);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data item!'
                    });
                    itemSelect.html('<option value="">Error loading</option>');
                }
            });
        }

        function updatePreview() {
            let type = $('#type').val();
            let itemText = $('#item_id option:selected').text();
            let periodDays = $('#period_days').val();
            let startDate = $('#start_date').val();

            if (type && itemText !== 'Pilih Item' && periodDays && startDate) {
                let preview = `
                    <div class="preview-item">
                        <span class="preview-label">Item:</span> 
                        <span class="preview-value">${type.charAt(0).toUpperCase() + type.slice(1)} - ${itemText}</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Periode:</span> 
                        <span class="preview-value">Setiap ${periodDays} hari</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Mulai:</span> 
                        <span class="preview-value">${formatDate(startDate)}</span>
                    </div>
                `;
                
                // Calculate next maintenance dates
                if (startDate && periodDays) {
                    let nextDates = calculateNextDates(startDate, parseInt(periodDays));
                    preview += `
                        <div class="preview-item mt-2">
                            <span class="preview-label">3 Jadwal Maintenance Berikutnya:</span><br>
                            <small class="text-muted">
                                ${nextDates.map(date => `• ${formatDate(date)}`).join('<br>')}
                            </small>
                        </div>
                    `;
                }

                $('#preview_content').html(preview);
                $('#schedule_preview').show();
            } else {
                $('#schedule_preview').hide();
            }
        }

        function formatDate(dateString) {
            let date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }

        function calculateNextDates(startDate, periodDays) {
            let dates = [];
            let currentDate = new Date(startDate);
            
            for (let i = 1; i <= 3; i++) {
                currentDate = new Date(currentDate.getTime() + (periodDays * 24 * 60 * 60 * 1000));
                dates.push(currentDate.toISOString().split('T')[0]);
            }
            
            return dates;
        }

        function submitForm() {
            let formData = new FormData($('#schedule_form')[0]);
            
            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            $.ajax({
                url: '/maintenance-schedule',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route("maintenance-schedule.index") }}';
                        }
                    });
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Show validation errors
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            let fieldElement = $(`[name="${field}"]`);
                            fieldElement.addClass('is-invalid');
                            $(`#error-${field}`).text(messages[0]);
                        });
                        
                        // Scroll to first error
                        let firstError = $('.is-invalid').first();
                        if (firstError.length) {
                            $('html, body').animate({
                                scrollTop: firstError.offset().top - 100
                            }, 500);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat menyimpan data.'
                        });
                    }
                },
                complete: function() {
                    $('#submit_btn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Schedule');
                }
            });
        }
    </script>
@endsection