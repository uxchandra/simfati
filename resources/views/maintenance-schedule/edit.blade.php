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
                                <label>Mesin <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_machine_id" name="machine_id" required>
                                    <option value="">Pilih Mesin</option>
                                </select>
                                <small class="form-text text-muted">Mesin yang akan dijadwalkan maintenance</small>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-machine_id"></div>
                            </div>

                            <div class="form-group">
                                <label>Period (Hari) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_period_days" name="period_days" 
                                       placeholder="Masukkan periode dalam hari" min="1" required>
                                <small class="form-text text-muted">Interval maintenance dalam hari</small>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-period_days"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                                <small class="form-text text-muted">Tanggal mulai schedule</small>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-start_date"></div>
                            </div>

                            <div class="form-group">
                                <label>PIC <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_user_id" name="user_id" required>
                                    <option value="">Pilih PIC</option>
                                </select>
                                <small class="form-text text-muted">Person in Charge untuk maintenance</small>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-user_id"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" id="update_schedule">
                    <i class="fas fa-save"></i> Update Schedule
                </button>
            </div>
        </div>
    </div>
</div>