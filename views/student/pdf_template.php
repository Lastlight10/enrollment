<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .section-title { background: #f4f4f4; padding: 5px; font-weight: bold; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .text-end { text-align: right; }
        .footer { margin-top: 50px; font-size: 9pt; text-align: center; color: #777; }
        .status-text { font-size: 7pt; font-weight: bold; text-transform: uppercase; }
        .payment_text, .details{font-size: 9pt;}
        .header h4 {
            margin: 2px 0;
            font-weight: normal;
            font-size: 8pt;
            color: #555;
        }
        .logo {
            width: 80px; /* Adjust width as needed */
            height: auto;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="static/images/UMLOGO.jpg" alt="UM Logo" class="logo">
        <h4>The University of Manila</h4>
        <h4>546 Delos Santos St., 403, Manila, 1008 Metro Manila</h4>
        <h4>Tel No.: 735-5256 | 735-5085</h4>
        <h2>OFFICIAL ENROLLMENT SUMMARY</h2>
        <h3 style="margin: 5px 0;"><?= strtoupper(htmlspecialchars($e->student_name ?? 'STUDENT RECORD')) ?></h3>
        
        <p>Reference ID: <?= $e->id ?> | Date: <?= date('F j, Y') ?></p>
    </div>

    <div class="section-title">Student & Academic Information</div>
    <div class="details">
        <p><strong> Name:</strong> <?= htmlspecialchars($e->user->first_name ?? 'N/A') ?> <?= htmlspecialchars($e->user->mid_name ?? 'N/A') ?> <?= htmlspecialchars($e->user->last_name ?? 'N/A') ?></p>
        <p><strong>Status:</strong> <?= strtoupper(htmlspecialchars($e->status ?? 'N/A')) ?></p>
        <table>
            <tr>
                <td><strong>Course:</strong> <?= htmlspecialchars($e->course?->course_name ?? $e->course_id ?? 'N/A') ?></td>
                <td><strong>Year Level:</strong> <?= htmlspecialchars($e->grade_year ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td><strong>Academic Period:</strong> <?= htmlspecialchars($e->period?->acad_year ?? '') ?> <?= htmlspecialchars($e->period?->semester ?? '') ?></td>
                <td><strong>ID Number:</strong> <?= htmlspecialchars($e->id_number ?? $e->user_id ?? 'N/A') ?></td>
            </tr>
        </table>
    </div>
    

    <div class="section-title">Enrolled Subjects</div>
    <table class="details">
        <thead>
            <tr>
                <th>Code</th>
                <th>Subject Name</th>
                <th width="80px" class="text-end">Units</th>
            </tr>
        </thead>
        <tbody>
            <?php $totalUnits = 0; foreach(($e->subjects ?? []) as $s): $totalUnits += $s->units; ?>
            <tr>
                <td><?= htmlspecialchars($s->subject_code ?? '') ?></td>
                <td><?= htmlspecialchars($s->subject_name ?? $s->subject_title ?? '') ?></td>
                <td class="text-end"><?= $s->units ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-end"><strong>Total Units</strong></td>
                <td class="text-end"><strong><?= $totalUnits ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($e->payments)): ?>
        <div class="section-title">Billing & Payment Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($e->payments as $p): ?>
                <tr class="payment_text">
                    <td><?= strtoupper(htmlspecialchars($p->payment_type ?? '')) ?></td>
                    <td class="status-text">
                        <?php if($p->status === 'paid'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3">
                                <i class="bi bi-check-circle-fill me-1"></i> PAID
                            </span>
                        <?php elseif($p->status === 'need_verification'): ?>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3">
                                <i class="bi bi-hourglass-split me-1"></i> PENDING VERIFICATION
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-muted border px-3">
                                <?= strtoupper($p->status ?? 'UNPAID') ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>PHP <?= number_format($p->amount ?? 0, 2) ?></td>
                    <td><?= htmlspecialchars($p->remarks ?? '') ?: 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="footer">
        <?php if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'staff' || $_SESSION['user_type'] === 'admin')): ?>
            <p style="text-align:left; border-top: 1px solid #ccc; padding-top: 10px;">
                <strong>Verified by:</strong> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Authorized Personnel') ?>
            </p>
        <?php endif; ?>
        <p>This is a system-generated document. Printed on <?= date('Y-m-d h:i A') ?></p>
    </div>
</body>
</html>