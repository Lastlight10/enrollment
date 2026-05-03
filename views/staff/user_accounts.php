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

<div class="row mb-3">
  <div class="col-md-3">
    <!-- NEW: Print Button -->
    <button type="button" class="btn btn-outline-danger shadow-sm" onclick="printFilteredUsers()">
      <i class="bi bi-file-pdf"></i> Export to PDF
    </button>
  </div>
  <div class="col-md-3 ms-auto">
    <select id="typeFilter" class="form-select shadow-sm" onchange="filterUsers()">
      <option value="all">All Account Types</option>
      <option value="staff">Staff Only</option>
      <option value="student">Students Only</option>
    </select>
  </div>
  <div class="col-md-4">
    <div class="input-group shadow-sm">
      <span class="input-group-text bg-white border-end-0">
        <i class="bi bi-search text-muted"></i>
      </span>
      <input 
        type="text" 
        id="userSearch" 
        class="form-control border-start-0 ps-0" 
        placeholder="Search name, username, or email..."
        onkeyup="filterUsers()"
        maxlength="50"
      >
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-dark">
        <tr>
          <th class="d-none">ID Number</th>
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
          <tr class="user-row">
            <td class="d-none"><?= htmlspecialchars($user->id_number) ?></td>
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
    <form action="/staff/user_accounts/create" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label small fw-bold">First Name</label>
            <input type="text" name="first_name" class="form-control" required maxlength="30"
            pattern="^[A-Za-z\s]+$" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold">Middle Name</label>
            <input type="text" name="mid_name" class="form-control" maxlength="20"
             pattern="^[A-Za-z]+$" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold">Last Name</label>
            <input type="text" name="last_name" class="form-control" required maxlength="30"
            pattern="^[A-Za-z\s]+$" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" class="form-control" required maxlength="30" pattern="^[a-zA-Z0-9]+$" 
              oninput="this.value = this.value.replace(/\s+/g, '')">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Email</label>
            <input type="email" name="email" class="form-control" required maxlength="50" oninput="this.value = this.value.replace(/\s+/g, '')">
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
            <input type="date" name="birth_date" class="form-control" required min="1960-01-01" max="2010-01-01">
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
              <option value="active" selected>Active</option>
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
              pattern="^[A-Za-z\s]+$" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold">Middle Name</label>
            <input type="text" name="mid_name" id="edit_mid_name" class="form-control" maxlength="20"
              pattern="^[A-Za-z]+$" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold">Last Name</label>
            <input type="text" name="last_name" id="edit_last_name" class="form-control" required maxlength="30"
              pattern="^[A-Za-z\s]+$" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" id="edit_username" class="form-control" required maxlength="30"
              pattern="^[0-9A-Za-z\s]+$" oninput="this.value = this.value.replace(/\s+/g, '')">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-bold">Email</label>
            <input type="email" name="email" id="edit_email" class="form-control" required maxlength="50" 
              oninput="this.value = this.value.replace(/\s+/g, '')">
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
            <select name="status" id="edit_status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="col-md-12" id="course_selection_container">
              <label class="form-label small fw-bold text-uppercase text-muted">Official Enrolled Course</label>
              <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold">ID Number:</label>
                <input type="text" name="id_number" id="id_number" class="form-control"
                  oninput="this.value = this.value.replace(/\s+/g, '')" disable>
              </div>
              
              <div class="border rounded p-2 bg-light shadow-sm">
                  <div class="input-group input-group-sm mb-2">
                      <span class="input-group-text bg-white border-end-0">
                          <i class="bi bi-search text-muted"></i>
                      </span>
                      <input 
                          type="text" 
                          id="modalCourseSearch" 
                          class="form-control border-start-0 border-end-0 ps-0" 
                          placeholder="Type to filter courses..."
                          maxlength="50"
                          onkeyup="filterModalCourses()">
                      <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="resetModalCourseFilter()">
                          <i class="bi bi-x-lg text-muted"></i>
                      </button>
                  </div>

                  <select name="course_id" id="modal_course_id" class="form-select shadow-none" size="5" 
                      style="height: 150px; overflow-y: auto; border: 1px solid #dee2e6;">
                      <option value="" id="modalCoursePlaceholder" class="text-muted italic">-- Select Course --</option>
                      <?php foreach ($courses as $c): ?>
                          <option value="<?= $c->id ?>">
                              <?= htmlspecialchars($c->course_name ?? '') ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
                  
                  <div id="modalNoCourseMessage" class="small text-danger mt-2 d-none">
                      <i class="bi bi-exclamation-circle"></i> No matching courses found.
                  </div>
              </div>
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
      window.location.href = '/staff/user_accounts/delete/' + id;
    }
  }

  function filterModalCourses() {
    const input = document.getElementById("modalCourseSearch");
    const filter = input.value.toLowerCase();
    const select = document.getElementById("modal_course_id");
    const options = select.options;
    const placeholder = document.getElementById('modalCoursePlaceholder');
    const noResults = document.getElementById('modalNoCourseMessage');
    let foundCount = 0;

    for (let i = 0; i < options.length; i++) {
        // Skip placeholder
        if (options[i].id === 'modalCoursePlaceholder') continue;

        const text = options[i].textContent || options[i].innerText;
        const isMatch = text.toLowerCase().indexOf(filter) > -1;
        
        options[i].style.display = isMatch ? "" : "none";
        if (isMatch) foundCount++;
    }

    // Toggle "No results" and placeholder visibility
    noResults.classList.toggle('d-none', foundCount > 0 || filter === "");
    placeholder.style.display = (filter === "") ? "" : "none";
}
function resetModalCourseFilter() {
    const input = document.getElementById("modalCourseSearch");
    input.value = "";
    filterModalCourses();
    input.focus();
}
  
  function filterUsers() {
  const searchTerm = document.getElementById('userSearch').value.toLowerCase();
  const typeFilter = document.getElementById('typeFilter').value.toLowerCase();
  const rows = document.querySelectorAll('.user-row');
  let visibleCount = 0;

  rows.forEach(row => {
    // Corrected Indices based on your <tr> structure:
    const idNumber = row.cells[0].textContent.toLowerCase(); // Hidden ID
    const name     = row.cells[1].textContent.toLowerCase(); // Name
    const username = row.cells[2].textContent.toLowerCase(); // Username
    const email    = row.cells[3].textContent.toLowerCase(); // Email
    
    // Type is in the 5th column (index 4)
    const type = row.cells[4].textContent.trim().toLowerCase();

    // Logic: Match Search Text (across multiple fields)
    const matchesSearch = idNumber.includes(searchTerm) || 
                          name.includes(searchTerm) || 
                          username.includes(searchTerm) || 
                          email.includes(searchTerm);
                          
    // Logic: Match Type Filter
    const matchesType = (typeFilter === "all") || (type === typeFilter);

    if (matchesSearch && matchesType) {
      row.style.display = "";
      visibleCount++;
    } else {
      row.style.display = "none";
    }
  });

  // Handle "No results" row
  let noResultsRow = document.getElementById('noUserResults');
  if (visibleCount === 0) {
    if (!noResultsRow) {
      const tbody = document.querySelector('tbody');
      const tr = document.createElement('tr');
      tr.id = 'noUserResults';
      // Colspan should be 6 to match your <thead>
      tr.innerHTML = `<td colspan="6" class="text-center py-4 text-muted">No accounts match your criteria.</td>`;
      tbody.appendChild(tr);
    }
  } else if (noResultsRow) {
    noResultsRow.remove();
  }
}
  function editUser(user) {
    const form = document.getElementById('editForm');
    form.action = '/staff/user_accounts/update/' + user.id;
    document.getElementById('id_number').value = user.id_number;

    // Standard fields
    document.getElementById('edit_first_name').value = user.first_name;
    document.getElementById('edit_mid_name').value = user.mid_name || '';
    document.getElementById('edit_last_name').value = user.last_name;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_type').value = user.type;
    document.getElementById('edit_status').value = user.status;
    document.getElementById('hidden_edit_type').value = user.type;

    // --- STUDENT ONLY LOGIC ---
    resetModalCourseFilter();

    // Show course selection ONLY if user is a student
    const courseContainer = document.getElementById('course_selection_container');
    if (user.type === 'student') {
        courseContainer.classList.remove('d-none');
        document.getElementById('modal_course_id').value = user.course_id || "";
    } else {
        courseContainer.classList.add('d-none');
    }

    // Show the modal (using your preferred bootstrap method)
    const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    editModal.show();
  }
  function printFilteredUsers() {
    const type = document.getElementById('typeFilter').value;
    const search = document.getElementById('userSearch').value;
    
    // Construct the URL with current filter parameters
    const url = `/staff/user_accounts/print?type=${type}&search=${encodeURIComponent(search)}`;
    
    // Open in new tab
    window.open(url, '_blank');
}
</script>