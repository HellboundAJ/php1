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
    <title>Scratchpad</title>
    <link rel="stylesheet" href="view.css">
</head>
<body>
    <h2>Public scratchpad</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q=mysqli_query($conn,"select * from notes where vis='public' and type='note'");
            while ($row=mysqli_fetch_assoc($q)) {
                $uid=$row['uid'];
                $user_q=mysqli_query($conn,"select username from users where id=$uid");
                $user_row=mysqli_fetch_assoc($user_q);
                echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$user_row['username']."</td>";
                    echo "<td>".htmlspecialchars($row['data'])."</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <h3>Private Scratchpad</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $currentid=$_SESSION['user_id'];
            $q=mysqli_query($conn,"select * from notes where vis='private' and uid=$currentid and type='note'");
            while ($row=mysqli_fetch_assoc($q)) {
                $uid=$row['uid'];
                $user_q=mysqli_query($conn,"select username from users where id=$uid");
                $user_row=mysqli_fetch_assoc($user_q);
                echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$user_row['username']."</td>";
                    echo "<td>".htmlspecialchars($row['data'])."</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <a href="scratchpad.php">← Back to Scratchpad</a>
</body>
</html>
        