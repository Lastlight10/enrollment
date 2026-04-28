<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="mb-0">Enrollment Management</h2>
    <p class="text-muted">Review applications and manually set student fees.</p>
  </div>
</div>

<?php foreach (['error' => 'danger', 'success' => 'success'] as $key => $type): ?>
  <?php if (isset($_SESSION[$key])): ?>
    <div class="alert alert-<?= $type ?> alert-dismissible fade show border-0 shadow-sm" role="alert">
      <i class="bi bi-<?= $key === 'error' ? 'exclamation-triangle' : 'check-circle' ?>-fill me-2"></i>
      <?= $_SESSION[$key]; unset($_SESSION[$key]); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

<button type="button" class="btn btn-danger shadow-sm mb-3" onclick="openBulkAnnounceModal()">
    <i class="bi bi-megaphone-fill me-2"></i> Announce to All Enrolled Students
</button>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="small fw-bold text-muted">SEARCH STUDENT</label>
        <div class="input-group shadow-sm">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
          <input type="text" id="enrollmentSearch" class="form-control border-start-0 ps-0" placeholder="Name, ID, or Username..." onkeyup="filterEnrollments()" maxlength="50">
        </div>
      </div>

      <div class="col-md-2">
        <label class="small fw-bold text-muted">COURSE</label>
        <select id="filterCourse" class="form-select shadow-sm" onchange="filterEnrollments()">
          <option value="">All Courses</option>
          </select>
      </div>

      <div class="col-md-2">
        <label class="small fw-bold text-muted">STATUS</label>
        <select id="filterStatus" class="form-select shadow-sm" onchange="filterEnrollments()">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="enrolled">Enrolled</option>
          <option value="rejected">Rejected</option>
          <option value="dropped">Dropped</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="small fw-bold text-muted">YEAR LEVEL</label>
        <select id="filterYear" class="form-select shadow-sm" onchange="filterEnrollments()">
          <option value="">All Years</option>
          <option value="1st Year">1st Year</option>
          <option value="2nd Year">2nd Year</option>
          <option value="3rd Year">3rd Year</option>
          <option value="4th Year">4th Year</option>
          <option value="5th Year">5th Year</option>
          <option value="Irregular">Irregular</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="small fw-bold text-muted">ACADEMIC PERIOD</label>
        <select id="filterPeriod" class="form-select shadow-sm" onchange="filterEnrollments()">
          <option value="">All Periods</option>
          </select>
      </div>

      <div class="col-md-2">
        <label class="small fw-bold text-muted">DATE APPLIED</label>
        <input type="date" id="filterDate" class="form-control shadow-sm" onchange="filterEnrollments()" min="1960-01-01">
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Payments</th>
            <th class="ps-4">Student Details</th>
            <th>Applied Course</th>
            <th>Academic Period</th>
            <th>Date Applied</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="enrollmentTableBody">
          <?php if(empty($enrollments)): ?>
            <tr><td colspan="5" class="text-center py-5 text-muted">No applications found.</td></tr>
          <?php else: ?>

            <?php foreach($enrollments as $e): ?>
              <?php 
                // Uses the Full Name attribute from your User model with null-safety
                $displayName = htmlspecialchars($e->user?->full_name ?? 'Unknown Student'); 
                $payments = $e->payments ?? [];
                $paidCount = 0;
                $pendingCount = 0;
                
                foreach($payments as $p) {
                    if($p->status === 'paid') $paidCount++;
                    if($p->status === 'unpaid' || $p->status === 'pending') $pendingCount++;
                }
              ?>
              <tr>
                <td class="ps-4">
                  <div class="d-flex gap-2">
                    <div class="text-center">
                        <span class="badge bg-success d-block mb-1" title="Paid Payments"><?= $paidCount ?></span>
                        <small class="x-small text-uppercase">Paid</small>
                    </div>
                    <div class="text-center">
                        <span class="badge bg-warning text-dark d-block mb-1" title="Pending Payments"><?= $pendingCount ?></span>
                        <small class="x-small text-uppercase">Pend</small>
                    </div>
                  </div>
                </td>
                <td class="ps-4 searchable-student">
                  <div class="d-flex align-items-center">                    
                    <div>
                      <div class="fw-bold text-dark mb-0" style="line-height: 1.2;">
                        <?= $displayName ?>
                      </div>
                      
                      <div class="d-flex flex-column flex-sm-row gap-sm-2 mt-1">
                        <small class="text-muted d-flex align-items-center">
                          <i class="bi bi-person-badge me-1"></i> 
                          <?= htmlspecialchars($e->user?->id_number ?? 'No ID') ?>
                        </small>
                        <span class="text-muted d-none d-sm-inline">•</span>
                        <small class="text-muted d-flex align-items-center">
                          <i class="bi bi-at me-1"></i> 
                          <?= htmlspecialchars($e->user?->username ?? 'N/A') ?>
                        </small>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="fw-bold text-primary"><?= htmlspecialchars($e->course?->course_code ?? 'N/A') ?></div>
                  <small class="badge bg-light text-dark border"><?= htmlspecialchars($e->grade_year) ?></small>
                </td>
                <td>
                  <div class="small fw-bold"><?= htmlspecialchars($e->period?->acad_year ?? 'N/A') ?></div>
                  <small class="text-muted"><?= htmlspecialchars($e->period?->semester ?? '') ?></small>
                  <span class="d-none searchable-period"><?= htmlspecialchars(($e->period?->acad_year ?? '') . ' ' . ($e->period?->semester ?? '')) ?></span>
                </td>
                <td><?= date('M d, Y', strtotime($e->created_at)) ?></td>
                <td>
                  <?php 
                    $statusClass = match($e->status) {
                      'enrolled' => 'bg-success',
                      'rejected' => 'bg-danger',
                      'dropped'  => 'bg-secondary',
                      default    => 'bg-warning text-dark'
                    };
                  ?>
                  <span class="badge rounded-pill <?= $statusClass ?> px-3"><?= ucfirst($e->status) ?></span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm shadow-sm">
                    <a href="/staff/enrollments/details/<?= $e->id ?>" class="btn btn-outline-secondary" title="View Details">
                      <i class="bi bi-eye"></i> View
                    </a>

                    <?php if($e->status === 'pending'): ?>
                      <button class="btn btn-primary px-3" onclick="openEnrollModal(<?= $e->id ?>, '<?= addslashes($displayName) ?>')">
                        <i class="bi bi-check-lg me-1"></i> Enroll
                      </button>
                      <button class="btn btn-outline-danger" onclick="openRejectModal(<?= $e->id ?>, '<?= addslashes($displayName) ?>')">
                        <i class="bi bi-x-circle"></i> Reject
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="bulkAnnounceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form id="bulkAnnounceForm" method="POST" action="/staff/enrollments/announce" class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title fw-bold">Global Payment Announcement</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        
        <div id="idContainer"></div>

        <div class="mb-3">
          <label class="form-label small fw-bold">SELECT PERIOD</label>
          <select name="payment_type" class="form-select" required>
            
            <option value="prelim">Prelim</option>
            <option value="midterm">Midterm</option>
            <option value="finals">Finals</option>
            <option value="others">Others</option>
          </select>
        </div>
        <div class="row">
          <div class="col-6">
            <label class="small fw-bold">START DATE</label>
            <input type="date" id="announceStartDate" name="startDate" class="form-control" required onchange="validateDates()">
          </div>
          <div class="col-6">
            <label class="small fw-bold">END DATE</label>
            <input type="date" id="announceEndDate" name="endDate" class="form-control" required onchange="validateDates()">
          </div>
        </div>
        <div id="dateErrorMessage" class="text-danger x-small mt-2" style="display:none;">
            * Dates must be in the future and End Date must be after Start Date.
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger rounded-pill px-4">Send to Emails</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form id="rejectForm" method="POST" class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title fw-bold">Reject Application</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light">
        <div class="mb-3">
          <label class="small text-muted fw-bold">REJECTING APPLICATION FOR:</label>
          <div id="rejectStudentName" class="h5 text-dark fw-bold"></div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-bold text-muted">REASON FOR REJECTION (Staff Only)</label>
          <textarea name="staff_comments" class="form-control border-0 shadow-sm" rows="4" placeholder="e.g., Incomplete requirements..." required maxlength="100"></textarea>
        </div>
      </div>
      <div class="modal-footer border-0 bg-white">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Confirm Reject</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="enrollModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form id="enrollForm" method="POST" class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-bold">Approve Enrollment</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light">
        <div class="mb-3">
          <label class="small text-muted fw-bold">STUDENT NAME</label>
          <div id="studentName" class="h5 text-dark fw-bold"></div>
        </div>
        <hr class="text-muted opacity-25">
        <label class="small text-muted fw-bold mb-2">FEE BREAKDOWN</label>
        
        <div id="fee-container">
          
          <div class="row g-2 mb-2 fee-row align-items-end">
            <small>Minimum of amount of ₱1000</small>
            <div class="col-7">
              <label class="form-label x-small mb-1">Type</label>
              <select name="fees[0][type]" class="form-select border-0 shadow-sm" required onchange="validateUniquePayments()">
                <option value="downpayment">Downpayment</option>
                <option value="prelim">Prelim</option>
                <option value="midterm">Midterm</option>
                <option value="finals">Finals</option>
                <option value="others">Others</option>
              </select>
            </div>
            <div class="col-4">
              <label class="form-label x-small mb-1">Amount</label>
              <input 
                  type="number" 
                  name="fees[0][amount]" 
                  class="form-control border-0 shadow-sm" 
                  placeholder="0.00" 
                  step="0.01" 
                  required 
                  min="1000" 
                  max="99999"
                  oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5);">
            </div>
            <div class="col-1 text-end">
              <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)">
                <i class="bi bi-dash-circle-fill fs-5"></i>
              </button>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-3 border-dashed py-2" onclick="addRow()">
          <i class="bi bi-plus-circle me-1"></i> Add Fee Component
        </button>
      </div>
      <div class="modal-footer border-0 bg-white d-flex justify-content-between align-items-center">
        <div class="fw-bold text-primary h5 mb-0" id="live-total">
          Total: ₱0.00
        </div>
        
        <div>
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Confirm & Enroll</button>
        </div>
      </div>
    </form>
  </div>
</div>

<style>
  .x-small { font-size: 0.75rem; font-weight: bold; color: #6c757d; }
  .border-dashed { border-style: dashed !important; border-width: 2px; }
  .fee-row .form-select, .fee-row .form-control { font-size: 0.9rem; }
</style>

<script>
  let feeIndex = 1;
  let enrollModalInstance = null;
  let rejectModalInstance = null;
  let announceModalInstance = null;

  // 1. Live Total Calculation Logic
  function updateLiveTotal() {
    let total = 0;
    document.querySelectorAll('.fee-row input[type="number"]').forEach(input => {
      total += parseFloat(input.value) || 0;
    });
    const totalDisplay = document.getElementById('live-total');
    if (totalDisplay) {
      totalDisplay.innerText = 'Total: ₱' + total.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }
  }

  // Attach listener to the modal to catch all input changes
  document.addEventListener('DOMContentLoaded', () => {
    populateFilters();
    const modal = document.getElementById('enrollModal');
    if (modal) {
      modal.addEventListener('input', updateLiveTotal);
    }
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const minDateString = tomorrow.toISOString().split('T')[0];
    
    const startInput = document.getElementById('announceStartDate');
    const endInput = document.getElementById('announceEndDate');
    
    if(startInput && endInput) {
        startInput.min = minDateString;
        endInput.min = minDateString;
    }
  });
  document.getElementById('enrollForm').addEventListener('submit', function(e) {
      const rows = document.querySelectorAll('.fee-row');
      
      if (rows.length < 4 || rows.length > 5) {
          e.preventDefault(); // Stop form submission
          alert("Invalid Fee Count: You must provide exactly 4 or 5 fee components.");
          return false;
      }
  });

  function validateDates() {
      const startInput = document.getElementById('announceStartDate');
      const endInput = document.getElementById('announceEndDate');
      const errorMsg = document.getElementById('dateErrorMessage');
      const submitBtn = document.querySelector('#bulkAnnounceForm button[type="submit"]');

      const startDate = startInput.value;
      const endDate = endInput.value;

      let isValid = true;

      // 1. Check if dates are empty
      if (!startDate || !endDate) {
          isValid = false;
      } 
      // 2. Check if Start Date is equal to or after End Date
      else if (startDate >= endDate) {
          isValid = false;
          errorMsg.innerText = "* End Date must be strictly AFTER Start Date.";
          errorMsg.style.display = "block";
      } else {
          errorMsg.style.display = "none";
      }

      // Disable button if invalid
      submitBtn.disabled = !isValid;
  }

  function openBulkAnnounceModal() {
    const idContainer = document.getElementById('idContainer');
    idContainer.innerHTML = ''; // Clear previous
    
    // Find all rows that are NOT hidden by your filterEnrollments() function
    const visibleRows = document.querySelectorAll('#enrollmentTableBody tr:not([style*="display: none"]):not(.no-results)');
    
    if (visibleRows.length === 0) {
      alert("No students found in the current list.");
      return;
    }

    visibleRows.forEach(row => {
      // Extract ID from the 'View' link or a data attribute
      const viewLink = row.querySelector('a[href*="/details/"]');
      const id = viewLink.getAttribute('href').split('/').pop();
      
      // Add hidden input for each student
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'enrollment_ids[]';
      input.value = id;
      idContainer.appendChild(input);
    });

    document.getElementById('bulkAnnounceForm').reset();
    document.getElementById('dateErrorMessage').style.display = "none";
    document.querySelector('#bulkAnnounceForm button[type="submit"]').disabled = true;
    const modal = new bootstrap.Modal(document.getElementById('bulkAnnounceModal'));
    modal.show();
  }

  function openEnrollModal(id, name) {
    const form = document.getElementById('enrollForm');
    form.action = '/staff/enrollments/approve/' + id;
    document.getElementById('studentName').innerText = name;
    if (!enrollModalInstance) enrollModalInstance = new bootstrap.Modal(document.getElementById('enrollModal'));
    
    // Reset total on open
    updateLiveTotal();
    enrollModalInstance.show();
  }

  function openRejectModal(id, name) {
    const form = document.getElementById('rejectForm');
    form.action = '/staff/enrollments/reject/' + id; 
    document.getElementById('rejectStudentName').innerText = name;
    if (!rejectModalInstance) rejectModalInstance = new bootstrap.Modal(document.getElementById('rejectModal'));
    rejectModalInstance.show();
  }

    function addRow() {
      const container = document.getElementById('fee-container');
      const rows = document.querySelectorAll('.fee-row');
      
      // Check if we already reached the limit of 5
      if (rows.length >= 5) {
          alert("Limit reached: You can only add a maximum of 5 fees per student.");
          return;
      }

      const div = document.createElement('div');
      div.className = 'row g-2 mb-2 fee-row align-items-end';
      
      div.innerHTML = `
        <div class="col-7">
          <select name="fees[${feeIndex}][type]" class="form-select border-0 shadow-sm payment-type-select" required onchange="validateUniquePayments()">
            <option value="" disabled selected>Select Type</option>
            <option value="prelim">Prelim</option>
            <option value="midterm">Midterm</option>
            <option value="finals">Finals</option>
            <option value="others">Others</option>
          </select>
        </div>
        <div class="col-4">
          <input 
            type="number" 
            name="fees[${feeIndex}][amount]" 
            class="form-control border-0 shadow-sm" 
            placeholder="0.00" 
            step="0.01" 
            required 
            min="0.01" 
            max="999999"
            oninput="if(this.value.length > 8) this.value = this.value.slice(0, 8);">
        </div>
        <div class="col-1 text-end">
          <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)">
            <i class="bi bi-dash-circle-fill fs-5"></i>
          </button>
        </div>
      `;
      container.appendChild(div);
      feeIndex++;
      updateLiveTotal(); // Ensure total updates when new row appears
  }

  function removeRow(btn) {
    const rows = document.querySelectorAll('.fee-row');
    if (rows.length <= 4) {
      alert("Required: You must have at least 4 fees (e.g., Downpayment + 3 Major Exams).");
      return;
    }
    btn.closest('.fee-row').remove();
    updateLiveTotal();
  }

  function validateUniquePayments() {
    const selects = document.querySelectorAll('.payment-type-select');
    const selectedValues = [];

    selects.forEach(select => {
        if (select.value === "") return; // Skip empty/placeholder values

        if (selectedValues.includes(select.value)) {
            alert("This payment type has already been added. Please select a different type.");
            select.value = ""; // Reset the selection
        } else {
            selectedValues.push(select.value);
        }
    });
  }

function populateFilters() {
    const rows = document.querySelectorAll('#enrollmentTableBody tr:not(.no-results)');
    const courses = new Set();
    const periods = new Set(); // New Set for periods

    rows.forEach(row => {
        const courseCode = row.querySelector('td:nth-child(3) .fw-bold')?.innerText;
        if (courseCode) courses.add(courseCode);

        // Get text from our hidden searchable-period span
        const periodText = row.querySelector('.searchable-period')?.innerText.trim();
        if (periodText && periodText !== "N/A") periods.add(periodText);
    });

    // Populate Course Select
    const courseSelect = document.getElementById('filterCourse');
    courses.forEach(course => {
        if (![...courseSelect.options].some(opt => opt.value === course)) {
            const opt = document.createElement('option');
            opt.value = course;
            opt.innerHTML = course;
            courseSelect.appendChild(opt);
        }
    });

    // Populate Period Select
    const periodSelect = document.getElementById('filterPeriod');
    periods.forEach(period => {
        if (![...periodSelect.options].some(opt => opt.value === period)) {
            const opt = document.createElement('option');
            opt.value = period;
            opt.innerHTML = period;
            periodSelect.appendChild(opt);
        }
    });
}

function filterEnrollments() {
    // 1. Get all current filter values
    const searchTerm = document.getElementById('enrollmentSearch').value.toLowerCase().trim();
    const filterCourse = document.getElementById('filterCourse').value;
    const filterStatus = document.getElementById('filterStatus').value.toLowerCase();
    const filterYear = document.getElementById('filterYear').value;
    const filterDateInput = document.getElementById('filterDate').value;
    const filterPeriod = document.getElementById('filterPeriod').value;

    const rows = document.querySelectorAll('#enrollmentTableBody tr:not(.no-results)');
    let visibleCount = 0;

    rows.forEach(row => {
        // 2. Extract data from the row
        const studentText = row.querySelector('.searchable-student').innerText.toLowerCase();
        const courseCode = row.querySelector('td:nth-child(3) .fw-bold').innerText.trim();
        const yearLevel = row.querySelector('td:nth-child(3) .badge').innerText.trim();
        const status = row.querySelector('td:nth-child(6) .badge').innerText.toLowerCase().trim();
        const periodText = row.querySelector('.searchable-period').innerText.trim();

        // Date Parsing Logic
        const rowDateRaw = row.querySelector('td:nth-child(5)').innerText.trim();
        const dateObj = new Date(rowDateRaw);
        const rowDateFormatted = `${dateObj.getFullYear()}-${String(dateObj.getMonth() + 1).padStart(2, '0')}-${String(dateObj.getDate()).padStart(2, '0')}`;

        // 3. Perform Logical Comparison
        // If the filter is empty/default, it automatically matches (true)
        const matchesSearch = searchTerm === "" || studentText.includes(searchTerm);
        const matchesCourse = filterCourse === "" || courseCode === filterCourse;
        const matchesStatus = filterStatus === "" || status === filterStatus;
        const matchesYear   = filterYear === "" || yearLevel === filterYear;
        const matchesDate   = filterDateInput === "" || rowDateFormatted === filterDateInput;
        const matchesPeriod = filterPeriod === "" || periodText === filterPeriod;

        // 4. Final Decision: Row must satisfy ALL conditions
        if (matchesSearch && matchesCourse && matchesStatus && matchesYear && matchesDate && matchesPeriod) {
            row.style.display = ""; // Show
            visibleCount++;
        } else {
            row.style.display = "none"; // Hide
        }
    });

    // 5. Handle "No results found"
    let noResultsMsg = document.querySelector('.no-results');
    if (visibleCount === 0) {
        if (!noResultsMsg) {
            const tbody = document.getElementById('enrollmentTableBody');
            const tr = document.createElement('tr');
            tr.className = 'no-results';
            tr.innerHTML = `<td colspan="7" class="text-center py-5 text-muted">No applications match your filters.</td>`;
            tbody.appendChild(tr);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}
</script>

