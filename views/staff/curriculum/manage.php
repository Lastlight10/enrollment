<div class="container py-4">
  <div class="mb-3">
    <a href="/staff/curriculum" class="btn btn-sm btn-link ps-0 text-decoration-none">
      <i class="bi bi-arrow-left"></i> Back to Curriculum List
    </a>
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
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-0">Manage Curriculum</h2>
      <p class="text-muted">Course: <span class="text-primary fw-bold"><?= htmlspecialchars($course->course_name) ?></span></p>
    </div>
    <div class="d-flex gap-2">
      <div class="input-group input-group-sm" style="width: 250px;">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
        <input type="text" id="subjectSearch" class="form-control border-start-0" placeholder="Search subjects..." maxlength="30">
      </div>
      <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
        <i class="bi bi-plus-lg"></i> Add Subject
      </button>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="curriculumTable">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Year Level</th>
            <th>Semester</th>
            <th>Subject</th>
            <th class="text-center">Units</th>
            <th class="text-end pe-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(count($course->curriculumSubjects) > 0): ?>
            <?php foreach($course->curriculumSubjects as $s): ?>
              <tr>
                <td class="ps-4"><span class="badge bg-secondary"><?= $s->pivot->year_level ?></span></td>
                <td class="fw-medium"><?= $s->pivot->semester ?></td>
                <td>
                  <div class="fw-bold text-dark subject-code"><?= $s->subject_code ?></div>
                  <div class="small text-muted subject-title"><?= $s->subject_title ?></div>
                </td>
                <td class="text-center"><?= $s->units ?></td>
                <td class="text-end pe-4">
                  <button class="btn btn-sm btn-outline-info me-1" 
                    onclick="editCurriculum(<?= $s->id ?>, '<?= $s->pivot->year_level ?>', '<?= $s->pivot->semester ?>')">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" 
                    onclick="confirmDelete(<?= $s->id ?>, '<?= $s->subject_code ?>')">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr id="noResults">
              <td colspan="5" class="text-center py-5 text-muted">No subjects assigned yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal fade" id="addSubjectModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="/staff/curriculum/add" method="POST" class="modal-content border-0 shadow">
      <input type="hidden" name="course_id" value="<?= $course->id ?>">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Add Subjects to Roadmap</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-bold mb-0">Select Subjects</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="selectAllCheck" onclick="toggleAllCheckboxes(this)">
              <label class="form-check-label small fw-bold" for="selectAllCheck">Select All</label>
            </div>
          </div>
          
          <input type="text" class="form-control form-control-sm mb-2" id="modalSubjectSearch" placeholder="Filter subjects..." onkeyup="filterModalSubjects()" maxlength="50">

          <div class="border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto;" id="checkboxList">
            <?php foreach($allSubjects as $sub): ?>
              <div class="form-check mb-2 subject-item">
                <input class="form-check-input subject-checkbox" type="checkbox" name="subject_ids[]" value="<?= $sub->id ?>" id="sub_<?= $sub->id ?>">
                <label class="form-check-label d-block" for="sub_<?= $sub->id ?>">
                  <span class="fw-bold text-dark d-block mb-0"><?= $sub->subject_code ?></span>
                  <span class="small text-muted"><?= $sub->subject_title ?></span>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold text-primary small">Target Year Level</label>
            <select name="year_level" class="form-select form-select-sm" required>
              <option value="1st Year">1st Year</option>
              <option value="2nd Year">2nd Year</option>
              <option value="3rd Year">3rd Year</option>
              <option value="4th Year">4th Year</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold text-primary small">Target Semester</label>
            <select name="semester" class="form-select form-select-sm" required>
              <option value="1st Semester">1st Semester</option>
              <option value="2nd Semester">2nd Semester</option>
              <option value="Summer">Summer</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 bg-light rounded-bottom">
        <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary px-4 shadow-sm">Add Selected Subjects</button>
      </div>
    </form>
  </div>
</div>
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="/staff/curriculum/update" method="POST" class="modal-content border-0 shadow">
      <input type="hidden" name="course_id" value="<?= $course->id ?>">
      <input type="hidden" name="subject_id" id="edit_subject_id">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title fw-bold">Update Subject Placement</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Year Level</label>
            <select name="year_level" id="edit_year" class="form-select" required>
              <option value="1st Year">1st Year</option>
              <option value="2nd Year">2nd Year</option>
              <option value="3rd Year">3rd Year</option>
              <option value="4th Year">4th Year</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Semester</label>
            <select name="semester" id="edit_sem" class="form-select" required>
              <option value="1st Semester">1st Semester</option>
              <option value="2nd Semester">2nd Semester</option>
              <option value="Summer">Summer</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-info px-4 text-white">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <form action="/staff/curriculum/delete" method="POST" class="modal-content border-0 shadow">
      <input type="hidden" name="course_id" value="<?= $course->id ?>">
      <input type="hidden" name="subject_id" id="delete_subject_id">
      <div class="modal-body text-center py-4">
        <i class="bi bi-exclamation-circle text-danger fs-1"></i>
        <h5 class="mt-3">Remove Subject?</h5>
        <p class="text-muted small">Are you sure you want to remove <strong id="delete_code_label"></strong>?</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger px-4">Remove</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  // Search Functionality
  document.getElementById('subjectSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#curriculumTable tbody tr:not(#noResults)');
    
    rows.forEach(row => {
      let code = row.querySelector('.subject-code').textContent.toLowerCase();
      let title = row.querySelector('.subject-title').textContent.toLowerCase();
      if (code.includes(filter) || title.includes(filter)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });

  // Edit Function
  function editCurriculum(subId, year, sem) {
    document.getElementById('edit_subject_id').value = subId;
    document.getElementById('edit_year').value = year;
    document.getElementById('edit_sem').value = sem;
    let editMdl = new bootstrap.Modal(document.getElementById('editModal'));
    editMdl.show();
  }

  // Delete Function
  function confirmDelete(subId, code) {
    document.getElementById('delete_subject_id').value = subId;
    document.getElementById('delete_code_label').innerText = code;
    let delMdl = new bootstrap.Modal(document.getElementById('deleteModal'));
    delMdl.show();
  }
  // 1. Toggle Select All Checkboxes
  function toggleAllCheckboxes(master) {
    const checkboxes = document.querySelectorAll('.subject-checkbox');
    checkboxes.forEach(cb => {
      // Only toggle checkboxes that are currently visible (not filtered out)
      if (cb.closest('.subject-item').style.display !== 'none') {
        cb.checked = master.checked;
      }
    });
  }

  // 2. Filter subjects inside the modal checkbox list
  function filterModalSubjects() {
    let filter = document.getElementById('modalSubjectSearch').value.toLowerCase();
    let items = document.querySelectorAll('.subject-item');
    
    items.forEach(item => {
      let text = item.innerText.toLowerCase();
      item.style.display = text.includes(filter) ? '' : 'none';
    });
  }
</script>