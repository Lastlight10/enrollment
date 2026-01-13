<div class="container-fluid py-4">
  <div class="row mb-4">
    <div class="col-md-8">
      <h2 class="fw-bold">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Student') ?>!</h2>
      <p class="text-muted">Academic Year 2025-2026 | First Semester</p>
    </div>
    <div class="col-md-4 text-md-end">
      <span class="badge p-2 <?= $status === 'active' ? 'bg-success' : 'bg-warning' ?>">
        Account Status: <?= ucfirst($status ?? 'Pending') ?>
      </span>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title mb-4">Enrollment Progress</h5>
      <div class="row text-center position-relative">
        <div class="col-4">
          <div class="rounded-circle bg-primary text-white d-inline-block p-3 mb-2">
            <i class="bi bi-file-earmark-text fs-4"></i>
          </div>
          <p class="small fw-bold">1. Registration<br><span class="text-success">Completed</span></p>
        </div>
        <div class="col-4">
          <div class="rounded-circle <?= $is_paid ? 'bg-primary' : 'bg-secondary' ?> text-white d-inline-block p-3 mb-2">
            <i class="bi bi-cash-stack fs-4"></i>
          </div>
          <p class="small">2. Payment<br><span class="text-muted"><?= $is_paid ? 'Verified' : 'Pending Cashier' ?></span></p>
        </div>
        <div class="col-4">
          <div class="rounded-circle <?= $status === 'active' ? 'bg-primary' : 'bg-secondary' ?> text-white d-inline-block p-3 mb-2">
            <i class="bi bi-check2-circle fs-4"></i>
          </div>
          <p class="small">3. Admission<br><span class="text-muted">Final Approval</span></p>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-7">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0 py-3">
          <h5 class="mb-0">Announcements</h5>
        </div>
        <div class="card-body">
          <div class="list-group list-group-flush">
            <div class="list-group-item px-0">
              <h6 class="mb-1 fw-bold text-primary">Schedule of Orientation</h6>
              <p class="mb-1 small">The orientation for new students will be held on Jan 20, 2026, at the Main Hall.</p>
              <small class="text-muted">Posted 2 days ago</small>
            </div>
            <div class="list-group-item px-0">
              <h6 class="mb-1 fw-bold text-primary">Library Clearance</h6>
              <p class="mb-1 small">Please ensure all borrowed books are returned before the end of the month.</p>
              <small class="text-muted">Posted 1 week ago</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card border-0 shadow-sm h-100 bg-dark text-white">
        <div class="card-body d-flex flex-column justify-content-center text-center">
          <i class="bi bi-info-circle fs-1 mb-3 text-warning"></i>
          <h5>Required Action</h5>
          <p class="small opacity-75">Please proceed to the Cashier to settle your tuition balance of <strong>₱15,500.00</strong> to finalize your enrollment.</p>
          <button class="btn btn-outline-light mt-3">Download Assessment Form</button>
        </div>
      </div>
    </div>
  </div>
</div>