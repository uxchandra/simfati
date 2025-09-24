@extends('layouts.app')


@section('content')
    <div class="section-header">
        <h1>Machines - {{ isset($category) ? $category->name : 'Pilih Kategori' }}</h1>
        @if(isset($category))
        <div class="ml-auto">
            <a href="{{ isset($category) ? url('/categories/'.$category->id.'/machines/create') : '#' }}" class="btn btn-primary"><i class="fa fa-plus"></i> Machine</a>
        </div>
        @endif
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_category" class="display" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Model</th>
                                    <th>Nomor Seri</th>
                                    <th>Lane</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($category))
        {{-- Modal Create is included from machine.create --}}
    @endif

    <script>
        $(document).ready(function () {
            $('#table_category').DataTable({ paging: true });
            @if(isset($category))
                loadCategoryMachines();
            @endif
        });

        function loadCategoryMachines() {
            $.ajax({
                url: "{{ isset($category) ? url('/categories/'.$category->id.'/machines') : '' }}",
                type: "GET",
                dataType: 'JSON',
                success: function(response) {
                    let counter = 1;
                    const table = $('#table_category').DataTable();
                    table.clear();
                    $.each(response.data, function(_, value) {
                    let badgeClass = '';
                        switch(value.status) {
                            case 'active': badgeClass = 'badge badge-success'; break;
                            case 'inactive': badgeClass = 'badge badge-secondary'; break;
                            default: badgeClass = 'badge badge-primary';
                        }

                        const row = `
                            <tr>
                                <td>${counter++}</td>
                                <td>${value.kode ?? '-'}</td>
                                <td>${value.model ?? '-'}</td>
                                <td>${value.nomor_seri ?? '-'}</td>
                                <td>${value.lane ?? '-'}</td>
                                <td><span class="${badgeClass}">${(value.status || '').charAt(0).toUpperCase() + (value.status || '').slice(1)}</span></td>
                            </tr>
                        `;
                        table.row.add($(row)).draw(false);
                    });
                }
            });
        }
    </script>
@endsection