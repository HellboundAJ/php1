<?php
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role']!='admin') {
    exit("Admins only");
}
if (isset($_POST['adduser'])) {
    $username=$_POST['username'];
    $password=$_POST['password'];
    $role=$_POST['role'];

    if ($role!= "admin" && $role!="user") {
        echo "Role must be either 'admin' or 'user'.";
        exit;
    }
    if ($username=="" || $password=="" || $role =="") {
        echo "All fields are required.";
        exit;
    }
    $check=mysqli_query($conn, "select * from users where username='$username'");
    if (mysqli_num_rows($check)>0) {
        echo "Username already taken.";
    } else {
        mysqli_query($conn,"insert into users (username, password, role) values ('$username', '$password', '$role')");
        header("Location: admin.php");
        exit;
    }
}
?>
<body>
<div class="wrapper"> 
<form method="post">
    <h1>ADD NEW USER</h1>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="text" name="role" placeholder="Role (admin/user)" required><br>
    <input type="submit" name="adduser" class="btn" value="Add User">
</form>