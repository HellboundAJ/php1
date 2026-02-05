<?php
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit;
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($conn, "delete from notes where id=$id");
    header("Location: admin.php");
    exit;
}
?>
