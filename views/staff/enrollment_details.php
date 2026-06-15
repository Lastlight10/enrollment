<div class="mb-4">
    <a href="/staff/enrollments" class="btn btn-link text-decoration-none p-0">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
    <h2 class="mt-2">Enrollment Details</h2>
</div>
<?php foreach (['error' => 'danger', 'success' => 'success'] as $key => $type): ?>
  <?php if (isset($_SESSION[$key])): ?>
    <div class="alert alert-<?= $type ?> alert-dismissible fade show border-0 shadow-sm" role="alert">
      <i class="bi bi-<?= $key === 'error' ? 'exclamation-triangle' : 'check-circle' ?>-fill me-2"></i>
      <?= $_SESSION[$key]; unset($_SESSION[$key]); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
<?php endforeach; ?>
<div class="mb-3 no-print">
      <a href="/staff/enrollments/print/pdf/<?= $e->id ?>" target="_blank" class="btn btn-danger shadow-sm">
        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download Official PDF
      </a>
    </div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">Student Information</h5>
                <p class="mb-1 text-muted small">FULL NAME</p>
                <p class="fw-bold"><?= htmlspecialchars($e->user?->full_name ?? 'Unknown') ?></p>
                
                <p class="mb-1 text-muted small">ID NUMBER</p>
                <p class="fw-bold"><?= htmlspecialchars($e->id_number ?? 'N/A') ?></p>

                <p class="mb-1 text-muted small">SCHOLARSHIP</p>
                <?php 
                    $scholar = $e->scholar_type ?? 'non_scholar';
                    $color = ($scholar == 'full-scholar') ? 'success' : (($scholar == 'half-scholar') ? 'primary' : 'secondary');
                ?>
                <p class="fw-bold text-<?= $color ?> text-uppercase"><?= ucwords(str_replace('_', ' ', $scholar)) ?></p>
                
                <hr class="opacity-25">

                <h5 class="card-title fw-bold mb-3">Course Details</h5>
                <p class="mb-1 text-muted small">ACADEMIC PERIOD</p>
                <!-- Displays: 2025-2026 1st Semester -->
                <p class="fw-bold text-dark">
                    <?= htmlspecialchars($e->period?->acad_year ?? 'N/A') ?> 
                    <span class="text-muted small"><?= htmlspecialchars($e->period?->semester ?? '') ?></span>
                </p>

                <p class="mb-1 text-muted small">APPLIED FOR</p>
                <p class="text-primary fw-bold mb-1"><?= htmlspecialchars($e->course?->course_name ?? 'N/A') ?></p>
                <span class="badge bg-light text-dark border"><?= $e->grade_year ?></span>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Payment Schedule</h5>
            </div>
            <div class="card-body p-0">
                <?php if($e->payments->isEmpty()): ?>
                    <div class="p-4 text-center text-muted small">No fees generated yet.</div>
                <?php else: ?>
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Type</th>
                                <th>Amount</th>
                                <th class="text-end pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $totalFees = 0; 
                                foreach($e->payments as $p): 
                                $totalFees += $p->amount;
                            ?>
                                <tr>
                                    <td class="ps-3 small fw-bold">
                                      <?= ($p->payment_type === 'full_payment') ? 'Full Payment' : ucfirst($p->payment_type) ?>
                                    </td>
                                    <td class="small">₱<?= number_format($p->amount, 2) ?></td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <?php if($p->proof_path): ?>
                                                <?php if($p->status === 'need_verification'): ?>
                                                    <button class="btn btn-sm btn-primary text-white rounded-pill px-3 shadow-sm" 
                                                            onclick="openPaymentReview(<?= $p->id ?>, '<?= $p->payment_type ?>', '<?= $p->proof_path ?>', '<?= htmlspecialchars(addslashes($p->remarks ?? '')) ?>', '<?= $p->status ?>')">
                                                        <i class="bi bi-shield-check me-1"></i> Verify Now
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="View Receipt"
                                                            onclick="openPaymentReview(<?= $p->id ?>, '<?= $p->payment_type ?>', '<?= $p->proof_path ?>', '<?= htmlspecialchars(addslashes($p->remarks ?? '')) ?>', '<?= $p->status ?>')">
                                                        <i class="bi bi-eye"></i> Review
                                                    </button>
                                                    <span class="badge rounded-pill bg-<?= $p->status === 'paid' ? 'success' : 'danger' ?>" style="font-size: 0.7rem;">
                                                        <?= strtoupper($p->status) ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-warning text-dark" style="font-size: 0.7rem;">
                                                    <?= strtoupper($p->status) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="ps-3 fw-bold small">TOTAL FEES</td>
                                <td colspan="2" class="ps-2 fw-bold text-primary">
                                    ₱<?= number_format($totalFees, 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Selected Subjects</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Code</th>
                            <th>Subject Title</th>
                            <th class="text-center">Units</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalUnits = 0; ?>
                        <?php foreach($e->subjects as $subject): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($subject->subject_code ?? '') ?></td>
                                <td><?= htmlspecialchars($subject->subject_title ?? '') ?></td>
                                <td class="text-center"><?= $subject->units ?? 0 ?></td>
                            </tr>
                            <?php $totalUnits += ($subject->units ?? 0); ?>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="2" class="text-end fw-bold">Total Units:</td>
                            <td class="text-center fw-bold text-primary"><?= $totalUnits ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="d-flex justify-content-end gap-2">
          <?php if($e->status === 'pending'): ?>
            <!-- Action buttons removed: Enrollment/Rejection must be handled elsewhere -->
            <span class="badge bg-warning text-dark px-4 py-2 shadow-sm">
              <i class="bi bi-clock-history me-1"></i> Application Pending
            </span>
          <?php elseif($e->status === 'enrolled'): ?>
            <form action="/staff/enrollments/drop/<?= $e->id ?>" method="POST" onsubmit="return confirm('Are you sure you want to DROP this student?')">
              <button type="submit" class="btn btn-danger px-4 shadow-sm">
                <i class="bi bi-person-x me-1"></i> Drop Student
              </button>
            </form>
          <?php endif; ?>
      </div>
    </div>
</div>
<?php if($e->status === 'enrolled'): ?>
    <button type="button" class="btn btn-primary shadow-sm ms-2" onclick="openAddPaymentModal(<?= $e->id ?>, '<?= addslashes($e->user?->full_name) ?>')">
        <i class="bi bi-plus-circle me-1"></i> Add Fee/Payment
    </button>
<?php endif; ?>



<div class="modal fade" id="paymentReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="paymentReviewForm" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Review <span id="reviewType"></span> Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-7 text-center bg-light rounded p-2">
                    <a id="receiptLink" target="_blank">
                        <img id="receiptPreview" src="" class="img-fluid rounded shadow-sm" style="max-height: 450px; cursor: zoom-in;">
                    </a>
                    <p class="text-muted small mt-2">Click image to view full size</p>
                </div>
                <div class="col-md-5">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ACTION</label>
                        <select name="status" class="form-select border-0 shadow-sm mb-3" required>
                            <option value="paid">Approve (Mark as Paid)</option>
                            <option value="unpaid">Reject (Mark as Unpaid)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">REMARKS / FEEDBACK</label>
                        <textarea name="remarks" id="reviewRemarks" class="form-control border-0 shadow-sm" rows="5" placeholder="e.g. Receipt is blurry, please re-upload." maxlength="100"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Verification</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="rejectForm" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">Reject Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="mb-3">
                    <label class="small text-muted fw-bold">STUDENT:</label>
                    <div id="rejectStudentName" class="h5 text-dark fw-bold"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">REASON FOR REJECTION</label>
                    <textarea name="staff_comments" class="form-control border-0 shadow-sm" rows="4" placeholder="State the reason..." required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 bg-white">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold">Add Additional Fees</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="addPaymentForm" method="POST">
                    <div id="additional-fee-container">
                        <div class="row g-2 mb-2 additional-fee-row align-items-end">
                            <div class="col-7">
                                <label class="form-label small fw-bold text-muted">FEE TYPE</label>
                                <input type="text" name="fees[0][type]" class="form-control border-0 shadow-sm" placeholder="e.g. Graduation Fee, ID" required maxlength="20"
                                pattern="[A-Za-z\s]+"
                                title="Please enter letters only"
                                oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                            </div>
                             <div class="col-4">
                              <label class="form-label small fw-bold text-muted">AMOUNT</label>
                              <input type="number" 
                                  name="fees[0][amount]" 
                                  class="form-control border-0 shadow-sm" 
                                  onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                  step="0.01" 
                                  required 
                                  min="1" 
                                  oninput="if(this.value.length > 6) this.value = this.value.slice(0, 6);"
                                  placeholder="0.00">
                            </div>
                            <div class="col-1"></div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-link text-decoration-none mt-2" onclick="addAdditionalRow()">
                        <i class="bi bi-plus-circle-fill me-1"></i> Add Another Row
                    </button>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addPaymentForm" class="btn btn-primary rounded-pill px-4">Save Fees</button>
            </div>
        </div>
    </div>
</div>

<script>
 // Keep track of indices and modal instances globally
let feeIndex = 1;
let addPaymentIndex = 1;
let enrollModalInstance = null;
let rejectModalInstance = null;
let paymentReviewModalInstance = null;
let addPaymentModalInstance = null;

/**
 * UPDATED: Calculate total for ANY number input in the active modal
 * This now checks both .fee-row and .additional-fee-row
 */
function updateLiveTotal() {
    let total = 0;
    // Selects all number inputs within the modals to ensure nothing is missed
    const inputs = document.querySelectorAll('.fee-row input[type="number"], .additional-fee-row input[type="number"]');
    
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    const totalDisplay = document.getElementById('live-total');
    if (totalDisplay) {
        totalDisplay.innerText = 'Total: ₱' + total.toLocaleString(undefined, {
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2
        });
    }
}

// Add Fee Modal
function openAddPaymentModal(id, name) {
    const form = document.getElementById('addPaymentForm');
    form.action = '/staff/enrollments/add-fees/' + id;
    
    if (!addPaymentModalInstance) {
        addPaymentModalInstance = new bootstrap.Modal(document.getElementById('addPaymentModal'));
    }
    addPaymentModalInstance.show();
}
function addAdditionalRow() {
    const container = document.getElementById('additional-fee-container');
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 additional-fee-row align-items-end';
    
    div.innerHTML = `
        <div class="col-7">
            <input type="text" name="fees[${addPaymentIndex}][type]" class="form-control border-0 shadow-sm" placeholder="Type fee name..." required>
        </div>
        <div class="col-4">
            <input type="number" 
                   name="fees[${addPaymentIndex}][amount]" 
                   class="form-control border-0 shadow-sm" 
                   required 
                   min="1" 
                   onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                   oninput="if(this.value.length > 6) this.value = this.value.slice(0, 6); updateLiveTotal();">
        </div>
        <div class="col-1 text-end">
            <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)">
                <i class="bi bi-dash-circle-fill fs-5"></i>
            </button>
        </div>`;
        
    container.appendChild(div);
    addPaymentIndex++;
    updateLiveTotal();
}

// Enroll Modal
function openEnrollModal(id, name) {
    const form = document.getElementById('enrollForm');
    form.action = '/staff/enrollments/approve/' + id;
    document.getElementById('studentName').innerText = name;
    
    if (!enrollModalInstance) {
        enrollModalInstance = new bootstrap.Modal(document.getElementById('enrollModal'));
        // Listen for inputs to update total
        document.getElementById('enrollModal').addEventListener('input', updateLiveTotal);
    }
    enrollModalInstance.show();
}

// Payment Review Modal
function openPaymentReview(id, type, path, currentRemarks, status) {
    const form = document.getElementById('paymentReviewForm');
    if (!form) return;

    form.action = '/staff/enrollments/payments/verify/' + id;
    document.getElementById('reviewType').innerText = type.charAt(0).toUpperCase() + type.slice(1);
    
    const imagePath = '/static/images/uploads/payments/' + path;
    document.getElementById('receiptPreview').src = imagePath;
    document.getElementById('receiptLink').href = imagePath;
    
    const remarksField = document.getElementById('reviewRemarks');
    if (remarksField) {
        remarksField.value = (currentRemarks && currentRemarks !== 'null') ? currentRemarks : '';
    }

    const statusSelect = form.querySelector('select[name="status"]');
    if (statusSelect) {
        statusSelect.value = (status === 'need_verification') ? 'paid' : status;
    }

    if (!paymentReviewModalInstance) {
        paymentReviewModalInstance = new bootstrap.Modal(document.getElementById('paymentReviewModal'));
    }
    paymentReviewModalInstance.show();
}

// Generic Row Removal
function removeRow(btn) {
    const row = btn.closest('.row');
    const container = row.parentElement;
    
    // Allow removal only if more than one row exists
    if (container.querySelectorAll('.row').length > 1) {
        row.remove();
        updateLiveTotal();
    }
}

// Utility to ensure "Total" resets when modals close
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('hidden.bs.modal', () => {
        const totalDisplay = document.getElementById('live-total');
        if (totalDisplay) totalDisplay.innerText = 'Total: ₱0.00';
    });
});
</script>