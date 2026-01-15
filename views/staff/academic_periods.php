<div class="d-flex justify-content-between align-items-center mb-4">
  <h2>Academic Periods</h2>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
    <i class="bi bi-calendar-plus"></i> Add New Period
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
          <th class="ps-3">Academic Year</th>
          <th>Semester</th>
          <th>Status</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($periods)): ?>
          <tr>
            <td colspan="4" class="text-center py-4 text-muted">No academic periods found.</td>
          </tr>
        <?php else: ?>
          <?php foreach($periods as $period): ?>
            <tr>
              <td class="ps-3 fw-bold"><?= htmlspecialchars($period->acad_year) ?></td>
              <td><?= htmlspecialchars($period->semester) ?></td>
              <td>
                <?php if($period->is_active): ?>
                  <span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-primary" onclick='editPeriod(<?= json_encode($period) ?>)' title="Edit Period">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-outline-danger" onclick="confirmDelete(<?= $period->id ?>)" title="Delete Period">
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

<div class="modal fade" id="addPeriodModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="/staff/academic_periods/create" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Academic Period</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-bold">Academic Year</label>
          <input type="text" name="acad_year" class="form-control" placeholder="e.g., 2025-2026" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold">Semester</label>
          <select name="semester" class="form-select" required>
            <option value="1st Semester">1st Semester</option>
            <option value="2nd Semester">2nd Semester</option>
            <option value="Summer">Summer</option>
          </select>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addActive">
          <label class="form-check-label" for="addActive">Set as Active Semester</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Period</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editPeriodModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editPeriodForm" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Academic Period</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-bold">Academic Year</label>
          <input type="text" name="acad_year" id="edit_acad_year" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold">Semester</label>
          <select name="semester" id="edit_semester" class="form-select" required>
            <option value="1st Semester">1st Semester</option>
            <option value="2nd Semester">2nd Semester</option>
            <option value="Summer">Summer</option>
          </select>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
          <label class="form-check-label" for="edit_is_active">Set as Active Semester</label>
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
  let periodModal = null;

  function editPeriod(period) {
    const form = document.getElementById('editPeriodForm');
    form.action = '/staff/academic_periods/update/' + period.id;

    document.getElementById('edit_acad_year').value = period.acad_year;
    document.getElementById('edit_semester').value = period.semester;
    document.getElementById('edit_is_active').checked = period.is_active == 1;

    if (!periodModal) {
      periodModal = new bootstrap.Modal(document.getElementById('editPeriodModal'));
    }
    periodModal.show();
  }

  function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this period? This may affect enrollment records.')) {
      window.location.href = '/staff/academic_periods/delete/' + id;
    }
  }
</script>