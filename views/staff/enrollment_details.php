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
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3">Student Information</h5>
                <p class="mb-1 text-muted small">NAME</p>
                <p class="fw-bold"><?= htmlspecialchars($e->user?->full_name ?? 'Unknown') ?></p>
                
                <p class="mb-1 text-muted small">USERNAME / ID</p>
                <p class="fw-bold"><?= htmlspecialchars($e->user?->username ?? 'N/A') ?></p>
                
                <hr>
                
                <h5 class="card-title fw-bold mb-3">Course Details</h5>
                <p class="mb-1 text-muted small">APPLIED FOR</p>
                <p class="text-primary fw-bold"><?= htmlspecialchars($e->course?->course_name ?? 'N/A') ?></p>
                <span class="badge bg-light text-dark border">Year <?= $e->grade_year ?></span>
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
                            <?php foreach($e->payments as $p): ?>
                                <tr>
                                    <td class="ps-3 small fw-bold"><?= ucfirst($p->payment_type) ?></td>
                                    <td class="small">₱<?= number_format($p->amount, 2) ?></td>
                                    <td class="text-end pe-3">
                                        <?php if($p->proof_path && $p->status === 'unpaid'): ?>
                                            <button class="btn btn-sm btn-info text-white rounded-pill px-3" 
                                                    onclick="openPaymentReview(<?= $p->id ?>, '<?= $p->payment_type ?>', '<?= $p->proof_path ?>', '<?= htmlspecialchars(addslashes($p->remarks ?? '')) ?>')">
                                                Review Receipt
                                            </button>
                                        <?php else: ?>
                                            <span class="badge rounded-pill <?= $p->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-size: 0.7rem;">
                                                <?= strtoupper($p->status) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
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
                                <td><?= htmlspecialchars($subject->subject_name ?? '') ?></td>
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
                <button class="btn btn-outline-danger px-4" onclick="openRejectModal(<?= $e->id ?>, '<?= addslashes($e->user?->full_name ?? 'Unknown') ?>')">Reject Application</button>
                <button class="btn btn-primary px-4 shadow-sm" onclick="openEnrollModal(<?= $e->id ?>, '<?= addslashes($e->user?->full_name ?? 'Unknown') ?>')">Proceed to Enrollment</button>
            <?php elseif($e->status === 'enrolled'): ?>
                <form action="/staff/enrollments/drop/<?= $e->id ?>" method="POST" onsubmit="return confirm('Are you sure you want to DROP this student?')">
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-person-x me-1"></i> Drop Student
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

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
                            <option value="unpaid">Reject (Keep Unpaid)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">REMARKS / FEEDBACK</label>
                        <textarea name="remarks" id="reviewRemarks" class="form-control border-0 shadow-sm" rows="5" placeholder="e.g. Receipt is blurry, please re-upload."></textarea>
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

<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="enrollForm" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold">Approve Enrollment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="mb-3">
                    <label class="small text-muted fw-bold">STUDENT NAME</label>
                    <div id="studentName" class="h5 text-dark fw-bold"></div>
                </div>
                <hr class="text-muted opacity-25">
                <label class="small text-muted fw-bold mb-2">SET UP FEE BREAKDOWN</label>
                <div id="fee-container">
                    <div class="row g-2 mb-2 fee-row align-items-end">
                        <div class="col-7">
                            <select name="fees[0][type]" class="form-select border-0 shadow-sm" required>
                                <option value="downpayment">Downpayment</option>
                                <option value="prelim">Prelim</option>
                                <option value="midterm">Midterm</option>
                                <option value="finals">Finals</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <input type="number" name="fees[0][amount]" class="form-control border-0 shadow-sm" step="0.01" required placeholder="0.00">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-3" onclick="addRow()">+ Add Fee Row</button>
            </div>
            <div class="modal-footer border-0 bg-white">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Confirm & Enroll</button>
            </div>
        </form>
    </div>
</div>



<script>
    let enrollModalInstance = null;
    let rejectModalInstance = null;
    let paymentReviewModalInstance = null;
    let feeIndex = 1;

    function openEnrollModal(id, name) {
        const form = document.getElementById('enrollForm');
        form.action = '/staff/enrollments/approve/' + id;
        document.getElementById('studentName').innerText = name;
        if (!enrollModalInstance) enrollModalInstance = new bootstrap.Modal(document.getElementById('enrollModal'));
        enrollModalInstance.show();
    }

    function openRejectModal(id, name) {
        const form = document.getElementById('rejectForm');
        form.action = '/staff/enrollments/reject/' + id; 
        document.getElementById('rejectStudentName').innerText = name;
        if (!rejectModalInstance) rejectModalInstance = new bootstrap.Modal(document.getElementById('rejectModal'));
        rejectModalInstance.show();
    }

    function openPaymentReview(id, type, path, currentRemarks) {
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

        if (!paymentReviewModalInstance) {
            paymentReviewModalInstance = new bootstrap.Modal(document.getElementById('paymentReviewModal'));
        }
        paymentReviewModalInstance.show();
    }

    function addRow() {
        const container = document.getElementById('fee-container');
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 fee-row align-items-end';
        div.innerHTML = `
            <div class="col-7">
                <select name="fees[${feeIndex}][type]" class="form-select border-0 shadow-sm" required>
                    <option value="downpayment">Downpayment</option>
                    <option value="prelim">Prelim</option>
                    <option value="midterm">Midterm</option>
                    <option value="finals">Finals</option>
                    <option value="others">Others</option>
                </select>
            </div>
            <div class="col-4">
                <input type="number" name="fees[${feeIndex}][amount]" class="form-control border-0 shadow-sm" placeholder="0.00" step="0.01" required>
            </div>
            <div class="col-1 text-end">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)">
                    <i class="bi bi-dash-circle-fill fs-5"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
        feeIndex++;
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.fee-row');
        if (rows.length > 1) btn.closest('.fee-row').remove();
    }
</script>