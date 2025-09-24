@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Tambah Machine - {{ $category->name }}</h1>
        <div class="ml-auto">
            <a href="{{ url('/categories/'.$category->id) }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form id="form_create_machine">
                        <input type="hidden" id="category_id" value="{{ $category->id }}">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Kode</label>
                                <input type="text" class="form-control" id="kode" placeholder="Masukkan Kode">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-kode"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <select class="form-control" id="status">
                                    <option value="">Pilih Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-status"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Model</label>
                                <input type="text" class="form-control" id="model" placeholder="Masukkan Model">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-model"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nomor Seri</label>
                                <input type="text" class="form-control" id="nomor_seri" placeholder="Masukkan Nomor Seri">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-nomor_seri"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Kapasitas</label>
                                <input type="text" class="form-control" id="kapasitas" placeholder="Masukkan Kapasitas">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-kapasitas"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Lane</label>
                                <input type="text" class="form-control" id="lane" placeholder="Masukkan Lane (opsional)">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-lane"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Tahun Pembuatan</label>
                                <input type="text" class="form-control" id="tahun_pembuatan" placeholder="YYYY">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-tahun_pembuatan"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Tanggal Instalasi</label>
                                <input type="text" class="form-control" id="tgl_instal" placeholder="YYYY-MM-DD atau bebas">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-tgl_instal"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Power</label>
                                <input type="text" class="form-control" id="power" placeholder="Masukkan Power">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-power"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sumber Daya</label>
                                <input type="text" class="form-control" id="power_source" placeholder="Masukkan Sumber Daya">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-power_source"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" id="description" rows="2" placeholder="Deskripsi mesin"></textarea>
                            <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-description"></div>
                        </div>

                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea class="form-control" id="keterangan" rows="2" placeholder="Keterangan"></textarea>
                            <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-keterangan"></div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Spesifikasi Stamping (opsional)</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Capacity (kN)</label>
                                <input type="text" class="form-control" id="capacity_kn" placeholder="Capacity kN">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-capacity_kn"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Slide Stroke</label>
                                <input type="text" class="form-control" id="slide_stroke" placeholder="Slide Stroke">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-slide_stroke"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Stroke per Minute (SPM)</label>
                                <input type="text" class="form-control" id="stroke_per_minute" placeholder="SPM">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-stroke_per_minute"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Die Height</label>
                                <input type="text" class="form-control" id="die_height" placeholder="Die Height">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-die_height"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Slide Adjustment</label>
                                <input type="text" class="form-control" id="slide_adjustment" placeholder="Slide Adjustment">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-slide_adjustment"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Slide Area</label>
                                <input type="text" class="form-control" id="slide_area" placeholder="Slide Area">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-slide_area"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Bolster Area</label>
                                <input type="text" class="form-control" id="bolster_area" placeholder="Bolster Area">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-bolster_area"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Main Motor</label>
                                <input type="text" class="form-control" id="main_motor" placeholder="Main Motor">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-main_motor"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Req. Air Pressure</label>
                                <input type="text" class="form-control" id="req_air_pressure" placeholder="Req. Air Pressure">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-req_air_pressure"></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Max Upper Die Weight</label>
                                <input type="text" class="form-control" id="max_upper_die_weight" placeholder="Max Upper Die Weight">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-max_upper_die_weight"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Braking Time</label>
                                <input type="text" class="form-control" id="braking_time" placeholder="Braking Time">
                                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-braking_time"></div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="button" class="btn btn-secondary mr-2" onclick="window.location='{{ url('/categories/'.$category->id) }}'">Batal</button>
                            <button type="button" class="btn btn-primary" id="store">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#store').on('click', function(e) {
            e.preventDefault();
            const token = $("meta[name='csrf-token']").attr("content");
            const formData = new FormData();
            formData.append('category_id', $('#category_id').val());
            formData.append('kode', $('#kode').val());
            formData.append('description', $('#description').val());
            formData.append('kapasitas', $('#kapasitas').val());
            formData.append('model', $('#model').val());
            formData.append('tahun_pembuatan', $('#tahun_pembuatan').val());
            formData.append('nomor_seri', $('#nomor_seri').val());
            formData.append('power', $('#power').val());
            formData.append('tgl_instal', $('#tgl_instal').val());
            formData.append('keterangan', $('#keterangan').val());
            formData.append('capacity_kn', $('#capacity_kn').val());
            formData.append('slide_stroke', $('#slide_stroke').val());
            formData.append('stroke_per_minute', $('#stroke_per_minute').val());
            formData.append('die_height', $('#die_height').val());
            formData.append('slide_adjustment', $('#slide_adjustment').val());
            formData.append('slide_area', $('#slide_area').val());
            formData.append('bolster_area', $('#bolster_area').val());
            formData.append('main_motor', $('#main_motor').val());
            formData.append('req_air_pressure', $('#req_air_pressure').val());
            formData.append('max_upper_die_weight', $('#max_upper_die_weight').val());
            formData.append('power_source', $('#power_source').val());
            formData.append('braking_time', $('#braking_time').val());
            formData.append('lane', $('#lane').val());
            formData.append('status', $('#status').val());
            formData.append('_token', token);

            $.ajax({
                url: '/machine',
                type: 'POST',
                cache: false,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: `${response.message}`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function(){
                        window.location = "{{ url('/categories/'.$category->id) }}";
                    }, 1200);
                },
                error: function(xhr) {
                    const err = xhr.responseJSON || {};
                    function showErr(field){
                        if (err[field] && err[field][0]) {
                            $('#alert-'+field).removeClass('d-none').addClass('d-block').html(err[field][0]);
                        }
                    }
                    showErr('kode');
                    showErr('description');
                    showErr('kapasitas');
                    showErr('model');
                    showErr('tahun_pembuatan');
                    showErr('nomor_seri');
                    showErr('power');
                    showErr('tgl_instal');
                    showErr('keterangan');
                    showErr('capacity_kn');
                    showErr('slide_stroke');
                    showErr('stroke_per_minute');
                    showErr('die_height');
                    showErr('slide_adjustment');
                    showErr('slide_area');
                    showErr('bolster_area');
                    showErr('main_motor');
                    showErr('req_air_pressure');
                    showErr('max_upper_die_weight');
                    showErr('power_source');
                    showErr('braking_time');
                    showErr('lane');
                    showErr('status');
                }
            });
        });
    </script>
@endsection


