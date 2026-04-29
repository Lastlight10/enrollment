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
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-custom-green">My Enrollments</h2>
    <a href="/student/enroll" class="btn btn-custom-green rounded-pill px-4">
      <i class="bi bi-plus-lg"></i> New Enrollment
    </a>
  </div>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-2">
          <label class="small fw-bold text-muted">REFERENCE #</label>
          <input type="text" id="filterRef" class="form-control shadow-sm" placeholder="Ex: 123456" onkeyup="filterEnrollments()" maxlength="6">
        </div>

        <div class="col-md-2">
          <label class="small fw-bold text-muted">COURSE</label>
          <select id="filterCourse" class="form-select shadow-sm" onchange="filterEnrollments()">
            <option value="">All Courses</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="small fw-bold text-muted">STATUS</label>
          <select id="filterStatus" class="form-select shadow-sm" onchange="filterEnrollments()">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="enrolled">Enrolled</option>
            <option value="rejected">Rejected</option>
            <option value="dropped">Dropped</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="small fw-bold text-muted">ACADEMIC PERIOD/SEM</label>
          <select id="filterPeriod" class="form-select shadow-sm" onchange="filterEnrollments()">
            <option value="">All Periods</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <?php if(count($enrollments) > 0): ?>
    <div class="card border-0 shadow-sm d-none d-md-block">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-4">Payments</th>
                <th class="ps-4">Reference #</th>
                <th>Course</th>
                <th>Academic Period</th>
                <th>Status</th>
                <th>Date Submitted</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="enrollmentTableBody">
              <?php foreach($enrollments as $en): ?>
              <tr>
                <td class="ps-4">
                  <div class="d-flex flex-column gap-1">
                      <?php 
                      $payments = $en->payments;
                      if($payments->count() > 0): 
                          foreach($payments as $p): ?>
                              <div class="d-flex align-items-center small">
                                  <i class="bi <?= $p->status === 'paid' ? 'bi-check-circle-fill text-success' : 'bi-clock-history text-warning' ?> me-2"></i>
                                  <span class="text-secondary text-uppercase"><?= htmlspecialchars($p->payment_type) ?>:</span>
                                  <span class="ms-1 fw-bold">₱<?= number_format($p->amount, 2) ?></span>
                              </div>
                          <?php endforeach; 
                      else: ?>
                          <span class="text-muted small">No fees generated</span>
                      <?php endif; ?>
                  </div>
              </td>
                <td class="ps-4 fw-bold searchable-ref"><?= $en->id ?></td>
                <td><?= htmlspecialchars($en->course->course_code ?? 'N/A') ?></td>
                <td>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($en->period->acad_year ?? 'N/A') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($en->period->semester ?? '') ?></div>
                    
                    <span class="d-none searchable-period">
                        <?= htmlspecialchars(($en->period->acad_year ?? '') . ' ' . ($en->period->semester ?? '')) ?>
                    </span>
                </td>
                <td>
                  <span class="badge rounded-pill px-3 <?= $en->status === 'enrolled' ? 'badge-status-enrolled' : 'badge-status-pending' ?>">
                    <?= strtoupper($en->status) ?>
                  </span>
                </td>
                <td><?= $en->created_at->format('M d, Y') ?></td>
                <td class="text-center">
                  <a href="/student/enrollment/details/<?= $en->id ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">
                    <i class="bi bi-eye"></i> View & Upload
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="d-md-none">
      <?php foreach($enrollments as $en): ?>
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div class="mt-2 p-2 bg-light rounded">
                  <small class="text-muted d-block mb-1">Payment Status</small>
                  <?php foreach($en->payments as $p): ?>
                      <div class="d-flex justify-content-between small border-bottom-dashed py-1">
                          <span><?= htmlspecialchars(strtoupper($p->payment_type)) ?></span>
                          <span class="fw-bold <?= $p->status === 'paid' ? 'text-success' : 'text-danger' ?>">
                              <?= $p->status === 'paid' ? 'Paid' : '₱'.number_format($p->amount, 2) ?>
                          </span>
                      </div>
                  <?php endforeach; ?>
              </div>
              <div>
                <span class="text-muted small">Ref #<?= $en->id ?></span>
                <h6 class="fw-bold mb-0"><?= htmlspecialchars($en->course->course_name ?? 'N/A') ?></h6>
              </div>
              <span class="badge rounded-pill px-2 <?= $en->status === 'enrolled' ? 'badge-status-enrolled' : 'badge-status-pending' ?>">
                <?= strtoupper($en->status) ?>
              </span>
            </div>
            
            <div class="row g-0 py-2 border-top border-bottom my-2">
              <div class="col-6">
                <small class="text-muted d-block">Period</small>
                <span class="small fw-bold"><?= htmlspecialchars($en->period->acad_year ?? 'N/A') ?></span>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Submitted</small>
                <span class="small"><?= $en->created_at->format('M d, Y') ?></span>
              </div>
            </div>

            <a href="/student/enrollment/details/<?= $en->id ?>" class="btn btn-outline-success rounded-pill w-100 mt-2">
              <i class="bi bi-eye"></i> View & Upload
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php else: ?>
    <div class="text-center py-5 card border-0 shadow-sm">
      <i class="bi bi-folder2-open display-1 text-muted"></i>
      <p class="mt-3 text-muted">You haven't submitted any enrollments yet.</p>
      <div class="mt-2">
        <a href="/student/enroll" class="btn btn-custom-green rounded-pill">Enroll Now</a>
      </div>
    </div>
  <?php endif; ?>
</div>
<script>
 function filterEnrollments() {
    // Get values safely
    const refInput = document.getElementById('filterRef')?.value.toLowerCase() || "";
    const nameInput = document.getElementById('enrollmentSearch')?.value.toLowerCase() || "";
    const courseSelect = document.getElementById('filterCourse')?.value || "";
    const statusSelect = document.getElementById('filterStatus')?.value.toLowerCase() || "";
    const periodSelect = document.getElementById('filterPeriod')?.value || "";

    const rows = document.querySelectorAll('#enrollmentTableBody tr');

    rows.forEach(row => {
        // 1. Reference # (from the ID column)
        const refText = row.querySelector('.searchable-ref')?.innerText.replace('#', '').toLowerCase() || "";
        
        // 2. Course Code
        const courseCode = row.querySelector('td:nth-child(3)')?.innerText.toLowerCase() || "";
        
        // 3. Status
        const statusText = row.querySelector('.badge')?.innerText.toLowerCase() || "";
        
        // 4. Period (from our hidden span)
        const periodText = row.querySelector('.searchable-period')?.innerText.trim() || "";

        // Logic: Should this row be shown?
        const matchesRef = refText.includes(refInput);
        const matchesName = courseCode.includes(nameInput); // Using this as a secondary search
        const matchesCourse = courseSelect === "" || courseCode.toUpperCase() === courseSelect.toUpperCase();
        const matchesStatus = statusSelect === "" || statusText === statusSelect;
        const matchesPeriod = periodSelect === "" || periodText === periodSelect;

        if (matchesRef && matchesName && matchesCourse && matchesStatus && matchesPeriod) {
            row.style.setProperty('display', '', 'important');
        } else {
            row.style.setProperty('display', 'none', 'important');
        }
    });
}

// Call this once on page load to fill the dropdowns dynamically
document.addEventListener('DOMContentLoaded', () => {
    const periodSet = new Set();
    const courseSet = new Set();
    
    document.querySelectorAll('.searchable-period').forEach(el => periodSet.add(el.innerText.trim()));
    document.querySelectorAll('#enrollmentTableBody tr td:nth-child(3)').forEach(el => courseSet.add(el.innerText.trim()));

    const periodSelect = document.getElementById('filterPeriod');
    periodSet.forEach(p => {
        if(p) periodSelect.innerHTML += `<option value="${p}">${p}</option>`;
    });

    const courseSelect = document.getElementById('filterCourse');
    courseSet.forEach(c => {
        if(c) courseSelect.innerHTML += `<option value="${c}">${c}</option>`;
    });
});
</script>