<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";

/**
 * Require one or more roles. Pass a single int or array of ints.
 * Redirects to login if not authenticated.
 * Shows 403 if authenticated but wrong role.
 */
function requireRole($roleIds) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }
    $allowed = is_array($roleIds) ? $roleIds : [$roleIds];
    if (!in_array($_SESSION['role_id'], $allowed)) {
        http_response_code(403);
        $css = '../assets/style.css';
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
        <link rel='stylesheet' href='$css'>
        <title>403 Access Denied</title></head>
        <body class='auth-body'><div class='auth-card' style='text-align:center'>
        <div style='font-size:52px;margin-bottom:16px'>🔒</div>
        <h2 style='color:var(--red);margin-bottom:8px'>403 — Access Denied</h2>
        <p style='color:var(--muted);font-size:14px;margin-bottom:24px'>You do not have permission to view this page.</p>
        <a href='../auth/login.php' class='btn btn-primary'>Back to Login</a>
        </div></body></html>";
        exit;
    }
}
