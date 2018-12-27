<?php
require __DIR__.'/lib/db.inc.php';
?>



<html>
	<head>
		<style type="text/css">
			#addp, #addc, #editp, #editc {margin: 10% 10% 10% 10%}
		</style>
		<title>Recover</title>
		<script src="//cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
	</head>
	
	<body>
		<div id="myTabContent" class="tab-content">
			<div class="tab-pane fade in active" id="addp">
			   <fieldset>
					<legend> Recover Password</legend>
					<form id="addp_prod_insert" method="POST" onsubmit="return checkEmail();" action="auth-process.php?action=recoverpass">
						<label for="email"> Email *</label>
						<div> <input id="email" type="email" name="email" required="required"/></div>
						<span id="hint"></span><br>
						<input type="submit" value="Recover"/>
					</form>
					<div id="success_hint">
					</div>
				</fieldset>
			</div>
		</div>
	</body>
	<script>
		var t = 3;
		function checkEmail(){
			var email = document.getElementById("email").value;
			$.ajax({
				type: "post",
				data:  "email=" + email,
				dataType: "text",
				url: "auth-process.php?action=checkemail",
				async:false,			
				success: function (data) {
					if (data == "0"){
						document.getElementById("hint").innerHTML="<font color='red'>No such email</font>";
					} else {
						sendemail(email);
					}
				}  
			});	
			return false;
		}
		
		function sendemail(email){
			$.ajax({
				type: "post",
				data:  "email=" + email,
				dataType: "text",
				url: "auth-process.php?action=recoverpass",
				async:false,			
				success: function (data) {
					alert(data);
					if (data == "0"){
						alert("System error! Please try ")
					} else {
						document.getElementById("addp_prod_insert").value="";
						document.getElementById("success_hint").value="success,jump to loog in";
						setInterval("refer()",1000);
					}
				}  
			});	
		}
		
		function refer(){
			if(t == 0){
				t = 3;
				location = "login.php";
			}
			document.getElementById('success_hint').innerHTML = "Send email success! Redirect to the login page in " + t + " seconds";
			t--;
		}
	</script>
</html>
