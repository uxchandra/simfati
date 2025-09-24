<!-- Modal Tambah PIC User -->
<div class="modal fade" id="modal_tambah_pic" tabindex="-1" role="dialog" aria-labelledby="modal_tambah_pic"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign PIC to Machines</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Pilih User <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="user_id" style="width: 100%">
                                    <option value="">-- Pilih User --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">
                                            {{ $user->name }} - {{ $user->department->kode ?? 'No Dept' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-user_id"></div>
                            </div>

                            <div class="form-group">
                                <label>Pilih Mesin <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="machine_ids" multiple="multiple" style="width: 100%">
                                    @foreach ($machines as $machine)
                                        <option value="{{ $machine->id }}">
                                            {{ $machine->machine_code }} {{--- {{ $machine->lane ? 'Lane ' . $machine->lane : 'No Lane' }} --}}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-machine_ids"></div>
                                <small class="text-muted">Anda dapat memilih lebih dari satu mesin</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="store">Assign PIC</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styling untuk Select2 multiple selection cards/chips */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff !important; /* Warna background card */
        border: 1px solid #0056b3 !important; /* Border card */
        color: #ffffff !important; /* Warna text putih */
    }

    /* Styling untuk tombol X (remove) */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ffffff !important; /* Warna X putih */
        border-right: 1px solid rgba(255,255,255,0.3) !important;
        font-weight: bold !important;
    }

    /* Hover effect pada tombol X */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ff6b6b !important; /* Warna merah saat hover */
        background-color: rgba(255,255,255,0.2) !important;
    }
</style>

<!-- Script for Create Modal -->
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#user_id').select2({
            dropdownParent: $('#modal_tambah_pic'),
            placeholder: "Cari dan pilih user...",
            allowClear: true
        });

        $('#machine_ids').select2({
            dropdownParent: $('#modal_tambah_pic'),
            placeholder: "Cari dan pilih mesin...",
            allowClear: true
        });

        // Preview user info when selected
        $('#user_id').on('change', function() {
            let selectedUserId = $(this).val();
            if (selectedUserId) {
                let selectedOption = $(this).find('option:selected');
                let userData = selectedOption.text().split(' - ');
                let nameAndUsername = userData[0].split(' (');
                
                $('#preview_name').text(nameAndUsername[0]);
                $('#preview_username').text(nameAndUsername[1] ? nameAndUsername[1].replace(')', '') : '');
                $('#preview_department').text(userData[1] || 'No Department');
                
                // Get current machine count for this user
                $.ajax({
                    url: `/pic-users/machines/user/${selectedUserId}`,
                    type: "GET",
                    success: function(response) {
                        $('#preview_machine_count').text(response.length);
                    }
                });
                
                $('#user_preview').removeClass('d-none');
            } else {
                $('#user_preview').addClass('d-none');
            }
        });

        // Reset modal when closed
        $('#modal_tambah_pic').on('hidden.bs.modal', function() {
            $('#user_id').val('').trigger('change');
            $('#machine_ids').val([]).trigger('change');
            $('#user_preview').addClass('d-none');
            $('.alert-danger').addClass('d-none').removeClass('d-block');
        });
    });
</script>