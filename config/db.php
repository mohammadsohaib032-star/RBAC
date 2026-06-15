<?php
$conn = new mysqli("localhost", "root", "", "cs_department_rbac");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('logAudit')) {
    function logAudit($conn, $userId, $action) {
        $userId = intval($userId);
        $action = $conn->real_escape_string($action);
        $conn->query(
            "INSERT INTO audit_logs (user_id, action) VALUES ($userId, '$action')"
        );
    }
}
