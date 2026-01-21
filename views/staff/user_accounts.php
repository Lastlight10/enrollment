<div class="d-flex justify-content-between align-items-center mb-4">
  <h2>User Accounts</h2>
  
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class="bi bi-person-plus"></i> Add New Account
  </button>
</div>

<?php if (isset($error) || isset($_SESSION['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?= $error ?? $_SESSION['error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($success) || isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      <?= $success ?? $_SESSION['success'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($info) || isset($_SESSION['info'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <i class="bi bi-info-circle-fill me-2"></i>
      <?= $info ?? $_SESSION['info'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['info']); ?>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-dark">
        <tr>
          <th class="ps-3">Name</th>
          <th>Username</th>
          <th>Email</th>
          <th>Type</th>
          <th>Status</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($users as $user): ?>
          <tr>
            <td class="ps-3"><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></td>
            <td><?= htmlspecialchars($user->username) ?></td>
            <td><?= htmlspecialchars($user->email) ?></td>
            <td>
              <span class="badge bg-info text-dark">
                <?= ucfirst($user->type) ?>
              </span>
            </td>
            <td>
              <span class="badge <?= $user->status === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                <?= ucfirst($user->status) ?>
              </span>
            </td>
            <td class="text-center">
              <div class="btn-group btn-group-sm">
                <button 
                  class="btn btn-outline-primary" 
                  onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)"
                  title="Edit">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-outline-danger" onclick="confirmDelete(<?= $user->id ?>)" title="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="/staff/users/create" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label small fw-bold">First Name</label>
            <input type="text" name="first_name" class="form-control" required maxlength="30"
            pattern="^[A-Za-z\s]+$" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '');">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold">Middle Name</label>
            <input type="text" name="mid_name" class="form-control" maxlength="20"
             pattern="^[A-Za-z]+$" oninput="this.value = this.value.replace(/[^A-Za-z]/g, '');">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold">Last Name</label>
            <input type="text" name="last_name" class="form-control" required maxlength="30"
            pattern="^[A-Za-z\s]+$" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '');">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" class="form-control" required maxlength="30" pattern="^[a-zA-Z0-9]+$" 
              oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Email</label>
            <input type="email" name="email" class="form-control" required maxlength="50">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Password</label>
            <div class="input-group">
              <input type="password" name="password" id="add_password" class="form-control" required maxlength="30"
               pattern="^[a-zA-Z0-9]+$" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-eye" id="toggleIcon"></i>
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Birth Date</label>
            <input type="date" name="birth_date" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Account Type</label>
            <select name="type" class="form-select">
              <option value="staff">Staff</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Initial Status</label>
            <select name="status" class="form-select">
              <option value="inactive" selected>Inactive</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Account</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="editForm" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label small fw-bold">First Name</label>
            <input type="text" name="first_name" id="edit_first_name" class="form-control" required maxlength="30"
              pattern="^[A-Za-z\s]+$" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '');">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold">Middle Name</label>
            <input type="text" name="mid_name" id="edit_mid_name" class="form-control" maxlength="20"
              pattern="^[A-Za-z]+$" oninput="this.value = this.value.replace(/[^A-Za-z]/g, '');">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold">Last Name</label>
            <input type="text" name="last_name" id="edit_last_name" class="form-control" required maxlength="30"
              pattern="^[A-Za-z\s]+$" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '');">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" id="edit_username" class="form-control" required maxlength="30"
              pattern="^[0-9A-Za-z\s]+$" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '');">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Email</label>
            <input type="email" name="email" id="edit_email" class="form-control" required maxlength="50">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Account Type</label>
            <select name="type" id="edit_type" class="form-select" disabled>
              <option value="staff">Staff</option>
              <option value="student">Student</option>
            </select>
            <input type="hidden" name="type" id="hidden_edit_type">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Status</label>
            <select name="status" id="edit_status" class="form-select" disabled>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <input type="hidden" name="status" id="hidden_edit_status">
          </div>
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
    document.addEventListener('DOMContentLoaded', function () {
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#add_password');
    const toggleIcon = document.querySelector('#toggleIcon');

    if (togglePassword) {
      togglePassword.addEventListener('click', function () {
        // Toggle the type attribute
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle the icon
        toggleIcon.classList.toggle('bi-eye');
        toggleIcon.classList.toggle('bi-eye-slash');
      });
    }
  });
  function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this account? This action cannot be undone.')) {
      window.location.href = '/staff/users/delete/' + id;
    }
  }
  let editModalInstance = null;
  function editUser(user) {
  // Set the form action dynamically
  const form = document.getElementById('editForm');
  form.action = '/staff/users/update/' + user.id;

  // Populate fields
  document.getElementById('edit_first_name').value = user.first_name;
  document.getElementById('edit_mid_name').value = user.mid_name || '';
  document.getElementById('edit_last_name').value = user.last_name;
  document.getElementById('edit_username').value = user.username;
  document.getElementById('edit_email').value = user.email;

  document.getElementById('edit_type').value = user.type;
  document.getElementById('edit_status').value = user.status;

  document.getElementById('hidden_edit_type').value = user.type;
  document.getElementById('hidden_edit_status').value = user.status;

  // Show the modal
  if (!editModalInstance) {
    editModalInstance = new bootstrap.Modal(document.getElementById('editUserModal'));
  }
  editModalInstance.show();
}
</script>