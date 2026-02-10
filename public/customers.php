<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$phone = trim((string) ($_GET['phone'] ?? ''));
$invoices = ($phone !== '' && !$dbError && $repo) ? $repo->getCustomerInvoices($phone) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Customer Invoice History</h1>
        <div class="nav">
            <a href="index.php">Create Invoice</a>
            <a href="records.php">Invoice Records</a>
        </div>
    </div>

    <div class="card">
        <?php if ($dbError): ?><div class="alert error">Database connection failed. Please check configuration.</div><?php endif; ?>
        <form method="get" class="grid">
            <div class="col-6">
                <label>Customer Phone</label>
                <input type="text" name="phone" value="<?= e($phone); ?>" placeholder="Enter phone number">
            </div>
            <div class="col-3" style="display:flex;align-items:flex-end;">
                <button type="submit">View Invoices</button>
            </div>
        </form>

        <?php if ($phone !== ''): ?>
            <div class="table-wrap" style="margin-top:16px;">
                <table>
                    <thead><tr><th>Invoice #</th><th>Date</th><th>Total</th><th>Reprint</th></tr></thead>
                    <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?= e($invoice['invoice_number']); ?></td>
                            <td><?= e($invoice['invoice_date']); ?></td>
                            <td><?= e($appConfig['currency']) . e(number_format((float) $invoice['grand_total'], 2)); ?></td>
                            <td><a href="print.php?id=<?= (int) $invoice['id']; ?>&view=thermal">Thermal</a> | <a href="print.php?id=<?= (int) $invoice['id']; ?>&view=a4">A4</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
