<?php

declare(strict_types=1);

final class InvoiceRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function nextInvoiceNumber(): string
    {
        $stmt = $this->pdo->query('SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1');
        $last = $stmt->fetchColumn();

        if (!$last) {
            return 'INV-' . date('Y') . '-0001';
        }

        $parts = explode('-', (string) $last);
        $sequence = (int) end($parts) + 1;
        return sprintf('INV-%s-%04d', date('Y'), $sequence);
    }

    public function saveInvoice(array $invoiceData, array $items): int
    {
        $this->pdo->beginTransaction();

        $insertInvoice = $this->pdo->prepare(
            'INSERT INTO invoices (invoice_number, customer_name, customer_phone, invoice_date, subtotal, tax_total, grand_total)
             VALUES (:invoice_number, :customer_name, :customer_phone, :invoice_date, :subtotal, :tax_total, :grand_total)'
        );

        $insertInvoice->execute([
            ':invoice_number' => $invoiceData['invoice_number'],
            ':customer_name' => $invoiceData['customer_name'],
            ':customer_phone' => $invoiceData['customer_phone'],
            ':invoice_date' => $invoiceData['invoice_date'],
            ':subtotal' => $invoiceData['subtotal'],
            ':tax_total' => $invoiceData['tax_total'],
            ':grand_total' => $invoiceData['grand_total'],
        ]);

        $invoiceId = (int) $this->pdo->lastInsertId();

        $insertItem = $this->pdo->prepare(
            'INSERT INTO invoice_items (invoice_id, item_name, quantity, unit_price, discount_percent, line_total)
             VALUES (:invoice_id, :item_name, :quantity, :unit_price, :discount_percent, :line_total)'
        );

        foreach ($items as $item) {
            $insertItem->execute([
                ':invoice_id' => $invoiceId,
                ':item_name' => $item['item_name'],
                ':quantity' => $item['quantity'],
                ':unit_price' => $item['unit_price'],
                ':discount_percent' => $item['discount_percent'],
                ':line_total' => $item['line_total'],
            ]);
        }

        $this->pdo->commit();

        return $invoiceId;
    }

    public function findInvoice(int $invoiceId): ?array
    {
        $invoiceStmt = $this->pdo->prepare('SELECT * FROM invoices WHERE id = :id LIMIT 1');
        $invoiceStmt->execute([':id' => $invoiceId]);
        $invoice = $invoiceStmt->fetch();

        if (!$invoice) {
            return null;
        }

        $itemStmt = $this->pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = :id ORDER BY id ASC');
        $itemStmt->execute([':id' => $invoiceId]);

        $invoice['items'] = $itemStmt->fetchAll();

        return $invoice;
    }

    public function searchInvoices(?string $invoiceNumber, ?string $invoiceDate): array
    {
        $sql = 'SELECT * FROM invoices WHERE 1=1';
        $params = [];

        if (!empty($invoiceNumber)) {
            $sql .= ' AND invoice_number LIKE :invoice_number';
            $params[':invoice_number'] = '%' . $invoiceNumber . '%';
        }

        if (!empty($invoiceDate)) {
            $sql .= ' AND invoice_date = :invoice_date';
            $params[':invoice_date'] = $invoiceDate;
        }

        $sql .= ' ORDER BY invoice_date DESC, id DESC LIMIT 200';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getCustomerInvoices(string $phone): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, invoice_number, invoice_date, grand_total FROM invoices
             WHERE customer_phone = :phone ORDER BY invoice_date DESC, id DESC LIMIT 100'
        );
        $stmt->execute([':phone' => $phone]);

        return $stmt->fetchAll();
    }
}
