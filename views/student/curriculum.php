<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-custom-green">Course Prospectus</h2>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="/student/dashboard" class="text-custom-green">Home</a></li>
        <li class="breadcrumb-item active">Curriculum</li>
      </ol>
    </nav>
  </div>

  <div class="row mb-4">
    <div class="col-md-6 col-lg-4">
      <div class="input-group shadow-sm">
        <span class="input-group-text bg-white border-end-0">
          <i class="bi bi-search text-custom-green"></i>
        </span>
        <input type="text" id="curriculumSearch" class="form-control border-start-0 ps-0" placeholder="Search by curriculum name..."
        maxlength="50">
      </div>
    </div>
  </div>
  <div class="row" id="curriculumList">
  <?php if(!empty($curriculums)): ?>
    <?php foreach($curriculums as $course): ?>
      <div class="col-md-6 col-lg-4 mb-4 curriculum-card">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex flex-column">
            <div class="mb-3">
              <i class="bi bi-journal-bookmark-fill text-custom-green display-6"></i>
            </div>
            <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($course->course_name ?? $course->name) ?></h5>

            <div class="mt-3">
              <a href="/student/curriculum/view/<?= $course->id ?>" class="btn btn-custom-green rounded-pill w-100">
                <i class="bi bi-eye-fill me-2"></i>View Subjects
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="text-center py-5 card border-0 shadow-sm">
      <i class="bi bi-book display-1 text-muted"></i>
      <p class="mt-3 text-muted">No course curriculums are currently listed.</p>
    </div>
  <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('curriculumSearch');
    const cards = document.querySelectorAll('.curriculum-card');

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.toLowerCase();

        cards.forEach(card => {
            const title = card.querySelector('.card-title').textContent.toLowerCase();
            
            // Check if the title includes the search letters
            if (title.includes(searchTerm)) {
                card.style.display = ""; // Show
            } else {
                card.style.display = "none"; // Hide
            }
        });
    });
});
</script>