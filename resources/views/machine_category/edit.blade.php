<!-- Modal Edit Machine -->
<div class="modal fade" id="modal_edit_category" tabindex="-1" role="dialog" aria-labelledby="modal_edit_category"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" id="category_id">

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" id="edit_name" placeholder="Masukkan Category Name">
                        <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit-machine_name"></div>
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