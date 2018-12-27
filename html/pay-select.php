<?php
	require __DIR__.'/lib/db.inc.php';
	require __DIR__.'/lib/auth.php';
	
	session_start();
	$email = check_auth();
?>

<html>
	<head>
		<style type="text/css">
			#addp{
				margin: 10% 10% 10% 10%
			}	
			img{  
				width: auto;  
				height: auto;  
				max-width: 20%;  
				max-height: 20%;     
			}
		</style>
		<script src="//cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
		<script type="text/javascript">
			var total = 0;
			var voucher_value = 0;
			var my_cart_info = "{";
			var storage = window.localStorage;
			
			var data = storage.getItem("shop_cart");			
			
			if(data == null){
				alert("No item need to be checked, redirect to the main page");
				window.location.href="//localhost/index.php";
			}
			
			var json = JSON.parse(data);	
			var str = "Total: HKD ";
				
			// Get each product data
			for(var i = 0, l = json.length; i < l; i++){
				var pid = json[i]["pid"];
				var quantity = json[i]["quantity"];
				my_cart_info += ("\"" + pid + "\":" + quantity + ",");
				
				$.ajax({
					type: "post",
					data:  "pid=" + pid + "&nonce=" + <?php echo csrf_getNonce("prod_fetchOne");?>,
					dataType: "text",
					url: "admin-process.php?action=prod_fetchOne",
					async:false,			
					success: function (data) {
						data = data.slice(9);
						var obj = JSON.parse(data);

						total += parseInt(quantity) * parseFloat(obj["success"][0]["price"]);
					},
					error: function(data){  
						alert("error" + data);  
					}
				});		
			}
			
			my_cart_info += "\"total_price\":" + total + "}";
				
			function cal_total(){
				var data = storage.getItem("shop_cart");					
				var json = JSON.parse(data);	
				var str = "Total: HKD ";
				total = 0;
				// Get each product data
				for(var i = 0, l = json.length; i < l; i++){
					var pid = json[i]["pid"];
					var quantity = json[i]["quantity"];
					var discount_data = "";
					
					$.ajax({
						type: "post",
						data:  "pid=" + pid,
						dataType: "text",
						url: "checkout-process.php?action=getDiscount&method=discount",
						async:false,			
						success: function (data) {
							if(data == "")
								discount_data = "";
							else
								discount_data = data;
						},
						error: function(data){  
							alert("error" + data);  
						}  
					});
					
					if (discount_data == ""){
						$.ajax({
							type: "post",
							data:  "pid=" + pid + "&nonce=" + <?php echo csrf_getNonce("prod_fetchOne");?>,
							dataType: "text",
							url: "admin-process.php?action=prod_fetchOne",
							async:false,			
							success: function (data) {
								data = data.slice(9);
								var obj = JSON.parse(data);
								total += parseInt(quantity) * parseFloat(obj["success"][0]["price"]);
							},
							error: function(data){  
								alert("error" + data);  
							}  
						});		
					} else {
						// money discount
						if(discount_data[0] == "$"){
							discount_data = discount_data.substr(1);
							discount_data = discount_data.split("@"); 
							
							$.ajax({
								type: "post",
								data:  "pid=" + pid + "&nonce=" + <?php echo csrf_getNonce("prod_fetchOne");?>,
								dataType: "text",
								url: "admin-process.php?action=prod_fetchOne",
								async:false,			
								success: function (data) {
									data = data.slice(9);
									var obj = JSON.parse(data);
									var item_total_price = parseInt(quantity) * parseFloat(obj["success"][0]["price"]);
																		var offset_price = parseInt(parseInt(item_total_price)/parseInt(discount_data[0]))*parseInt(discount_data[1]);
									total += (item_total_price - offset_price);
								},
								error: function(data){  
									alert("error" + data);  
								}  
							});	
						}else{ // item discount
							discount_data = discount_data.substr(1);
							discount_data = discount_data.split("@"); 
							offset_item = parseInt(parseInt(quantity)/parseInt(discount_data[0]));
							$.ajax({
								type: "post",
								data:  "pid=" + pid + "&nonce=" + <?php echo csrf_getNonce("prod_fetchOne");?>,
								dataType: "text",
								url: "admin-process.php?action=prod_fetchOne",
								async:false,			
								success: function (data) {
									data = data.slice(9);
									var obj = JSON.parse(data);
									total += (parseInt(quantity) - offset_item) * parseFloat(obj["success"][0]["price"]);
								},
								error: function(data){  
									alert("error" + data);  
								}  
							});	
						}
					}
					
				}
				total = total - voucher_value;
				document.getElementById("total").innerHTML = (str + total);
			}
			
			function my_submit(){
				var storage = window.localStorage;
			
				var data = storage.getItem("shop_cart");			
				
				if(data == null){
					alert("All items have been checked, redirect to previous page");
					back();
					return false;
				}
						
				var radios = document.getElementsByName("pay_method");
				var value = 0;
				for(var i = 0; i < radios.length; i++){
					if(radios[i].checked == true){
						value = radios[i].value;
					}
				}
				if (value == "paypal"){
					paypal_pay();
				}
				else if (value == "alipay")
					alipay_pay();
				else
					alert("Please select one payment method!")
				return false;
			}
			
			function paypal_pay(){
				var form = document.getElementById("hidden_paypal");
				var xhr = (window.XMLHttpRequest)
					? new XMLHttpRequest()
					: new ActiveXObject("Microsoft.XMLHTTP"),
					async = true;
				xhr.open('POST', 'checkout-process.php?action=genDigest&method=paypal', async);
				xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
				
				xhr.onreadystatechange = function(){
					if(xhr.readyState == 4 && xhr.status == 200){
						console.log(xhr.responseText);
						json = JSON.parse(xhr.responseText);
						form.custom.value = json["digest"]
						form.invoice.value = json["invoice"];
						console.log(my_cart_info);
						var cart_json = JSON.parse(my_cart_info);
						var i = 0;
						
						// change to one product with the total amount to make the calulation of the voucher easier
	
						var newItem = document.createElement("input");
						newItem.type = "hidden";
						newItem.name = "item_name_1";
						newItem.value = "total";
						var newItem2 = document.createElement("input");
						newItem2.type = "hidden";
						newItem2.name = "quantity_1";
						newItem2.value = "1";							
						var newItem3 = document.createElement("input");
						newItem3.type = "hidden";
						newItem3.name = "amount_1";
						newItem3.value = total;
							
						form.appendChild(newItem);
						form.appendChild(newItem2);
						form.appendChild(newItem3);
												
						
						/* for(var k in cart_json){	
							if(k == "total_price")
								continue;
							else
								i++;
							var newItem = document.createElement("input");
							newItem.type = "hidden";
							newItem.name = "item_name_" + i;
							newItem.value = k.toString();
							var newItem2 = document.createElement("input");
							newItem2.type = "hidden";
							newItem2.name = "quantity_" + i;
							newItem2.value = cart_json[k].toString();
							console.log(newItem);	
							
							var newItem3 = document.createElement("input");
							newItem3.type = "hidden";
							newItem3.name = "amount_" + i;
							
							var amount = 0;
							$.ajax({
								type: "post",
								data:  "pid=" + k,
								dataType: "text",
								url: "checkout-process.php?action=getAmount&method=paypal",
								async:false,			
								success: function (data) {
									amount = parseFloat(data);
								},
								error: function(data){  
									alert("error" + data);  
								}
							});

							newItem3.value = amount.toString();
							
							//apply discount
							form.appendChild(newItem);
							form.appendChild(newItem2);
							form.appendChild(newItem3);
						} */
						console.log(form);
								
						var storage = window.localStorage;
						storage.removeItem("shop_cart");
						document.getElementById("hidden_paypal").submit();
					}
				};
				xhr.send('cart=' + my_cart_info);
			}
			
			function alipay_pay(){				
				$.ajax({
					type: "post",
					data:  "cart=" + my_cart_info,
					dataType: "text",
					url: "checkout-process.php?action=genDigest&method=alipay",
					async:false,			
					success: function (data) {
						var json = JSON.parse(data);
						document.getElementById("WIDout_trade_no").value =  json["invoice"];
						document.getElementById("WIDsubject").value = "Shopping Mall Order";
						document.getElementById("WIDtotal_amount").value = total;
						document.getElementById("hidden_alipay").submit();
					},
					error: function(data){  
						alert("error" + data);  
					}
				});	
			}
			
			function back(){
				var url = storage.getItem("url");
				storage.removeItem("url");
				location = url;
			}
			
			function voucher(){
				var code = document.getElementById("voucher_code").value;
				if (code == null || code == "")
					return;
				else{
					$.ajax({
						type: "post",
						data:  "code=" + code,
						dataType: "text",
						url: "checkout-process.php?action=checkVoucher&method=voucher",
						async:false,			
						success: function (data) {
							if (data == "0"){
								document.getElementById("voucher_hint").innerHTML="<font color='red'>Invalid voucher!</font>";
								document.getElementById("voucher_hint").value = "";
								document.getElementById("voucher_hint").focus();
							} else {
								if (parseInt(data) > total){
									document.getElementById("voucher_hint").innerHTML="<font color='red'>Cannot apply voucher larger than total amount</font>";
									document.getElementById("voucher_hint").value = "";
									document.getElementById("voucher_hint").focus();
								}else{
									document.getElementById("voucher_hint").innerHTML="<font color='green'>Success</font>";
									document.getElementById("voucher_code").disabled = true;
									document.getElementById("voucher_button").disabled = true;
									voucher_value = parseInt(data);
									cal_total();
								}
							}
						},
						error: function(data){  
							alert("error" + data);  
						}
					});	
				}
			}
		</script>
		<title>Select Payment</title>
	</head>
	
	<body>
		<div id="addp">
			<fieldset>
				<legend>Payment</legend>
				<p id="total"></p>
				<p>Voucher Code:</p>
				<input type="text" id="voucher_code"/>
				<span id="voucher_hint"></span><br>
				<button id="voucher_button" onclick="voucher();">Apply</button>
				<form id="pay-select" method="POST" action="" onsubmit="return my_submit();">
					
					<input type="radio" name="pay_method" value="paypal">
					<img src="/images/paypal.jpg"  alt="PayPal Image" /><br>
					<input type="radio" name="pay_method" value="alipay"/>
					<img src="/images/alipay.jpg"  alt="Alipay Image" />
					<br><br>
					<input type="submit" value="Pay"/>
				</form>
				
				
				<form id="hidden_alipay" name="alipayment" action="/alipay/pagepay/pagepay.php" method="POST">
					<input type="hidden" id="WIDout_trade_no" name="WIDout_trade_no" />
					<input type="hidden" id="WIDsubject" name="WIDsubject" />
					<input type="hidden" id="WIDtotal_amount" name="WIDtotal_amount" />
					<input type="hidden" id="WIDbody" name="WIDbody" />
				</form>
				
				<form id="hidden_paypal" method="POST" action="https://www.sandbox.paypal.com/cgi-bin/webscr.php" target="_blank" onsubmit="my_submit()">
					<input type="hidden" name="cmd" value="_cart"/>
					<input type="hidden" name="upload" value="1"/>
					<input type="hidden" name="business" value="kingyufly-facilitator@outlook.com"/>
					<input type="hidden" name="currency_code" value="HKD"/>
					<input type="hidden" name="custom" value="0"/>
					<input type="hidden" name="invoice" value="0"/>
				</form>
				<button onclick="back();">Back</button>
			</fieldset>
		</div>
	</body>
	<script type="text/javascript">
		cal_total();
	</script>
</html>
