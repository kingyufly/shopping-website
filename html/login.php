<?php
require __DIR__.'/lib/db.inc.php';
?>



<html>
	<head>
		<style type="text/css">
			#addp, #addc, #editp, #editc {margin: 10% 10% 10% 10%}
		</style>
		<title>Login</title>
	</head>
	
	<body>
		<div id="myTabContent" class="tab-content">
			<div class="tab-pane fade in active" id="addp">
			   <fieldset>
					<legend> Log in</legend>
					<form id="addp_prod_insert" method="POST" action="auth-process.php?action=login">
						<label for="email"> Email *</label>
						<div> <input id="email" type="email" name="email" required="required"/></div>
						<label for="password"> Password *</label>
						<div> <input id="password" type="password" name="password" required="required"/></div>
						<br>
						<input type="submit" value="Login"/>
					</form>
					<p>donot have a account? try to <a href="signup.php">sign up</a> first</p>
					<p>forget your password? try <a href="recoverpass.php">recover your password</a> here</p>
					<p>want to use the website as a guest? <a href="index.php">click here to the main page</a></p>
				</fieldset>
			</div>
		</div>
	</body>
</html>



