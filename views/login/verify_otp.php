<div class="card-body p-4 text-center">
  <h3>Verify Your Email</h3>
  <p class="text-muted">An OTP has been sent to <strong><?= htmlspecialchars($email) ?></strong></p>

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

  <form action="/auth/verify-otp" method="POST">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
    
    <div class="mb-4">
      <label class="form-label">Enter 6-Digit Code</label>
      <input type="text" name="otp_code" class="form-control form-control-lg text-center fw-bold" 
             placeholder="000000" maxlength="6" required autofocus>
    </div>

    <div class="d-grid">
      <button type="submit" class="btn btn-primary btn-lg">Verify Account</button>
    </div>
  </form>

  <div class="mt-4">
    <p class="small text-muted">Didn't receive the code? <br>
      <a href="/auth/resend-otp?email=<?= urlencode($email) ?>" class="text-decoration-none">Resend Code</a>
    </p>
  </div>
</div>