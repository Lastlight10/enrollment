<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #004d00; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #004d00; color: white; padding: 8px; text-align: left; }
        td { padding: 6px; border-bottom: 1px solid #eee; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; }
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
        <div class="report-title">USER ACCOUNTS REPORT</div>
        <div>School Enrollment System</div>
        <div>Date Generated: <?= date('F d, Y h:i A') ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Number</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Account Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr>
                <td><?= $user->id_number ?: 'N/A' ?></td>
                <td><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></td>
                <td><?= htmlspecialchars($user->username) ?></td>
                <td><?= htmlspecialchars($user->email) ?></td>
                <td><?= ucfirst($user->type) ?></td>
                <td><?= ucfirst($user->status) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Verified by: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Authorized Personnel') ?></p>
        <p>This report contains <?= count($users) ?> accounts.</p>
    </div>
</body>
</html>