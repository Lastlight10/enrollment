<div class="container py-4">
  <div class="mb-4">
    <a href="/student/curriculum" class="btn btn-link text-custom-green p-0 mb-2 text-decoration-none">
      <i class="bi bi-arrow-left"></i> Back to Curriculums
    </a>
    <h2 class="fw-bold text-custom-green"><?= htmlspecialchars($course->course_name ?? $course->name) ?></h2>
    <p class="text-muted small">Academic Requirements & Subject List</p>
  </div>
  <div class="row g-2 mb-3">
    <div class="col-md-4">
      <select id="filterYear" class="form-select shadow-sm border-0">
        <option value="">All Year Levels</option>
        <option value="1st Year">1st Year</option>
        <option value="2nd Year">2nd Year</option>
        <option value="3rd Year">3rd Year</option>
        <option value="4th Year">4th Year</option>
      </select>
    </div>
    <div class="col-md-4">
      <select id="filterSemester" class="form-select shadow-sm border-0">
        <option value="">All Semesters</option>
        <option value="1st Semester">1st Semester</option>
        <option value="2nd Semester">2nd Semester</option>
        <option value="Summer">Summer</option>
      </select>
    </div>
</div>

  <?php if(!empty($course->curriculumSubjects)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-4" style="width: 20%;">Year & Sem</th>
                <th style="cursor: pointer;" onclick="sortTable('code')">
                  Subject Code <i class="bi bi-arrow-down-up small text-muted"></i>
                </th>
                <th style="cursor: pointer;" onclick="sortTable('title')">
                  Subject Description <i class="bi bi-arrow-down-up small text-muted"></i>
                </th>
                <th class="text-center">Units</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($course->curriculumSubjects as $subject): ?>
                <tr class="curriculum-row" 
                  data-year="<?= $subject->pivot->year_level ?>" 
                  data-sem="<?= $subject->pivot->semester ?>">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
  const filterYear = document.getElementById('filterYear');
  const filterSem = document.getElementById('filterSemester');
  const tableBody = document.querySelector('tbody');
  
  // Track sort directions
  let sortDirections = { code: true, title: true };

  // --- Filtering Logic ---
  function applyFilters() {
    const yearVal = filterYear.value;
    const semVal = filterSem.value;
    const rows = document.querySelectorAll('.curriculum-row');

    rows.forEach(row => {
      const matchesYear = yearVal === "" || row.getAttribute('data-year') === yearVal;
      const matchesSem = semVal === "" || row.getAttribute('data-sem') === semVal;
      row.style.display = (matchesYear && matchesSem) ? "" : "none";
    });
  }

  // --- Sorting Logic ---
  window.sortTable = function(type) {
    const rows = Array.from(document.querySelectorAll('.curriculum-row'));
    const isAsc = sortDirections[type];

    rows.sort((a, b) => {
      let valA, valB;

      if (type === 'code') {
        valA = a.querySelector('.badge').textContent.trim();
        valB = b.querySelector('.badge').textContent.trim();
      } else {
        // Targets the Subject Description cell
        valA = a.cells[2].textContent.trim();
        valB = b.cells[2].textContent.trim();
      }

      // Use localeCompare with numeric:true to handle codes like CC 101 vs CC 102 properly
      const comparison = valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' });
      return isAsc ? comparison : -comparison;
    });

    // Re-append rows to the DOM in the new order
    rows.forEach(row => tableBody.appendChild(row));
    
    // Toggle direction for next click
    sortDirections[type] = !isAsc;
    applyFilters(); // Re-apply current filters to the new order
  };

  // Run automatic sort by code on page load
  sortTable('code');

  filterYear.addEventListener('change', applyFilters);
  filterSem.addEventListener('change', applyFilters);
});
</script>