<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-custom-green">My Enrollments</h2>
    <a href="/student/enroll" class="btn btn-custom-green rounded-pill px-4">
      <i class="bi bi-plus-lg"></i> New Enrollment
    </a>
  </div>

  <?php if(count($enrollments) > 0): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-4">Reference #</th>
                <th>Course</th>
                <th>Academic Period</th>
                <th>Status</th>
                <th>Date Submitted</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($enrollments as $en): ?>
              <tr>
                <td class="ps-4 fw-bold">#<?= $en->id ?></td>
                <td><?= htmlspecialchars($en->course->course_name ?? 'N/A') ?></td>
                <td class="ps-4">
                    <div class="fw-bold text-dark"><?= htmlspecialchars($en->period->acad_year ?? 'N/A') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($en->period->semester ?? '') ?></div>
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