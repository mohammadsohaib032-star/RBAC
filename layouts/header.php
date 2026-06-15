<?php if (!isset($pageTitle)) $pageTitle = "CS Department"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | CS Department</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="layout">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
  <div class="topbar">
    <h1><?= e($pageTitle) ?></h1>
  </div>
