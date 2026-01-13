<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Student Portal' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body { background-color: #f0f2f5; }
    .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.08); }
    .card { border-radius: 12px; }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="/user/home">Student Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="/user/dashboard">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="/user/enroll">Enroll Now</a></li>
          <li class="nav-item"><a class="nav-link" href="/user/status">Semester Status</a></li>
          <li class="nav-item"><a class="nav-link" href="/user/curriculum">Curriculum</a></li>
          <li class="nav-item">
            <a href="/auth/logout" class="text-danger" onclick="return confirmLogout(event)">
              <i class="bi bi-box-arrow-right"></i> Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <main>
    <?= $content ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function confirmLogout(event) {
      event.preventDefault(); // Stop the browser from following the link immediately
      
      const confirmed = confirm("Are you sure you want to log out of your account?");
      
      if (confirmed) {
        // If they click OK, manually trigger the redirect
        window.location.href = event.currentTarget.href;
      }
      
      return false;
    }
  </script>
</body>
</html>