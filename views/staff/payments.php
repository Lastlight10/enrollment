<style>
  .small_font{
    font-size: 12px;
  }
</style>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="text-uppercase fw-bold">Payment Records</h2>
  <a href="/staff/payments/print_report" id="printReportBtn" class="btn btn-primary shadow-sm" target="_blank">
    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Generate Full Report
  </a>
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
<div class="row mb-3 g-3">
    <div class="col-md-4">
        <label class="small fw-bold">Search</label>
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="paymentSearch" class="form-control border-start-0 ps-0" placeholder="Name or ID Number..." onkeyup="filterPayments()" maxlength="50">
        </div>
    </div>
    <div class="col-md-3">
        <label class="small fw-bold">From Date</label>
        <input type="date" id="dateFrom" class="form-control shadow-sm" onchange="filterPayments()">
    </div>
    <div class="col-md-3">
        <label class="small fw-bold">To Date</label>
        <input type="date" id="dateTo" class="form-control shadow-sm" onchange="filterPayments()">
    </div>
    <div class="col-md-2">
        <label class="small fw-bold">Status</label>
        <select id="statusFilter" class="form-select shadow-sm" onchange="filterPayments()">
            <option value="">All Statuses</option>
            <option value="paid">Paid</option>
            <option value="unpaid">Unpaid</option>
            <option value="need_verification">Need Verification</option>
        </select>
    </div>
  <div class="col-md-2">
      <label class="small fw-bold">Type</label>
      <!-- Removed hardcoded options -->
      <select id="typeFilter" class="form-select shadow-sm" onchange="filterPayments()">
          <option value="">All Types</option>
      </select>
  </div>
    <div class="col-md-4">
        <label class="small fw-bold">Period</label>
        <select id="periodFilter" class="form-select shadow-sm" onchange="filterPayments()">
            <option value="">All Periods</option>
            <!-- Options will be populated by JavaScript -->
        </select>
    </div>
    <div class="col-md-2">
        <label class="small fw-bold text-muted">YEAR LEVEL</label>
        <select id="filterYear" class="form-select shadow-sm" onchange="filterPayments()">
          <option value="">All Years</option>
          <option value="1st Year">1st Year</option>
          <option value="2nd Year">2nd Year</option>
          <option value="3rd Year">3rd Year</option>
          <option value="4th Year">4th Year</option>
          <option value="5th Year">5th Year</option>
          <option value="Irregular">Irregular</option>
        </select>
      </div>
    
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" id="paymentTable">
        <thead class="table-light">
          <tr class="text-uppercase small">
            <th>Date</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Year</th>
            <th>Period</th>
            <th>Type</th>
            <th>Status</th>
            <th class="text-end">Amount</th>
          </tr>
        </thead>
        <tbody class="small_font">
          <?php foreach ($payments as $p): 
          $yr = $p->enrollment->grade_year;
            if (!$yr) {
                $yearLabel = "Irregular";
            } else {
                $yearLabel = $yr ;
            }
            
            ?>
          <tr data-payment-id="<?= $p->id ?>">
            <td class="align-middle"><?= $p->created_at->format('m/d/Y') ?></td>
            <td class="align-middle fw-bold"><?= $p->enrollment->user->id_number ?></td>
            <td class="align-middle text-uppercase">
              <?= $p->enrollment->user->first_name ?> <?= $p->enrollment->user->mid_name ?> <?= $p->enrollment->user->last_name ?>
            </td>
            <td class="align-middle" data-year="<?= $yearLabel ?>">
              <?= $yearLabel ?>
            </td>
            <td class="align-middle" data-period-id="<?= $p->enrollment->period_id ?>">
              <?= $p->enrollment->period->acad_year ?? 'N/A' ?> - <?= $p->enrollment->period->semester ?? '' ?>
            </td>
            <td class="align-middle">
                <?php 
                    if ($p->payment_type === 'full_payment') {
                        echo "Full Payment";
                    } else {
                        echo ucfirst($p->payment_type);
                    }
                ?>
            </td>
            <td class="align-middle">
                <?php
                    // Determine the color class based on the raw status
                    $statusClass = 'bg-secondary';
                    if($p->status === 'paid') $statusClass = 'bg-success';
                    elseif($p->status === 'need_verification') $statusClass = 'bg-warning text-dark';
                    elseif($p->status === 'unpaid') $statusClass = 'bg-danger';

                    // Map the internal status to a user-friendly display label
                    $displayStatus = $p->status;
                    if($p->status === 'need_verification') {
                        $displayStatus = 'Pending Verification';
                    }
                ?>
                <span class="badge <?= $statusClass ?> text-uppercase">
                    <?= $displayStatus ?>
                </span>
            </td>
            <td class="text-end align-middle fw-bold">₱<?= number_format($p->amount, 2) ?></td>
          </tr>
          <?php endforeach; ?>
          <tr id="noResultsRow" style="display: none;">
            <td colspan="7" class="text-center py-4 text-muted">
              <i class="bi bi-exclamation-circle me-1"></i> No matching records found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  function populatePeriodFilter() {
    const periodFilter = document.getElementById('periodFilter');
    const rows = document.querySelectorAll('#paymentTable tbody tr:not(#noResultsRow)');
    
    // Use a Map to store unique period IDs and their display text
    const periods = new Map();

    rows.forEach(row => {
        const periodCell = row.cells[4]; // The "Period" column
        const periodId = periodCell.getAttribute('data-period-id');
        const periodText = periodCell.textContent.trim();

        if (periodId && !periods.has(periodId)) {
            periods.set(periodId, periodText);
        }
    });

    // Clear existing options except the "All" option
    periodFilter.innerHTML = '<option value="">All Periods</option>';

    // Sort and append the periods found in the table
    Array.from(periods.entries())
        .sort((a, b) => b[1].localeCompare(a[1])) // Sort descending (recent years first)
        .forEach(([id, text]) => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = text;
            periodFilter.appendChild(option);
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    populatePeriodFilter();
    filterPayments(); // Run initial filter
});
function filterPayments() {
    const searchText = document.getElementById('paymentSearch').value.toLowerCase();
    const statusSelect = document.getElementById('statusFilter').value;
    const typeSelect = document.getElementById('typeFilter').value.toLowerCase();
    const periodSelect = document.getElementById('periodFilter').value; 
    const yearSelect = document.getElementById('filterYear').value; // NEW
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const rows = document.querySelectorAll('#paymentTable tbody tr:not(#noResultsRow)');
    const noResultsRow = document.getElementById('noResultsRow');
    const printBtn = document.getElementById('printReportBtn');

    let visibleCount = 0;
    let visibleIds = [];

    rows.forEach(row => {
        const studentID = row.cells[1].textContent.toLowerCase();
        const studentName = row.cells[2].textContent.toLowerCase();
        const rowYear = row.cells[3].getAttribute('data-year'); // Index 3: Year
        const rowPeriodId = row.cells[4].getAttribute('data-period-id'); // Index 4: Period
        const typeValue = row.cells[5].textContent.trim().toLowerCase();
        
        // Search & Status Logic
        const matchesSearch = studentID.includes(searchText) || studentName.includes(searchText);
        
        const badge = row.querySelector('.badge');
        let matchesStatus = true;
        if (statusSelect !== "") {
            if (statusSelect === "paid" && !badge.classList.contains('bg-success')) matchesStatus = false;
            if (statusSelect === "unpaid" && !badge.classList.contains('bg-danger')) matchesStatus = false;
            if (statusSelect === "need_verification" && !badge.classList.contains('bg-warning')) matchesStatus = false;
        }

        // Filter Logic
        const matchesType = (typeSelect === "" || typeValue === typeSelect);
        const matchesPeriod = (periodSelect === "" || rowPeriodId === periodSelect);
        const matchesYear = (yearSelect === "" || rowYear === yearSelect); // NEW

        // Date Logic
        let matchesDate = true;
        if (dateFrom || dateTo) {
            const rowDate = new Date(row.cells[0].textContent.trim());
            const checkTime = new Date(rowDate.getFullYear(), rowDate.getMonth(), rowDate.getDate()).getTime();

            if (dateFrom) {
                const dFrom = new Date(dateFrom);
                if (checkTime < dFrom.setHours(0,0,0,0)) matchesDate = false;
            }
            if (dateTo) {
                const dTo = new Date(dateTo);
                if (checkTime > dTo.setHours(0,0,0,0)) matchesDate = false;
            }
        }

        const isVisible = (matchesSearch && matchesStatus && matchesType && matchesPeriod && matchesYear && matchesDate);
        row.style.display = isVisible ? "" : "none";
        
        if (isVisible) {
            visibleCount++;
            visibleIds.push(row.getAttribute('data-payment-id'));
        }
    });

    // Update UI and Print Link
    if (visibleCount === 0) {
        if (noResultsRow) noResultsRow.style.display = "";
        printBtn.classList.add('disabled', 'btn-secondary');
        printBtn.style.pointerEvents = 'none';
    } else {
        if (noResultsRow) noResultsRow.style.display = "none";
        printBtn.classList.remove('disabled', 'btn-secondary');
        printBtn.classList.add('btn-primary');
        printBtn.style.pointerEvents = 'auto';
        printBtn.href = `/staff/payments/print_report?ids=${visibleIds.join(',')}`;
    }
}
/**
 * Scans the "Type" column (Index 5) and populates the dropdown
 */
function populateTypeFilter() {
    const typeFilter = document.getElementById('typeFilter');
    const rows = document.querySelectorAll('#paymentTable tbody tr:not(#noResultsRow)');
    
    const types = new Set();

    rows.forEach(row => {
        const typeCell = row.cells[5]; // The "Type" column
        const typeText = typeCell.textContent.trim();

        if (typeText) {
            types.add(typeText);
        }
    });

    // Clear existing options except "All Types"
    typeFilter.innerHTML = '<option value="">All Types</option>';

    // Sort alphabetically and append
    Array.from(types)
        .sort()
        .forEach(type => {
            const option = document.createElement('option');
            // We use the exact text for the value so the filter matching stays simple
            option.value = type.toLowerCase(); 
            option.textContent = type;
            typeFilter.appendChild(option);
        });
}


document.addEventListener('DOMContentLoaded', () => {
  populateTypeFilter();
    populatePeriodFilter(); // Build the dropdown based on table content
    filterPayments();       // Set initial button state/visibility
});</script>