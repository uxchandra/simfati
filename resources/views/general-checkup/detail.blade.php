<!-- Modal Detail -->
<div class="modal fade" tabindex="-1" role="dialog" id="modal_detail">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modal_detail_title">
                    <i class="fas fa-clipboard-check"></i> Detail General Checkup
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal_detail_body" style="max-height: 75vh; overflow-y: auto;">
                <!-- Content akan di-load via AJAX -->
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Show Modal Detail
$('body').on('click', '.btn-detail', function() {
    let id = $(this).data('id');

    $.ajax({
        url: `/general-checkup/detail/${id}`,
        type: "GET",
        dataType: 'JSON',
        beforeSend: function() {
            $('#modal_detail_body').html(`
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Loading...</p>
                </div>
            `);
            $('#modal_detail').modal('show');
        },
        success: function(response) {
            let data = response.data;
            
            // Header Information
            let headerInfo = `
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Umum</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td width="40%"><strong>Checkup Code</strong></td>
                                        <td>: ${data.checkup_code}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Checkup</strong></td>
                                        <td>: ${data.checkup_date}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type</strong></td>
                                        <td>: <span class="badge ${data.item_type === 'machine' ? 'badge-primary' : 'badge-success'}">${data.item_type.charAt(0).toUpperCase() + data.item_type.slice(1)}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Item Code</strong></td>
                                        <td>: ${data.item_code}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td width="40%"><strong>Item Name</strong></td>
                                        <td>: ${data.item_name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Inspector</strong></td>
                                        <td>: ${data.inspector}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Shift</strong></td>
                                        <td>: <span class="badge badge-info">${data.shift}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Overall Status</strong></td>
                                        <td>: <span class="badge ${getStatusClass(data.overall_status.toLowerCase())}">${data.overall_status.charAt(0).toUpperCase() + data.overall_status.slice(1)}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        ${data.notes ? `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong><i class="fas fa-sticky-note"></i> General Notes:</strong><br>
                                        ${data.notes}
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;

            // Detail Checkup Items
            let detailsHtml = '';
            if (data.details && data.details.length > 0) {
                detailsHtml = `
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-list-check"></i> Detail Checkup Items</h6>
                        </div>
                        <div class="card-body">
                `;

                $.each(data.details, function(key, detail) {
                    let statusClass = getStatusClass(detail.item_status.toLowerCase().replace(' ', '_'));
                    
                    detailsHtml += `
                        <div class="mb-4 ${key > 0 ? 'border-top pt-4' : ''}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-primary mb-0">
                                    <i class="fas fa-wrench"></i> ${detail.check_item_name}
                                </h6>
                                <span class="badge ${statusClass} badge-lg">${detail.item_status}</span>
                            </div>
                            
                            ${detail.maintenance_notes ? `
                                <div class="alert alert-warning mb-3">
                                    <strong><i class="fas fa-tools"></i> Maintenance Notes:</strong><br>
                                    ${detail.maintenance_notes}
                                </div>
                            ` : ''}
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="50%"><i class="fas fa-clipboard-list"></i> Standard</th>
                                            <th width="15%" class="text-center"><i class="fas fa-check-circle"></i> Result</th>
                                            <th width="35%"><i class="fas fa-comment"></i> Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                    
                    if (detail.standards && detail.standards.length > 0) {
                        $.each(detail.standards, function(index, standard) {
                            let resultClass = standard.result === 'OK' ? 'text-success' : 'text-danger';
                            let resultIcon = standard.result === 'OK' ? 'fas fa-check-circle' : 'fas fa-times-circle';
                            
                            detailsHtml += `
                                <tr>
                                    <td>${standard.standard_name}</td>
                                    <td class="text-center">
                                        <span class="${resultClass}">
                                            <i class="${resultIcon}"></i> 
                                            <strong>${standard.result}</strong>
                                        </span>
                                    </td>
                                    <td>${standard.notes || '<em class="text-muted">-</em>'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        detailsHtml += `
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox"></i><br>
                                    <em>Belum ada standard untuk item ini</em>
                                </td>
                            </tr>
                        `;
                    }
                    
                    detailsHtml += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                });
                
                detailsHtml += `
                        </div>
                    </div>
                `;
            } else {
                detailsHtml = `
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-list-check"></i> Detail Checkup Items</h6>
                        </div>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted"><em>Belum ada detail checkup untuk item ini</em></p>
                        </div>
                    </div>
                `;
            }

            // Photos section
            let photosHtml = '';
            if (data.photos && data.photos.length > 0) {
                photosHtml = `
                    <div class="card mb-0">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-camera"></i> Foto Dokumentasi (${data.photos.length} foto)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                `;
                
                $.each(data.photos, function(key, photo) {
                    photosHtml += `
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="card border">
                                <div class="position-relative">
                                    <img src="${photo.photo_url}" 
                                         class="card-img-top photo-thumbnail" 
                                         style="height: 200px; object-fit: cover; cursor: pointer;" 
                                         alt="Checkup Photo"
                                         data-photo-url="${photo.photo_url}"
                                         data-photo-desc="${photo.photo_description}"
                                         data-photo-date="${photo.uploaded_at}">
                                    <div class="position-absolute" style="top: 5px; right: 5px;">
                                        <span class="badge badge-dark badge-sm">
                                            <i class="fas fa-expand-alt"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-2">
                                    <p class="card-text small mb-1">${photo.photo_description}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> ${photo.uploaded_at}
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                photosHtml += `
                            </div>
                        </div>
                    </div>
                `;
            } else {
                photosHtml = `
                    <div class="card mb-0">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-camera"></i> Foto Dokumentasi</h6>
                        </div>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                            <p class="text-muted"><em>Tidak ada foto dokumentasi</em></p>
                        </div>
                    </div>
                `;
            }

            // Footer info
            let footerInfo = `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body p-3">
                                <div class="row text-center">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-user-plus"></i> Created by: <strong>${data.created_by}</strong>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt"></i> Created at: <strong>${data.created_at}</strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Combine all content
            let modalContent = headerInfo + detailsHtml + photosHtml + footerInfo;

            $('#modal_detail_title').html(`<i class="fas fa-clipboard-check"></i> Detail Checkup - ${data.checkup_code}`);
            $('#modal_detail_body').html(modalContent);
        },
        error: function(xhr, status, error) {
            console.error('Error loading detail:', error);
            $('#modal_detail_body').html(`
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h6>Gagal memuat detail data!</h6>
                    <p class="mb-0">Silakan coba lagi atau hubungi administrator.</p>
                </div>
            `);
        }
    });
});

// Photo click handler for SweetAlert2 popup
$('body').on('click', '.photo-thumbnail', function() {
    let photoUrl = $(this).data('photo-url');
    let photoDesc = $(this).data('photo-desc');
    let photoDate = $(this).data('photo-date');
    
    Swal.fire({
        title: 'Foto Dokumentasi',
        html: `
            <div class="text-center">
                <img src="${photoUrl}" 
                     class="img-fluid rounded" 
                     style="max-width: 100%; max-height: 70vh; object-fit: contain; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" 
                     alt="Checkup Photo">
                <div class="mt-3 p-3 bg-light rounded">
                    <p class="mb-2"><strong>Deskripsi:</strong></p>
                    <p class="text-muted">${photoDesc}</p>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> ${photoDate}
                    </small>
                </div>
            </div>
        `,
        showCloseButton: true,
        showConfirmButton: false,
        width: 'auto',
        padding: '20px',
        customClass: {
            popup: 'swal2-photo-popup'
        }
    });
});

// Helper function (pastikan ini ada di file yang sama atau di global scope)
function getStatusClass(status) {
    switch(status) {
        case 'good': return 'badge-success';
        case 'not_good': return 'badge-danger';
        case 'problem': return 'badge-warning';
        case 'critical': return 'badge-danger';
        default: return 'badge-secondary';
    }
}
</script>

<style>
.swal2-photo-popup {
    max-width: 90vw !important;
}

.photo-thumbnail {
    transition: all 0.3s ease;
}

.photo-thumbnail:hover {
    opacity: 0.8;
    transform: scale(1.02);
}

.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.8em;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.table-borderless td {
    border: none;
    padding: 0.25rem 0.5rem;
}

.modal-xl .modal-dialog {
    max-width: 1200px;
}
</style>