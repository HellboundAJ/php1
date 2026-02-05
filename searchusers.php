<?php
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit;
}

$q = $_GET['q'] ?? '';

$stmt = mysqli_prepare($conn,
    "SELECT id, username, password, role FROM users 
     WHERE username LIKE ? AND username != 'admin'"
);
$like = "%$q%";
mysqli_stmt_bind_param($stmt, "s", $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['username']}</td>";
    echo "<td>{$row['password']}</td>";
    echo "<td>{$row['role']}</td>";
    echo "<td>
            <a href='deleteuser.php?id={$row['id']}' 
               class='btn btn-danger btn-sm'>Delete</a>
          </td>";
    echo "</tr>";
}
