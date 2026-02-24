<div class="container py-4">
  <div class="row">
    <div class="col-12">
      <?php foreach (['error' => 'danger', 'success' => 'success', 'info' => 'info'] as $key => $type): ?>
        <?php if (isset($_SESSION[$key])): ?>
          <div class="alert alert-<?= $type ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-<?= $key === 'error' ? 'exclamation-triangle' : ($key === 'success' ? 'check-circle' : 'info-circle') ?>-fill me-2"></i>
            <?= $_SESSION[$key]; unset($_SESSION[$key]); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="row align-items-center mb-4">
    <div class="col-md-8">
      <h2 class="fw-bold text-dark">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?>!</h2>
      <p class="text-muted small text-uppercase mb-0">Student Enrollment History</p>
    </div>
    <div class="col-md-4 text-md-end">
      <a href="/student/enroll" class="btn btn-custom-green btn-sm px-3 shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> New Enrollment
      </a>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
      <h5 class="mb-0 fw-bold text-custom-green">My Enrollments</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light text-secondary small text-uppercase">
            <tr>
              <th class="ps-4">Period</th>
              <th>Course & Year</th>
              <th>Subjects</th>
              <th>Status</th>
              <th class="text-end pe-4">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($history) && count($history) > 0): ?>
              <?php foreach ($history as $record): ?>
                <tr>
                  <td class="ps-4">
                    <div class="fw-bold text-dark"><?= htmlspecialchars($record->period->acad_year ?? 'N/A') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($record->period->semester ?? '') ?></div>
                  </td>
                  <td>
                    <div class="small fw-medium text-dark"><?= htmlspecialchars($record->course->course_name ?? 'N/A') ?></div>
                    <div class="text-muted small"><?= $record->grade_year ?></div>
                  </td>
                  <td>
                    <span class="badge border text-dark bg-white" data-bs-toggle="tooltip" data-bs-html="true" 
                      title="<?php foreach($record->subjects as $s) echo htmlspecialchars($s->subject_code) . '<br>'; ?>">
                      <?= count($record->subjects) ?> Subjects
                    </span>
                  </td>
                  <td>
                    <span class="badge rounded-pill <?= $record->status === 'enrolled' ? 'badge-status-enrolled' : 'badge-status-pending' ?>">
                      <?= ucfirst($record->status) ?>
                    </span>
                  </td>
                  <td class="text-end pe-4">
                    <button type="button" class="btn btn-light btn-sm border" data-bs-toggle="modal" data-bs-target="#modal<?= $record->id ?>">
                        <i class="bi bi-eye"></i> Details
                    </button>

                    <div class="modal fade" id="modal<?= $record->id ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header text-white" style="background-color: #004d00;">
                                    <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>Enrollment Details</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4 text-start">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase fw-bold d-block text-start">Academic Period</label>
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($record->period->acad_year ?? 'N/A') ?></span>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <label class="text-muted small text-uppercase fw-bold d-block">Status</label>
                                            <span class="badge rounded-pill <?= $record->status === 'enrolled' ? 'badge-status-enrolled' : 'badge-status-pending' ?>">
                                                <?= ucfirst($record->status) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-custom-green border-bottom pb-2 mb-3">Enrolled Subjects</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead class="table-light small text-uppercase">
                                                <tr><th>Code</th><th>Subject Title</th><th class="text-center">Units</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php $totalUnits = 0; foreach($record->subjects as $subject): $totalUnits += $subject->units; ?>
                                                    <tr>
                                                        <td class="fw-bold"><?= htmlspecialchars($subject->subject_code) ?></td>
                                                        <td class="small"><?= htmlspecialchars($subject->subject_title) ?></td>
                                                        <td class="text-center"><?= $subject->units ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Tooltips (Already in your code)
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    // 2. Modal Event Listeners (Optional)
    // This triggers whenever ANY modal is opened
    const allModals = document.querySelectorAll('.modal');
    allModals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function (event) {
            console.log('Opening details for Enrollment ID:', this.id);
        });
    });
});
</script>