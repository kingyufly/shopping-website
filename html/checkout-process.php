<?php
	include_once('lib/db.inc.php');
	include_once('lib/auth.php');

	session_start();
	$email = check_auth();

	if ($email == false)
		$email = "GUEST";
	// input validation
	if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
		echo json_encode(array('failed'=>'undefined'));
		exit();
	}

	$action = $_REQUEST['action'];
	$method = $_REQUEST['method'];

	switch ($action) {
		case "genDigest":{
			genDigest();
			break;
		}
		case "getAmount":{
			getAmount();
			break;
		}
		case "checkVoucher":{
			checkVoucher();
			break;
		}
		case "getDiscount":{
			getDiscount();
			break;
		}
		default:{
			echo json_encode(array('failed'=>'undefined'));
			exit();
	   }
	}

	function checkVoucher(){
		$code = $_POST["code"];
		$db = new PDO('sqlite:/var/www/shop.db');
		$db->query("PRAGMA foreign_keys = ON;");
		
		$sql="SELECT * FROM voucher where voucher = ?;";
		$q = $db->prepare($sql);
		$q->bindParam(1, $code);
		$res = null;
		if ($q->execute())
			$res = $q->fetch();
		
		if($res == null){
			echo "0";
		} else {
			echo $res["money"];
		}
	}
	
	function getDiscount(){
		$pid = $_POST["pid"];
		$db = new PDO('sqlite:/var/www/shop.db');
		$db->query("PRAGMA foreign_keys = ON;");
		
		$sql="SELECT * FROM discount where pid = ?;";
		$q = $db->prepare($sql);
		$q->bindParam(1, $pid);
		$res = null;
		if ($q->execute())
			$res = $q->fetch();
		
		if($res == null){
			echo "";
		} else {
			echo $res["discount_data"];
		}
	}
	
	function genDigest(){
		global $email;
		global $method;
		$salt = mt_rand(); /*generate a salt*/
		$shoppingcart_info = "";
		$cart_info = json_decode($_POST["cart"]); /*get cart information*/
		$totalPrice = $cart_info->total_price;
		/* PDO operations → get price of each product and calculate the total price */
		$db = ierg4210_DB();
		$sql = $db->prepare("select name, price from products where pid = ?");
		foreach($cart_info as $k=>$v){
			if($k == "total_price")
				continue;
			$sql->bindParam(1, $k);
			$res = null;
			if ($sql->execute())
				$res = $sql->fetch();
			$name = $res["name"];
			$price = $res["price"];
			
			$shoppingcart_info = $shoppingcart_info.$name."&".$price."&".$v."|";
			/*your implementation here*/
		}
		
		$shoppingcart_info = substr($shoppingcart_info, 0, -1);
		$currency = "HKD";
		//$email = "kingyufly-facilitator@outlook.com";
		$digest = sha1($currency.$email.$salt.$shoppingcart_info.$totalPrice);
		
		$millisecond = get_millisecond();
		$millisecond = str_pad($millisecond,3,'0',STR_PAD_RIGHT);
		$time_stamp = date("YmdHis").$millisecond;
		
		$db = new PDO('sqlite:/var/www/orders.db');
		$db->query("PRAGMA foreign_keys = ON;");
		
		$sql="SELECT * FROM orders limit 1;";
		$q = $db->prepare($sql);
		$res = null;
		if ($q->execute())
			$res = $q->fetch();
		
		$invoice = 0;
		if($res == null){
			$invoice = $time_stamp."1";
		} else {
			$sql="SELECT MAX(orderid) FROM orders;";
			$q = $db->prepare($sql);
			$res = null;
			if ($q->execute())
				$res = $q->fetch();
			
			$invoice = $res["MAX(orderid)"];
			$invoice = intval(substr($invoice, 17)) + 1;
			$invoice = $time_stamp.$invoice;
		}
		$sql="INSERT INTO orders (orderid, email, digest, salt, txid, method) VALUES (?, ?, ?, ?, \"notyet\", ?);";
		$q = $db->prepare($sql);
		$q->bindParam(1, $invoice);
		$q->bindParam(2, $email);
		$q->bindParam(3, $digest);
		$q->bindParam(4, $salt);
		$q->bindParam(5, $method);
		$q->execute();		
		
		detail_record($invoice);
		//update_orderid($invoice, $orderid);
		$returnval = json_encode(array("digest"=>$digest, "invoice"=>$invoice));
		echo $returnval;
	}
	
	function getAmount(){
		$pid = $_POST["pid"]; /*get cart information*/
		/* PDO operations → get price of each product and calculate the total price */
		$db = ierg4210_DB();
		$sql = $db->prepare("select price from products where pid = ?");
		$sql->bindParam(1, $pid);
		$res = null;
		if ($sql->execute())
			$res = $sql->fetch();
		$price = $res["price"];
		echo $price;
	}
	
	function detail_record($orderid){
		$db = new PDO('sqlite:/var/www/orders.db');
		$db->query("PRAGMA foreign_keys = ON;");
		$sql="INSERT INTO details (orderid, detail) VALUES (?, ?);";
		$q = $db->prepare($sql);
		$q->bindParam(1, $orderid);
		$q->bindParam(2, $_POST["cart"]);
		$q->execute();
	}
	
	function update_orderid($orderid_old, $orderid){
		$db = new PDO('sqlite:/var/www/orders.db');
		$db->query("PRAGMA foreign_keys = ON;");
		$sql="UPDATE orders SET orderid = ? WHERE orderid = ?;";
		$q = $db->prepare($sql);
		
		$q->bindParam(1, $orderid);
		$q->bindParam(2, $orderid_old);
		$q->execute();
	}
	
	function get_millisecond()  
	{  
		list($usec, $sec) = explode(" ", microtime());  
		$msec=round($usec*1000);
		return $msec;          
	}  
?>
