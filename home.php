<?php
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
  <div>
    <div class="banner">
      <div class="navbar">
        <h1>Welcome <?php echo $_SESSION['username'] ?></h1>
        <ul>
          <li><a href="home.php">HOME</a></li>
          <li><a href="scratchpad.php">SCRATCHPAD</a></li>
          <li><a href="files.php">FILES</a></li> 
          <li><a href="admin.php">ADMIN</a></li>
          <li><a href="logout.php">LOGOUT</a></li>
        </ul>
      </div>
    
    <form action="" method="post" enctype="multipart/form-data">
        <label> image to upload </label>
        <input type="file" name="image" value=""> 
        <input type="submit" name="upload" value="Upload">
        <select name="vis">
          <option value="public">Public</option> <br>
          <option value="private">Private</option> <br>
        </select>
    </div>
</form>
<?php 
    if (isset($_POST["upload"])) {
      $uid=$_SESSION["user_id"];
      $name=basename($_FILES['image']['name']);
      $data=file_get_contents($_FILES['image']['tmp_name']);
      $vis=$_POST["vis"];
      $tempname=$_FILES['image']['tmp_name'];
      $folder="images/".$name;
      if (!preg_match("/\.(txt|jpg|jpeg|png|gif|pdf)$/i",$name)) {
        exit("Invalid file");
    }
      move_uploaded_file($tempname,$folder);
      echo $folder."<br>";
      $stmt=mysqli_prepare($conn,"insert into notes (uid,name,data,vis) values (?,?,?,?)");
      mysqli_stmt_bind_param($stmt,"isss",$uid,$name,$data,$vis);
      mysqli_stmt_execute($stmt);
      echo "file uploaded";
    }
?>
<script>
function loadDoc() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange =function() {
        if (this.readyState ==4 && this.status ==200) {
            document.getElementById("demo").innerHTML =
            this.responseText;
       }
    };
    xhttp.open("GET", "lal.txt", true);
    xhttp.send();
}
</script>
</body>
</html>