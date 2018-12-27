<?php
include_once('lib/db.inc.php');

function check_auth(){
	if(!empty($_SESSION['t4210']))
		return $_SESSION['t4210']['em'];
	if(!empty($_COOKIE['t4210'])){
		if($t = json_decode(stripcslashes($_COOKIE['t4210']),true)){
			if(time() > $t['exp'])
				return false;
			$db = ierg4210_DB();
			$q = $db->prepare("SELECT * FROM users where email=?;");
			$q->bindParam(1, $t['em']);
			$q->execute();
				
			if ($r = $q->fetch()){
				$realk = hash_hmac('sha1', $t['exp'].$r['password'], $r['salt']);
				if($realk == $t['k']){
					$_SESSION['t4210'] = $t;
					return $t['em'];
				}
			}
		}
	}
}

function csrf_getNonce($action){
	$nonce = mt_rand().mt_rand();
	
	if(!isset($_SESSION['csrf_nonce']))
		$_SESSION['csrf_nonce'] = array();
	$_SESSION['csrf_nonce'][$action] = $nonce;
	return $nonce;
}

function csrf_verifyNonce($action, $receivedNonce){
	if(($action == "prod_fetchAll") || ($action == "prod_fetchOne"))
		return true;
	if(isset($receivedNonce) && $_SESSION['csrf_nonce'][$action] == $receivedNonce){
		if($_SESSION['t4210'] == null)
			unset($_SESSION['csrf_nonce'][$action]);
		return true;
	}
	throw new Exception('csrf_attack');
}

function check_admin($email){
	$user_data = ierg4210_user_fetchOne_byemail($email);
	if($user_data[0]["flag"] == 1){
		return "true";
	} else {
		return "false";
	}
}
?>