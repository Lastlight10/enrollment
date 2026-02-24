<div class="card-body p-4">
    <h2 class="text-center mb-4">Reset Password</h2>

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
    <p class="text-muted text-center mb-4">
        Enter the email address associated with your account to receive a verification code.
    </p>

    <form action="/auth/forgotpass" method="POST">
        <div class="mb-4">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email" 
                   class="form-control form-control-lg" 
                   placeholder="Enter your email" required autofocus maxlength="50">
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Send OTP</button>
            <a href="/auth/login" class="btn btn-link">Return to Login</a>
        </div>
    </form>
</div>