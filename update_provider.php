<?php
session_start();
include 'Login_dbconn.php';
if(!isset($_SESSION['user_id'])){
    die("Not logged in");
}
$provider_id = $_SESSION['user_id'];
$sql = "SELECT * FROM service_details WHERE provider_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$provider_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
if($_SERVER['REQUEST_METHOD']=="POST"){
    $b = $_POST['business_name'];
    $c = $_POST['category'];
    $a = $_POST['about_service'];
    $p = $_POST['price'];
    $l = $_POST['locations'];
    if($data){ 
        $sql="UPDATE service_details SET business_name=?,category=?,about_service=?,price=?,locations=? WHERE provider_id=?";
        $stmt=$conn->prepare($sql);
        $stmt->bind_param("sssssi",$b,$c,$a,$p,$l,$provider_id);
        $stmt->execute();
        $stmt->close();
    }else{ 
        $sql="INSERT INTO service_details (provider_id,business_name,category,about_service,price,locations) VALUES (?,?,?,?,?,?)";
        $stmt=$conn->prepare($sql);
        $stmt->bind_param("isssss",$provider_id,$b,$c,$a,$p,$l);
        $stmt->execute();
        $stmt->close();
    } 
    $conn->query("UPDATE users SET status='pending' WHERE user_id=$provider_id");

    header("Location: dashboard_provider.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="dasboard_providerstyle.css">
</head>
<body>
<div class="container">
<header class="header">
 <h1>Edit & Re-Submit Business Profile</h1>
</header>
<div class="main-content">
<form method="POST">
<label>Business Name</label>
<input type="text" name="business_name" value="<?=$data['business_name'] ?? ''?>" required>
<label>Service Category</label>
<select name="category" required>
  <?php
    $cats=['Plumber','Carpenter','Electrician','Mechanic'];
    foreach($cats as $c){
      $sel = (isset($data['category']) && $data['category']==$c)?"selected":"";
      echo "<option $sel>$c</option>";
    }
  ?>
</select>
<label>About Service</label>
<textarea name="about_service" required><?=$data['about_service'] ?? ''?></textarea>
<label>Service Price</label>
<input type="text" name="price" value="<?=$data['price'] ?? ''?>">
<label>Locations (comma separated)</label>
<input type="text" name="locations" value="<?=$data['locations'] ?? ''?>">
<button type="submit">Save & Submit</button>
</form>
</div>
</div>
</body>
</html>
