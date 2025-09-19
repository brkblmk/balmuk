<?php
session_start();
require_once '../config/database.php';
require_once '../config/security.php';

// Logout aktivitesi kaydet
if (isset($_SESSION['admin_id'])) {
    logActivity('logout', 'auth', null, $_SESSION['admin_id']);
}

// Session'ı temizle
session_destroy();

// Login sayfasına yönlendir
header('Location: login.php?logout=1');
exit;
?>