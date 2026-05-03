<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .status-badge { text-transform: uppercase; font-size: 10px; font-weight: bold; }
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
        <div class="report-title">ENROLLMENT SUMMARY REPORT</div>
        <p>Generated on: <?= date('F j, Y g:i A') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Number</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Year</th>
                <th>Status</th>
                <th>Date Applied</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($enrollments as $row): ?>
            <tr>
                <td><?= $row->id_number ?></td>
                <td><?= $row->user->full_name ?? 'N/A' ?></td>
                <td><?= $row->course->course_name ?? 'N/A' ?></td>
                <td><?= $row->grade_year ?></td>
                <td><?= strtoupper($row->status) ?></td>
                <td><?= date('M d, Y', strtotime($row->created_at)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="footer">
        <p>Verified by: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Authorized Personnel') ?></p>
        <p>This report contains <?= count($enrollments) ?> transaction records.</p>
    </div>
</body>
</html>