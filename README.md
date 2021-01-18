# 資料庫功課
## 目的
1. 用php寫一個網頁，最好有後台
2. 使用資料庫，必須要看的到程式碼
## ER diagram
![](https://i.imgur.com/8b7pDF0.jpg)
## Schema
![](https://i.imgur.com/G2sHtFQ.jpg)
## 程式碼
### 註冊
#### check_empty
```
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
```
用於檢查各項輸入是否為空，如果為空則返回0，在主程式會有一個變數用於接這個參數，並判定能不能寫入資料庫，以此避免缺漏資料填入資料庫。
#### register
```
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
```
在前面的一個function檢查過確定沒有錯誤後，便會呼叫這個函式，進行與資料庫的溝通。
第一步為檢察身分，學生與後台人員分別有不同的schema儲存，以便後續如果有不同資料輸入(例如帳號為email)之類的值存入。
檢查完身分後，首先會檢查id是否有存在資料庫，如果id不存在資料庫表示這個人不屬於這堂課的學生或是不是後台人員，便會拒絕註冊。
如果確定這個人是這堂課的成員，便會檢查是否重複註冊，如果密碼不為空即表示這個人已經進行過註冊，則會跳出提示不允許重複註冊。
確認過身分、重複註冊後，就是更新資料庫的部分，如果成功更新便會跳出提示，並回到homepage。
#### main
```
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
```
主程式主要即收到request後，進行函式的呼叫，在
**$_SERVER["REQUEST_METHOD"] == "POST"** 
這個地方會在使用者按了按鈕後觸發。
此外，
* **$_POST** 為接收封包 。
* **$_GET** 則接收一個URL。
### 首頁 : 登入頁面
#### sign_in
```
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
```
跟上面的註冊類似，先進行身分檢查，再依據身放至所屬資料庫尋找使用者，確定有這個使用者後，進行密碼的驗證，並進行判斷後輸出或跳轉頁面。
比較特別功能為 **$_SESSION** 會將 $id 這個參數傳至被跳轉的頁面，以便針對不同使用者輸出不同頁面。
這個在後面的頁面會使用到，會有更清楚的理解。
#### main
```
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$id = $_POST['id'];
	$pw = $_POST['pw'];
	$identity = $_POST['identity'];
	sign_in($identity,$id,$pw);
}
```
主程式簡單多了，僅進行收到參數後呼叫函式。
### 後台
#### search_student
```
function search_student($identity,$year,$search_id=""){
		$sql = "SELECT * FROM $identity WHERE course_year = $year";
		if($search_id != ""){
			$sql = $sql." && id = $search_id";
		}
		return $sql;
	}
```
選擇要顯示的是學生的頁面或是後台人員的頁面
#### show_table
```
function show_table($sql,$per,$identity){
		$mysqli = new mysqli("localhost","root","","termproject");
		$result = $mysqli->query($sql);
		$data_nums = mysqli_num_rows($result);
		$p = page_mode($data_nums,$per,$identity);
		$sql = $sql.$p;
		$result = $mysqli->query($sql);
		echo '
		<table>
			<th> 學年度 </th>
			<th> 學號 </th>
			<th> 密碼 </th>
			<th> 姓名 </th>
			<th> 更新資料 </th>
		';
		while($data = mysqli_fetch_array($result,MYSQLI_ASSOC)){
			$year = $data['course_year'];
			$id = $data['id'];
			$password = $data['password'];
			$name = $data['name'];
			fill_table($year,$id,$password,$name,$identity);
		}
		if(array_key_exists('remove', $_POST)){
			$old_id = $_POST['ID'];
			$old_identity = $_POST['IDENTITY'];
			//echo $old_identity;
			$del = "DELETE FROM $old_identity WHERE id = $old_id";
			//echo $del;
			$mysqli->query($del);
			echo "<script> {window.alert('$delete_id 刪除成功');location.href='back.php'} </script>";
		}
		if(array_key_exists('update',$_POST)){
			$old_id = $_POST['ID'];
			$old_identity = $_POST['IDENTITY'];
			$new_course_year = $_POST['update_course_year'];
			$new_id = $_POST['update_id'];
			$new_password = $_POST['update_password'];
			$new_name = $_POST['update_name'];
			$update = "UPDATE $old_identity SET course_year = $new_course_year,id = '$new_id',password = '$new_password',name = '$new_name' WHERE id=$old_id";
			//echo $update;
			$mysqli->query($update);
			echo "<script> {window.alert('$old_id 更新成功');location.href='back_2.php'} </script>";
		}
		insert_table($identity,$year);
		if(array_key_exists('insert',$_POST)){
			$identity = $_POST['IDENTITY'];
			$new_course_year = $_POST['insert_course_year'];
			$new_id = $_POST['insert_id'];
			$new_password = $_POST['insert_password'];
			$new_name = $_POST['insert_name'];
			$insert = "INSERT INTO $identity (course_year, id, password,name) 
							VALUES ($new_course_year, '$new_id', '$new_password','$new_name')";
			//echo $insert;
			$mysqli->query($insert);
			//echo $mysqli->errno == 1062;
			if($mysqli->errno == 1062)
				echo "<script> {window.alert('$new_id 新增失敗，重複插入');location.href='back_2.php'} </script>";
			echo "<script> {window.alert('$old_id 新增成功');location.href='back_2.php'} </script>";
		}
		echo '</table>';
		get_page($data_nums,$per);
	}
```
這個函式會製作出整個顯示的表格，接到的SQL語法、每頁有幾個及想要輸出的身分(學生或後台)
裡面有呼叫的函式分別為 **page_mode** 和
**get_page**,兩者都是用於顯示及換頁; **fill_table** 和 **insert_table** 則是製作表格在後面會再詳細描述。
其中，在每一列的後面會輸出一個 update 按鈕及 delete 的按鈕，如果delete的按鈕被按壓就會將這個 Row 的人刪除，如果是 update 則會依照現在被輸入在表格內的資料來做更新。
最後一個 Row 會顯示插入列，依照上面的所有打字框可以進行插入，但是在這裡並不會檢查是否有資料缺漏(因設定上後台操控不見得需要幫使用者完成所有設定)。
#### page_mode
```
function page_mode($data_nums,$per,$identity){
		$pages = ceil($data_nums / $per);
		if (!isset($_POST['page_test'])){ //假如$_POST['page_test']未設置
			$page=1; //則在此設定起始頁數
		} 
		else {
			$page = intval($_POST['page_test']);	//確認頁數只能夠是數值資料
			//echo $page;
		}
		$start = ($page-1)*$per;
		return ' LIMIT '.$start.', '.$per;
	}
```
會依照從資料庫取出來的資料筆數，來計算需要幾頁可以放得下所有資料(幾筆資料一頁已經在前面的函式完成輸入)，會在 get_page 中的頁樹選擇改變 SQL 的搜尋式。
#### get_page
```
function get_page($data_nums,$per){
		if(!isset($_POST['page_test']))
			$post = 1;
		else
			$post = $_POST['page_test'];
		$pages = ceil($data_nums / $per);
		echo '共 '.$data_nums.' 筆、 '.$pages.' 頁';
		echo '<form method="post">';
		echo'<select name = "page_test">';
			for($i = 1;$i <= $pages;$i++){
				if($i == $post)
					echo '<option value = "'.$i.'"selected>第'.$i.'頁</option>"';
				else
					echo '<option value = "'.$i.'">第'.$i.'頁</option>"';
			}
		echo'</select>';
		echo '<input type = "submit" value = "change_page" name = "change_page">';
		echo '</form>';
	}
```
取得使用者輸入的頁碼後，進行查詢，會跟前面的函式一起做動。
#### 保持選項用
```
if(!isset($_POST['year']))
		$post = '109';
	else
		$post = $_POST['year'];
	if(!isset($_POST['identity']))
		$i = 'student';
	else
		$i = $_POST['identity'];
```
其中，在每一個 select 的區塊及 radio 的區塊，都會保留最後一個選擇的選項，因此在前面多個函式都會有這個結構。
**!isset($_POST['year'])** 這個函式會檢查 $_POST 是否有填入東西，如果沒有填入則會填入預設的值，
```
if($year_bar["course_year"] == $post){
	echo '<option value = "'.$year_bar["course_year"].'"selected>'.$year_bar["course_year"].'學年度</option>"';
}
else{
	echo '<option value = "'.$year_bar["course_year"].'">'.$year_bar["course_year"].'學年度</option>"';
}
```
檢查是否和前面取得的值相同，如果相同，則會有 selected，反之則沒有，則可以將最後選到的選項預設選上。
#### fill_table
```
function fill_table($year,$id,$password,$name,$identity){
	if(!isset($_POST['update_course_year']))
		$post = $year;
	else
		$post = $_POST['update_course_year'];
	echo '
	<tr>
		<form method="post">
		<td><select name = "update_course_year">';
		$mysqli = new mysqli("localhost","root","","termproject");
		$year_search = $mysqli->query("SELECT * FROM course");
		$year_num = mysqli_num_rows($year_search);
		while($year_bar = mysqli_fetch_array($year_search,MYSQLI_ASSOC)){
			if($year_bar["course_year"] == $post)
			    echo '<option value = "'.$year_bar["course_year"].'"selected>'.$year_bar["course_year"].'學年度</option>"';
			else
			    echo '<option value = "'.$year_bar["course_year"].'">'.$year_bar["course_year"].'學年度</option>"';
	        }
                echo'</select>
		</td>
		<td> <input name = "update_id" value="'.$id.'"></td>
		<td> <input name = "update_password" value="'.$password.'"></td>
		<td> <input name = "update_name" value="'.$name.'"></td>
		<td><input type = "hidden" value = '.$id.' name = "ID">
		    <input type = "hidden" value = '.$identity.' name = "IDENTITY">
		    <input type = "submit" value = "delete" name = "remove"><br><br>
		    <input type = "submit" value = "update" name = "update">
		</td>
	    </form>
        </tr>';
}
```

填入表格內容，大部分都是html的語法，就不特別贅述。
#### insert_table
```
function insert_table($identity,$year){
	if(!isset($_POST['update_course_year']))
		$post = $year;
	else
		$post = $_POST['update_course_year'];
	echo' 
	<tr>
	    <form method="post">
		<td><select name = "insert_course_year">';
			$mysqli = new mysqli("localhost","root","","termproject");
			$year_search = $mysqli->query("SELECT * FROM course");
			$year_num = mysqli_num_rows($year_search);
			while($year_bar = mysqli_fetch_array($year_search,MYSQLI_ASSOC)){
			if($year_bar["course_year"] == $post)
		            echo '<option value = "'.$year_bar["course_year"].'"selected>'.$year_bar["course_year"].'學年度</option>"';
			else
			    echo '<option value = "'.$year_bar["course_year"].'">'.$year_bar["course_year"].'學年度</option>"';
			}
		echo'</select>
		    </td>
		    <td> <input name = "insert_id" value=""></td>
		    <td> <input name = "insert_password" value=""></td>
		    <td> <input name = "insert_name" value=""></td>
		    <td>
		        <input type = "hidden" value = '.$identity.' name = "IDENTITY">
		        <input type = "submit" value = "insert" name = "insert"><br><br>
		    </td>
	    </form>
	</tr>';
}
```
插入資料的表格。
#### main
```
$identity = @$_POST['identity'];
	$year = @$_POST['year'];
	$search_id = @$_POST['search_id'];
	$sql = "";
	if($identity == ""){
		$identity = "student";
		$year = 109;
		$sql = search_student($identity,$year);
	}
	else if($search_id == ""){
		$sql = search_student($identity,$year);
	}
	else
		$sql = search_student($identity,$year,$search_id);
	//echo $sql;
	//echo $identity;
	show_table($sql,5,$identity);
```
取得身分、學年度及學號，進行函式呼叫。
### 插入學生資料、插入學生成績
這兩個分頁會取得使用者上船的檔案並進行解讀，然後輸入資料庫。
解讀方式為開啟外部指令，呼叫事先寫好的python程式。
```
$file = $_FILES['file'];
$new_filename = "$year"."年學生資料";
move_uploaded_file($file["tmp_name"],"upload/".$new_filename.".xlsx");
```
接收使用者上傳的檔案，進行重新命名後存到upload的資料夾。
```
$cmd = "D:/server/xampp/htdocs/DEMO/read_excel.py D:/server/xampp/htdocs/DEMO/upload/$new_filename";
$output = shell_exec($cmd);
```
利用外部指令呼叫python的程式，python的程式則會將 excel 的檔案讀取後寫出一份同名的.txt檔。
則後續就進行讀取.txt檔並寫入資料庫，後面程式碼就省略了。
