<div class="card-body p-4">
  <h3 class="text-center mb-4">Create Account</h3>

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

  <form action="/auth/register" method="POST">
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-control" required maxlength="30">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Middle Name</label>
        <input type="text" name="mid_name" class="form-control" maxlength="20">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" required maxlength="30">
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required maxlength="30">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required maxlength="30">
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Birth Date</label>
        <input type="date" name="birth_date" class="form-control" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input type="password" name="password" id="regPassword" class="form-control" required>
          <button class="btn btn-outline-secondary" type="button" id="toggleRegPassword">Show</button>
        </div>
      </div>
    </div>

    <div class="d-grid gap-2 mt-3">
      <button type="submit" class="btn btn-success btn-lg">Register Now</button>
    </div>

    <div class="text-center mt-3">
      <p>Already have an account? <a href="/auth/login">Login here</a></p>
    </div>
  </form>
</div>

<script>
  document.getElementById('toggleRegPassword').addEventListener('click', function() {
    const pwd = document.getElementById('regPassword');
    const isPwd = pwd.getAttribute('type') === 'password';
    pwd.setAttribute('type', isPwd ? 'text' : 'password');
    this.textContent = isPwd ? 'Hide' : 'Show';
  });
</script>