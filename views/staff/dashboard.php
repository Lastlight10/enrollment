<div class="row mb-4">
  <div class="col-md-12">
    <h2>Welcome back, Staff!</h2>
    <p class="text-muted">Here is what's happening with enrollments today.</p>

    <?php if (isset($error) || isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= $error ?? $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <?php if (isset($success) || isset($_SESSION['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= $success ?? $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($info) || isset($_SESSION['info'])): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?= $info ?? $_SESSION['info'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['info']); ?>
  <?php endif; ?>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm p-3">
      <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded p-3 me-3">
          <i class="bi bi-person-check fs-3"></i>
        </div>
        <div>
          <h6 class="mb-0 text-muted">Active Students</h6>
          <h3 class="mb-0">1,204</h3>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card border-0 shadow-sm p-3">
      <div class="d-flex align-items-center">
        <div class="bg-warning text-dark rounded p-3 me-3">
          <i class="bi bi-clock-history fs-3"></i>
        </div>
        <div>
          <h6 class="mb-0 text-muted">Pending Reviews</h6>
          <h3 class="mb-0">48</h3>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card border-0 shadow-sm p-3">
      <div class="d-flex align-items-center">
        <div class="bg-success text-white rounded p-3 me-3">
          <i class="bi bi-cash-stack fs-3"></i>
        </div>
        <div>
          <h6 class="mb-0 text-muted">Paid Accounts</h6>
          <h3 class="mb-0">912</h3>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-0 py-3">
    <h5 class="mb-0 font-weight-bold">Recent Registrations</h5>
  </div>
  <div class="table-responsive p-3">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th>Course</th>
          <th>Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>#2026-001</td>
          <td>John Wick</td>
          <td>BS Computer Science</td>
          <td>2026-01-13</td>
          <td><span class="badge bg-info">Reviewing</span></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>