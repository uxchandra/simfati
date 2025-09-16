<!-- Modal Edit Machine -->
<div class="modal fade" id="modal_edit_machine" tabindex="-1" role="dialog" aria-labelledby="modal_edit_machine"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Machine</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" id="machine_id">
                    
                    <div class="form-group">
                        <label>Machine Code</label>
                        <input type="text" class="form-control" id="edit_machine_code" placeholder="Masukkan Machine Code">
                        <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-machine_code"></div>
                    </div>

                    <div class="form-group">
                        <label>Machine Name</label>
                        <input type="text" class="form-control" id="edit_machine_name" placeholder="Masukkan Machine Name">
                        <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-machine_name"></div>
                    </div>

                    <div class="form-group">
                        <label>Section</label>
                        <input type="text" class="form-control" id="edit_section" placeholder="Masukkan Section">
                        <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-section"></div>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="edit_status">
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                        <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-status"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="update">Update</button>
            </div>
        </div>
    </div>
</div>