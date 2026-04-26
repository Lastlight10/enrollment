<div class="card-body p-4">
    <h3 class="text-center mb-4">Set New Password</h3>
    <p class="text-muted text-center mb-4">Please enter a strong password to secure your account.</p>

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

    <form action="/auth/reset-password" method="POST" onsubmit="return validateForm()">
        <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>">

        <div class="mb-3">
            <label class="form-label">New Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Enter new password" required autofocus maxlength="30" oninput="validateLength(this, 'userHint')">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
                    Show
                </button>
            </div>
            <small id="userHint" class="text-danger d-none">Must be 6-30 characters.</small>
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <div class="input-group">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-lg" placeholder="Confirm new password" required maxlength="30" oninput="validateLength(this, 'passHint')">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', this)">
                    Show
                </button>
            </div>
            <small id="passHint" class="text-danger d-none">Must be 6-30 characters.</small>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Update Password</button>
        </div>
    </form>
</div>

<script>
    function validateForm() {
        const new_password = document.getElementsByName('password')[0];
        const confirm_password = document.getElementsByName('confirm_password')[0];
      
      
      // Check lengths one last time
      const isNewPassValid = new_password.value.length >= 6 && new_password.value.length <= 30;
      const isConfirmPassValid = confirm_password.value.length >= 6 && confirm_password.value.length <= 30;

      if (!isNewPassValid || !isConfirmPassValid) {
        // Manually trigger the hints if they tried to submit too early
        validateLength(new_password, 'userHint');
        validateLength(confirm_password, 'passHint');
        
        alert("Please ensure the Password is between 6 and 30 characters.");
        return false; // This stops the form from submitting
      }

      return true; // This allows the form to submit
  }
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
  function validateLength(input, hintId) {
    input.value = input.value.replace(/\s+/g, '');
    const hint = document.getElementById(hintId);
    const len = input.value.length;

    if (len > 0 && (len < 6 || len > 30)) {
      hint.classList.remove('d-none');
      input.classList.add('is-invalid');
    } else {
      hint.classList.add('d-none');
      input.classList.remove('is-invalid');
    }
  }
</script>