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
<div class="container py-5">
  <form action="/student/enroll/submit" method="POST" id="enrollmentForm">
    <div class="row g-4">
      
      
      <div class="col-lg-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 90px;">
          <div class="card-header bg-primary text-white py-3" >
            <h5 class="mb-0 fw-bold">Enrollment Details</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-bold">Academic Period</label>
              <select name="period_id" class="form-select <?= empty($periods) ? 'is-invalid' : '' ?>" required>
                  <?php if (count($periods) > 0): ?>
                      <option value="" disabled selected>-- Select Active Period --</option>
                      <?php foreach ($periods as $p): ?>
                          <option value="<?= $p->id ?>">
                              <?= htmlspecialchars($p->acad_year) ?> - <?= htmlspecialchars($p->semester) ?>
                          </option>
                      <?php endforeach; ?>
                  <?php else: ?>
                      <option value="" disabled>No active periods available</option>
                  <?php endif; ?>
              </select>
              <?php if (count($periods) === 0): ?>
                  <div class="invalid-feedback">
                      Enrollment is currently closed. Please wait for staff to activate a period.
                  </div>
              <?php endif; ?>
          </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Course</label>
              <select name="course_id" class="form-select" required>
                <?php foreach ($courses as $c): ?>
                  <option value="<?= $c->id ?>"><?= htmlspecialchars($c->course_name ?? '') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Year Level</label>
                <select name="grade_year" class="form-select" required>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                    <option value="5th Year">5th Year</option>
                    <option value="Irregular">Irregular</option>
                </select>
            </div>
            <div class="mb-4">
              <label class="form-label fw-bold">Scholarship</label>
              <select name="scholar_type" class="form-select" required>
                <option value="non-scholar">Regular</option>
                <option value="scholar">Full Scholar</option>
                <option value="half-scholar">Half Scholar</option>
              </select>
            </div>
            
            <div class="p-3 bg-light rounded shadow-sm border mb-4">
              <div class="d-flex justify-content-between mb-1">
                <span>Selected Subjects:</span>
                <span id="subjectCount" class="fw-bold">0</span>
              </div>
              <div class="d-flex justify-content-between text-primary fw-bold fs-5">
                <span>Total Units:</span>
                <span id="totalUnits">0</span>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Submit Application</button>
            <a href="/student/dashboard" class="btn btn-link w-100 mt-2 text-muted">Cancel</a>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Available Subjects</h5>
            <input type="text" id="subjectSearch" class="form-control form-control-sm w-50" placeholder="Search by code or name...">
          </div>
          <div class="table-responsive" style="max-height: 350px;">
            <table class="table table-hover align-middle mb-0" id="availableTable">
              <thead class="table-light sticky-top">
                <tr>
                  <th>Code</th>
                  <th>Description</th>
                  <th class="text-center">Units</th>
                  <th class="text-end pe-4">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($subjects as $s): ?>
                  <tr id="row-<?= $s->id ?>">
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s->subject_code) ?></span></td>
                    <td class="small fw-medium"><?= htmlspecialchars($s->subject_title) ?></td>
                    <td class="text-center"><?= $s->units ?></td>
                    <td class="text-end pe-4">
                      <button type="button" class="btn btn-sm btn-outline-success add-subject" 
                        style="border-color: #004d00; color: #004d00;"
                        data-id="<?= $s->id ?>" 
                        data-code="<?= $s->subject_code ?>" 
                        data-desc="<?= $s->subject_title ?>" 
                        data-units="<?= $s->units ?>">
                        <i class="bi bi-plus-lg"></i> Add
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card shadow-sm border-0">
          <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 fw-bold">My Chosen Subjects</h5>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped align-middle mb-0" id="chosenTable">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Code</th>
                  <th>Description</th>
                  <th class="text-center">Units</th>
                  <th class="text-end pe-4">Action</th>
                </tr>
              </thead>
              <tbody id="chosenBody">
                <tr id="emptyPlaceholder">
                  <td colspan="4" class="text-center py-4 text-muted small">No subjects selected yet. Click "Add" above.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </form>
</div>

<script>
  const chosenBody = document.getElementById('chosenBody');
  const emptyPlaceholder = document.getElementById('emptyPlaceholder');
  const totalUnitsEl = document.getElementById('totalUnits');
  const subjectCountEl = document.getElementById('subjectCount');
  let selectedUnits = 0;
  let selectedCount = 0;

  // Add Subject Logic
  document.querySelectorAll('.add-subject').forEach(btn => {
    btn.addEventListener('click', function() {
      const id = this.dataset.id;
      const code = this.dataset.code;
      const desc = this.dataset.desc;
      const units = parseInt(this.dataset.units);

      // Prevent duplicates
      if (document.getElementById(`chosen-${id}`)) return;

      // Hide placeholder
      emptyPlaceholder.style.display = 'none';

      // Update counters
      selectedUnits += units;
      selectedCount++;
      updateUI();

      // Create row in chosen table
      const tr = document.createElement('tr');
      tr.id = `chosen-${id}`;
      tr.innerHTML = `
        <td class="ps-4">
          <span class="fw-bold">${code}</span>
          <input type="hidden" name="subjects[]" value="${id}">
        </td>
        <td class="small">${desc}</td>
        <td class="text-center">${units}</td>
        <td class="text-end pe-4">
          <button type="button" class="btn btn-sm btn-outline-danger remove-subject" data-id="${id}" data-units="${units}">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      `;
      chosenBody.appendChild(tr);

      // Disable button in available table
      this.classList.add('disabled');
      this.innerText = 'Added';
    });
  });

  // Remove Subject Logic
  chosenBody.addEventListener('click', function(e) {
    if (e.target.closest('.remove-subject')) {
      const btn = e.target.closest('.remove-subject');
      const id = btn.dataset.id;
      const units = parseInt(btn.dataset.units);

      document.getElementById(`chosen-${id}`).remove();
      
      const addBtn = document.querySelector(`.add-subject[data-id="${id}"]`);
      addBtn.classList.remove('disabled');
      addBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Add';

      selectedUnits -= units;
      selectedCount--;
      updateUI();

      if (selectedCount === 0) emptyPlaceholder.style.display = 'table-row';
    }
  });

  // Search Filter
  document.getElementById('subjectSearch').addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    document.querySelectorAll('#availableTable tbody tr').forEach(row => {
      if (row.id === 'emptyPlaceholder') return;
      row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
  });

  function updateUI() {
    totalUnitsEl.innerText = selectedUnits;
    subjectCountEl.innerText = selectedCount;
  }
</script>