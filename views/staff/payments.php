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
        <select id="typeFilter" class="form-select shadow-sm" onchange="filterPayments()">
            <option value="">All Types</option>
            <option value="downpayment">Downpayment</option>
            <option value="prelim">Prelim</option>
            <option value="midterm">Midterm</option>
            <option value="finals">Finals</option>
            <option value="others">Others</option>
        </select>
    </div>
<div class="col-md-4">
    <label class="small fw-bold">Period</label>
    <select id="periodFilter" class="form-select shadow-sm" onchange="filterPayments()">
        <option value="">All Periods</option>
        <!-- Options will be populated by JavaScript -->
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
            <th>Period</th>
            <th>Type</th>
            <th>Status</th>
            <th class="text-end">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
          <tr data-payment-id="<?= $p->id ?>">
            <td class="align-middle"><?= $p->created_at->format('m/d/Y') ?></td>
            <td class="align-middle fw-bold"><?= $p->enrollment->user->id_number ?></td>
            <td class="align-middle text-uppercase">
              <?= $p->enrollment->user->first_name ?> <?= $p->enrollment->user->mid_name ?> <?= $p->enrollment->user->last_name ?>
            </td>
            <td class="align-middle" data-period-id="<?= $p->enrollment->period_id ?>">
              <?= $p->enrollment->period->acad_year ?? 'N/A' ?> - <?= $p->enrollment->period->semester ?? '' ?>
            </td>
            <td class="align-middle"><?= ucfirst($p->payment_type) ?></td>
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
        const periodCell = row.cells[3]; // The "Period" column
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
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const rows = document.querySelectorAll('#paymentTable tbody tr:not(#noResultsRow)');
    const noResultsRow = document.getElementById('noResultsRow');
    const printBtn = document.getElementById('printReportBtn');

    let visibleCount = 0;
    let visibleIds = []; // NEW: Array to hold IDs of visible rows

    rows.forEach(row => {
        const rowDateText = row.cells[0].textContent.trim(); 
        const rowDate = new Date(rowDateText);
        const studentID = row.cells[1].textContent.toLowerCase();
        const studentName = row.cells[2].textContent.toLowerCase();
        const rowPeriodId = row.cells[3].getAttribute('data-period-id');
        const typeValue = row.cells[4].textContent.trim().toLowerCase(); 
        
        const badge = row.querySelector('.badge');
        const isPending = badge.classList.contains('bg-warning');
        const isPaid = badge.classList.contains('bg-success');
        const isUnpaid = badge.classList.contains('bg-danger');

        const matchesSearch = studentID.includes(searchText) || studentName.includes(searchText);
        
        let matchesStatus = true;
        if (statusSelect !== "") {
            if (statusSelect === "paid" && !isPaid) matchesStatus = false;
            if (statusSelect === "unpaid" && !isUnpaid) matchesStatus = false;
            if (statusSelect === "need_verification" && !isPending) matchesStatus = false;
        }

        const matchesType = (typeSelect === "" || typeValue === typeSelect);
        const matchesPeriod = (periodSelect === "" || rowPeriodId === periodSelect);

        let matchesDate = true;
        const checkDate = new Date(rowDate.getFullYear(), rowDate.getMonth(), rowDate.getDate()).getTime();

        if (dateFrom) {
            const dFrom = new Date(dateFrom);
            const from = new Date(dFrom.getFullYear(), dFrom.getMonth(), dFrom.getDate()).getTime();
            if (checkDate < from) matchesDate = false;
        }

        if (dateTo) {
            const dTo = new Date(dateTo);
            const to = new Date(dTo.getFullYear(), dTo.getMonth(), dTo.getDate()).getTime();
            if (checkDate > to) matchesDate = false;
        }

        const isVisible = (matchesSearch && matchesStatus && matchesType && matchesPeriod && matchesDate);
        row.style.display = isVisible ? "" : "none";
        
        if (isVisible) {
            visibleCount++;
            // NEW: Get the ID from the data attribute and add it to the list
            visibleIds.push(row.getAttribute('data-payment-id'));
        }
    });

    if (visibleCount === 0) {
        if (noResultsRow) noResultsRow.style.display = "";
        printBtn.classList.add('disabled', 'btn-secondary');
        printBtn.style.pointerEvents = 'none'; 
        printBtn.href = "#";
    } else {
        if (noResultsRow) noResultsRow.style.display = "none";
        printBtn.classList.remove('disabled', 'btn-secondary');
        printBtn.classList.add('btn-primary');
        printBtn.style.pointerEvents = 'auto';

        // CHANGED: Send the list of IDs instead of filter text
        const params = new URLSearchParams({
            ids: visibleIds.join(',') 
        });
        printBtn.href = `/staff/payments/print_report?${params.toString()}`;
    }
}
document.addEventListener('DOMContentLoaded', () => {
    populatePeriodFilter(); // Build the dropdown based on table content
    filterPayments();       // Set initial button state/visibility
});</script>