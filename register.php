<html>
<body>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
學年度 : <select name= "year">
		<option value=109> 109學年度</option>
	 </select><br>
姓名 : <input type="text" name="name"><br>
學號 : <input type="text" name="id"><br>
密碼 : <input type="password" name="pw"><br>
請再次輸入密碼 : <input type="password" name="pw2"><br>
身分 : <select name = "identity">
				<option value="student"> 學生</option>
				<option value="staff"> 後台人員</option>
		</select><br>
<input type="submit">
<input type ="button" onclick="javascript:location.href='index.php'"  value="回到首頁"></input>
</form>

<?php
function check_empty($year,$name,$id,$password,$password2){
	if(empty($year)){
		echo "<script>alert('請填入學年度') </script>";
		return 0;
	}
	if(empty($name)){
		echo "<script>alert('請填入姓名')</script>";
		return 0;
	}
	if(empty($id)){
		echo "<script>alert('請填入學號')</script>";
		return 0;
	}
	if(empty($password)){
		echo "<script>alert('請填入密碼')</script>";
		return 0;
	}
	if(empty($password2)){
		echo "<script>alert('請再次輸入密碼')</script>";
		return 0;
	}
	if($password != $password2){
		echo "<script>alert('密碼不符合，請再試一次')</script>";
		return 0;
	}
	return 1;
}
function register($identity,$year,$id,$name,$password){
	$mysqli = new mysqli("localhost","root","","termproject");
	$sql = "";
	$update = "";
	if($identity == "student"){
		$sql = "SELECT * FROM student WHERE id = $id";
		$update = "UPDATE student SET course_year = $year,name = '$name',password='$password' WHERE id = $id";
	}
	else if($identity == "staff"){
		$sql = "SELECT * FROM back WHERE id = $id";
		$update = "UPDATE back SET course_year = $year,name = '$name',password='$password' WHERE id = $id";
	}
	$data = $mysqli->query($sql);
	$result = $data->fetch_assoc();
	if($result == "")
		echo "<script> {window.alert('您沒有註冊權限');location.href='register.php'} </script>";
	else if($result["id"] == $id){
		if($result["password"] == NULL){
			$mysqli->query($update);
			echo "<script> {window.alert('註冊成功');location.href='index.php'} </script>";
		}
		else
			echo "<script> {window.alert('重複註冊');location.href='register.php'} </script>";
	}
}
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$year = $_POST['year'];
	$name = $_POST['name'];
	$id = $_POST['id'];
	$password = $_POST['pw'];
	$password2 = $_POST['pw2'];
	$identity = $_POST['identity'];
	$temp = check_empty($year,$name,$id,$password,$password2);
	if($temp == 1)
		register($identity,$year,$id,$name,$password);
	
}
?>
</body>
</html>
