<div class="d-flex justify-content-between align-items-center mb-4">
  <h2>Subject Management</h2>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
    <i class="bi bi-book"></i> Add New Subject
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

<div class="card shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-dark">
        <tr>
          <th class="ps-3">Code</th>
          <th>Subject Title</th>
          <th>Units</th>
          <th>Course/Program</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($subjects)): ?>
          <tr>
            <td colspan="5" class="text-center py-4 text-muted">No subjects found.</td>
          </tr>
        <?php else: ?>
          <?php foreach($subjects as $subject): ?>
            <tr>
              <td class="ps-3 fw-bold text-primary"><?= htmlspecialchars($subject->subject_code) ?></td>
              <td><?= htmlspecialchars($subject->subject_title) ?></td>
              <td><?= htmlspecialchars($subject->units) ?></td>
              <td>
                <span class="badge bg-info text-dark">
                  <?= htmlspecialchars($subject->course->course_code ?? 'General') ?>
                </span>
              </td>
              <td class="text-center">
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-primary" onclick='editSubject(<?= json_encode($subject) ?>)' title="Edit">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-outline-danger" onclick="confirmDelete(<?= $subject->id ?>)" title="Delete">
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

<div class="modal fade" id="addSubjectModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="/staff/subjects/create" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-bold">Course/Program</label>
          <select name="course_id" class="form-select" required>
            <option value="">Select Course</option>
            <?php foreach($courses as $course): ?>
              <option value="<?= $course->id ?>"><?= htmlspecialchars($course->course_code) ?> - <?= htmlspecialchars($course->course_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row">
          <div class="col-md-8 mb-3">
            <label class="form-label small fw-bold">Subject Code</label>
            <input type="text" name="subject_code" class="form-control" placeholder="e.g., IPT101" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label small fw-bold">Units</label>
            <input type="number" name="units" class="form-control" min="1" max="6" value="3" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold">Subject Title</label>
          <input type="text" name="subject_title" class="form-control" placeholder="e.g., Integrative Programming" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Subject</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editSubjectModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editSubjectForm" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-bold">Course/Program</label>
          <select name="course_id" id="edit_course_id" class="form-select" required>
            <?php foreach($courses as $course): ?>
              <option value="<?= $course->id ?>"><?= htmlspecialchars($course->course_code) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row">
          <div class="col-md-8 mb-3">
            <label class="form-label small fw-bold">Subject Code</label>
            <input type="text" name="subject_code" id="edit_subject_code" class="form-control" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label small fw-bold">Units</label>
            <input type="number" name="units" id="edit_units" class="form-control" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold">Subject Title</label>
          <input type="text" name="subject_title" id="edit_subject_title" class="form-control" required>
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
  let editSubjectModal = null;

  function editSubject(subject) {
    const form = document.getElementById('editSubjectForm');
    form.action = '/staff/subjects/update/' + subject.id;

    document.getElementById('edit_course_id').value = subject.course_id;
    document.getElementById('edit_subject_code').value = subject.subject_code;
    document.getElementById('edit_subject_title').value = subject.subject_title;
    document.getElementById('edit_units').value = subject.units;

    if (!editSubjectModal) {
      editSubjectModal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
    }
    editSubjectModal.show();
  }

  function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this subject? This cannot be undone if students are already enrolled.')) {
      window.location.href = '/staff/subjects/delete/' + id;
    }
  }
</script>