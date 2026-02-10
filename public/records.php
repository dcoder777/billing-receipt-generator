<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$invoiceNumber = trim((string) ($_GET['invoice_number'] ?? ''));
$invoiceDate = trim((string) ($_GET['invoice_date'] ?? ''));
$results = (!$dbError && $repo) ? $repo->searchInvoices($invoiceNumber ?: null, $invoiceDate ?: null) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Records</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Invoice Records</h1>
        <div class="nav">
            <a href="index.php">Create Invoice</a>
            <a href="customers.php">Customers</a>
        </div>
    </div>

    <div class="card">
        <?php if ($dbError): ?><div class="alert error">Database connection failed. Please check configuration.</div><?php endif; ?>
        <form method="get" class="grid">
            <div class="col-4">
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" value="<?= e($invoiceNumber); ?>" placeholder="INV-2026-0001">
            </div>
            <div class="col-4">
                <label>Date</label>
                <input type="date" name="invoice_date" value="<?= e($invoiceDate); ?>">
            </div>
            <div class="col-4" style="display:flex;align-items:flex-end;">
                <button type="submit">Search</button>
            </div>
        </form>

        <div class="table-wrap" style="margin-top: 16px;">
            <table>
                <thead><tr><th>#</th><th>Date</th><th>Customer</th><th>Phone</th><th>Total</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?= e($row['invoice_number']); ?></td>
                        <td><?= e($row['invoice_date']); ?></td>
                        <td><?= e($row['customer_name']); ?></td>
                        <td><?= e($row['customer_phone']); ?></td>
                        <td><?= e($appConfig['currency']) . e(number_format((float) $row['grand_total'], 2)); ?></td>
                        <td>
                            <a href="print.php?id=<?= (int) $row['id']; ?>&view=a4">A4</a> |
                            <a href="print.php?id=<?= (int) $row['id']; ?>&view=thermal">Thermal</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
