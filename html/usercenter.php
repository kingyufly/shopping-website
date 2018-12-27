<?php
	require __DIR__.'/lib/db.inc.php';
	require __DIR__.'/lib/auth.php';
	//require __DIR__.'/auth-process.php';

	$email = check_auth();
	if ($email == false){
		header('Location: login.php', true, 302);
		exit();
	}
	$db = new PDO('sqlite:/var/www/orders.db');
	$db->query("PRAGMA foreign_keys = ON;");
	$sql="SELECT * FROM orders WHERE email = ? ORDER BY orderid DESC limit 5;";
	$q = $db->prepare($sql);
	$q->bindParam(1, $email);
	$res = null;
	if ($q->execute())
		$res = $q->fetchAll();
	
	$admin = check_admin($email);
?>

<html>
	<head>
		<link rel="stylesheet" href="//cdn.static.runoob.com/libs/bootstrap/3.3.7/css/bootstrap.min.css">
		<script src="//cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
		<script src="//cdn.static.runoob.com/libs/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<style type="text/css">
			#ch_pwd, #all_order{margin: 10% 10% 10% 10%}
			#title {text-align:right; width:100%}
		</style>
		<title>User Center</title>
	</head>
	
	<body>
		<div id="title">
		Welcome 
		<?php
			echo $email;
		?> 
		<a href="auth-process.php?action=logout">log out</a>
		</div>
		<div>
			<ul id="myTab" class="nav nav-tabs">
				<li class="active">
					<a href="#ch_pwd" data-toggle="tab">
						Change password
					</a>
				</li>
				<?php
					if($admin == "false"){
						echo "<li><a href=\"#all_order\" data-toggle=\"tab\">Orders</a></li>";
					}
				?>
				
			</ul>

			<div id="myTabContent" class="tab-content">
				<div class="tab-pane fade in active" id="ch_pwd">
				   <fieldset>
						<legend> Change Password</legend>
						<label for="password_old"> Original Password *</label>
						<div><input id="password_old" type="password" name="password_old"/></div>
						<button onclick="validate_old()">Validate</button><span id="vali_result"></span>
						<form id="change_pwd" method="POST" action="auth-process.php?action=changepwd" enctype="multipart/form-data">
							<label for="password"> New Password *</label>
							<div> <input id="password" type="password" name="password" required="required"/></div>
							<label for="password_re"> Confirm New Password *</label>
							<div> <input id="password_re" type="password" name="password_re" required="required" onkeyup="validate()"/>
							<input type="hidden" name="email" value="<?php echo $email;?>">
							<span id="hint"></span>
							</div>
							<input id="submit" type="submit" value="Change"/>
						</form>
					</fieldset>
				</div>
				<?php
					if($admin == "false"){
						echo "<div class=\"tab-pane fade\" id=\"all_order\"><fieldset><legend>All Orders</legend>";
						echo "<table border=\"1\">";
						echo "<tr><th>Order ID</th><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th>Transaction ID</th></tr>";
							
						foreach($res as $order){
							$orderid = $order["orderid"];
							$txid = $order["txid"];
							
							$db = new PDO('sqlite:D:/CUHK/IEMS5718/ASP/SQLite/data/orders.db');
							$sql = "SELECT * FROM details WHERE orderid = ?;";
						
							$q = $db->prepare($sql);
							$q->bindParam(1, $orderid);
							$data = null;
							
							if ($q->execute())
								$data = $q->fetch();
							$detail = $data["detail"];
							$detail = json_decode($detail);
							$length = -1;
							foreach($detail as $k=>$v){
								$length = $length + 1;
							}
								
							$count = -1;
							foreach($detail as $k=>$v){
								if($count == $length - 1)
									continue;
									
								$product_data = ierg4210_prod_fetchOne_byID(intval($k));
								$name = $product_data[0]["name"];
								$price = $product_data[0]["price"];
								if($count == -1)
									echo "<tr><td rowspan=\"".$length."\">".$orderid."</td><td>".$name."</td><td>".$v."</td><td>".$price."</td><td rowspan=\"".$length."\">".$detail->total_price."</td><td rowspan=\"".$length."\">".$txid."</td></tr>";
								else
									echo "<tr><td>".$name."</td><td>".$v."</td><td>".$price."</td></tr>";
									
								$count = $count + 1;
							}
						} 
						echo "</table></fieldset></div>";
					}
				?>
			</div>
		</div>
	</body>
	<script>
		document.getElementById("password").disabled = true;
		document.getElementById("password_re").disabled = true;
		document.getElementById("submit").disabled = true;
		
		function validate_old(){
			var passowrd_old = document.getElementById("password_old").value;
						
			$.ajax({
				type: "post",
				data:  "email=<?php echo $email?>&password=" + passowrd_old,
				dataType: "text",
				url: "auth-process.php?action=valid_old",
				async:true,			
				success: function (data) {
					if (data == "true") {
						document.getElementById("password").disabled = false;
						document.getElementById("password_re").disabled = false;
						document.getElementById("submit").disabled = false;
						document.getElementById("vali_result").innerHTML="<font color='green'> Right!</font>";
					}else{
						document.getElementById("vali_result").innerHTML="<font color='red'> Wrong! Please try again!</font>";
					}
				},
				error: function(data){  
					alert("error" + data);  
				}  
			});		
		}

		function validate() {
			var password = document.getElementById("password").value;
			var password_re = document.getElementById("password_re").value;
			if(password == password_re) {
				document.getElementById("hint").innerHTML="<font color='green'>same password</font>";
				document.getElementById("submit").disabled = false;
			} else {
				document.getElementById("hint").innerHTML="<font color='red'>different password</font>";
				document.getElementById("submit").disabled = true;
			}
		}
	</script>
</html>



