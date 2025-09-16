@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Request Perbaikan</h1>
        <div class="ml-auto">
            <a href="{{ route('repair-request.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-tools"></i> Form Request Perbaikan</h6>
                </div>
                <div class="card-body">
                    <form id="repair_form" enctype="multipart/form-data">
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
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="problem_description">Deskripsi Masalah <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="problem_description" name="problem_description" rows="4" 
                                              placeholder="Jelaskan masalah yang terjadi secara detail..." required></textarea>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Berikan deskripsi yang jelas agar teknisi dapat memahami masalah
                                    </small>
                                    <div class="invalid-feedback" id="error-problem_description"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Foto Dokumentasi -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="photos">Foto Dokumentasi <span class="text-muted">(Optional)</span></label>
                                    <button type="button" class="btn btn-info btn-block" id="capture_photo">
                                        <i class="fas fa-camera"></i> Ambil Foto Masalah
                                    </button>
                                    
                                    <!-- Hidden file input untuk camera capture -->
                                    <input type="file" id="camera_input" accept="image/*" capture="environment" multiple style="display: none;">
                                    
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Foto membantu teknisi memahami masalah dengan lebih baik. Format: JPG, PNG (Max: 2MB per foto)
                                    </small>
                                    <div class="invalid-feedback" id="error-photos"></div>
                                </div>
                                
                                <!-- Preview Photos -->
                                <div id="photo_preview" class="row mt-3"></div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="submit_btn">
                                    <i class="fas fa-paper-plane"></i> Kirim Request
                                </button>
                                <button type="button" class="btn btn-secondary btn-lg px-5 ml-2" onclick="window.location.href='{{ route('repair-request.index') }}'">
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
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .photo-preview-img {
                height: 120px;
            }
        }
        
        /* Touch-friendly for mobile */
        @media (hover: none) and (pointer: coarse) {
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
        let selectedPhotos = [];

        $(document).ready(function() {
            // Event listeners
            $('#type').on('change', function() {
                loadItemOptions();
                updatePreview();
            });

            $('#item_id').on('change', function() {
                updatePreview();
            });

            $('#problem_description').on('input', function() {
                updatePreview();
            });

            $('#capture_photo').on('click', function() {
                $('#camera_input').click();
            });

            $('#camera_input').on('change', function() {
                handlePhotoSelection(this.files);
            });

            $('#repair_form').on('submit', function(e) {
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
                url: '/repair-request/get-options',
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

        function handlePhotoSelection(files) {
            // Merge with existing photos
            Array.from(files).forEach(file => {
                selectedPhotos.push(file);
            });
            displayPhotoPreview();
            updatePreview();
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
            updatePreview();
        }

        function submitForm() {
            let formData = new FormData($('#repair_form')[0]);
            
            // Add selected photos
            selectedPhotos.forEach(function(file, index) {
                formData.append(`photos[${index}]`, file);
            });
            
            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            $.ajax({
                url: '/repair-request',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#submit_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: `Request ${response.request_code} berhasil dikirim!`,
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route("repair-request.index") }}';
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
                            text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat mengirim request.'
                        });
                    }
                },
                complete: function() {
                    $('#submit_btn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim Request');
                }
            });
        }
    </script>
@endsection