<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="rounded-circle bg-custom-green d-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px; font-size: 1.8rem; font-weight: bold;">
                <?= strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) ?>
              </div>
            </div>
            <div class="ms-4">
              <h3 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></h3>
              <p class="text-muted mb-0 small text-uppercase tracking-wider">
                Student ID: <span class="text-dark fw-bold"><?= htmlspecialchars($user->id_number) ?></span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?= $_SESSION['error']; unset($_SESSION['error']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?= $_SESSION['info']; unset($_SESSION['info']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?= $_SESSION['success']; unset($_SESSION['success']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
          <h5 class="mb-0 fw-bold text-custom-green">
            <i class="bi bi-person-gear me-2"></i>Account Settings
          </h5>
        </div>
        <div class="card-body p-4">
          <form action="/student/profile/update" method="POST" onsubmit="return validateForm()">
            <div class="row g-3">
              <div class="col-12">
                <h6 class="text-muted small text-uppercase fw-bold mb-2">Personal Information</h6>
                <hr class="mt-0 opacity-10">
              </div>
              
              <div class="col-md-4">
                <label class="form-label small fw-bold">First Name</label>
                <input type="text" name="first_name" class="form-control form-control-sm" value="<?= htmlspecialchars($user->first_name) ?>" required maxlength="30" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
              </div>
              
              <div class="col-md-4">
                <label class="form-label small fw-bold">Middle Name</label>
                <input type="text" name="mid_name" class="form-control form-control-sm" value="<?= htmlspecialchars($user->mid_name ?? '') ?>" maxlength="20" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
              </div>
              
              <div class="col-md-4">
                <label class="form-label small fw-bold">Last Name</label>
                <input type="text" name="last_name" class="form-control form-control-sm" value="<?= htmlspecialchars($user->last_name) ?>" required maxlength="30" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Course Enrolled</label>
                <input type="text" name="course" class="form-control form-control-sm " 
                value="<?= ($user_course && $user_course->course) ? htmlspecialchars($user_course->course->course_name) : 'Not Enrolled' ?>" 
                readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Birth Date</label>
                <input type="date" name="birth_date" class="form-control form-control-sm" value="<?= $user->birth_date ? date('Y-m-d', strtotime($user->birth_date)) : '' ?>" min="1960-01-01" max="2010-01-01">
              </div>

              <div class="col-12 mt-4">
                <h6 class="text-muted small text-uppercase fw-bold mb-2">Login Credentials</h6>
                <hr class="mt-0 opacity-10">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Email Address</label>
                <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($user->email) ?>" required maxlength="50" oninput="this.value = this.value.replace(/\s+/g, '')">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Username</label>
                <input type="text" name="username" class="form-control form-control-sm" value="<?= htmlspecialchars($user->username) ?>" required minlength="6" maxlength="30" oninput="validateLength(this, 'userHint')" pattern=".{6,30}">
                <small id="userHint" class="text-danger d-none">Must be 6-30 characters.</small>
              </div>

              <div class="col-12">
                <div class="p-3 rounded-3 bg-light border mt-2">
                  <div class="d-flex align-items-center">
                    <i class="bi bi-shield-lock text-custom-green fs-5 me-3"></i>
                    <div>
                      <p class="mb-0 small fw-bold">Change Password</p>
                      <p class="mb-0 small text-muted">Leave blank to keep your current password.</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">New Password</label>
                <input type="password" name="password" id="regPassword" class="form-control form-control-sm" placeholder="Enter new password" oninput="validateLength(this, 'passHint')" minlength="6" maxlength="30">
                <small id="passHint" class="text-danger d-none">Must be 6-30 characters.</small>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Confirm New Password</label>
                <input type="password" name="password_confirmation" id="regConfirm" class="form-control form-control-sm" placeholder="Repeat new password" oninput="validateLength(this, 'passHint')" minlength="6" maxlength="30">
              </div>

              <div class="col-12 text-end mt-4 pt-3 border-top">
                <button type="reset" class="btn btn-light btn-sm px-4 border me-2">Reset Changes</button>
                <button type="submit" class="btn btn-custom-green btn-sm px-5 shadow-sm">
                  Update Profile
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function validateForm() {
    // We let the form submit to the backend so that session errors can be displayed.
    // The visual indicators (validateLength) provide immediate feedback while typing.
    const username = document.getElementsByName('username')[0];
    const password = document.getElementById('regPassword');
    
    validateLength(username, 'userHint');
    validateLength(password, 'passHint');

    return true; // Always true to allow backend to catch specific errors
  }

  function validateLength(input, hintId) {
    input.value = input.value.replace(/\s+/g, '');
    const hint = document.getElementById(hintId);
    const len = input.value.length;

    // We only show red warning if they have actually typed something wrong
    if (len > 0 && (len < 6 || len > 30)) {
      hint.classList.remove('d-none');
      input.classList.add('is-invalid');
    } else {
      hint.classList.add('d-none');
      input.classList.remove('is-invalid');
    }
  }
</script>