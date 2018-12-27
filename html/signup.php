<?php
require __DIR__.'/lib/db.inc.php';
?>

<html>
	<head>
		<style type="text/css">
			#addp, #addc, #editp, #editc {margin: 10% 10% 10% 10%}
		</style>
		<title>Sign up</title>
	</head>
	
	<body>
		<div class="tab-pane fade in active" id="addp">
			<fieldset>
				<legend> New User</legend>
				<form id="addp_prod_insert" method="POST" action="auth-process.php?action=signup">
					<label for="email"> Email *</label>
					<div> <input id="email" type="email" name="email" required="required"/></div>
					<label for="password"> Password *</label>
					<div> <input id="password" type="password" name="password" required="required"/></div>
					<label for="password_re"> Confirm Password *</label>
					<div> <input id="password_re" type="password" name="password_re" required="required" onkeyup="validate()"/>
					<span id="hint"></span>
					</div>

					<input id="submit" type="submit" value="Sign up"/>
					<p>have an account already? here to <a href="login.php">log in</a></p>
				</form>
			</fieldset>
		</div>
	</body>
	<script>
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