<!DOCTYPE html>
<html>
<head>
    <title>Calculation History</title>
    <link rel="stylesheet" href="<?= URLROOT; ?>/css/style.css">
</head>
<body>
    <div class="history">
        <h2>Calculation History</h2>
        <table>
            <tr>
                <th>First Number</th>
                <th>Operator</th>
                <th>Second Number</th>
                <th>Result</th>
            </tr>
            <?php foreach ($data['calculations'] as $calc): ?>
                <tr>
                    <td><?= htmlspecialchars($calc->first_number); ?></td>
                    <td><?= htmlspecialchars($calc->operation); ?></td>
                    <td><?= htmlspecialchars($calc->second_number); ?></td>
                    <td><?= htmlspecialchars($calc->result); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a href="<?= URLROOT; ?>/calculate">Back to Calculator</a>
    </div>
</body>
</html>