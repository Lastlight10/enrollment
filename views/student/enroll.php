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
            <div id="scholarAutoSuggestContainer" class="mb-3 d-none">
              <button type="button" id="btnAutoLoad" class="btn btn-outline-success btn-sm w-100 border-dashed">
                <i class="bi bi-magic"></i> Load Recommended Subjects
              </button>
              <div class="form-text text-center text-success">Recommended for your Year Level</div>
            </div>
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
            
            <div class="input-group input-group-sm mb-2 shadow-sm">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input 
                type="text" 
                id="courseSearchInput" 
                class="form-control border-start-0 border-end-0 ps-0" 
                maxlength="50"
                placeholder="Type to filter courses..."
                onkeyup="filterCourses()">
              <button class="btn btn-outline-secondary border-start-0 bg-white text-muted" type="button" onclick="resetCourseFilter()">
                <i class="bi bi-x"></i>
              </button>
            </div>

            <select name="course_id" id="courseSelect" class="form-select" required size="4" style="height: auto;">
              <option value="" disabled selected id="coursePlaceholder">-- Select Course --</option>
              <?php foreach ($courses as $c): ?>
                <option value="<?= $c->id ?>"><?= htmlspecialchars($c->course_name ?? '') ?></option>
              <?php endforeach; ?>
            </select>
            <div id="noCourseMessage" class="small text-danger mt-1 d-none">No matching courses found.</div>
          </div>
            <div class="mb-3">
              <label class="form-label fw-bold">ID Number</label>
              <input 
                type="text" 
                name="id_number" 
                id="id_number" 
                class="form-control" 
                required 
                minlength="7"
                maxlength="7" 
                pattern="\d{7}" 
                title="ID Number must be exactly 7 digits (e.g., 2500123)"
                oninput="this.value = this.value.replace(/[^0-9]/g, '');">
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
                <option value="non-scholar">Non Scholar</option>
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
            <input type="text" id="subjectSearch" class="form-control form-control-sm w-50" placeholder="Search by code or name..." maxlength="30">
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
  function sortChosenSubjects() {
    const rows = Array.from(chosenBody.querySelectorAll('tr:not(#emptyPlaceholder)'));
    
    rows.sort((a, b) => {
        const codeA = a.querySelector('td:first-child').textContent.trim().toLowerCase();
        const codeB = b.querySelector('td:first-child').textContent.trim().toLowerCase();
        return codeA.localeCompare(codeB);
    });

    // Re-append sorted rows to the body
    rows.forEach(row => chosenBody.appendChild(row));
}
  function filterCourses() {
    const input = document.getElementById('courseSearchInput');
    const filter = input.value.toLowerCase();
    const options = courseSelect.getElementsByTagName('option');
    const noResult = document.getElementById('noCourseMessage');
    let hasMatch = false;

    for (let i = 0; i < options.length; i++) {
      if (options[i].id === 'coursePlaceholder') continue;
      const txtValue = options[i].textContent || options[i].innerText;
      if (txtValue.toLowerCase().indexOf(filter) > -1) {
          options[i].style.display = "";
          hasMatch = true;
      } else {
          options[i].style.display = "none";
      }
    }
    noResult.classList.toggle('d-none', hasMatch || filter === "");
  }

  function resetCourseFilter() {
      const input = document.getElementById('courseSearchInput');
      input.value = "";
      filterCourses();
      input.focus();
    }
    // 1. --- UI Element Definitions (ALL at the top) ---
    const courseSelect = document.getElementById('courseSelect');
    const yearSelect = document.querySelector('select[name="grade_year"]');
    const periodSelect = document.querySelector('select[name="period_id"]');
    const scholarSelect = document.querySelector('select[name="scholar_type"]'); // Moved up
    
    const autoLoadBtn = document.getElementById('btnAutoLoad');
    const autoLoadContainer = document.getElementById('scholarAutoSuggestContainer');
    
    const chosenBody = document.getElementById('chosenBody');
    const emptyPlaceholder = document.getElementById('emptyPlaceholder');
    const totalUnitsEl = document.getElementById('totalUnits');
    const subjectCountEl = document.getElementById('subjectCount');
    const chosenCard = chosenBody.closest('.card');

    let selectedUnits = 0;
    let selectedCount = 0;

    // 2. --- Core Logic: Fetch Suggested Subjects ---
    async function fetchSuggestedSubjects() {
        const courseId = courseSelect.value;
        const yearLevel = yearSelect.value;
        const periodId = periodSelect.value;
        const scholarType = scholarSelect.value;

        // CRITICAL: Only auto-fetch if it's a scholar type
        if (scholarType !== 'scholar' && scholarType !== 'half-scholar') {
            return; 
        }

        // Don't fetch if basic info is missing
        if (!courseId || !yearLevel || !periodId) return;

        try {
            chosenCard.classList.add('card-loading');
            
            const url = `/student/enroll/suggested-subjects?course_id=${courseId}&year_level=${encodeURIComponent(yearLevel)}&period_id=${periodId}`;
            const response = await fetch(url);
            
            if (!response.ok) throw new Error('Network response was not ok');
            const subjects = await response.json();

            // Clear existing before adding new suggested ones
            clearChosenSubjects();

            if (subjects.length > 0) {
                subjects.forEach(s => {
                    addSubjectToChosen(s.id, s.subject_code, s.subject_title, s.units);
                });
                sortChosenSubjects();
            }
        } catch (error) {
            console.error("Fetch error:", error);
        } finally {
            chosenCard.classList.remove('card-loading');
        }
    }

    // 3. --- Helper Functions ---
    function addSubjectToChosen(id, code, desc, units) {
        if (document.getElementById(`chosen-${id}`)) return;

        emptyPlaceholder.style.display = 'none';
        selectedUnits += parseInt(units);
        selectedCount++;

        const tr = document.createElement('tr');
        tr.id = `chosen-${id}`;
        tr.innerHTML = `
            <td class="ps-4"><span class="fw-bold">${code}</span><input type="hidden" name="subjects[]" value="${id}"></td>
            <td class="small">${desc}</td>
            <td class="text-center">${units}</td>
            <td class="text-end pe-4">
                <button type="button" class="btn btn-sm btn-outline-danger remove-subject" data-id="${id}" data-units="${units}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        chosenBody.appendChild(tr);

        const addBtn = document.querySelector(`.add-subject[data-id="${id}"]`);
        if (addBtn) {
            addBtn.classList.add('disabled');
            addBtn.innerText = 'Added';
        }
        sortChosenSubjects();
        updateUI();
    }

    function clearChosenSubjects() {
        const rows = chosenBody.querySelectorAll('tr:not(#emptyPlaceholder)');
        rows.forEach(row => row.remove());
        selectedUnits = 0;
        selectedCount = 0;
        document.querySelectorAll('.add-subject').forEach(btn => {
            btn.classList.remove('disabled');
            btn.innerHTML = '<i class="bi bi-plus-lg"></i> Add';
        });
        emptyPlaceholder.style.display = 'table-row';
        updateUI();
    }

    function updateUI() {
        totalUnitsEl.innerText = selectedUnits;
        subjectCountEl.innerText = selectedCount;
    }

    // 4. --- Event Listeners ---
    scholarSelect.addEventListener('change', function() {
      if (this.value === 'scholar' || this.value === 'half-scholar') {
        autoLoadContainer.classList.remove('d-none');
      } else {
        autoLoadContainer.classList.add('d-none');
        // Clear subjects if they are no longer a scholar
        clearChosenSubjects(); 
      }
    });
    // Update search input when a course is selected from the list
    courseSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      if (selectedOption && selectedOption.id !== 'coursePlaceholder') {
        const courseName = selectedOption.text;
        document.getElementById('courseSearchInput').value = courseName;
        
        // Optional: Re-run filter so only the selected one shows, 
        // or just leave it as is so the user sees their choice clearly.
        filterCourses(); 
      }
    });
    // Trigger auto-load when ANY relevant dropdown changes
    [courseSelect, yearSelect, periodSelect, scholarSelect].forEach(el => {
        el.addEventListener('change', () => {
            // Toggle the "Magic Button" visibility
            if (scholarSelect.value === 'scholar' || scholarSelect.value === 'half-scholar') {
                autoLoadContainer.classList.remove('d-none');
            } else {
                autoLoadContainer.classList.add('d-none');
            }
            // Run the auto-fetch
            fetchSuggestedSubjects();
        });
    });

    // Manual "Magic Button" click
    autoLoadBtn.addEventListener('click', async function() {
        if (!courseSelect.value || !yearSelect.value || !periodSelect.value) {
            alert("Please select Academic Period, Course, and Year Level first!");
            return;
        }
        await fetchSuggestedSubjects();
    });

    // Manual Add buttons (from Available table)
    document.querySelectorAll('.add-subject').forEach(btn => {
        btn.addEventListener('click', function() {
            addSubjectToChosen(this.dataset.id, this.dataset.code, this.dataset.desc, this.dataset.units);
        });
    });

    // Remove buttons (from Chosen table)
    chosenBody.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-subject');
        if (btn) {
            const id = btn.dataset.id;
            const units = parseInt(btn.dataset.units);
            document.getElementById(`chosen-${id}`).remove();
            const addBtn = document.querySelector(`.add-subject[data-id="${id}"]`);
            if (addBtn) {
                addBtn.classList.remove('disabled');
                addBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Add';
            }
            selectedUnits -= units;
            selectedCount--;
            if (selectedCount === 0) emptyPlaceholder.style.display = 'table-row';
            updateUI();
        }
    });

    // Subject Search (Filtering Available Table)
    document.getElementById('subjectSearch').addEventListener('keyup', function() {
        const value = this.value.toLowerCase();
        document.querySelectorAll('#availableTable tbody tr').forEach(row => {
            if (row.id === 'emptyPlaceholder') return;
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });
</script>