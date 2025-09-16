@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>General Checkup</h1>
        <div class="ml-auto">
            <a href="{{ route('general-checkup.index') }}" class="btn btn-dark">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form id="checkup_form" enctype="multipart/form-data">
                @csrf
                
                <!-- Informasi Checkup -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle"></i> Informasi Checkup</h6>
                    </div>
                    <div class="card-body">
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="shift">Shift <span class="text-danger">*</span></label>
                                    <select class="form-control" id="shift" name="shift" required>
                                        <option value="">Pilih Shift</option>
                                        <option value="morning">Pagi (Morning)</option>
                                        <option value="afternoon">Siang (Afternoon)</option>
                                        <option value="night">Malam (Night)</option>
                                    </select>
                                    <div class="invalid-feedback" id="error-shift"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check Items -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6><i class="fas fa-list-check"></i> Check Items</h6>
                    </div>
                    <div class="card-body">
                        <div id="check_items_container">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-info-circle"></i><br>
                                Pilih Type dan Item untuk memuat check items
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Keseluruhan & Catatan -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6><i class="fas fa-clipboard-check"></i> Status Keseluruhan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="overall_status">Status Keseluruhan <span class="text-danger">*</span></label>
                                    <select class="form-control" id="overall_status" name="overall_status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="good">Good</option>
                                        <option value="problem">Problem</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                    <div class="invalid-feedback" id="error-overall_status"></div>
                                </div>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Catatan Umum</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Catatan umum tentang checkup ini..."></textarea>
                            <div class="invalid-feedback" id="error-notes"></div>
                        </div>
                    </div>
                </div>

                <!-- Foto Dokumentasi -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6><i class="fas fa-camera"></i> Foto Dokumentasi</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="photos">Foto Dokumentasi</label>
                            <button type="button" class="btn btn-info btn-block" id="capture_photo">
                                <i class="fas fa-camera"></i> Ambil Foto
                            </button>
                            
                            <!-- Hidden file input untuk camera capture -->
                            <input type="file" id="camera_input" accept="image/*" capture="environment" multiple style="display: none;">
                            
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Ambil foto dokumentasi untuk checkup ini. Format: JPG, PNG (Max: 2MB per foto)
                            </small>
                            <div class="invalid-feedback" id="error-photos"></div>
                        </div>
                        
                        <!-- Preview Photos -->
                        <div id="photo_preview" class="row mt-3"></div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="submit_btn">
                            <i class="fas fa-save"></i> Simpan Checkup
                        </button>
                        <button type="button" class="btn btn-dark btn-lg px-5 ml-2" onclick="window.location.href='{{ route('general-checkup.index') }}'">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Professional & Mobile-friendly styles */
        .check-item-card {
            border: 1px solid #e3e6f0;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .check-item-header {
            background: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .check-item-body {
            padding: 20px;
        }
        
        .standard-item {
            border: 1px solid #e9ecef;
            border-radius: 0.4rem;
            padding: 15px;
            margin-bottom: 12px;
            background: #f8f9fa;
            transition: all 0.2s ease;
        }
        
        .standard-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.1);
        }
        
        .standard-item.border-danger {
            border-color: #dc3545 !important;
            box-shadow: 0 2px 8px rgba(220,53,69,0.2);
        }
        
        .standard-name {
            font-weight: 500;
            color: #495057;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .result-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .result-btn {
            flex: 1;
            max-width: 100px;
            padding: 10px 16px;
            border: 2px solid #dee2e6;
            border-radius: 0.4rem;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .result-btn.active-ok {
            background: #28a745;
            border-color: #28a745;
            color: white;
            box-shadow: 0 2px 6px rgba(40,167,69,0.3);
        }
        
        .result-btn.active-ng {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
            box-shadow: 0 2px 6px rgba(220,53,69,0.3);
        }
        
        .result-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        }
        
        .result-btn.active-ok:hover {
            background: #218838;
        }
        
        .result-btn.active-ng:hover {
            background: #c82333;
        }
        
        .photo-preview-item {
            position: relative;
            margin-bottom: 15px;
        }
        
        .photo-preview-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 0.4rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .photo-remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .standards-title {
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .check-item-header {
                padding: 12px 15px;
            }
            
            .check-item-body {
                padding: 15px;
            }
            
            .standard-item {
                padding: 12px;
            }
            
            .result-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            .result-btn {
                flex: none;
                max-width: none;
                padding: 12px 16px;
                font-size: 15px;
            }
            
            .photo-preview-img {
                height: 120px;
            }
        }
        
        /* Touch-friendly for mobile */
        @media (hover: none) and (pointer: coarse) {
            .result-btn {
                padding: 14px 18px;
                font-size: 16px;
            }
            
            .form-control {
                font-size: 16px;
                padding: 12px 16px;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 16px;
            }
        }
    </style>

    <script>
        let checkItemsData = [];
        let selectedPhotos = [];

        $(document).ready(function() {
            // Event listeners
            $('#type').on('change', function() {
                loadItemOptions();
            });

            $('#item_id').on('change', function() {
                if ($(this).val()) {
                    loadCheckItems(); // Auto load saat item dipilih
                } else {
                    $('#check_items_container').html(`
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle"></i><br>
                            Pilih Type dan Item untuk memuat check items
                        </div>
                    `);
                }
            });

            $('#capture_photo').on('click', function() {
                $('#camera_input').click();
            });

            $('#camera_input').on('change', function() {
                handlePhotoSelection(this.files);
            });

            $('#checkup_form').on('submit', function(e) {
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
                url: '/checkitem/get-options',
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

        function loadCheckItems() {
            let type = $('#type').val();
            let itemId = $('#item_id').val();

            if (!type || !itemId) {
                return;
            }

            $.ajax({
                url: '/general-checkup/get-check-items',
                type: 'GET',
                data: { type: type, item_id: itemId },
                beforeSend: function() {
                    $('#check_items_container').html(`
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin"></i> Loading check items...
                        </div>
                    `);
                },
                success: function(response) {
                    checkItemsData = response.data;
                    renderCheckItems();
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat check items!'
                    });
                    $('#check_items_container').html(`
                        <div class="text-center text-danger py-4">
                            <i class="fas fa-exclamation-triangle"></i><br>
                            Gagal memuat check items
                        </div>
                    `);
                }
            });
        }

        function renderCheckItems() {
            if (checkItemsData.length === 0) {
                $('#check_items_container').html(`
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle"></i><br>
                        Tidak ada check items untuk item yang dipilih
                    </div>
                `);
                return;
            }

            let html = '';
            
            checkItemsData.forEach(function(checkItem, itemIndex) {
                html += `
                    <div class="check-item-card">
                        <div class="check-item-header">
                            <h6 class="text-primary mb-0">
                                <i class="fas fa-wrench"></i> ${checkItem.item_name}
                            </h6>
                        </div>
                        
                        <div class="check-item-body">
                            <input type="hidden" name="checkup_details[${itemIndex}][check_item_id]" value="${checkItem.id}">
                            <input type="hidden" name="checkup_details[${itemIndex}][item_status]" value="good">
                            <input type="hidden" name="checkup_details[${itemIndex}][maintenance_notes]" value="">
                `;
                
                if (checkItem.standards && checkItem.standards.length > 0) {
                    html += '<div class="standards-title"><i class="fas fa-clipboard-check"></i> Standards</div>';
                    
                    checkItem.standards.forEach(function(standard, standardIndex) {
                        html += `
                            <div class="standard-item">
                                <div class="standard-name">${standard.standard_name}</div>
                                <div class="result-buttons">
                                    <div class="result-btn" onclick="selectResult(${itemIndex}, ${standardIndex}, 'OK')" data-result="OK">
                                        <i class="fas fa-check"></i> OK
                                    </div>
                                    <div class="result-btn" onclick="selectResult(${itemIndex}, ${standardIndex}, 'NG')" data-result="NG">
                                        <i class="fas fa-times"></i> NG
                                    </div>
                                </div>
                                <input type="hidden" name="checkup_details[${itemIndex}][standards][${standardIndex}][check_standard_id]" value="${standard.id}">
                                <input type="hidden" name="checkup_details[${itemIndex}][standards][${standardIndex}][result]" id="result_${itemIndex}_${standardIndex}" required>
                            </div>
                        `;
                    });
                } else {
                    html += `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Tidak ada standards untuk check item ini
                        </div>
                    `;
                }
                
                html += '</div></div>';
            });
            
            $('#check_items_container').html(html);
        }

        function selectResult(itemIndex, standardIndex, result) {
            // Update hidden input
            $(`#result_${itemIndex}_${standardIndex}`).val(result);
            
            // Update button states
            let container = $(`#result_${itemIndex}_${standardIndex}`).closest('.standard-item');
            container.find('.result-btn').removeClass('active-ok active-ng');
            
            if (result === 'OK') {
                container.find('.result-btn[data-result="OK"]').addClass('active-ok');
            } else {
                container.find('.result-btn[data-result="NG"]').addClass('active-ng');
            }
        }

        function handlePhotoSelection(files) {
            // Merge with existing photos
            Array.from(files).forEach(file => {
                selectedPhotos.push(file);
            });
            displayPhotoPreview();
        }

        function displayPhotoPreview() {
            let previewContainer = $('#photo_preview');
            previewContainer.empty();
            
            selectedPhotos.forEach(function(file, index) {
                if (file.type.startsWith('image/')) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        let photoHtml = `
                            <div class="col-6 col-md-3 photo-preview-item">
                                <img src="${e.target.result}" class="photo-preview-img" alt="Preview">
                                <button type="button" class="photo-remove-btn" onclick="removePhoto(${index})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        previewContainer.append(photoHtml);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function removePhoto(index) {
            selectedPhotos.splice(index, 1);
            displayPhotoPreview();
        }

        function submitForm() {
            // Validate required fields first
            if (!validateForm()) {
                return;
            }

            let formData = new FormData($('#checkup_form')[0]);
            
            // Add selected photos
            selectedPhotos.forEach(function(file, index) {
                formData.append(`photos[${index}]`, file);
            });
            
            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            $.ajax({
                url: '/general-checkup',
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
                        text: `Checkup ${response.checkup_code} berhasil disimpan!`,
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route("general-checkup.index") }}';
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
                    $('#submit_btn').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Checkup');
                }
            });
        }

        function validateForm() {
            let isValid = true;
            
            // Check if all standards have been selected
            $('input[id^="result_"]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).closest('.standard-item').addClass('border-danger');
                } else {
                    $(this).closest('.standard-item').removeClass('border-danger');
                }
            });
            
            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Silakan pilih hasil (OK/NG) untuk semua standar yang ada.'
                });
            }
            
            return isValid;
        }
    </script>
@endsection