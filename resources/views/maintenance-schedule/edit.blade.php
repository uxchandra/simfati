<!-- Modal Edit Maintenance Schedule -->
<div class="modal fade" id="modal_edit_schedule" tabindex="-1" role="dialog" aria-labelledby="modal_edit_schedule"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Maintenance Schedule</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" id="edit_schedule_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type</label>
                                <select class="form-control" id="edit_type" name="type" required>
                                    <option value="">Pilih Type</option>
                                    <option value="machine">Machine</option>
                                    <option value="part">Part</option>
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-type"></div>
                            </div>

                            <div class="form-group">
                                <label>Item</label>
                                <select class="form-control" id="edit_item_id" name="item_id" required>
                                    <option value="">Pilih Item</option>
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-item_id"></div>
                            </div>

                            <div class="form-group">
                                <label>Period (Days)</label>
                                <input type="number" class="form-control" id="edit_period_days" name="period_days" 
                                       placeholder="Masukkan periode dalam hari" min="1" required>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-period_days"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-start_date"></div>
                            </div>

                            <div class="form-group">
                                <label>PIC</label>
                                <select class="form-control" id="edit_user_id" name="user_id" required>
                                    <option value="">Pilih PIC</option>
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-user_id"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="update_schedule">Update</button>
            </div>
        </div>
    </div>
</div>