<?php

require_once 'db_connect.php';

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);
$userType = $loggedIn ? $_SESSION['type'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infinity Corporations</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if ($loggedIn): ?>
    <!-- Topbar -->
    <?php include 'topbar.php'; ?>
    
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content p-4" style="flex: 1; margin-left: 250px;">
    <?php endif; ?>