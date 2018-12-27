<?php
require __DIR__.'/lib/db.inc.php';
require __DIR__.'/lib/auth.php';

session_start();
$email = check_auth();
if ($email == false || $email != "admin@cuhk.edu.hk"){
	header('Location: login.php', true, 302);
	exit();
} else {
	$res = ierg4210_cat_fetchall();
	$options = '';

	foreach ($res as $value){
		$options .= '<option value="'.$value["catid"].'"> '.$value["name"].' </option>';
	}
	
	$db = new PDO('sqlite:/var/www/orders.db');
	$db->query("PRAGMA foreign_keys = ON;");
	$sql="SELECT * FROM orders;";
	$q = $db->prepare($sql);
	$res = null;
	if ($q->execute())
		$res = $q->fetchAll();
}
?>



<html>
	<head>
		<link rel="stylesheet" href="//cdn.static.runoob.com/libs/bootstrap/3.3.7/css/bootstrap.min.css">
		<script src="//cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
		<script src="//cdn.static.runoob.com/libs/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<style type="text/css">
			#addp, #addc, #editp, #editc, #allorder, #resetpass{margin: 10% 10% 10% 10%}
			#title {text-align:right; width:100%}
		</style>
		<script type="text/javascript">
			function editp_del(){ 
				var user_select = document.getElementById("editp_prod_pid").value;
				document.getElementById("delp_hidden").value = user_select;
				return true;
			}
			function editc_del(){
				var user_select = document.getElementById("editc_cat_catid").value;
				document.getElementById("delc_hidden").value = user_select;
				return true;
			}
			function getOptions(catid){
				$.ajax({
					type: "post",
					data:  "catid=" + catid + "&nonce=" + <?php echo csrf_getNonce("prod_fetchAll");?>,	
					dataType: "text",
					url: "admin-process.php?action=prod_fetchAll",
					async:true,
					
					success: function (data) {
						data = data.slice(9);
						var str = "";
						var obj = JSON.parse(data);

						for(var tmp in obj["success"]){
							str += ("<option value=\"" + obj["success"][tmp]["pid"] + "\">" + obj["success"][tmp]["name"] + "</option>");
						}
						document.getElementById("editp_prod_pid").innerHTML = str;
					},
					error: function(data){  
						alert("error" + data);  
					}  
				});
			}
			$(document).ready(
				function(){
					$("#editp_prod_catid").change(
						function(){
							var catid = $("#editp_prod_catid option:selected").val();
							if(catid != 0)
								getOptions(catid);
							if(catid == 0)
								document.getElementById("editp_prod_pid").innerHTML = "";
						}
					);
				}
			);
			
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
						
			function validate_nonce(){
				var email = document.getElementById("email").value;
				var nonce = document.getElementById("nonce").value;
							
				$.ajax({
					type: "post",
					data:  "email=" + email + "&nonce=" + nonce,
					dataType: "text",
					url: "auth-process.php?action=valid_nonce",
					async:true,			
					success: function (data) {
						alert(data);
						if (data == "1") {
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
				document.getElementById("newpass_email").value = email;
				return false;				
			}
		</script>
		<title>Admin Panel</title>
	</head>
	
	<body>
		<div id="title">
		<?php
			echo "Welcome ".$email." | <a href=\"usercenter.php\" target=\"_blank\">USER CENTER</a> | <a href=\"auth-process.php?action=logout\">LOG OUT</a>";
		?>
		</div>
		<div>
			<ul id="myTab" class="nav nav-tabs">
				<li class="active">
					<a href="#addp" data-toggle="tab">
						Add New Product
					</a>
				</li>
				<li>
					<a href="#addc" data-toggle="tab">
						Add New Category
					</a>
				</li>
				<li>
					<a href="#editp" data-toggle="tab">
						Edit Product
					</a>
				</li>
				<li>
					<a href="#editc" data-toggle="tab">
						Edit Category
					</a>
				</li>
				<li>
					<a href="#allorder" data-toggle="tab">
						All Orders
					</a>
				</li>
				<li>
					<a href="#resetpass" data-toggle="tab">
						Set Password
					</a>
				</li>
			</ul>

			<div id="myTabContent" class="tab-content">
				<div class="tab-pane fade in active" id="addp">
				   <fieldset>
						<legend> New Product</legend>
						<form id="addp_prod_insert" method="POST" action="admin-process.php?action=prod_insert" enctype="multipart/form-data">
							<label for="addp_prod_catid"> Category *</label>
							<div> <select id="addp_prod_catid" name="catid"><?php echo $options; ?></select></div>
							<label for="addp_prod_name"> Name *</label>
							<div>
								<!-- <input id="addp_prod_name" type="text" name="name" required="required" pattern="^[\w\-]+$"/> -->
								<input id="addp_prod_name" type="text" name="name" required="required"/>
							</div>
							<label for="addp_prod_price"> Price *</label>
							<div> <input id="addp_prod_price" type="text" name="price" required="required" pattern="^\d+\.?\d*$"/></div>
							<label for="addp_prod_desc"> Description *</label>
							<div> <input id="addp_prod_desc" type="text" required="required" name="description"/> </div>
							<label for="addp_prod_image"> Image * </label>
							<div> <input type="file" name="file" required="true" accept="image/jpeg"/> </div>
							<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("prod_insert")?>">
							<input type="submit" value="Add"/>
						</form>
					</fieldset>
				</div>
				
				<div class="tab-pane fade" id="addc">
					<fieldset>
						<legend> New Category</legend>
						<form id="addc_cat_insert" method="POST" action="admin-process.php?action=cat_insert">
							<label for="addc_cat_name"> Name *</label>
							<div> <input id="addc_cat_name" type="text" name="name" required="required" pattern="^[\w\-]+$"/></div>
							<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("cat_insert")?>">
							<input type="submit" value="Add"/>
						</form>
					</fieldset>
				</div>

				<div class="tab-pane fade" id="editp">
					<fieldset>
						<legend> Edit Product</legend>
						<form id="editp_prod_update" method="POST" action="admin-process.php?action=prod_edit" enctype="multipart/form-data">
							<label for="editp_prod_catid"> Category *</label>
							<div> <select id="editp_prod_catid" name="catid">
								<option value="0">Select</option>
								<?php echo $options; ?>
							</select></div>
							
							
							
							<label for="editp_prod_catid"> Product *</label>
							<div> <select id="editp_prod_pid" name="pid"></select></div>
							
							<label for="editp_prod_name"> New Name *</label>
							<div> 
								<!-- <input id="editp_prod_name" type="text" name="name" required="required" pattern="^[\w\-]+$"/> -->
								<input id="editp_prod_name" type="text" name="name" required="required"/>
							</div>
							<label for="editp_prod_price"> New Price *</label>
							<div> <input id="editp_prod_price" type="text" name="price" required="required" pattern="^\d+\.?\d*$"/></div>
							<label for="editp_prod_desc"> New Description *</label>
							<div> <input id="editp_prod_desc" type="text" required="required" name="description"/> </div>
							<label for="editp_prod_image"> New Image * </label>
							<div> <input type="file" name="file" required="true" accept="image/jpeg"/> </div>
							<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("prod_edit")?>">
							<input type="submit" value="Modity"/>
						</form>
						<form id="editp_prod_del" method="POST" action="admin-process.php?action=prod_delete">
							<input type="hidden" id="delp_hidden" name="pid" value=""></input>
							<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("prod_delete")?>">
							<button onclick="return editp_del();">Delete</button>
						</form>
					</fieldset>
				</div>

				<div class="tab-pane fade" id="editc">
				   <fieldset>
						<legend> Edit Category</legend>
						<form id="editc_cat_update" method="POST" action="admin-process.php?action=cat_edit">
							<label for="editc_cat_catid"> Category *</label>
							<div> <select id="editc_cat_catid" name="catid"><?php echo $options; ?></select></div>
							<label for="editc_cat_name"> New Name *</label>
							<div> <input id="editc_cat_name" type="text" name="name" required="required" pattern="^[\w\-]+$"/></div>
							<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("cat_edit")?>">
							<input type="submit" value="Modity"/>
						</form>
						<form id="editc_cat_del" method="POST" action="admin-process.php?action=cat_delete">
							<input type="hidden" id="delc_hidden" name="catid" value=""></input>
							<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("cat_delete")?>">
							<button onclick="return editc_del();">Delete</button>
						</form>
					</fieldset>
				</div>
				
				<div class="tab-pane fade" id="allorder">
					<fieldset>
						<legend> All Orders</legend>
						<table border="1">
							<tr><th>Order ID</th><th>User</th><th>Digest</th><th>Salt</th><th>Transaction ID</th><th>Payment Method</th></tr>
							<?php
								foreach($res as $order){
									echo "<tr><td>".$order["orderid"]."</td><td>".$order["email"]."</td><td>".$order["digest"]."</td><td>".$order["salt"]."</td><td>".$order["txid"]."</td><td>".$order["method"]."</td></tr>";
								}
							?>
						</table>
					</fieldset>
				</div>
				
				<div class="tab-pane fade" id="resetpass">
					<fieldset>
						<legend> Set Password</legend>
							<label for="email"> Email *</label>
							<div> <input id="email" type="email" name="email" required="required"/></div>
							<label for="nonce"> PIN *</label>
							<div> <input id="nonce" name="nonce" required="required"/></div>
							<button onclick="validate_nonce()">Validate</button><span id="vali_result"></span>
							<br>
						<form id="set_newpass" method="POST" action="admin-process.php?action=adminchangepwd">
							<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("adminchangepwd")?>">
							<input type="hidden" id="newpass_email" name="email">
							<label for="password"> Password *</label>
							<div> <input id="password" type="password" name="password" required="required"/></div>
							<label for="password_re"> Confirm Password *</label>
							<div> <input id="password_re" type="password" name="password_re" required="required" onkeyup="validate()"/>
							<span id="hint"></span>
							<br>
							<button id="submit" onclick="return true;">Submit</button>
						</form>
					</fieldset>
				</div>
			</div>
		</div>
	</body>
	<script>
		document.getElementById("password").disabled = true;
		document.getElementById("password_re").disabled = true;
		document.getElementById("submit").disabled = true;
	</script>
</html>



