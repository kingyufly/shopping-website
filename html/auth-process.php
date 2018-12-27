<?php
include_once('lib/db.inc.php');
include_once('lib/auth.php');
require_once("lib/PHPMailer.php");
require_once("lib/SMTP.php");
header('Content-Type: application/json');

// input validation
if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	echo json_encode(array('failed'=>'undefined'));
	exit();
}

$action=$_REQUEST['action'];

switch ($action) {
	case "login":{
		ierg4210_login();
		break;
	}
	case "logout":{
		ierg4210_logout();
		break;
	}
	case "signup":{
		ierg4210_signup();
		break;
	}
	case "valid_old":{
		ierg4210_valid_old();
		break;
	}
	case "changepwd":{
		ierg4210_changepwd();
		break;
	}
	case "checkemail":{
		ierg4210_checkemail();
		break;
	}
	case "recoverpass":{
		ierg4210_recoverpass();
		break;
	}
	case "valid_nonce":{
		ierg4210_valid_nonce();
		break;
	}
	default:{
		echo json_encode(array('failed'=>'undefined'));
		exit();
   }
}

function ierg4210_valid_nonce(){
	$email = $_POST["email"];
	$nonce = $_POST["nonce"];
	
	$db = new PDO('sqlite:/var/www/recover.db');
	$db->query("PRAGMA foreign_keys = ON;");
		
	$sql="select * from nonce where email=?;";
	$q = $db->prepare($sql);
	$q->bindParam(1, $email);
	$res = null;
	if ($q->execute())
		$res = $q->fetch();
		
	if($res == null){
		echo "0";
	} else {
		if($res["nonce"] == $nonce)
			echo "1";
		else
			echo "0";
	}
}

function ierg4210_recoverpass(){
	$email = $_POST["email"];
	$nonce = mt_rand().mt_rand();
	
	$mail = new PHPMailer();
	$mail->SMTPDebug = 1;
	$mail->isSMTP();
	$mail->SMTPAuth = true;
	$mail->Host = 'smtp.qq.com';
	$mail->SMTPSecure = 'ssl';
	$mail->Port = 465;
	$mail->CharSet = 'UTF-8';
	$mail->FromName = 'System admin';
	$mail->Username = '1392253945@qq.com';
	$mail->Password = "bcwhrudeztiabaej";
	$mail->From = '1392253945@qq.com';
	$mail->isHTML(true);
	$mail->addAddress($email);
	$mail->Subject = 'Recover Password';
	$mail->Body = '<html>Please provide the PIN to the system admin to set a new password!<br>PIN:'.$nonce.'</html>';
	$status = $mail->send();

	$db = new PDO('sqlite:/var/www/recover.db');
	$db->query("PRAGMA foreign_keys = ON;");
		
	$sql="insert into nonce values (?, ?);";
	$q = $db->prepare($sql);
	$q->bindParam(1, $email);
	$q->bindParam(2, $nonce);
	$q->execute();
	echo $status;
}

function ierg4210_checkemail(){
	$email = $_POST["email"];
	$db = new PDO('sqlite:/var/www/cart.db');
	$db->query("PRAGMA foreign_keys = ON;");
		
	$sql="SELECT * FROM users where email = ?;";
	$q = $db->prepare($sql);
	$q->bindParam(1, $email);
	$res = null;
	if ($q->execute())
		$res = $q->fetch();
		
	if($res == null){
		echo "0";
	} else {
		echo "1";
	}
}

function ierg4210_login(){
	if(empty($_POST["email"]) || empty($_POST["password"])
		|| !preg_match("/^[\w\-\/][\w\'\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST["email"])
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST["password"])
	)
		throw new Exception("Wrong Credentials");
	
	$email=$_POST["email"];
	$password=$_POST["password"];

	$user_data = ierg4210_user_fetchOne_byemail($email);
	if(sizeof($user_data) != 1){
		echo "Wrong user name, please check the email address and re-login! Redirect to the log in page in 5 seconds...";
		header('Refresh: 5; url=login.php');
		exit();
	}
	$password = hash_hmac('sha256', $password, $user_data[0]["salt"]);
	
	if($password == $user_data[0]["password"]){
		echo "the same";
		if ($user_data[0]["flag"] == 1){
			session_start();
			//set token
			$exp = time() + 3600; //3600s
			$token = array(
				'em'=>$email,
				'exp'=>$exp,
				'k'=>hash_hmac('sha1', $exp.$password, $user_data[0]["salt"]));
			
			//create cookie
			setcookie('t4210', json_encode($token), $exp, '', '', false, true);
			
			//set session
			$_SESSION['t4210'] = $token;
			session_regenerate_id();
			header('Location: admin_easy.php', true, 302);
			exit();
			// add cookie and session
		}else{
			session_start();
			//set token
			$exp = time() + 3600; //3600s
			$token = array(
				'em'=>$email,
				'exp'=>$exp,
				'k'=>hash_hmac('sha1', $exp.$password, $user_data[0]["salt"]));
			
			//create cookie
			setcookie('t4210', json_encode($token), $exp, '', '', false, true);
			
			//set session
			$_SESSION['t4210'] = $token;
			session_regenerate_id();
			header('Location: index.php', true, 302);
			exit();
			// add cookie and session
		}
	}else{
		throw new Exception("Wrong Credentials");
	}
}

function ierg4210_logout(){
	// clear session and cookie
	session_start();
	unset($_SESSION);
	session_destroy();
	setcookie('t4210', "", time() - 3600);
	header('Location: login.php', true, 302);
	exit();
}

function ierg4210_signup(){
	if(empty($_POST["email"]) || empty($_POST["password"])
		|| !preg_match("/^[\w\-\/][\w\'\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST["email"])
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST["password"])
	)
		throw new Exception("Wrong Credentials");
	
	$email=$_POST["email"];
	$password=$_POST["password"];
	$user_data = ierg4210_user_fetchOne_byemail($email);
	if(sizeof($user_data) != 0){
		echo "The email address has already registered! Redirect to the sign up page in 5 seconds...";
		header('Refresh: 5; url=signup.php');
		exit();
	} else {
		ierg4210_user_insert();
		echo "Sign up success! Redirect to the sign up page in 5 seconds...";
		header('Refresh: 5; url=signup.php');
		exit();
	}
}

function ierg4210_valid_old(){
	$email = $_POST["email"];
	$password_old = $_POST["password"];
	$user_data = ierg4210_user_fetchOne_byemail($email);
	$password = hash_hmac('sha256', $password_old, $user_data[0]["salt"]);
	if($user_data[0]["password"] == $password){
		echo "true";
	} else {
		echo "false";
	}
}

function ierg4210_changepwd(){
	$email = $_POST["email"];
	$password = $_POST["password"];
	ierg4210_user_update_pwd($email, $password);	
	ierg4210_logout();
    header('Location: login.php');
    exit();
}
?>
