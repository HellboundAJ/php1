<?php
include "db.php";
unset($_SESSION['user_id']);
header("Location: login.php");
exit;
?>
