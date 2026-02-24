<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'Staff Portal' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/static/css/staff/layout.css">
  <style>
    /* Ensure the body and wrapper take full height */
    body, html { height: 100%; margin: 0; }
    .wrapper { display: flex; min-height: 100vh; }
    
    /* Sidebar styling */
    .sidebar {
      width: 250px;
      background: #212529;
      color: white;
      flex-shrink: 0; /* Prevents sidebar from shrinking */
    }

    /* Main content styling */
    .main-content {
      flex-grow: 1; /* Takes up remaining space */
      background: #f8f9fa;
      padding: 20px;
      overflow-y: auto;
    }

    .nav-link { color: #adb5bd; padding: 10px 20px; display: block; text-decoration: none; }
    .nav-link:hover, .nav-link.active { color: white; background: #343a40; }
  </style>
</head>
<body>

  <div class="wrapper">
    <nav class="sidebar">
      <div class="p-4">
        <h4 class="mb-4">Enrollment System</h4>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a href="/staff/dashboard" class="nav-link <?= ($title === 'Staff Home') ? 'active' : '' ?>">
              Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a href="/staff/enrollments" class="nav-link <?= ($title === 'Manage Enrollments') ? 'active' : '' ?>">
              Manage Enrollments
            </a>
          </li>
          <li class="nav-item">
            <a href="/staff/courses" class="nav-link <?= ($title === 'Manage Courses') ? 'active' : '' ?>">
              Manage Courses
            </a>
          </li>
          <li class="nav-item">
            <a href="/staff/subjects" class="nav-link <?= ($title === 'Manage Subjects') ? 'active' : '' ?>">
              Manage Subjects
            </a>
          </li>
          <li class="nav-item">
            <a href="/staff/academic_periods" class="nav-link <?= ($title === 'Academic Periods') ? 'active' : '' ?>">
              Manage Acadamic Periods
            </a>
          </li>
          <li class="nav-item">
            <a href="/staff/curriculum" class="nav-link <?= ($title === 'Manage Curriculum') ? 'active' : '' ?>">
              Manage Curriculum
            </a>
          </li>
          <li class="nav-item">
            <a href="/staff/user_accounts" class="nav-link <?= ($title === 'Manage Accounts') ? 'active' : '' ?>">
              Manage Users
            </a>
          </li>
        </ul>
        <div class="mt-5">
          <a href="/auth/logout" class="text-danger" onclick="return confirmLogout(event)">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <?= $content ?>
    </main>
  </div>

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