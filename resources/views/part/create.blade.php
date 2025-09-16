<!-- Modal Tambah Part -->
<div class="modal fade" id="modal_tambah_part" tabindex="-1" role="dialog" aria-labelledby="modal_tambah_part"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Part</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Part Code</label>
                                <input type="text" class="form-control" id="part_code" placeholder="Masukkan Part Code">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-part_code"></div>
                            </div>

                            <div class="form-group">
                                <label>Part Name</label>
                                <input type="text" class="form-control" id="part_name" placeholder="Masukkan Part Name">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-part_name"></div>
                            </div>

                            <div class="form-group">
                                <label>Part Type</label>
                                <input type="text" class="form-control" id="part_type" placeholder="Masukkan Part Type">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-part_type"></div>
                            </div>

                            <div class="form-group">
                                <label>Machine</label>
                                <select class="form-control" id="machine_id" style="width: 100%">
                                    <option value="">Pilih Machine</option>
                                    @foreach ($machines as $machine)
                                        <option value="{{ $machine->id }}">{{ $machine->machine_name }}</option>
                                    @endforeach
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-machine_id"></div>
                            </div>

                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" class="form-control" id="model" placeholder="Masukkan Model">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-model"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">

                            <div class="form-group">
                                <label>Process</label>
                                <input type="text" class="form-control" id="process" placeholder="Masukkan Process">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-process"></div>
                            </div>

                            <div class="form-group">
                                <label>Customer</label>
                                <input type="text" class="form-control" id="customer" placeholder="Masukkan Customer">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-customer"></div>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" id="status">
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="obsolete">Obsolete</option>
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-status"></div>
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