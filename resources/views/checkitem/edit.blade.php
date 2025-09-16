@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Edit Indikator Checksheet</h1>
        <div class="ml-auto">
            <a href="{{ route('checkitem.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form id="checkitem_form">
                        @csrf
                        @method('PUT')
                        
                        <!-- Current Item Info -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong><i class="fas fa-info-circle"></i> Sedang mengedit:</strong> 
                                    {{ ucfirst($data['type']) }} - {{ $data['code'] }} ({{ $data['name'] }})
                                </div>
                            </div>
                        </div>

                        <!-- Select Type & Item -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="type" name="type" required>
                                        <option value="">Pilih Type</option>
                                        <option value="machine" {{ $data['type'] == 'machine' ? 'selected' : '' }}>Machine</option>
                                        <option value="part" {{ $data['type'] == 'part' ? 'selected' : '' }}>Part</option>
                                    </select>
                                    <div class="invalid-feedback" id="error-type"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_id">Item <span class="text-danger">*</span></label>
                                    <select class="form-control" id="item_id" name="item_id" required>
                                        <option value="">Pilih Item</option>
                                        @if($data['type'] == 'machine')
                                            @foreach($machines as $machine)
                                                <option value="{{ $machine->id }}" {{ $machine->id == $data['id'] ? 'selected' : '' }}>
                                                    {{ $machine->machine_code }} - {{ $machine->machine_name }}
                                                </option>
                                            @endforeach
                                        @else
                                            @foreach($parts as $part)
                                                <option value="{{ $part->id }}" {{ $part->id == $data['id'] ? 'selected' : '' }}>
                                                    {{ $part->part_code }} - {{ $part->part_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="invalid-feedback" id="error-item_id"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Check Items Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Check Items <span class="text-danger">*</span></label>
                                    <div class="card">
                                        <div class="card-body">
                                            <div id="check_items_container">
                                                <!-- Existing check items akan di-load di sini -->
                                            </div>
                                            
                                            <button type="button" class="btn btn-success btn-sm" id="add_check_item">
                                                <i class="fas fa-plus"></i> Tambah Check Item
                                            </button>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback" id="error-check_items"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" onclick="window.location.href='{{ route('checkitem.index') }}'">
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
        .check-item-card {
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            margin-bottom: 15px;
            background-color: #f8f9fc;
        }
        
        .standard-item {
            border: 1px solid #d1ecf1;
            border-radius: 0.25rem;
            padding: 8px 12px;
            margin-bottom: 8px;
            background-color: #d1ecf1;
            position: relative;
        }
        
        .remove-btn {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #dc3545;
            font-size: 16px;
            cursor: pointer;
        }
        
        .remove-btn:hover {
            color: #c82333;
        }
        
        .standards-container {
            min-height: 50px;
            border: 1px dashed #dee2e6;
            border-radius: 0.25rem;
            padding: 10px;
            background-color: #fff;
        }
        
        .empty-standards {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
    </style>

    <script>
        let checkItemIndex = 0;
        const existingData = @json($data);

        $(document).ready(function() {
            // Load existing data
            loadExistingData();

            // Event listener untuk type change
            $('#type').on('change', function() {
                loadItemOptions();
            });

            // Event listener untuk add check item
            $('#add_check_item').on('click', function() {
                addCheckItem();
            });

            // Form submit
            $('#checkitem_form').on('submit', function(e) {
                e.preventDefault();
                submitForm();
            });
        });

        function loadExistingData() {
            $('#check_items_container').empty();
            
            existingData.check_items.forEach(function(checkItem, index) {
                checkItemIndex = index;
                addCheckItem(checkItem.item_name);
                
                // Add existing standards
                checkItem.standards.forEach(function(standard) {
                    addStandardToItem(index, standard.standard_name);
                });
                
                checkItemIndex++;
            });
        }

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
                success: function(response) {
                    let options = '<option value="">Pilih Item</option>';
                    
                    $.each(response.data, function(key, item) {
                        let selected = item.id == existingData.id ? 'selected' : '';
                        if (type === 'machine') {
                            options += `<option value="${item.id}" ${selected}>${item.machine_code} - ${item.machine_name}</option>`;
                        } else {
                            options += `<option value="${item.id}" ${selected}>${item.part_code} - ${item.part_name}</option>`;
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
                }
            });
        }

        function addCheckItem(itemName = '') {
            let checkItemHtml = `
                <div class="check-item-card p-3" data-index="${checkItemIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-primary">
                            <i class="fas fa-wrench"></i> Check Item ${checkItemIndex + 1}
                        </h6>
                        <button type="button" class="btn btn-danger btn-sm remove-check-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Check Item <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               name="check_items[${checkItemIndex}][item_name]" 
                               placeholder="Contoh: Base Plate, Clamper, Handle" 
                               value="${itemName}"
                               required>
                    </div>
                    
                    <div class="form-group mb-0">
                        <label>Standards <span class="text-muted">(Optional)</span></label>
                        <div class="standards-container" id="standards_${checkItemIndex}">
                            <div class="empty-standards">
                                Belum ada standard. Klik "Tambah Standard" untuk menambahkan.
                            </div>
                        </div>
                        <button type="button" class="btn btn-info btn-sm mt-2 add-standard" data-index="${checkItemIndex}">
                            <i class="fas fa-plus"></i> Tambah Standard
                        </button>
                    </div>
                </div>
            `;
            
            $('#check_items_container').append(checkItemHtml);
            
            if (!itemName) {
                checkItemIndex++;
            }
        }

        function addStandardToItem(index, standardName = '') {
            let standardsContainer = $(`#standards_${index}`);
            
            // Remove empty message if exists
            standardsContainer.find('.empty-standards').remove();
            
            let standardHtml = `
                <div class="standard-item">
                    <input type="text" 
                           class="form-control" 
                           name="check_items[${index}][standards][]" 
                           placeholder="Contoh: tidak pecah, permukaan tidak aus"
                           value="${standardName}"
                           style="padding-right: 40px;">
                    <button type="button" class="remove-btn remove-standard">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            standardsContainer.append(standardHtml);
        }

        // Event delegation untuk dynamic elements
        $(document).on('click', '.remove-check-item', function() {
            $(this).closest('.check-item-card').remove();
        });

        $(document).on('click', '.add-standard', function() {
            let index = $(this).data('index');
            addStandardToItem(index);
        });

        $(document).on('click', '.remove-standard', function() {
            let standardItem = $(this).closest('.standard-item');
            let container = standardItem.parent();
            
            standardItem.remove();
            
            // Show empty message if no standards left
            if (container.find('.standard-item').length === 0) {
                container.html('<div class="empty-standards">Belum ada standard. Klik "Tambah Standard" untuk menambahkan.</div>');
            }
        });

        function submitForm() {
            let formData = new FormData($('#checkitem_form')[0]);
            
            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            $.ajax({
                url: `/checkitem/${existingData.type}/${existingData.id}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengupdate...');
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route("checkitem.index") }}';
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
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat mengupdate data.'
                        });
                    }
                },
                complete: function() {
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Update');
                }
            });
        }
    </script>
@endsection