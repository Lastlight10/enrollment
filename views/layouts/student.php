<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Student Portal' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/static/css/student/layout.css">
  <style>
    body { background-color: #f0f2f5; }
    .navbar { 
      box-shadow: 0 2px 4px rgba(0,0,0,.08); 
      background-color: #004d00 !important; 
    }
    .card { border-radius: 12px; }
    .text-custom-green { color: #004d00 !important; }
    .btn-custom-green { 
      background-color: #004d00 !important; 
      color: white !important;
      border: none;
    }
    .btn-custom-green:hover { background-color: #003300 !important; color: white !important; }
    .badge-status-enrolled { background-color: #004d00; color: white; }
    .badge-status-pending { background-color: #ffc107; color: #000; }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="/student/dashboard">Student Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item"><a class="nav-link" href="/student/dashboard">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="/student/enroll">Enroll Now</a></li>
          <li class="nav-item"><a class="nav-link" href="/student/enrollments">My Enrollments</a></li>
          <li class="nav-item"><a class="nav-link" href="/student/curriculum">Curriculum</a></li>
          <li class="nav-item ms-lg-3">
            <a href="/auth/logout" class="btn btn-sm btn-outline-light border-0" onclick="return confirmLogout(event)">
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
      event.preventDefault();
      const confirmed = confirm("Are you sure you want to log out of your account?");
      if (confirmed) {
        window.location.href = event.currentTarget.href;
      }
      return false;
    }
  </script>
</body>
</html>