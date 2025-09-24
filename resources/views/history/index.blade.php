@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>History General Checkup</h1>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filter Controls -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Date Range:</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="start-date">
                                <input type="date" class="form-control" id="end-date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label>Status:</label>
                            <select class="form-control" id="status-filter">
                                <option value="all">All Status</option>
                                <option value="good">Good</option>
                                <option value="problem">Problem</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-2 mt-4">
                            <button class="btn btn-primary" id="apply-filter">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button class="btn btn-light" id="reset-filter">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="history-table">
                            <thead>
                                <tr>
                                    <th>Checkup Code</th>
                                    <th>Item Type</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Checkup Date</th>
                                    <th>Inspector</th>
                                    <th>Shift</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detail-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail General Checkup</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#history-table').DataTable({
        processing: true,
        serverSide: false, // We'll handle our own AJAX
        ajax: {
            url: '{{ route("history.getData") }}',
            data: function(d) {
                d.start_date = $('#start-date').val();
                d.end_date = $('#end-date').val();
                d.status = $('#status-filter').val();
            }
        },
        columns: [
            { data: 'checkup_code' },
            { 
                data: 'item_type',
                render: function(data) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }
            },
            { data: 'item_code' },
            { data: 'item_name' },
            { data: 'checkup_date' },
            { data: 'inspector' },
            { data: 'shift' },
            { 
                data: 'overall_status',
                render: function(data) {
                    var badgeClass = {
                        'good': 'badge-success',
                        'problem': 'badge-warning',
                        'critical': 'badge-danger'
                    }[data] || 'badge-secondary';
                    
                    return '<span class="badge ' + badgeClass + '">' + 
                           data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                }
            },
            { data: 'notes' },
            {
                data: 'id',
                render: function(data) {
                    return '<button class="btn btn-info btn-sm detail-btn" data-id="' + data + '">' +
                           '<i class="fas fa-eye"></i> Detail</button>';
                }
            }
        ],
        order: [[4, 'desc']], // Order by checkup date descending
    });

    // Apply filter
    $('#apply-filter').click(function() {
        table.ajax.reload();
    });

    // Reset filter
    $('#reset-filter').click(function() {
        $('#start-date').val('');
        $('#end-date').val('');
        $('#status-filter').val('all');
        table.ajax.reload();
    });

    // Handle detail button click
    $('#history-table').on('click', '.detail-btn', function() {
        var id = $(this).data('id');
        
        // Load detail data
        $.ajax({
            url: '/history/detail/' + id,
            method: 'GET',
            success: function(response) {
                var data = response.data;
                var modalContent = `
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Checkup Code:</strong> ${data.checkup_code}</p>
                                <p><strong>Item:</strong> ${data.item_type} - ${data.item_name}</p>
                                <p><strong>Inspector:</strong> ${data.inspector}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Date:</strong> ${data.checkup_date}</p>
                                <p><strong>Shift:</strong> ${data.shift}</p>
                                <p><strong>Status:</strong> ${data.overall_status}</p>
                            </div>
                        </div>
                        <hr>
                        <h6>Check Items:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.details.map(detail => `
                                        <tr>
                                            <td>${detail.check_item_name}</td>
                                            <td>${detail.item_status}</td>
                                            <td>${detail.maintenance_notes || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        ${data.photos.length > 0 ? `
                            <hr>
                            <h6>Photos:</h6>
                            <div class="row">
                                ${data.photos.map(photo => `
                                    <div class="col-md-4 mb-3">
                                        <img src="${photo.photo_url}" class="img-fluid" alt="Checkup Photo">
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                `;
                
                $('#detail-modal .modal-body').html(modalContent);
                $('#detail-modal').modal('show');
            },
            error: function() {
                alert('Failed to load detail data');
            }
        });
    });
});
</script>
@endpush
@endsection