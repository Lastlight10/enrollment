<div class="d-flex justify-content-between align-items-center mb-4">
  <h2>Course Management</h2>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
    <i class="bi bi-plus-circle"></i> Add New Course
  </button>
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
<div class="row mb-3">
  <div class="col-md-4 ms-auto">
    <div class="input-group shadow-sm">
      <span class="input-group-text bg-white border-end-0">
        <i class="bi bi-search text-muted"></i>
      </span>
      <input 
        type="text" 
        id="courseSearch" 
        class="form-control border-start-0 ps-0" 
        maxlength="50"
        placeholder="Search code or course name..."
        onkeyup="filterCourses()"
      >
    </div>
  </div>
</div>
<div class="card shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-dark">
        <tr>
          <th class="ps-3">Course Code</th>
          <th>Course Name</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="courseTableBody">
        <?php if(empty($courses)): ?>
          <tr>
            <td colspan="4" class="text-center py-4 text-muted">No courses found.</td>
          </tr>
        <?php else: ?>
          <?php foreach($courses as $course): ?>
            <tr>
              <td class="ps-3 fw-bold text-primary searchable-code">
                <?= htmlspecialchars($course->course_code) ?>
              </td>
              <td class="searchable-name">
                <?= htmlspecialchars($course->course_name) ?>
              </td>
              <td class="text-center">
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-primary" onclick='editCourse(<?= json_encode($course) ?>)' title="Edit Course">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-outline-danger" onclick="confirmDelete(<?= $course->id ?>)" title="Delete Course">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="addCourseModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="/staff/courses/create" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-bold">Course Code</label>
          <input 
            type="text" 
            name="course_code" 
            class="form-control" 
            placeholder="e.g., BSIT" 
            required 
            maxlength="6"
            pattern="[A-Z]{1,6}"
            oninput="this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');"
            title="Please use capital letters only (max 6 characters)">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold">Course Name</label>
          <input 
            type="text" 
            name="course_name" 
            class="form-control" 
            placeholder="e.g., BS Information Technology" 
            required
            pattern="[A-Za-z\s]+"
            oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '');"
            title="Please enter letters and spaces only"
            maxlength="80">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Course</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editCourseModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editCourseForm" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-bold">Course Code</label>
          <input type="text" name="course_code" id="edit_course_code" class="form-control" required
            placeholder="e.g., BSIT" 
            maxlength="5"
            pattern="[A-Z]{1,5}"
            oninput="this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');"
            title="Please use capital letters only (max 5 characters)">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold">Course Name</label>
          <input type="text" name="course_name" id="edit_course_name" class="form-control" required
            placeholder="e.g., BS Information Technology" 
            required
            pattern="[A-Za-z\s]+"
            oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '');"
            title="Please enter letters and spaces only"
            maxlength="50">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
  let editModalInstance = null;

  function editCourse(course) {
    const form = document.getElementById('editCourseForm');
    form.action = '/staff/courses/update/' + course.id;

    document.getElementById('edit_course_code').value = course.course_code;
    document.getElementById('edit_course_name').value = course.course_name;

    if (!editModalInstance) {
      editModalInstance = new bootstrap.Modal(document.getElementById('editCourseModal'));
    }
    editModalInstance.show();
  }

  function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this course? This may affect students enrolled in this program.')) {
      window.location.href = '/staff/courses/delete/' + id;
    }
  }
 function filterCourses() {
    // 1. Get the search input value
    const filter = document.getElementById('courseSearch').value.toLowerCase();
    
    // 2. Get all rows in the table body
    const rows = document.querySelectorAll('#courseTableBody tr');

    rows.forEach(row => {
        // Skip the "No courses found" row (it has only 1 cell with colspan)
        if (row.cells.length < 2) return;

        // 3. Find the specific cells within the row
        const codeCell = row.querySelector('.searchable-code');
        const nameCell = row.querySelector('.searchable-name');

        if (codeCell && nameCell) {
            const codeText = codeCell.textContent.toLowerCase();
            const nameText = nameCell.textContent.toLowerCase();

            // 4. If the filter is found in either the code OR the name, show the row
            if (codeText.includes(filter) || nameText.includes(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });
}
</script>