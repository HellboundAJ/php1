<?php
include "db.php";

if (!isset($_GET['username'])) {
    exit;
}
$username=$_GET['username'];
$q=mysqli_prepare($conn,"select * from users where username=?");
mysqli_stmt_bind_param($q, "s", $username);
mysqli_stmt_execute($q);
mysqli_stmt_store_result($q);
if (mysqli_stmt_num_rows($q)>0) {
    echo "Username taken";
} else {
    echo "available";
}


