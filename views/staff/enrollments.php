<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="mb-0">Enrollment Management</h2>
    <p class="text-muted">Review applications and manually set student fees.</p>
  </div>
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

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Student Details</th>
            <th>Applied Course</th>
            <th>Date Applied</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($enrollments)): ?>
            <tr><td colspan="5" class="text-center py-5 text-muted">No applications found.</td></tr>
          <?php else: ?>
            <?php foreach($enrollments as $e): ?>
              <?php 
                // Uses the Full Name attribute from your User model with null-safety
                $displayName = htmlspecialchars($e->user?->full_name ?? 'Unknown Student'); 
              ?>
              <tr>
                <td class="ps-4">
                  <div class="fw-bold text-dark"><?= $displayName ?></div>
                  <small class="text-muted"><?= htmlspecialchars($e->user?->username ?? 'N/A') ?></small>
                </td>
                <td>
                  <div class="fw-bold text-primary"><?= htmlspecialchars($e->course?->course_code ?? 'N/A') ?></div>
                  <small class="badge bg-light text-dark border"><?= htmlspecialchars($e->grade_year) ?></small>
                </td>
                <td><?= date('M d, Y', strtotime($e->created_at)) ?></td>
                <td>
                  <?php 
                    $statusClass = match($e->status) {
                      'enrolled' => 'bg-success',
                      'rejected' => 'bg-danger',
                      'dropped'  => 'bg-secondary',
                      default    => 'bg-warning text-dark'
                    };
                  ?>
                  <span class="badge rounded-pill <?= $statusClass ?> px-3"><?= ucfirst($e->status) ?></span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm shadow-sm">
                    <a href="/staff/enrollments/details/<?= $e->id ?>" class="btn btn-outline-secondary" title="View Details">
                      <i class="bi bi-eye"></i> View
                    </a>

                    <?php if($e->status === 'pending'): ?>
                      <button class="btn btn-primary px-3" onclick="openEnrollModal(<?= $e->id ?>, '<?= addslashes($displayName) ?>')">
                        <i class="bi bi-check-lg me-1"></i> Enroll
                      </button>
                      <button class="btn btn-outline-danger" onclick="openRejectModal(<?= $e->id ?>, '<?= addslashes($displayName) ?>')">
                        <i class="bi bi-x-circle"></i> Reject
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
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
          <label class="small text-muted fw-bold">REJECTING APPLICATION FOR:</label>
          <div id="rejectStudentName" class="h5 text-dark fw-bold"></div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold text-muted">REASON FOR REJECTION (Staff Only)</label>
          <textarea name="staff_comments" class="form-control border-0 shadow-sm" rows="4" placeholder="e.g., Incomplete requirements..." required></textarea>
        </div>
      </div>
      <div class="modal-footer border-0 bg-white">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Confirm Reject</button>
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
        <label class="small text-muted fw-bold mb-2">FEE BREAKDOWN</label>
        <div id="fee-container">
          <div class="row g-2 mb-2 fee-row align-items-end">
            <div class="col-7">
              <label class="form-label x-small mb-1">Type</label>
              <select name="fees[0][type]" class="form-select border-0 shadow-sm" required>
                <option value="downpayment">Downpayment</option>
                <option value="prelim">Prelim</option>
                <option value="midterm">Midterm</option>
                <option value="finals">Finals</option>
                <option value="others">Others</option>
              </select>
            </div>
            <div class="col-4">
              <label class="form-label x-small mb-1">Amount</label>
              <input type="number" name="fees[0][amount]" class="form-control border-0 shadow-sm" placeholder="0.00" step="0.01" required>
            </div>
            <div class="col-1 text-end">
              <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)">
                <i class="bi bi-dash-circle-fill fs-5"></i>
              </button>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-3 border-dashed py-2" onclick="addRow()">
          <i class="bi bi-plus-circle me-1"></i> Add Fee Component
        </button>
      </div>
      <div class="modal-footer border-0 bg-white">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Confirm & Enroll</button>
      </div>
    </form>
  </div>
</div>

<style>
  .x-small { font-size: 0.75rem; font-weight: bold; color: #6c757d; }
  .border-dashed { border-style: dashed !important; border-width: 2px; }
  .fee-row .form-select, .fee-row .form-control { font-size: 0.9rem; }
</style>

<script>
  let feeIndex = 1;
  let enrollModalInstance = null;
  let rejectModalInstance = null;

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