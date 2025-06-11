<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}

require_once 'includes/footer.php';
?>