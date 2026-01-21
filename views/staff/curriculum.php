<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-0">Program Curriculums</h2>
      <p class="text-muted">Manage the roadmap of subjects for each course</p>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createCurriculumModal">
      <i class="bi bi-plus-lg"></i> Setup New Curriculum
    </button>
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

  <div class="row">
    <?php if(count($curriculums) > 0): ?>
      <?php foreach($curriculums as $item): ?>
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="badge bg-primary text-primary bg-opacity-10 p-2">
                  <?= htmlspecialchars($item->course_code) ?>
                </span>
                <div class="dropdown">
                  <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/staff/curriculum/manage/<?= $item->id ?>"><i class="bi bi-pencil me-2"></i> Edit Subjects</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button class="dropdown-item text-danger" onclick="confirmDeleteCurriculum(<?= $item->id ?>, '<?= $item->course_name ?>')"><i class="bi bi-trash me-2"></i> Delete All</button></li>
                  </ul>
                </div>
              </div>
              <h5 class="fw-bold text-dark"><?= htmlspecialchars($item->course_name) ?></h5>
              <p class="text-muted small">
                <i class="bi bi-book me-1"></i> <?= count($item->curriculumSubjects) ?> Subjects Total
              </p>
            </div>
            <div class="card-footer bg-transparent border-0 pb-3">
              <a href="/staff/curriculum/manage/<?= $item->id ?>" class="btn btn-outline-primary w-100">
                Manage Subjects
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center py-5">
        <i class="bi bi-journal-x fs-1 text-muted"></i>
        <p class="mt-3 text-muted">No curriculums have been set up yet.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="createCurriculumModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="/staff/curriculum/setup" method="POST" class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Setup Program Curriculum</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-bold">Select Course</label>
          <select name="course_id" class="form-select" required>
            <option value="" selected disabled>-- Choose Course --</option>
            <?php foreach($availableCourses as $course): ?>
              <option value="<?= $course->id ?>"><?= $course->course_code ?> - <?= $course->course_name ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="submit" class="btn btn-primary px-4">Initialize</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="deleteCurriculumModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <form action="/staff/curriculum/delete-all" method="POST" class="modal-content border-0 shadow">
      <input type="hidden" name="course_id" id="del_course_id">
      <div class="modal-body text-center py-4">
        <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
        <h5 class="mt-3">Wipe Curriculum?</h5>
        <p class="text-muted small">Remove all subjects from <strong id="del_course_name"></strong>?</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger px-4">Wipe</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  function confirmDeleteCurriculum(id, name) {
    document.getElementById('del_course_id').value = id;
    document.getElementById('del_course_name').innerText = name;
    new bootstrap.Modal(document.getElementById('deleteCurriculumModal')).show();
  }
</script>