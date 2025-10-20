<?php
require_once __DIR__ . '/bootstrap.php';

use App\Controllers\VatController;

$controller = new VatController();
$action = $_GET['action'] ?? 'home';

if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleUpload();
    exit;
}
if ($action === 'progress' && isset($_GET['uploadId'])) {
  $controller->processUploadBatch($_GET['uploadId']);
  $controller->progress($_GET['uploadId']);
  exit;
}
if ($action === 'test' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleTest();
    exit;
}

$data = $controller->index();
$results = $data;
$uploadId = $_GET['uploadId'] ?? null;
include __DIR__ . '/templates/layout.php';
