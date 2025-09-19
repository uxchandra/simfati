<div class="modal fade" tabindex="-1" role="dialog" id="modal_edit_nama">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Department</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <form enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" id="department_id">
          <div class="form-group">
              <label>Kode Department</label>
              <input type="text" class="form-control" name="kode" id="edit_kode">
              <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-kode"></div>
          </div>
          <div class="form-group">
              <label>Nama Department</label>
              <input type="text" class="form-control" name="nama" id="edit_nama">
              <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-nama"></div>
          </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
          <button type="button" class="btn btn-dark" data-dismiss="modal">Keluar</button>
          <button type="button" class="btn btn-primary" id="update">Edit</button>
        </div>
      </form>
    </div>
  </div>
</div>