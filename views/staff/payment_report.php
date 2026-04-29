<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .report-title { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-table th { background: #f2f2f2; border: 1px solid #ccc; padding: 8px; text-align: left; }
        .summary-table td { border: 1px solid #ccc; padding: 6px; }
        .text-right { text-align: right; }
        .total-section { margin-top: 20px; text-align: right; font-size: 12px; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 9px; color: #777; font-style: italic; }
        .logo {
            width: 80px; /* Adjust width as needed */
            height: auto;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    
    
    <div class="header">
        <img src="/static/images/UMLOGO.jpg" alt="UM Logo" class="logo">
        <h4>The Universtity of Manila</h4>
        <h4>546 Delos Santos St., 403, Manila, 1008 Metro Manila</h4>
        <h4>Tel No.: 735-5256 | 735-5085</h4>
        <div class="report-title">PAYMENT SUMMARY REPORT</div>
        <div>School Enrollment System</div>
        <div>Date Generated: <?= date('F d, Y h:i A') ?></div>
    </div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Type</th>
                <th>Status</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
      <tbody>
            <?php 
            $totalAmount = 0;
            foreach ($payments as $p): 
                $totalAmount += $p->amount;
                $displayStatus = ($p->status === 'need_verification') ? 'PENDING VERIFICATION' : strtoupper($p->status);
            ?>
            <tr>
                <td><?= $p->created_at->format('Y-m-d') ?></td>
                <td><?= $p->enrollment->user->id_number ?></td>
                <td><?= strtoupper($p->enrollment->user->last_name . ', ' . $p->enrollment->user->first_name . " " .$p->enrollment->user->mid_name) ?></td>
                <td><?= ucfirst($p->payment_type) ?></td>
                <td style="color: <?= ($p->status === 'unpaid' ? 'red' : ($p->status === 'need_verification' ? 'orange' : 'green')) ?>;">
                    <?= $displayStatus ?>
                </td>
                <td class="text-right">₱<?= number_format($p->amount, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-section">
        GRAND TOTAL: ₱<?= number_format($totalAmount, 2) ?>
    </div>

    <div class="footer">
        <p>Verified by: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Authorized Personnel') ?></p>
        <p>This report contains <?= count($payments) ?> transaction records.</p>
    </div>
</body>
</html>