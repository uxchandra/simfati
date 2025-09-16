<!-- Modal Tambah User -->
<div class="modal fade" id="modal_tambah_user" tabindex="-1" role="dialog" aria-labelledby="modal_tambah_user"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" placeholder="Masukkan Nama Lengkap">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-name"></div>
                            </div>

                            <div class="form-group">
                                <label>Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" placeholder="Masukkan Username">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-username"></div>
                            </div>

                            <div class="form-group">
                                <label>Role <span class="text-danger">*</span></label>
                                <select class="form-control" id="role_id" style="width: 100%">
                                    <option value="">Pilih Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->role }}</option>
                                    @endforeach
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-role_id"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" placeholder="Masukkan Password">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-password"></div>
                            </div>

                            <div class="form-group">
                                <label>Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirmation" placeholder="Konfirmasi Password">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-password_confirmation"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="store">Simpan</button>
            </div>
        </div>
    </div>
</div>