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

    <form action="/auth/reset-password" method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>">

        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter new password" required autofocus maxlength="30">
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Confirm new password" required maxlength="30">
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Update Password</button>
        </div>
    </form>
</div>
