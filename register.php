<?php
include "db.php";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
<div class="wrapper"> 
<form method="post">
    <h1>Register</h1>
    <div class="input-box">
    <input type="text" name="username" id="username" required> <label for="">Username</label>
    <span id="userMsg"></span>
    <i class='bx  bx-user'></i>
    </div>
    <div class="input-box">
    <input type="password" name="password" required>  <label for="">Password</label>
    <i class='bx  bx-lock'></i>
    </div>
    <input type="submit" name="register" class="btn" value="Register">
<p>
Already have an account? <a href="login.php" class="lal">Login here</a>
</p>
</form>
</div>
<div>
<?php
if (isset($_POST['register'])) {

    $username=htmlspecialchars($_POST['username']);
    $password=htmlspecialchars($_POST['password']);
    
    if ($username=="" || $password=="") {
        echo "required";
    } else {
        $stmt=mysqli_prepare($conn,"select * from users where username=?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result)>0) {
            echo "username already taken";
        } else {
            $stmt=mysqli_prepare($conn,"insert into users (username,password,role) values (?,?,'user')");
            mysqli_stmt_bind_param($stmt, "ss", $username, $password);
            mysqli_stmt_execute($stmt);
            header("Location: login.php");
            exit;
            
        }
    }
}
?>
</div>
<script>
const user=document.getElementById("username");
const msg=document.getElementById("userMsg");
user.addEventListener("keyup", function () {
    if (this.value.length < 1) {
        msg.innerText = "";
        return;
    }
    fetch("usernamecheck.php?username=" + this.value)
      .then(res=>res.text())
      .then(data=>{
          if (data==="Username taken") {
              msg.style.color="red";
              msg.innerText="Username already taken";
          } else {
              msg.style.color="green";
              msg.innerText="Username available";
          }
      });
});
</script>

</body>
</html>
