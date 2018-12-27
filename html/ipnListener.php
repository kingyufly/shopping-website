<?php
	//error_log("hey");
	//error_log(print_r($_POST, true));
	error_reporting(E_ALL ^ E_NOTICE);
	$email = $_GET['ipn_email'];
	$header = "";
	$emailtext = "";
	// Read the post from PayPal and add 'cmd' 
	$req = 'cmd=_notify-validate'; 
	if(function_exists('get_magic_quotes_gpc')) 
	{ 
		$get_magic_quotes_exists = true; 
	} 
	
	// Handle escape characters, which depends on setting of magic quotes
	foreach ($_POST as $key => $value) {
		if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1){
			$value = urlencode(stripslashes($value)); 
		}else { 
			$value = urlencode($value); 
		} 
		$req .= "&$key=$value";
	}
	 // Post back to PayPal to validate 
	$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
	$header .= "Host: www.paypal.com\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
	$header .= "Content-Length: " . strlen($req) . "\r\n";
	$header .= "Connection: close\r\n\r\n";
	$fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
	 
	// Process validation from PayPal
	// TODO: This sample does not test the HTTP response code. All 
	// HTTP response codes must be handles or you should use an HTTP library, such as cUrl 
	if (!$fp) { // HTTP ERROR 
	} else { // NO HTTP ERROR 
		fputs ($fp, $header . $req); 
		while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED\r\n") == 0) { 
				// TODO: Check the payment_status is Completed
				error_log($_POST['payment_status']);
				if (empty($_POST['payment_status'])||$_POST['payment_status']!='Completed')
				{
					error_log("payment is not completed");
					break;
				}
				// Check that txn_id has not been previously processed
				global $db;
				$db = new PDO('sqlite:/var/www/orders.db');
				$db->query('PRAGMA foreign_keys = ON;');
				$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				$q = $db->prepare("SELECT * FROM orders LIMIT 100;");
				if ($q->execute()){
					 $cartOrder=$q->fetchAll();}
				$invoice=$_POST['invoice'];
				error_log("test invoice: ".$invoice);
				foreach($cartOrder as $car){
					if ($car['orderid'] == $invoice){
						if($car['txid'] == $_POST['txn_id']){
							 error_log("Duplicate Traction!!!");
							 break;
						}
					}
				}
				// Check that receiver_email is your Primary PayPal email
				/*
				TODO: use your email address here
				*/
				$email="kingyufly-facilitator@outlook.com";
				if($_POST['receiver_email']==$email){
					error_log("correct email");
				}else{
					error_log("incorect email");
					break;
				}

				/* $db = new PDO('sqlite:/var/www/orders.db');
				$sql="INSERT INTO test (data_data) VALUES (?);";
				$q = $db->prepare($sql);

				$q->bindParam(1, $_POST['invoice']);
				$q->execute();
				
				$db = new PDO('sqlite:/var/www/orders.db');
				$sql="INSERT INTO test (data_data) VALUES (?);";
				$q = $db->prepare($sql);

				$q->bindParam(1, $_POST['txn_id']);
				$q->execute(); */
				
				$trade_no = $_POST['txn_id'];
				$out_trade_no = $_POST['invoice'];
				
				$db = new PDO('sqlite:/var/www/orders.db');
				$db->query("PRAGMA foreign_keys = ON;");
				$sql="UPDATE orders SET txid = ? WHERE orderid = ?;";
				$q = $db->prepare($sql);
				
				$q->bindParam(1, $trade_no);
				$q->bindParam(2, $out_trade_no);
				$q->execute();
			} 
			else if (strcmp ($res, "INVALID") == 0) { 
				// If 'INVALID', send an email. TODO: Log for manual investigation. 
				foreach ($_POST as $key => $value){ 
					$emailtext .= $key . " = " .$value ."\n\n"; 
				} 
				error_log($email.'Live-INVALID IPN'.$emailtext.'\n\n'.$req);
				exit();
			} 
		}
		fclose ($fp);
	}
?>