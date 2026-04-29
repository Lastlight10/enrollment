<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="text-uppercase fw-bold">Payment Records</h2>
  <a href="/staff/payments/print_report" id="printReportBtn" class="btn btn-primary shadow-sm" target="_blank">
    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Generate Full Report
  </a>
</div>

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
            <th>Type</th>
            <th>Status</th>
            <th class="text-end">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
          <tr>
            <td class="align-middle"><?= $p->created_at->format('M d, Y') ?></td>
            <td class="align-middle fw-bold"><?= $p->enrollment->user->id_number ?></td>
            <td class="align-middle text-uppercase">
              <?= $p->enrollment->user->first_name ?> <?= $p->enrollment->user->mid_name ?> <?= $p->enrollment->user->last_name ?>
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
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
function filterPayments() {
    const searchText = document.getElementById('paymentSearch').value.toLowerCase();
    const statusSelect = document.getElementById('statusFilter').value.toLowerCase();
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const rows = document.querySelectorAll('#paymentTable tbody tr');
    const printBtn = document.getElementById('printReportBtn');

    // 1. Update Print Link for PDF Report
    const params = new URLSearchParams({
        search: searchText,
        status: statusSelect,
        from: dateFrom,
        to: dateTo
    });
    printBtn.href = `/staff/payments/print_report?${params.toString()}`;

    // 2. Visual Filtering
    rows.forEach(row => {
        const rowDateText = row.cells[0].textContent.trim(); // Format: "May 20, 2026"
        const rowDate = new Date(rowDateText);
        const studentID = row.cells[1].textContent.toLowerCase();
        const studentName = row.cells[2].textContent.toLowerCase();
        const statusValue = row.querySelector('.badge').textContent.trim().toLowerCase();

        // Check Search & Status
        const matchesSearch = studentID.includes(searchText) || studentName.includes(searchText);
        let matchesStatus = (statusSelect === "" || (statusSelect === "need_verification" ? statusValue.includes("pending") : statusValue === statusSelect));

        // Check Date Range
       let matchesDate = true;
    
        // Create a normalized date object from the table (Midnight)
        const checkDate = new Date(rowDate.getFullYear(), rowDate.getMonth(), rowDate.getDate()).getTime();
        
        if (dateFrom) {
            // Create normalized 'from' date (Midnight)
            const dFrom = new Date(dateFrom);
            const from = new Date(dFrom.getFullYear(), dFrom.getMonth(), dFrom.getDate()).getTime();
            if (checkDate < from) matchesDate = false;
        }
        
        if (dateTo) {
            // Create normalized 'to' date (Midnight)
            const dTo = new Date(dateTo);
            const to = new Date(dTo.getFullYear(), dTo.getMonth(), dTo.getDate()).getTime();
            if (checkDate > to) matchesDate = false;
        }

        row.style.display = (matchesSearch && matchesStatus && matchesDate) ? "" : "none";
    });
}

document.addEventListener('DOMContentLoaded', filterPayments);
</script>