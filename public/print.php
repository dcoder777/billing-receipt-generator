<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$view = $_GET['view'] ?? 'a4';
$invoice = (!$dbError && $repo) ? $repo->findInvoice($id) : null;

if ($dbError) {
    http_response_code(500);
    echo 'Database connection failed. Please check configuration.';
    exit;
}

if (!$invoice) {
    http_response_code(404);
    echo 'Invoice not found.';
    exit;
}

$isThermal = $view === 'thermal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print <?= e($invoice['invoice_number']); ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<div class="container no-print" style="padding-bottom:0;">
    <div class="actions">
        <a href="records.php">← Back to records</a>
        <button onclick="window.print()">Print</button>
        <a href="print.php?id=<?= (int) $invoice['id']; ?>&view=<?= $isThermal ? 'a4' : 'thermal'; ?>">Switch to <?= $isThermal ? 'A4' : 'Thermal'; ?></a>
    </div>
</div>

<div class="print-shell <?= $isThermal ? 'receipt-thermal' : ''; ?>">
    <h2 style="margin:0;"><?= e($appConfig['app_name']); ?></h2>
    <p>Invoice #: <?= e($invoice['invoice_number']); ?><br>
    Date: <?= e($invoice['invoice_date']); ?></p>
    <p>Customer: <?= e($invoice['customer_name'] ?: 'Walk-in'); ?><br>
    Phone: <?= e($invoice['customer_phone'] ?: '-'); ?></p>

    <table>
        <thead>
            <tr><th>Item</th><th>Qty</th><th>Rate</th><th>Disc%</th><th>Total</th></tr>
        </thead>
        <tbody>
            <?php foreach ($invoice['items'] as $item): ?>
                <tr>
                    <td><?= e($item['item_name']); ?></td>
                    <td><?= e(number_format((float) $item['quantity'], 2)); ?></td>
                    <td><?= e(number_format((float) $item['unit_price'], 2)); ?></td>
                    <td><?= e(number_format((float) $item['discount_percent'], 2)); ?></td>
                    <td><?= e(number_format((float) $item['line_total'], 2)); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-box">
        Subtotal: <?= e($appConfig['currency']) . e(number_format((float) $invoice['subtotal'], 2)); ?><br>
        Tax: <?= e($appConfig['currency']) . e(number_format((float) $invoice['tax_total'], 2)); ?><br>
        <strong>Total: <?= e($appConfig['currency']) . e(number_format((float) $invoice['grand_total'], 2)); ?></strong>
    </div>
</div>
</body>
</html>
