<!-- Modal Edit PIC User -->
<div class="modal fade" id="modal_edit_pic" tabindex="-1" role="dialog" aria-labelledby="modal_edit_pic"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit PIC Machines</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" id="edit_user_id">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Kelola Mesin <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="edit_machine_ids" multiple="multiple" style="width: 100%">
                                    @foreach ($machines as $machine)
                                        <option value="{{ $machine->id }}">
                                            {{ $machine->machine_code }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-machine_ids"></div>
                                <small class="text-muted">Kosongkan jika ingin menghapus semua mesin dari user ini</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="update">Update PIC</button>
            </div>
        </div>
    </div>
</div>

<!-- Script for Edit Modal -->
<script>
    $(document).ready(function() {
        // Initialize Select2 for edit modal
        $('#edit_machine_ids').select2({
            dropdownParent: $('#modal_edit_pic'),
            placeholder: "Cari dan pilih mesin...",
            allowClear: true
        });

        // Store original machines for comparison
        let originalMachines = [];

        // When edit modal is shown, load current machines
        $('#modal_edit_pic').on('shown.bs.modal', function() {
            let userId = $('#edit_user_id').val();
            
            // Load current machines
            $.ajax({
                url: `/pic-users/machines/user/${userId}`,
                type: "GET",
                success: function(response) {
                    originalMachines = response;
                    updateCurrentMachinesList(response);
                    updateChangesPreview();
                }
            });
        });

        // Update current machines list display
        function updateCurrentMachinesList(machines) {
            let html = '';
            if (machines && machines.length > 0) {
                machines.forEach(function(machine) {
                    html += `
                        <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                            <span>${machine.name || machine.kode}</span>
                            <span class="badge badge-primary badge-sm">Active</span>
                        </div>
                    `;
                });
            } else {
                html = '<p class="text-muted mb-0">Tidak ada mesin</p>';
            }
            $('#current_machines_list').html(html);
        }

        // Update changes preview when selection changes
        $('#edit_machine_ids').on('change', function() {
            updateChangesPreview();
        });

        function updateChangesPreview() {
            let selectedIds = $('#edit_machine_ids').val() || [];
            let originalIds = originalMachines.map(m => m.id.toString());
            
            let added = selectedIds.filter(id => !originalIds.includes(id));
            let removed = originalIds.filter(id => !selectedIds.includes(id));
            
            let html = '';
            
            if (added.length > 0) {
                html += '<div class="mb-2"><strong class="text-success">Ditambahkan:</strong><br>';
                added.forEach(function(id) {
                    let option = $(`#edit_machine_ids option[value="${id}"]`);
                    html += `<span class="badge badge-success badge-sm mr-1">${option.text().split(' - ')[0]}</span>`;
                });
                html += '</div>';
            }
            
            if (removed.length > 0) {
                html += '<div class="mb-2"><strong class="text-danger">Dihapus:</strong><br>';
                removed.forEach(function(id) {
                    let machine = originalMachines.find(m => m.id == id);
                    if (machine) {
                        html += `<span class="badge badge-danger badge-sm mr-1">${machine.name || machine.kode}</span>`;
                    }
                });
                html += '</div>';
            }
            
            if (added.length === 0 && removed.length === 0) {
                html = '<p class="text-muted mb-0">Tidak ada perubahan</p>';
            }
            
            $('#changes_preview').html(html);
        }

        // Reset modal when closed
        $('#modal_edit_pic').on('hidden.bs.modal', function() {
            $('#edit_machine_ids').val([]).trigger('change');
            $('#current_machines_list').html('<p class="text-muted">Loading...</p>');
            $('#changes_preview').html('<p class="text-muted">Pilih mesin untuk melihat perubahan</p>');
            $('.alert-danger').addClass('d-none').removeClass('d-block');
            originalMachines = [];
        });
    });
</script>