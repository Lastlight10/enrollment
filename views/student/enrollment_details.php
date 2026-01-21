<link rel="stylesheet" href="/static/css/student/enrollment_details.css">
<div class="container py-3">
  <div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
      <a href="/student/dashboard" class="btn btn-link text-decoration-none p-0">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
      <h2 class="mt-2 fw-bold">Enrollment Details</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Ref #<?= $e->id ?></li>
          <li class="breadcrumb-item active"><?= htmlspecialchars($e->course?->course_name) ?></li>
          <li class="breadcrumb-item active">Academic Year: <?= htmlspecialchars(string: $e->period?->acad_year) ?></li>
          <li class="breadcrumb-item active">Semester: <?= htmlspecialchars(string: $e->period?->semester) ?></li>
        </ol>
      </nav>
    </div>
    <div class="text-end">
      <span class="badge rounded-pill p-2 px-4 shadow-sm 
        <?= $e->status === 'enrolled' ? 'bg-success' : ($e->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
        <i class="bi bi-circle-fill me-1 small"></i> <?= strtoupper($e->status) ?>
      </span>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-8">
      <?php if($e->remarks): ?>
      <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center">
        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
        <div>
          <h6 class="fw-bold mb-1">Staff Instructions:</h6>
          <p class="mb-0 small"><?= htmlspecialchars($e->remarks) ?></p>
        </div>
      </div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold">Selected Subjects</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Code</th>
                  <th>Subject Name</th>
                  <th class="text-center">Units</th>
                </tr>
              </thead>
              <tbody>
                <?php $total = 0; foreach($e->subjects as $s): $total += $s->units; ?>
                <tr>
                  <td class="ps-4 fw-bold text-muted"><?= $s->subject_code ?></td>
                  <td><?= $s->subject_name ?></td>
                  <td class="text-center"><?= $s->units ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td colspan="2" class="text-end fw-bold">Total Enrolled Units:</td>
                  <td class="text-center fw-bold text-primary"><?= $total ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold">Billing & Payments</h5>
        </div>
        <div class="card-body">
          <?php foreach($e->payments as $p): ?>
          <div class="p-3 border rounded mb-3 <?= $p->status === 'paid' ? 'border-success bg-success-subtle' : 'bg-light' ?>">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-white text-dark border shadow-sm small"><?= strtoupper($p->payment_type) ?></span>
              <span class="small fw-bold <?= $p->status === 'paid' ? 'text-success' : 'text-muted' ?>">
                <?= strtoupper($p->status) ?>
              </span>
            </div>
            <h4 class="fw-bold mb-3 text-dark">₱<?= number_format($p->amount, 2) ?></h4>
            
            <?php if($p->remarks): ?>
              <div class="small text-danger mb-2">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($p->remarks) ?>
              </div>
            <?php endif; ?>

            <?php if($p->status === 'unpaid'): ?>
              <?php if($p->proof_path): ?>
                <div class="text-center bg-white p-2 rounded border border-info">
                  <div class="text-info small mb-2">
                    <i class="bi bi-hourglass-split"></i> Awaiting Verification
                  </div>
                  <a href="/static/images/uploads/payments/<?= $p->proof_path ?>" 
                    target="_blank" 
                    class="btn btn-sm btn-info text-white w-100 rounded-pill">
                    <i class="bi bi-image"></i> View Receipt
                  </a>
                </div>
              <?php else: ?>
                <button class="btn btn-primary w-100 rounded-pill shadow-sm" 
                        onclick="openUploadModal(<?= $p->id ?>, '<?= $p->payment_type ?>')">
                  <i class="bi bi-cloud-arrow-up me-1"></i> Upload Receipt
                </button>
              <?php endif; ?>
            <?php else: ?>
              <div class="text-success small text-center fw-bold">
                <i class="bi bi-patch-check-fill me-1"></i> PAYMENT CONFIRMED
              </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form id="uploadForm" action="/student/payment/upload" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
        <input type="hidden" name="enrollment_id" value="<?= $e->id ?>">
        <input type="hidden" name="payment_id" id="modal_payment_id">

        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold">Upload Proof of Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
    <p class="text-muted small">Type: <span id="modal_payment_type" class="fw-bold text-dark"></span></p>
    
    <input type="hidden" name="MAX_FILE_SIZE" value="5242880"> 
    
    <div class="mb-3 text-center">
      <label for="proof_image" class="form-label d-block p-5 border-2 border-dashed rounded bg-light cursor-pointer">
        <i class="bi bi-camera fs-1 text-muted"></i>
        <p class="mb-0 small text-muted">Select or drag receipt image (JPG/PNG)</p>
        <p class="text-xs text-danger mt-1" style="font-size: 0.7rem;">Max size: 5MB</p>
        <input type="file" name="proof_image" id="proof_image" class="d-none" accept="image/jpeg,image/png" required onchange="previewFile()">
      </label>
      <img id="img-preview" class="img-fluid mt-3 rounded shadow-sm d-none" style="max-height: 250px; object-fit: contain;">
    </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Payment Proof</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openUploadModal(id, type) {
  // FIX: Dynamically set the form action to include the payment ID
  const form = document.getElementById('uploadForm');
  form.action = '/student/payment/upload/' + id;

  document.getElementById('modal_payment_id').value = id;
  document.getElementById('modal_payment_type').innerText = type.toUpperCase();
  
  // Reset preview
  const preview = document.getElementById('img-preview');
  preview.classList.add('d-none');
  preview.src = '';
  
  new bootstrap.Modal(document.getElementById('uploadModal')).show();
}

function previewFile() {
  const preview = document.getElementById('img-preview');
  const file = document.getElementById('proof_image').files[0];
  const reader = new FileReader();

  reader.onloadend = function () {
    preview.src = reader.result;
    preview.classList.remove('d-none');
  }

  if (file) {
    reader.readAsDataURL(file);
  } else {
    preview.src = "";
  }
}
</script>

