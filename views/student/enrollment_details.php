<link rel="stylesheet" href="/static/css/student/enrollment_details.css">
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?= $_SESSION['error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <?= $_SESSION['success'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['info'])): ?>
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-info-circle-fill me-2"></i>
    <?= $_SESSION['info'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['info']); ?>
<?php endif; ?>
<div class="container py-3">
  <div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
      <a href="/student/dashboard" class="btn btn-link text-decoration-none p-0">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
      
      <h2 class="mt-2 fw-bold">Enrollment Details</h2>
      <div class="enrollment-info-bar p-3 p-md-4 bg-white shadow-sm rounded-4 mb-4">
        <div class="row g-3">
          <div class="col-6 col-md-auto info-group">
            <label class="d-block text-muted small">Reference</label>
            <span class="fw-bold">#<?= $e->id ?></span>
          </div>
          <div class="col-6 col-md-auto info-group border-start-md">
            <label class="d-block text-muted small">Student ID</label>
            <span class="fw-bold"><?= htmlspecialchars($e->id_number) ?></span>
          </div>
          <div class="col-6 col-md-auto info-group border-start-md">
            <label class="d-block text-muted small">Year Level</label>
            <span class="fw-bold"><?= htmlspecialchars($e->grade_year) ?></span>
          </div>
          <div class="col-12 col-md info-group border-start-md flex-grow-1">
            <label class="d-block text-muted small">Course</label>
            <span class="fw-bold d-inline-block text-nowrap" style="max-width: 100%;">
              <?= htmlspecialchars($e->course?->course_name) ?>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="text-end">
      <span class="badge rounded-pill p-2 px-4 shadow-sm 
        <?= $e->status === 'enrolled' ? 'bg-success' : ($e->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
        <i class="bi bi-circle-fill me-1 small"></i> <?= strtoupper($e->status) ?>
      </span>
    </div>
  </div>
    <div class="mb-3 no-print">
      <a href="/student/enrollment/pdf/<?= $e->id ?>" target="_blank" class="btn btn-danger shadow-sm">
        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download Official PDF
      </a>
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
    <div class="table-responsive d-none d-md-block">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4" style="width: 20%;">Code</th>
            <th style="width: 60%;">Subject Title</th>
            <th class="text-center" style="width: 20%;">Units</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0; foreach($e->subjects as $s): $total += $s->units; ?>
          <tr>
            <td class="ps-4 fw-bold text-muted"><?= $s->subject_code ?></td>
            <td><?= $s->subject_title ?></td>
            <td class="text-center"><?= $s->units ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="d-md-none">
      <?php foreach($e->subjects as $s): ?>
      <div class="p-3 border-bottom">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-bold text-primary small"><?= $s->subject_code ?></div>
            <div class="text-dark py-1"><?= $s->subject_title ?></div>
          </div>
          <span class="badge bg-light text-dark border"><?= $s->units ?> Units</span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="bg-light p-3 d-flex justify-content-between align-items-center">
      <span class="fw-bold ps-md-4">Total Enrolled Units:</span>
      <span class="fs-5 fw-bold text-primary pe-md-4"><?= $total ?></span>
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
            <?php 
              $cardBg = 'bg-light';
              if ($p->status === 'paid') $cardBg = 'border-success bg-success-subtle';
              if ($p->status === 'need_verification') $cardBg = 'border-info bg-info-subtle';
            ?>

            <div class="p-3 border rounded mb-3 <?= $cardBg ?>">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-white text-dark border shadow-sm small"><?= strtoupper($p->payment_type) ?></span>
                <span class="small fw-bold 
                  <?= $p->status === 'paid' ? 'text-success' : ($p->status === 'need_verification' ? 'text-info' : 'text-muted') ?>">
                  <?= $p->status === 'need_verification' ? 'PENDING VERIFICATION' : strtoupper($p->status) ?>
                </span>
              </div>
              
              <h4 class="fw-bold mb-3 text-dark">₱<?= number_format($p->amount, 2) ?></h4>
              
              <?php if($p->remarks): ?>
                <div class="small text-danger mb-2 p-2 bg-white rounded border-start border-danger border-3">
                  <i class="bi bi-exclamation-circle-fill"></i> <strong>Note:</strong> <?= htmlspecialchars($p->remarks) ?>
                </div>
              <?php endif; ?>

              <?php if($p->status === 'paid'): ?>
                <div class="text-success small text-center fw-bold">
                  <i class="bi bi-patch-check-fill me-1"></i> PAYMENT CONFIRMED
                </div>

              <?php elseif($p->status === 'need_verification' || ($p->status === 'unpaid' && $p->proof_path)): ?>
                <div class="text-center bg-white p-3 rounded-4 border border-info shadow-sm">
                  <div class="text-info fw-bold small mb-2">
                    <i class="bi bi-hourglass-split"></i> Awaiting Staff Verification
                  </div>
                  <div class="d-flex gap-2">
                      <a href="/static/images/uploads/payments/<?= $p->proof_path ?>" target="_blank" class="btn btn-sm btn-outline-info w-100 rounded-pill">
                        <i class="bi bi-eye"></i> View Sent Receipt
                      </a>
                      <button class="btn btn-sm btn-light border rounded-pill" onclick="openUploadModal(<?= $p->id ?>, '<?= $p->payment_type ?>')" title="Change Receipt">
                        <i class="bi bi-pencil"></i>
                      </button>
                  </div>
                </div>

              <?php else: ?>
                <?php if(strtolower($e->status) === 'enrolled'): ?>
                  <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary w-100 rounded-pill shadow-sm mb-1" 
                            onclick="openQRModal('<?= $p->payment_type ?>', '<?= number_format($p->amount, 2) ?>', 'gcash')">
                        <i class="bi bi-qr-code-scan me-1"></i> View GCASH QR
                    </button>
                    <button class="btn btn-primary w-100 rounded-pill shadow-sm" 
                            onclick="openUploadModal(<?= $p->id ?>, '<?= $p->payment_type ?>')">
                      <i class="bi bi-cloud-arrow-up me-1"></i> Upload Receipt
                    </button>
                  </div>
                <?php else: ?>
                  <div class="alert alert-secondary py-2 px-3 border-0 small mb-0 rounded-4">
                    <i class="bi bi-lock-fill me-1"></i> Available after <strong>APPROVAL</strong>.
                  </div>
                <?php endif; ?>
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
      <label for="proof_image" class="form-label d-block p-4 border-2 border-dashed rounded bg-light cursor-pointer position-relative">
        <div id="placeholder-content">
          <i class="bi bi-camera fs-1 text-muted"></i>
          <p class="mb-0 small text-muted">Select or drag receipt image (JPG/PNG)</p>
          <p class="text-danger mt-1" style="font-size: 0.7rem;">Max size: 5MB</p>
        </div>

        <img id="img-preview" class="img-fluid rounded shadow-sm d-none" 
            style="max-height: 250px; width: 100%; object-fit: contain;">
        
        <input type="file" name="proof_image" id="proof_image" class="d-none" 
              accept="image/jpeg,image/png" required onchange="previewFile()">
      </label>
      
      <div id="file-info" class="mt-2 d-none">
        <span id="file-name" class="small fw-bold text-primary"></span>
        <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none" onclick="resetUpload()">
          <i class="bi bi-x-circle"></i> Remove
        </button>
      </div>
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
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Scan to Pay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pb-4">
                <p class="text-muted mb-3">Please scan the QR code below using your payment app.</p>
                
                <div class="bg-light p-4 rounded-4 mb-3 d-inline-block border">
                    <img src="/static/images/payments/QR_CODE_GENERAL.jpg" 
                         id="qr-image"
                         alt="Payment QR" 
                         class="img-fluid" 
                         style="max-width: 250px;">
                </div>

                <div class="payment-details">
                    <h6 class="text-muted small mb-1" id="qr-payment-type">PAYMENT</h6>
                    <h3 class="fw-bold text-primary">₱<span id="qr-amount">0.00</span></h3>
                </div>

                <div class="alert alert-warning border-0 small mt-3">
                    <i class="bi bi-info-circle-fill"></i> After paying, please take a screenshot and <strong>Upload the Receipt</strong> to confirm your enrollment.
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-primary rounded-pill px-5" data-bs-dismiss="modal">I've Scanned It</button>
            </div>
        </div>
    </div>
</div>
<script>
const enrollmentStatus = '<?= strtolower(trim($e->status)) ?>';

function openUploadModal(id, type) {
  // Now this check will actually work
  if (enrollmentStatus !== 'enrolled') {
    alert("Cannot upload receipt. Current status: " + enrollmentStatus);
    return;
  }
  
  const form = document.getElementById('uploadForm');
  form.action = '/student/payment/upload/' + id;

  document.getElementById('modal_payment_id').value = id;
  document.getElementById('modal_payment_type').innerText = type.toUpperCase();
  
  const preview = document.getElementById('img-preview');
  preview.classList.add('d-none');
  preview.src = '';
  
  new bootstrap.Modal(document.getElementById('uploadModal')).show();
}

function openQRModal(type, amount, provider) {
    document.getElementById('qr-payment-type').innerText = type.toUpperCase();
    document.getElementById('qr-amount').innerText = amount;
    
    const qrImg = document.getElementById('qr-image');
    const title = document.querySelector('#qrModal .modal-title');

    if (provider === 'gcash') {
        // Matches your provided path: static/images/gcashQR.jpg
        qrImg.src = '/static/images/gcashQR.jpg'; 
        title.innerText = 'Pay via GCash';
    } else if (provider === 'maya') {
        qrImg.src = '/static/images/payments/MAYA_QR.jpg'; 
        title.innerText = 'Pay via Maya';
    } else {
        qrImg.src = '/static/images/payments/QR_CODE_GENERAL.jpg';
        title.innerText = 'Scan to Pay';
    }

    new bootstrap.Modal(document.getElementById('qrModal')).show();
}
function previewFile() {
  const preview = document.getElementById('img-preview');
  const placeholder = document.getElementById('placeholder-content');
  const fileInfo = document.getElementById('file-info');
  const fileName = document.getElementById('file-name');
  const file = document.getElementById('proof_image').files[0];
  
  const maxSize = 5 * 1024 * 1024; // 5MB

  if (file) {
    // 1. Basic Validation
    if (file.size > maxSize) {
      alert("File is too large! Please select an image under 5MB.");
      resetUpload();
      return;
    }

    // 2. Reader Logic
    const reader = new FileReader();
    reader.onloadend = function () {
      preview.src = reader.result;
      
      // Toggle UI visibility
      preview.classList.remove('d-none');
      placeholder.classList.add('d-none');
      fileInfo.classList.remove('d-none');
      fileName.innerText = file.name;
    }
    reader.readAsDataURL(file);
  }
}

function resetUpload() {
  const preview = document.getElementById('img-preview');
  const placeholder = document.getElementById('placeholder-content');
  const fileInfo = document.getElementById('file-info');
  const fileInput = document.getElementById('proof_image');

  fileInput.value = ""; // Clear the actual input
  preview.src = "";
  preview.classList.add('d-none');
  placeholder.classList.remove('d-none');
  fileInfo.classList.add('d-none');
}
</script>

