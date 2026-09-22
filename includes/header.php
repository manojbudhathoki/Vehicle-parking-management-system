<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? APP_NAME;

// Determine active page for nav highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description"
    content="SmartPark — Smart & convenient vehicle parking management. Reserve your slot, track sessions and get digital receipts.">
  <meta name="theme-color" content="#1d4ed8">
  <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- SmartPark Custom Styles (CSS-first; Bootstrap CSS removed) -->
  <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>

<body>
  <?php require __DIR__ . '/navbar.php'; ?>
  <main class="container page-container">
    <?php foreach (getFlashes() as $f): ?>
      <div class="alert alert-<?= e($f['type']) ?> alert-dismissible" role="alert">
        <?= e($f['message']) ?>
        <button type="button" class="btn-close" data-alert-dismiss aria-label="Close">&times;</button>
      </div>
    <?php endforeach; ?>