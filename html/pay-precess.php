<?php
include_once('lib/db.inc.php');
include_once('lib/auth.php');
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
	default:{
		echo json_encode(array('failed'=>'undefined'));
		exit();
   }
}

function pay_paypal(){
	
}

function pay_alipay(){

}
?>