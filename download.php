<?php
include "db.php";
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT name, data FROM notes WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $name, $data);
        mysqli_stmt_fetch($stmt);
        header("Content-Disposition: attachment; filename=\"$name\"");
        echo $data;
        exit;
    } else {
        exit("File not found.");
    }
} else {
    exit("No file specified.");
}