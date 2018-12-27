<?php
	require __DIR__.'/lib/db.inc.php';
	require __DIR__.'/lib/auth.php';
	
	session_start();
	$email = check_auth();
	//if ($email == false){
	//	header('Location: login.php', true, 302);
	//	exit();
	//} else {
		$catid = $_REQUEST['catid'];
		$index = $catid - 1;
		$cat_res = ierg4210_cat_fetchall();
		$product_res = ierg4210_prod_fetchAll_byCat($catid);
		$options = '';

		foreach ($cat_res as $value){
			$options .= '<option value="'.$value["catid"].'"> '.$value["name"].' </option>';
		}
	//}
?>

<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo $cat_res[$index]["name"]; ?></title>
		<script src="//cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
		<style>
			.menu {float: left}	
			.shopcartWhole {float: right; overflow:auto}
			.catagories {clear: both; float: left}
			ul.table {list-style: none}
			.shopcart {float: right}
			.shopcartsContent{display:none}
			.shopcartWhole:hover .shopcartsContent{display:block;}
			.item img{
				background-size:contain|cover;
				width:100%;
				height: auto;
				display:inline-block;
			}
			.catagories{
				width:20%;
		/*		float:left;*/
				background:red;
			}
			.items{
				width:80%;
				overflow:hidden;
				/* background:blue; */
			}
			.items img{
				width:100%;
				overflow:hidden;
				background:blue;
			}
			.item p{
				width: 100%;
				font-size: 20px
			}	
			.item td  
			{  
				white-space: nowrap;  
			}
			.contents {
				float: left;
				width:60%
			}
			.img {
				float: left;
				width:30%;
			}
			.buy {
				float: right;
				width:80%;
		/*		background:yellow;*/
			}
			.price{
				background:green;
				float: left;
				font-size: 20px;
				width: 20%;
				height: 20%
			}
			#title {text-align:right; width:100%}
		</style>
	</head>

	<body>
		<div id="title">
			<?php
				if($email == false){
					echo "Welcom guests! you can <a href=\"login.php\">login here</a>";
					//log in
				}else{
					echo "Welcome ".$email." | <a href=\"usercenter.php\" target=\"_blank\">USER CENTER</a> | <a href=\"auth-process.php?action=logout\">LOG OUT</a>";
					// user center
				}
			?>
		</div>
		<div class="menu">
			<a href="index.php">HOME</a><span>&gt;</span>
			<a href="category.php?catid=<?php echo $catid; ?>"><?php echo $cat_res[$index]["name"]; ?></a><span>&gt;</span>
		</div>
		<div class="shopcartWhole">
			<div class="shopcart">SHOP CHART</div>
			<div class="shopcartsContent">
				<table border="1" id="shopcart_table">
				</table>
				<p id="total"></p>
				<form id="pay-select" method="POST" action="pay-select.php">
				</form>
			</div>
		</div>
		<div class="catagories">
			<ul class="table">
				<li>Catagories</li>
				<?php
					foreach ($cat_res as $value){
						echo '<li><a href="category.php?catid='.$value["catid"].'">'.$value["name"].'</a></li>';
					}
				?>
			</ul>
		</div>
		<div class="items">   
			<table border="1">
					<tr>
						<td>Picture</td>
						<td>Name</td>
						<td>Price</td>
						<td>Discount</td>
						<td>Qty</td>
						<td></td>
					</tr>
					<?php
						foreach ($product_res as $value){
							echo "<tr><td><a href=\"product.php?pid=".$value["pid"];
							echo "\"><img src=\"images\\thumbnail\\".$value["pid"];
							echo ".jpg\"></a></td>";
							echo '<td><a href="product.php?pid='.$value["pid"].'">'.$value["name"].'</a></td>';
							echo '<td>'.$value["price"].'</td>';
							
							$db = new PDO('sqlite:/var/www/shop.db');
							$db->query("PRAGMA foreign_keys = ON;");
								
							$sql="select * from discount where pid=?;";
							$q = $db->prepare($sql);
							$q->bindParam(1, $value["pid"]);
							$res = null;
							if ($q->execute())
								$res = $q->fetch();
							
							if($res == null)
								echo '<td>None</td>';
							else
								echo '<td>'.$res["describe"].'</td>';
							
							echo '<td><input id="qty_'.$value["pid"].'" type="text" name="qty" required="required" pattern="^\d+\.?\d*$"/></td>';
							echo '<td><button id="confirm_'.$value["pid"].'">AddToCart</button></td></tr>';
						}
					?>
				</table>
		</div> 
		<script type="text/javascript">
			var category_add_item = function category_add_item(e){
				var str = e.target.id;
				str = str.split("_")[1];
				var qty = document.getElementById("qty_" + str).value;
				
				if (qty == null || qty == "")
					return;
					
				var reg =/^\d+$/;
				var re = new RegExp(reg);
				
				if(re.test("" + qty) == false){
					alert('Please input an integer');
					return;
				} else {
					var storage = window.localStorage;
					var data = storage.getItem("shop_cart");
					if (data == null || data == "[]"){
						var shop_cart = [{"pid": str, "quantity": qty}];
						data = JSON.stringify(shop_cart);
						storage.setItem("shop_cart", data);
						document.getElementById("qty_" + str).value = "";
					}else{
						var json = JSON.parse(data);
						// mdoify the original value
						for(var i = 0, l = json.length; i < l; i++){
							if(json[i]["pid"] == parseInt(str)){
								json[i]["quantity"] = parseInt(json[i]["quantity"]) + parseInt(qty);
								storage.setItem("shop_cart", JSON.stringify(json));
								document.getElementById("qty_" + str).value = "";
								break;
							}
						}
						// add a new item if no item exist
						var new_data = {"pid": str, "quantity": qty};
						json.push(new_data);
						storage.setItem("shop_cart", JSON.stringify(json));
						document.getElementById("qty_" + str).value = "";
					}
					repaint_shopcart();
				}
			}
			
			var add_item = function add_item(e){
				var str = e.target.id;
				str = str.split("_")[1];
				var storage = window.localStorage;
				var data = storage.getItem("shop_cart");
				var json = JSON.parse(data);
				for(var i = 0, l = json.length; i < l; i++){
					if(json[i]["pid"] == parseInt(str)){
						json[i]["quantity"] = parseInt(json[i]["quantity"]) + 1;
						storage.setItem("shop_cart", JSON.stringify(json));
						break;
					}
				}
				repaint_shopcart();
			}
			
			var sub_item = function sub_item(e){
				var str = e.target.id;
				str = str.split("_")[1];
				var storage = window.localStorage;
				var data = storage.getItem("shop_cart");
				var json = JSON.parse(data);
				for(var i = 0, l = json.length; i < l; i++){
					if(json[i]["pid"] == parseInt(str)){
						json[i]["quantity"] = parseInt(json[i]["quantity"]) - 1;
						if(json[i]["quantity"] <= 0){
							json.splice(i, 1);
							storage.setItem("shop_cart", JSON.stringify(json));
							break;
						} else {
							storage.setItem("shop_cart", JSON.stringify(json));
							break;
						}
					}
				}
				if(storage.getItem("shop_cart") == "[]")
					storage.removeItem("shop_cart");
				repaint_shopcart();
			}
			
			function check_cart(){
				var storage = window.localStorage;
				var data = storage.getItem("shop_cart");
				
				if(data == null || data == "[]"){
					alert("No goods are in the shop cart, please add at least one item")
					return false;
				}
				
				var url = window.location.href;
				storage.setItem("url", url);
				return true;
			}
			
			function repaint_shopcart(){
				var storage = window.localStorage;
				var data = storage.getItem("shop_cart");
				
				if(data == null){
					document.getElementById("shopcart_table").innerHTML = "<tr><td>Name</td><td>Price</td><td>Qty</td><td>Total</td></tr>";
					document.getElementById("pay-select").innerHTML =  "<input type=\"submit\" value=\"PAY\" onclick=\"return check_cart()\">";
					document.getElementById("total").innerHTML = "Total: 0";
					return;
				}
					
				var json = JSON.parse(data);
						
				var str = "<tr><td>Name</td><td>Price</td><td>Qty</td><td>Total</td></tr>";
				var str_form = "<input type=\"submit\" value=\"PAY\" onclick=\"return check_cart()\">";
				
				var total = 0;
				// Get each product data
				for(var i = 0, l = json.length; i < l; i++){
					var pid = json[i]["pid"];
					var quantity = json[i]["quantity"];
					
					var result = null;
					$.ajax({
						type: "post",
						data:  "pid=" + pid + "&nonce=" + <?php echo csrf_getNonce("prod_fetchOne");?>,
						dataType: "text",
						url: "admin-process.php?action=prod_fetchOne",
						async:false,			
						success: function (data) {
							data = data.slice(9);
							var obj = JSON.parse(data);

							str += "<tr><td>" + obj["success"][0]["name"] + "</td><td>" + obj["success"][0]["price"] + "</td><td><button id=\"" + "addButton_" + pid + "\">+</button><nobr id=\"" + "quantity_" + pid + "\">" + quantity
								+"</nobr><button id=\"" + "subButton_" + pid + "\">-</button></td><td>" + parseInt(quantity) * parseFloat(obj["success"][0]["price"]) + "</td></tr>";
							total += parseInt(quantity) * parseFloat(obj["success"][0]["price"]);
						},
						error: function(data){  
							alert("error" + data);  
						}  
					});		
				}
				document.getElementById("shopcart_table").innerHTML = str;
				document.getElementById("total").innerHTML = "Total: " + total;
				document.getElementById("pay-select").innerHTML = str_form;
				for(var i = 0, l = json.length; i < l; i++){
					var pid = json[i]["pid"];
					document.getElementById("addButton_" + pid).addEventListener("click", add_item);
					document.getElementById("subButton_" + pid).addEventListener("click", sub_item);
				}
			}

			repaint_shopcart();
			<?php
				foreach ($product_res as $value){
					echo 'document.getElementById("confirm_'.$value["pid"].'").addEventListener("click", category_add_item);';
				}
			?>
		</script>
	</body>
</html>
