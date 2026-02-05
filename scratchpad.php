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
    <title>Scratch Pad</title>
    <link rel="stylesheet" href="scratch.css">
</head>
<body>
    <form method="POST">
    <h1>Scratch Pad</h1>
    <textarea name="note" required rows="12" cols="300" placeholder="Write your notes here"></textarea>
    <select name="vis">
    <option value="public">Public</option> 
    <option value="private">Private</option> 
    </select>
    <button type="submit" name="save">Save Note</button>
    <br>
    <a href="logout.php">Logout</a>
    <a href="view.php"> View Scratches</a>

<?php   
if (isset($_POST['save'])) {
    $uid=$_SESSION['user_id'];
    $note=$_POST['note'];
    $vis=$_POST['vis'];

    $stmt=mysqli_prepare($conn,"insert into notes (uid,data,vis,type) VALUES (?,?,?,'note')");
    mysqli_stmt_bind_param($stmt, "iss", $uid, $note, $vis);
    mysqli_stmt_execute($stmt);
    echo "Note saved";
}
?>
    </form>
</body>
</html>