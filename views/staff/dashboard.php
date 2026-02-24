<div class="row mb-4">
  <div class="col-md-12">
    <h2>Welcome back, Staff!</h2>
    <p class="text-muted">Here is what's happening with enrollments today, <?= date('F d, Y') ?>.</p>

    <?php foreach (['error' => 'danger', 'success' => 'success', 'info' => 'info'] as $key => $color): ?>
      <?php if (isset($_SESSION[$key])): ?>
        <div class="alert alert-<?= $color ?> alert-dismissible fade show border-0 shadow-sm" role="alert">
          <i class="bi bi-<?= $key === 'error' ? 'exclamation-triangle' : ($key === 'success' ? 'check-circle' : 'info-circle') ?>-fill me-2"></i>
          <?= $_SESSION[$key]; unset($_SESSION[$key]); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm p-3">
      <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded p-3 me-3"><i class="bi bi-people fs-3"></i></div>
        <div>
          <h6 class="mb-0 text-muted">Active Students</h6>
          <h3 class="mb-0 fw-bold"><?= number_format($activeCount) ?></h3>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm p-3">
      <div class="d-flex align-items-center">
        <div class="bg-warning text-dark rounded p-3 me-3"><i class="bi bi-file-earmark-text fs-3"></i></div>
        <div>
          <h6 class="mb-0 text-muted">Pending Enrollments</h6>
          <h3 class="mb-0 fw-bold"><?= number_format($pendingCount) ?></h3>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
      <h6 class="text-muted small uppercase fw-bold">Verified Payments</h6>
      <h4 class="mb-0 text-success"><?= number_format($paidCount) ?></h4>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm p-3 border-start border-danger border-4">
      <h6 class="text-muted small uppercase fw-bold">Unverified/Pending</h6>
      <h4 class="mb-0 text-danger"><?= number_format($unpaidCount) ?></h4>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm p-3 bg-dark text-white">
      <h6 class="text-light small uppercase fw-bold">Total Collection</h6>
      <h4 class="mb-0">₱ <?= number_format($totalRevenue, 2) ?></h4>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
    <h5 class="mb-0 fw-bold">Recent Registrations</h5>
    <a href="/staff/enrollments" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
  </div>
  <div class="table-responsive p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-4">Student ID</th>
          <th>Name</th>
          <th>Course</th>
          <th>Date</th>
          <th class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($recentEnrollments)): ?>
          <tr><td colspan="5" class="text-center py-4 text-muted">No recent registrations found.</td></tr>
        <?php else: ?>
          <?php foreach($recentEnrollments as $e): ?>
            <tr>
              <td class="ps-4 fw-bold">#<?= $e->id ?></td>
              <td><?= htmlspecialchars($e->user->full_name) ?></td>
              <td><?= htmlspecialchars($e->course->course_code) ?></td>
              <td><?= date('M d, Y', strtotime($e->created_at)) ?></td>
              <td class="text-center">
                <?php 
                  $badge = match($e->status) {
                    'enrolled' => 'bg-success',
                    'pending'  => 'bg-warning text-dark',
                    'rejected' => 'bg-danger',
                    default    => 'bg-secondary'
                  };
                ?>
                <span class="badge rounded-pill <?= $badge ?> px-3"><?= ucfirst($e->status) ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>