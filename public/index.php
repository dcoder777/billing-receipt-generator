<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$error = null;
$success = null;

if ($dbError) {
    $error = 'Database connection failed. Please check configuration.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$dbError) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid session token. Please refresh and try again.';
    } else {
        try {
            $built = $service->validateAndBuild($_POST);
            $invoiceId = $repo->saveInvoice($built['invoice'], $built['items']);
            header('Location: print.php?id=' . $invoiceId . '&view=a4&saved=1');
            exit;
        } catch (Throwable $throwable) {
            $error = $throwable->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($appConfig['app_name']); ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1><?= e($appConfig['app_name']); ?></h1>
        <div class="nav no-print">
            <a href="index.php">Create Invoice</a>
            <a href="records.php">Invoice Records</a>
            <a href="customers.php">Customers</a>
        </div>
    </div>

    <div class="card">
        <?php if ($error): ?><div class="alert error"><?= e($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert success"><?= e($success); ?></div><?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <div class="grid">
                <div class="col-4">
                    <label>Customer Name (optional)</label>
                    <input type="text" name="customer_name" maxlength="120" placeholder="Walk-in customer">
                </div>
                <div class="col-4">
                    <label>Customer Phone (optional)</label>
                    <input type="text" name="customer_phone" maxlength="30" placeholder="10-digit phone">
                </div>
                <div class="col-4">
                    <label>Invoice Date</label>
                    <input type="date" name="invoice_date" value="<?= e(date('Y-m-d')); ?>" required>
                </div>
                <div class="col-3">
                    <label>GST / Tax %</label>
                    <input type="number" id="tax_percent" name="tax_percent" min="0" max="100" step="0.01" value="<?= e((string) $appConfig['default_tax_percent']); ?>">
                </div>
            </div>

            <div class="table-wrap" style="margin-top: 16px;">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Discount %</th>
                            <th>Line Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row">
                            <td><input name="item_name[]" required placeholder="Item name"></td>
                            <td><input class="qty" name="quantity[]" type="number" inputmode="decimal" min="0" step="0.01" value="1" required></td>
                            <td><input class="price" name="price[]" type="number" inputmode="decimal" min="0" step="0.01" value="0" required></td>
                            <td><input class="discount" name="discount[]" type="number" inputmode="decimal" min="0" max="100" step="0.01" value="0"></td>
                            <td class="line-total">0.00</td>
                            <td><button type="button" class="secondary remove-row">Remove</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="actions" style="margin-top: 12px;">
                <button type="button" id="addItemBtn" class="secondary">+ Add Item</button>
                <button type="submit">Save Invoice & Print</button>
            </div>

            <div class="total-box" style="margin-top: 12px;">
                Subtotal: <?= e($appConfig['currency']); ?><span id="subtotal_text">0.00</span><br>
                Tax: <?= e($appConfig['currency']); ?><span id="tax_text">0.00</span><br>
                <strong>Grand Total: <?= e($appConfig['currency']); ?><span id="grand_total_text">0.00</span></strong>
            </div>
        </form>
    </div>
</div>
<script src="assets/app.js"></script>
</body>
</html>
