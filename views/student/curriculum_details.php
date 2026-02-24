<div class="container py-4">
  <div class="mb-4">
    <a href="/student/curriculum" class="btn btn-link text-custom-green p-0 mb-2 text-decoration-none">
      <i class="bi bi-arrow-left"></i> Back to Curriculums
    </a>
    <h2 class="fw-bold text-custom-green"><?= htmlspecialchars($course->course_name ?? $course->name) ?></h2>
    <p class="text-muted small">Academic Requirements & Subject List</p>
  </div>

  <?php if(!empty($course->curriculumSubjects)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-4">Year & Sem</th>
                <th>Subject Code</th>
                <th>Subject Description</th>
                <th class="text-center">Units</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($course->curriculumSubjects as $subject): ?>
                <tr>
                  <td class="ps-4">
                    <span class="fw-bold text-dark"><?= $subject->pivot->year_level ?? 'N/A' ?></span>
                    <div class="small text-muted">Sem <?= $subject->pivot->semester ?? 'N/A' ?></div>
                  </td>
                  <td>
                    <span class="badge bg-secondary-subtle text-dark border fw-medium px-2">
                      <?= htmlspecialchars($subject->subject_code ?? '') ?>
                    </span>
                  </td>
                  <td class="text-dark">
                    <?= htmlspecialchars($subject->name ?? $subject->subject_title ?? '') ?>
                  </td>
                  <td class="text-center fw-bold"><?= $subject->units ?? 0 ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-warning border-0 shadow-sm">
      No subjects have been assigned to this curriculum yet.
    </div>
  <?php endif; ?>
</div>