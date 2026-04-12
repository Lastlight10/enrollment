<div class="card-body p-4">
  <h3 class="text-center mb-4">Create Account</h3>

  <!-- Alert Section (Kept as is) -->
  <?php if (isset($error) || isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= $error ?? $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Success/Info Alerts would go here... -->

  <form action="/auth/register" method="POST" onsubmit="return validateForm()">
    <!-- First Name -->
    <div class="mb-3">
      <label class="form-label">First Name</label>
      <input type="text" name="first_name" class="form-control" required 
      maxlength="30" oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
    </div>

    <!-- Middle Name -->
    <div class="mb-3">
      <label class="form-label">Middle Name</label>
      <input type="text" name="mid_name" class="form-control" maxlength="20" 
      oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
    </div>

    <!-- Last Name -->
    <div class="mb-3">
      <label class="form-label">Last Name</label>
      <input type="text" name="last_name" class="form-control" required maxlength="30" 
      oninput="this.value = this.value.replace(/[^A-Za-z.\s-]/g, '')">
    </div>

    <!-- Email -->
    <div class="mb-3">
      <label class="form-label">Email Address (Up to 50)</label>
      <input type="email" name="email" class="form-control" required maxlength="50" 
      oninput="this.value = this.value.replace(/\s+/g, '')">
    </div>

    <!-- Birth Date -->
    <div class="mb-3">
      <label class="form-label">Birth Date</label>
      <input type="date" name="birth_date" class="form-control" min="1960-01-01" max="2010-01-01" required >
    </div>

     <!-- Username -->
    <div class="mb-3">
      <label class="form-label">Username (6-30)</label>
      <input type="text" name="username" class="form-control" required minlength="6" maxlength="30"
        oninput="validateLength(this, 'userHint')"
        title="Username must have 6-30 characters." pattern=".{6,30}">
       <small id="userHint" class="text-danger d-none">Must be 6-30 characters.</small>
    </div>

    <!-- Password -->
    <div class="mb-3">
      <label class="form-label">Password (6-30)</label>
      <div class="input-group">

        <input type="password" name="password" id="regPassword" class="form-control" required 
          oninput="validateLength(this, 'passHint')"
          minlength="6"
          maxlength="30"
          title="Username must have 6-30 characters."
          pattern=".{6,30}">
         

        <button class="btn btn-outline-secondary" type="button" id="toggleRegPassword">Show</button>
        
      </div>
      <small id="passHint" class="text-danger d-none">Must be 6-30 characters.</small>
    </div>

    <div class="d-grid gap-2 mt-4">
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
    function validateForm() {
      const username = document.getElementsByName('username')[0];
      const password = document.getElementById('regPassword');
      
      // Check lengths one last time
      const isUserValid = username.value.length >= 6 && username.value.length <= 30;
      const isPassValid = password.value.length >= 6 && password.value.length <= 30;

      if (!isUserValid || !isPassValid) {
        // Manually trigger the hints if they tried to submit too early
        validateLength(username, 'userHint');
        validateLength(password, 'passHint');
        
        alert("Please ensure Username and Password are between 6 and 30 characters.");
        return false; // This stops the form from submitting
      }

      return true; // This allows the form to submit
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