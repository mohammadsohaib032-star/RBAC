<?php
session_start();
require "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

switch ($_SESSION['role_id']) {
    case 1: header("Location: admin/dashboard.php");         break;
    case 2: header("Location: teacher/dashboard.php");        break;
    case 3: header("Location: student/dashboard.php");        break;
    case 4: header("Location: faculty/lecture_schedule.php"); break;
    default: header("Location: auth/login.php");
}
exit;
