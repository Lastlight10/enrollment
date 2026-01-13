<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Enrollment System' ?></title>
  <link rel="stylesheet" href="/static/css/auth.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="auth-bg">
  <div class="auth-container">
    <div class="auth-card">
      <div class="row g-0 w-100"> <div class="col-md-5 d-none d-md-flex flex-column align-items-center justify-content-center p-4 text-center text-white left-branding">
            <img src="/static/images/UMLOGO.jpg" alt="Logo" class="mb-3 rounded-circle" style="width: 120px; border: 4px solid white;">
            <h2 class="fw-bold">Welcome Back!</h2>
            <p>Access the Enrollment Management Website to manage your academic profile.</p>
        </div>

        <div class="col-md-7 p-5 bg-white d-flex flex-column justify-content-center">

          <div class="auth-form-content">
            <?= $content ?>
          </div>
          
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>