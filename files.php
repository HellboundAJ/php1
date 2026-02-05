<?php
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>FILES</title>
</head>
<body>
    <h2>Public Files</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>File</th>
                <th>Username</th>
            </tr>
        </thead>
        <body>
            <?php
            $q=mysqli_query($conn,"select * from notes where vis='public'");
            while ($row=mysqli_fetch_assoc($q)) {
                $uid=$row['uid'];
                $user_q=mysqli_query($conn,"select username from users where id=$uid");
                $user_row=mysqli_fetch_assoc($user_q);
                echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td><a href='download.php?id=".$row['id']."'>".$row['name']."</a></td>";
                    echo "<td>".$user_row['username']."</td>";
                echo "</tr>";
            }
            ?>
        </body>
    </table>
    <h3>Private Files</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>File</th>
                <th>Username</th>
            </tr>
        </thead>
        <body>
            <?php
            $currentid=$_SESSION['user_id'];
            $q=mysqli_query($conn,"select * from notes where vis='private' and uid=$currentid");
            while ($row=mysqli_fetch_assoc($q)) {
                $uid=$row['uid'];
                $user_q=mysqli_query($conn,"select username from users where id=$uid");
                $user_row=mysqli_fetch_assoc($user_q);
                echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td><a href='download.php?id=".$row['id']."'>".$row['name']."</a></td>";
                    echo "<td>".$user_row['username']."</td>";
                echo "</tr>";
            }
            ?>
        </body>


    

