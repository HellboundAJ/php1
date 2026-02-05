<?php
include "db.php";
setcookie("biggie", "Ymkwc3tsMzRybjFuZ19waHBfMXNfZ3IzNHR9",time()+(86400*4),"/");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper"> 
        <form method="post">
            <h1>Login</h1>
            <div class="input-box">
                <input type="text" name="username" required> <label for="">Username</label>
                <i class='bx  bx-user'></i> 
            </div>
            <div class="input-box">
                <input type="password" name="password" required> <label for="">Password</label>
                <i class='bx  bx-lock'></i> 
            </div>
            <p><div class="remember-forgot">
            <input type="checkbox"> Remember me 
            </div></p>
            <input type="submit" name="login" class="btn" value="Login">
        </form>
    
        <?php
        if (isset($_POST['login'])) {

            $username = htmlspecialchars($_POST['username']);
            $password = htmlspecialchars($_POST['password']);

            $stmt = mysqli_prepare($conn,"select * from users where username=? and password=?");
            mysqli_stmt_bind_param($stmt, "ss", $username, $password);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                echo "Login successful <br>";
                header("Location: home.php");
                exit;
            } else {
                echo "Wrong username or password";
            }
            mysqli_stmt_close($stmt);
        }
        ?>
        <div class="register-link">
        <p>
        Dont have a account? <a href="register.php">Register</a>
        </p>
        </div>
    </div>
</body>
</html>
