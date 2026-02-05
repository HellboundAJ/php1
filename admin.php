<?php
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role']!='admin') {
    echo "Access denied. Admins only.<br>";
    echo "<a href='login.php'>Login</a>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="admin.css">
</head>
<body style="margin: 50px;">
    <div class="search-wrapper">Search Users
    <input type="search" id="search">          
    </div>
    <h1>List of Users</h1>
    <br>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Id</th>
                <th>Username</th>
                <th>Password</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id= "userTable">
            <?php
            $result=mysqli_query($conn,"select * from users where username!='admin'");
            while ($row=mysqli_fetch_assoc($result)) {
                echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$row['username']."</td>";
                    echo "<td>".$row['password']."</td>";
                    echo "<td>".$row['role']."</td>";
                    echo "<td>";
                    echo "<a href='deleteuser.php?id=".$row['id']."'class='btn btn-danger btn-sm'>Delete</a>";
                    echo "</td>";
                echo "</tr>";
            }
            ?>
            <a href="addnewuser.php" class="btn btn-success">Add New User</a>
            <a href="home.php">HOME</a>
        </tbody>
    </table>
    <h1>List of Files</h1>
    <br>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Id</th>
                <th>user id</th>
                <th>file</th>
                <th>username</th>
                <th>type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result=mysqli_query($conn,"select n.id,n.uid,n.name,u.username,n.type from notes n join users u on n.uid=u.id");
            while ($row=mysqli_fetch_assoc($result)) {
                echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$row['uid']."</td>";
                    echo "<td>".$row['name']."</td>";
                    echo "<td>".$row['username']."</td>";
                    echo "<td>".$row['type']."</td>";
                    echo "<td>";
                    echo "<a href='deletefile.php?id=".$row['id']."'class='btn btn-danger btn-sm'>Delete</a>";
                    echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
<script>
const search=document.getElementById("search");
const table=document.getElementById("userTable");

search.addEventListener("keyup",function() {
    const xhr=new XMLHttpRequest();
    xhr.open("GET","searchusers.php?q=" + encodeURIComponent(this.value), true);
    xhr.onreadystatechange=function () {
        if (xhr.readyState===4 && xhr.status===200) {
            table.innerHTML=xhr.responseText;
        }
    };
    xhr.send();
});
</script>



</body>
</html>