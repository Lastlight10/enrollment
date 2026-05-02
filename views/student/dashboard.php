<div class="container py-4">
    <!-- 1. Flash Messages -->
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

    <!-- 2. Header Section -->
    <div class="row align-items-end mb-4">
        <div class="col-md-7">
            <h2 class="fw-bold text-dark mb-1">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?>!</h2>
            <div class="mb-2">
              <label class="text-muted small text-uppercase fw-bold d-block">Course: </label>
                <span class="badge bg-light text-dark border text-uppercase">
                    <i class="bi bi-book me-1"></i> 
                    <?= ($user_course && isset($user_course->course)) ? htmlspecialchars($user_course->course->course_name) : 'No Active Course' ?>
                </span>
            </div>
            
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div class="text-muted small mb-2">
                <i class="bi bi-calendar3 me-1"></i> <?= date('F d, Y') ?> | 
                <i class="bi bi-clock me-1"></i> <span id="liveClock"><?= date("h:i A") ?></span>
            </div>
            <a href="/student/enroll" class="btn btn-custom-green shadow-sm px-4">
                <i class="bi bi-plus-lg me-1"></i> New Enrollment
            </a>
        </div>
    </div>

    <!-- 3. Instructions -->
    <div class="card border-0 bg-light mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold"><i class="bi bi-question-circle me-2"></i>How To Enroll?</h5>
            <ol class="text-muted mb-0">
                <li>Go to <strong>New Enrollment</strong>.</li>
                <li>Choose Academic Period, Course, Year Level, and Scholarship.</li>
                <li>Confirm Enrollment to Proceed.</li>
                <li>Wait for an email of approval from the staff.</li>
            </ol>
        </div>
    </div>

    <!-- 4. Enrollment Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-custom-green">My Enrollment Records</h5>
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
                                        <div class="small fw-medium text-dark"><?= htmlspecialchars($record->course->course_code ?? 'N/A') ?></div>
                                        <div class="text-muted small">Year Level: <?= $record->grade_year ?></div>
                                    </td>
                                    <td>
                                        <span class="badge border text-dark bg-white" data-bs-toggle="tooltip" data-bs-html="true" 
                                              title="<?php foreach($record->subjects as $s) echo htmlspecialchars($s->subject_code) . '<br>'; ?>">
                                            <?= count($record->subjects) ?> Subjects
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?= $record->status === 'enrolled' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= ucfirst($record->status) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal<?= $record->id ?>">
                                            <i class="bi bi-eye me-1"></i> View Details
                                        </button>

                                        <!-- Modal -->
                                        <div class="modal fade" id="modal<?= $record->id ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header text-white" style="background-color: #004d00;">
                                                        <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>Enrollment Details</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-start">
                                                        <div class="row mb-4">
                                                            <div class="col-md-6">
                                                                <label class="text-muted small text-uppercase fw-bold d-block">Academic Period</label>
                                                                <span class="fw-bold text-dark"><?= htmlspecialchars($record->period->acad_year ?? 'N/A') ?> (<?= htmlspecialchars($record->period->semester ?? '') ?>)</span>
                                                            </div>
                                                            <div class="col-md-6 text-md-end">
                                                                <label class="text-muted small text-uppercase fw-bold d-block">Current Status</label>
                                                                <span class="badge <?= $record->status === 'enrolled' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                                    <?= ucfirst($record->status) ?>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <h6 class="fw-bold text-custom-green border-bottom pb-2 mb-3">Enrolled Subjects</h6>
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
                                                            <tfoot class="table-light">
                                                                <tr>
                                                                    <td colspan="2" class="text-end fw-bold">Total Units:</td>
                                                                    <td class="text-center fw-bold"><?= $totalUnits ?></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
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
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No enrollment history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    // 2. Live Clock Implementation
    function updateClock() {
        const now = new Date();
        const options = { hour: '2-digit', minute: '2-digit', hour12: true };
        document.getElementById('liveClock').innerText = now.toLocaleTimeString([], options);
    }
    setInterval(updateClock, 1000);
});
</script>