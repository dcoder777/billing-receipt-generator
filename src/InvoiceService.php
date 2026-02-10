<?php

declare(strict_types=1);

final class InvoiceService
{
    public function __construct(private readonly InvoiceRepository $repo, private readonly array $appConfig)
    {
    }

    public function validateAndBuild(array $postData): array
    {
        $items = [];
        $itemNames = $postData['item_name'] ?? [];
        $quantities = $postData['quantity'] ?? [];
        $prices = $postData['price'] ?? [];
        $discounts = $postData['discount'] ?? [];

        $rowCount = max(count($itemNames), count($quantities), count($prices), count($discounts));

        for ($i = 0; $i < $rowCount; $i++) {
            $name = trim((string) ($itemNames[$i] ?? ''));
            $qty = (float) ($quantities[$i] ?? 0);
            $price = (float) ($prices[$i] ?? 0);
            $discount = (float) ($discounts[$i] ?? 0);

            if ($name === '' && $qty === 0.0 && $price === 0.0) {
                continue;
            }

            if ($name === '' || $qty <= 0 || $price < 0 || $discount < 0 || $discount > 100) {
                throw new InvalidArgumentException('Please provide valid item name, quantity, price, and discount.');
            }

            $line = $qty * $price;
            $line -= ($line * $discount / 100);

            $items[] = [
                'item_name' => $name,
                'quantity' => round($qty, 2),
                'unit_price' => round($price, 2),
                'discount_percent' => round($discount, 2),
                'line_total' => round($line, 2),
            ];
        }

        if (count($items) === 0) {
            throw new InvalidArgumentException('At least one invoice item is required.');
        }

        $subtotal = array_sum(array_column($items, 'line_total'));
        $taxPercent = isset($postData['tax_percent']) ? (float) $postData['tax_percent'] : (float) $this->appConfig['default_tax_percent'];
        $taxPercent = max(0, min(100, $taxPercent));
        $taxTotal = $subtotal * $taxPercent / 100;
        $grandTotal = $subtotal + $taxTotal;

        return [
            'invoice' => [
                'invoice_number' => $this->repo->nextInvoiceNumber(),
                'customer_name' => trim((string) ($postData['customer_name'] ?? '')) ?: null,
                'customer_phone' => trim((string) ($postData['customer_phone'] ?? '')) ?: null,
                'invoice_date' => $postData['invoice_date'] ?? date('Y-m-d'),
                'subtotal' => round($subtotal, 2),
                'tax_total' => round($taxTotal, 2),
                'grand_total' => round($grandTotal, 2),
            ],
            'items' => $items,
            'tax_percent' => $taxPercent,
        ];
    }
}
