<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/InvoiceRepository.php';
require_once __DIR__ . '/../src/InvoiceService.php';

$appConfig = require __DIR__ . '/../config/app.php';
$dbError = null;
$repo = null;
$service = null;

try {
    $repo = new InvoiceRepository(Database::connection());
    $service = new InvoiceService($repo, $appConfig);
} catch (Throwable $throwable) {
    $dbError = $throwable->getMessage();
}
