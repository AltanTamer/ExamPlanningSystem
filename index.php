<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("location: login.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['Role'];

switch ($role) {
    case 'Assistant':
        header("location: assistant.php");
        break;
    case 'Secretary':
        header("location: secretary.php");
        break;
    case 'Head of Department':
        header("location: head_of_department.php");
        break;
    case 'Head of Secretary':
        header("location: head_of_secretary.php");
        break;
    case 'Dean':
        header("location: dean.php");
        break;
    default:
        echo "Invalid role";
}
?>
