<style>
  #selectedSummaryList {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    background-image: linear-gradient(45deg, #f8f9fa 25%, transparent 25%, transparent 75%, #f8f9fa 75%, #f8f9fa), 
                      linear-gradient(45deg, #f8f9fa 25%, transparent 25%, transparent 75%, #f8f9fa 75%, #f8f9fa);
    background-size: 10px 10px;
    background-position: 0 0, 5px 5px;
  }

  .subject-item:hover {
    background-color: #f1f3f5;
    cursor: pointer;
  }
</style>
<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h2 class="fw-bold mb-0">Manage Curriculum</h2>
      <p class="text-muted mb-0">Course: <span class="text-primary fw-bold"><?= htmlspecialchars($course->course_name) ?></span></p>
    </div>
    
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <select id="filterYear" class="form-select form-select-sm" style="width: 130px;">
        <option value="">All Years</option>
        <option value="1st Year">1st Year</option>
        <option value="2nd Year">2nd Year</option>
        <option value="3rd Year">3rd Year</option>
        <option value="4th Year">4th Year</option>
      </select>

      <select id="filterSemester" class="form-select form-select-sm" style="width: 140px;">
        <option value="">All Semesters</option>
        <option value="1st Semester">1st Semester</option>
        <option value="2nd Semester">2nd Semester</option>
        <option value="Summer">Summer</option>
      </select>

      <div class="input-group input-group-sm" style="width: 200px;">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
        <input type="text" id="subjectSearch" class="form-control border-start-0" placeholder="Search..." maxlength="50">
      </div>

      <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
        <i class="bi bi-plus-lg"></i> Add Subject
      </button>
    </div>
  </div>
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
  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="curriculumTable">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Year Level</th>
            <th>Semester</th>
            <th>Subject</th>
            <th class="text-center">Units</th>
            <th class="text-end pe-4">Actions</th> <!-- Header stays clean -->
          </tr>
        </thead>
        <tbody>
          <?php if(count($course->curriculumSubjects) > 0): ?>
            <?php foreach($course->curriculumSubjects as $s): ?>
              <tr class="curriculum-row" 
                  data-year="<?= $s->pivot->year_level ?>" 
                  data-sem="<?= $s->pivot->semester ?>">
                <td class="ps-4">
                  <?php 
                    $year = $s->pivot->year_level;
                    $badgeClass = 'bg-secondary';

                    if (strpos($year, '1st') !== false) {
                      $badgeClass = 'bg-success';
                    } elseif (strpos($year, '2nd') !== false) {
                      $badgeClass = 'bg-primary';
                    } elseif (strpos($year, '3rd') !== false) {
                      $badgeClass = 'bg-info';
                    } elseif (strpos($year, '4th') !== false) {
                      $badgeClass = 'bg-danger';
                    }
                  ?>
                  <span class="badge <?= $badgeClass ?> shadow-sm text-uppercase">
                    <?= htmlspecialchars($year) ?>
                  </span>
                </td>
                <td class="fw-medium"><?= $s->pivot->semester ?></td>
                <td>
                  <div class="fw-bold text-dark subject-code"><?= $s->subject_code ?></div>
                  <div class="small text-muted subject-title"><?= $s->subject_title ?></div>
                </td>
                <td class="text-center"><?= $s->units ?></td>
                <td class="text-end pe-4">
                  <!-- ACTION BUTTONS MOVED HERE -->
                  <div class="btn-group shadow-sm">
                    <button type="button" 
                            class="btn btn-white btn-sm border" 
                            onclick="editCurriculum(<?= $s->id ?>, '<?= $s->pivot->year_level ?>', '<?= $s->pivot->semester ?>')"
                            title="Change Placement">
                      <i class="bi bi-pencil-square text-info"></i>
                    </button>
                    
                    <button type="button" 
                            class="btn btn-white btn-sm border" 
                            onclick="confirmDelete(<?= $s->id ?>, '<?= $s->subject_code ?>')"
                            title="Remove from Curriculum">
                      <i class="bi bi-trash3 text-danger"></i>
                    </button>
                  </div>
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
        <div id="selectedPreviewContainer" class="mb-3 d-none">
          <label class="form-label fw-bold text-success small">
            <i class="bi bi-check-circle-fill"></i> Ready to Add:
          </label>
          <div id="selectedSummaryList" class="p-2 border rounded bg-white" style="max-height: 100px; overflow-y: auto;">
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
  document.addEventListener('DOMContentLoaded', function() {
    const subjectSearch = document.getElementById('subjectSearch');
    const filterYear = document.getElementById('filterYear');
    const filterSemester = document.getElementById('filterSemester');
    const rows = document.querySelectorAll('.curriculum-row');
    const noResultsRow = document.getElementById('noResults');

    function performFilter() {
        const searchText = subjectSearch.value.toLowerCase();
        const yearVal = filterYear.value;
        const semVal = filterSemester.value;
        let visibleCount = 0;

        rows.forEach(row => {
            // Get data from table cells and attributes
            const code = row.querySelector('.subject-code').textContent.toLowerCase();
            const title = row.querySelector('.subject-title').textContent.toLowerCase();
            const rowYear = row.getAttribute('data-year');
            const rowSem = row.getAttribute('data-sem');

            // Check if row matches all three filters
            const matchesSearch = code.includes(searchText) || title.includes(searchText);
            const matchesYear = yearVal === "" || rowYear === yearVal;
            const matchesSem = semVal === "" || rowSem === semVal;

            if (matchesSearch && matchesYear && matchesSem) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show/Hide "No subjects assigned" row if all filtered out
        if (noResultsRow) {
            noResultsRow.style.display = (visibleCount === 0) ? '' : 'none';
        }
    }

    // Listen for changes on all filter inputs
    subjectSearch.addEventListener('keyup', performFilter);
    filterYear.addEventListener('change', performFilter);
    filterSemester.addEventListener('change', performFilter);
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
  function updateSelectedPreview() {
    const previewContainer = document.getElementById('selectedPreviewContainer');
    const summaryList = document.getElementById('selectedSummaryList');
    const checkedBoxes = document.querySelectorAll('.subject-checkbox:checked');
    
    // Clear the current preview
    summaryList.innerHTML = '';

    if (checkedBoxes.length > 0) {
        previewContainer.classList.remove('d-none');
        
        checkedBoxes.forEach(cb => {
            // Find the code and title from the associated label
            const label = document.querySelector(`label[for="${cb.id}"]`);
            const code = label.querySelector('.fw-bold').innerText;
            
            // Create a small removable badge for each selected item
            const badge = document.createElement('span');
            badge.className = 'badge bg-light text-dark border me-1 mb-1 d-inline-flex align-items-center';
            badge.style.fontSize = '0.75rem';
            badge.innerHTML = `
                ${code} 
                <i class="bi bi-x-lg ms-2 text-danger" 
                   style="cursor:pointer" 
                   onclick="document.getElementById('${cb.id}').click()"></i>
            `;
            summaryList.appendChild(badge);
        });
    } else {
        previewContainer.classList.add('d-none');
    }
}

// Update your existing toggleAllCheckboxes to call this
function toggleAllCheckboxes(master) {
    const checkboxes = document.querySelectorAll('.subject-checkbox');
    checkboxes.forEach(cb => {
        if (cb.closest('.subject-item').style.display !== 'none') {
            cb.checked = master.checked;
        }
    });
    updateSelectedPreview(); // New call
}

// Listen for any checkbox change inside the modal
document.getElementById('checkboxList').addEventListener('change', function(e) {
    if (e.target.classList.contains('subject-checkbox')) {
        updateSelectedPreview();
    }
});
</script>