<?php 
// views/templates/header.php
session_start();
require_once __DIR__ . '/../../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="../../views/public/index.php">
                <i class="fas fa-heartbeat" style="color: var(--primary-red);"></i> 
                <span style="color: var(--primary-red); font-weight:900;">Jeevan</span><span style="color: var(--primary-blue);">Setu</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ml-auto">
                    <!-- <li class="nav-item"><a class="nav-link" href="#needs">Urgent Needs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#process">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#benefits">Our Pledge</a></li> -->
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white ml-lg-3" href="register.php">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-primary ml-lg-2" href="login.php">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>