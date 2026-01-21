<div class="card-body p-4">
  <h3 class="text-center mb-4">Login</h3>
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

  <form action="/auth/login" method="POST">
    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required autofocus maxlength="30">
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <div class="input-group">
        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required maxlength="30">
        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
          <i class="bi bi-eye"></i> Show
        </button>
      </div>
    </div>

    <div class="d-grid gap-2">
      <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
    </div>

    <div class="text-center mt-4">
      <p class="mb-0">Did you <a href="/auth/forgotpass" class="text-decoration-none">forgot your Password?</a></p>
    </div>

    <div class="text-center mt-4">
      <p class="mb-0">Don't have an account? <a href="/auth/register" class="text-decoration-none">Register here</a></p>
    </div>
  </form>
</div>

<script>
  document.getElementById('togglePassword').addEventListener('click', function (e) {
    const password = document.getElementById('password');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    
    // Toggle button text or icon
    this.textContent = type === 'password' ? 'Show' : 'Hide';
  });
</script>