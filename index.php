<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">

id: <input type="text" name="id">*</span>
<br><br>
password:
<input type="password" name="pw" >*</span>
<br><br>
身分 : <select name = "identity" required>
				<option value = ""> 請選擇身分</option>
				<option value="student"> 學生</option>
				<option value="staff"> 後台人員</option>
		</select><br>
<input type="submit" name="submit" value="Submit">
</form>
<a href = "register.php"> 你是這堂課的學生嗎 </a>
<?php
function sign_in($identity,$id,$password){
	$mysqli = new mysqli("localhost","root","","termproject");
	$sql = "";
	$jump = "";
	if($identity == "student"){
		$sql = "SELECT * FROM student WHERE id = '$id'";
		$jump = "<script> {window.alert('學生 $id 登入成功');location.href='student_met.php'} </script>";
	}
	else if($identity == "staff"){
		$sql = "SELECT * FROM back WHERE id = '$id'";
		$jump = "<script> {window.alert('後台人員 $id 登入成功');location.href='back.php'} </script>";
	}
	$data = $mysqli->query($sql);
	if(mysqli_data_seek($data,0)){
		$result = mysqli_fetch_array($data,MYSQLI_ASSOC);
		#echo $result['password'];
		if($result['password'] == ""){
			echo "<script> if(confirm( '尚未註冊，是否註冊?'))  location.href='register.php';else location.href='index.php'; </script>";
		}
		else if($password == $result['password']){
			session_start();
			$_SESSION["id"] = $id;
			echo $jump;
		}
		else{
			echo "<script> {window.alert('密碼錯誤');location.href='index.php'} </script>";
		}
	}
	else{
		echo "<script> {window.alert('您沒有權限');location.href='index.php'} </script>";
	}	
}
$mysqli = new mysqli("localhost","root","","termproject");
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$id = $_POST['id'];
	$pw = $_POST['pw'];
	$identity = $_POST['identity'];
	sign_in($identity,$id,$pw);
}
?>
