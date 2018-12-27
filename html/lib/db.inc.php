<?php
function ierg4210_DB() {
	// connect to the database
	// TODO: change the following path if needed
	// Warning: NEVER put your db in a publicly accessible location
	$db = new PDO('sqlite:/var/www/cart.db');
	
	// enable foreign key support
	$db->query('PRAGMA foreign_keys = ON;');

	// FETCH_ASSOC: 
	// Specifies that the fetch method shall return each row as an
	// array indexed by column name as returned in the corresponding
	// result set. If the result set contains multiple columns with
	// the same name, PDO::FETCH_ASSOC returns only a single value
	// per column name. 
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	return $db;
}


function ierg4210_adminchangepwd(){
	$email = $_POST["email"];
	$password = $_POST["password"];
	ierg4210_user_update_pwd($email, $password);
	
	$db = new PDO('sqlite:/var/www/recover.db');
	$db->query("PRAGMA foreign_keys = ON;");
		
	$sql="delete from nonce where email=?;";
	$q = $db->prepare($sql);
	$q->bindParam(1, $email);
	$q->execute();

	header('Location: admin_easy.php');
    exit();
}

function ierg4210_user_update_pwd($email, $password){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();
	
	$salt = mt_rand();
	$password = hash_hmac('sha256', $password, $salt);
	$sql="UPDATE users SET salt=?, password=? WHERE email=?;";
    $q = $db->prepare($sql);
    
    $q->bindParam(1, $salt);
	$q->bindParam(2, $password);
	$q->bindParam(3, $email);
	$q->execute();
}

function ierg4210_user_fetchOne_byemail($email){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM users where email=?;");
	$q->bindParam(1, $email);
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_user_insert(){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();

    // TODO: complete the rest of the INSERT command
    if (!preg_match("/^[\w\-\/][\w\'\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST["email"])
		|| !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST["password"]))
        throw new Exception("invalid-email or password");

    $email = $_POST["email"];
	$salt = mt_rand();
	$password = hash_hmac('sha256', $_POST["password"], $salt);
	$sql="INSERT INTO users (userid, email, salt, password, flag) VALUES (null, ?, ?, ?, 0);";
    $q = $db->prepare($sql);
    
    $q->bindParam(1, $email);
	$q->bindParam(2, $salt);
	$q->bindParam(3, $password);
	$q->execute();

    header('Location: login.php');
    exit();
}

function ierg4210_cat_fetchall() {
    // DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM categories LIMIT 100;");
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchAll(){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();
	$catid = $_POST["catid"];
    $q = $db->prepare("SELECT * FROM products where catid=? LIMIT 100;");
	$q->bindParam(1, $catid);
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchAll_byCat($catid){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM products where catid=? LIMIT 100;");
	$q->bindParam(1, $catid);
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchOne(){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();
	$pid = $_POST["pid"];
    $q = $db->prepare("SELECT * FROM products where pid=?;");
	$q->bindParam(1, $pid);
    if ($q->execute())
        return $q->fetchAll();
}

function ierg4210_prod_fetchOne_byID($pid){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();
    $q = $db->prepare("SELECT * FROM products where pid=?;");
	$q->bindParam(1, $pid);
    if ($q->execute())
        return $q->fetchAll();
}

function _mime_content_type($filename) {
	$fi = new finfo(FILEINFO_MIME_TYPE);
	$mime_type = $fi->file($filename);
	echo $mime_type; // image/jpeg
	return $mime_type;
}

function changeSize($str_file)
{
    $size=getimagesize($str_file);
	$src=imagecreatefromjpeg($str_file);

    $w = $size['0'];
    $h = $size['1'];

    $max = 100;

    if ($w > $h) {
        $w = $max;
        $h = $h * ($max / $size['0']);
    } else {
        $h = $max;
        $w = $w * ($max / $size['1']);
    }

    $image = imagecreatetruecolor($w, $h);

    imagecopyresampled($image, $src, 0, 0, 0, 0, $w, $h, $size['0'], $size['1']);

	$str_file = str_replace("origin", "thumbnail", $str_file);

    $result = imagejpeg($image, $str_file);
    imagedestroy($image);

	return $result ;
}

// Since this form will take file upload, we use the tranditional (simpler) rather than AJAX form submission.
// Therefore, after handling the request (DB insert and file copy), this function then redirects back to admin.html
function ierg4210_prod_insert() {
    // input validation or sanitization

    // DB manipulation
    global $db;
    $db = ierg4210_DB();

    // TODO: complete the rest of the INSERT command
    if (!preg_match('/^\d*$/', $_POST['catid']))
        throw new Exception("invalid-catid");
    $_POST['catid'] = (int) $_POST['catid'];
    //if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
    //    throw new Exception("invalid-name");
    if (!preg_match('/^[\d\.]+$/', $_POST['price']))
        throw new Exception("invalid-price");
    //if (!preg_match('/^[\w\- ]+$/', $_POST['description']))
    //   throw new Exception("invalid-text");

    // Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg
    if ($_FILES["file"]["error"] == 0
        && $_FILES["file"]["type"] == "image/jpeg"
        && _mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg"
        && $_FILES["file"]["size"] < 5000000) {


        $catid = $_POST["catid"];
        $name = $_POST["name"];
        $price = $_POST["price"];
        $desc = $_POST["description"];
        $sql='INSERT INTO products VALUES (null, ?, ?, ?, ?);';
        $q = $db->prepare($sql);
        $q->bindParam(1, $catid);
        $q->bindParam(2, $name);
        $q->bindParam(3, $price);
        $q->bindParam(4, $desc);
		$q->execute();
        $lastId = $db->lastInsertId();

        // Note: Take care of the permission of destination folder (hints: current user is apache)
        if (move_uploaded_file($_FILES["file"]["tmp_name"], "images/origin/" . $lastId . ".jpg")) {
			changeSize("images/origin/" . $lastId . ".jpg");
            // redirect back to original page; you may comment it during debug
            header('Location: admin_easy.php');
            exit();
        }
    }
    // Only an invalid file will result in the execution below
    // To replace the content-type header which was json and output an error message
    header('Content-Type: text/html; charset=utf-8');
    if (_mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg"

        )
		echo 'true';
	else{
		echo _mime_content_type($_FILES["file"]["tmp_name"]);
		echo 'false';
	}
	echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
    exit();
}

function ierg4210_cat_insert(){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();

    // TODO: complete the rest of the INSERT command
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");

    $name = $_POST["name"];

	$sql="INSERT INTO categories (catid, name) VALUES (null, ?);";
    $q = $db->prepare($sql);
    
    $q->bindParam(1, $name);
	$q->execute();

    header('Location: admin_easy.php');
    exit();
}

function ierg4210_cat_delete(){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();

    $catid = $_POST["catid"];
	ierg4210_prod_delete_by_catid($catid);
	
	$sql="delete from categories where catid=?;";
    $q = $db->prepare($sql);
    
    $q->bindParam(1, $catid);
	$q->execute();
	echo $catid;

    header('Location: admin_easy.php');
    exit();
}

function ierg4210_prod_delete_by_catid($catid){
	global $db;
    $db = ierg4210_DB();

    $catid = $_POST["catid"];

	$q = $db->prepare("SELECT pid FROM products where catid=?;");
	$q->bindParam(1, $catid);
    if ($q->execute())
    $result = $q->fetchAll();
	
	foreach ($result as $item) {
		$file1 = "./images/origin/".$item["pid"] .".jpg";
		$file2 = "./images/thumbnail/".$item["pid"].".jpg";
		unlink($file1);
		unlink($file2);
	}
	
	$sql="delete from products where catid=?;";
    $q = $db->prepare($sql);
    
    $q->bindParam(1, $catid);
	$q->execute();
}

function ierg4210_prod_delete(){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();

    $pid = $_POST["pid"];

	$sql="delete from products where pid=?;";
    $q = $db->prepare($sql);
    
    $q->bindParam(1, $pid);
	$q->execute();
	
	$file1 = "./images/origin/" . $pid . ".jpg";
	$file2 = "./images/thumbnail/" . $pid . ".jpg";
	unlink($file1);
	unlink($file2);
    header('Location: admin_easy.php', true, 302);
    exit();
}

function ierg4210_cat_edit(){
	// DB manipulation
    global $db;
    $db = ierg4210_DB();

    // TODO: complete the rest of the INSERT command
    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
        throw new Exception("invalid-name");

	$catid = $_POST["catid"];
    $name = $_POST["name"];

	$sql="update categories set name=? where catid=?;";
    $q = $db->prepare($sql);
    
    $q->bindParam(1, $name);
	$q->bindParam(2, $catid);
	$q->execute();

    header('Location: admin_easy.php');
    exit();
}

function ierg4210_prod_edit(){
    // DB manipulation
    global $db;
    $db = ierg4210_DB();

    // TODO: complete the rest of the INSERT command
    if (!preg_match('/^\d*$/', $_POST['catid']))
        throw new Exception("invalid-catid");
    $_POST['catid'] = (int) $_POST['catid'];
    //if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
    //    throw new Exception("invalid-name");
    if (!preg_match('/^[\d\.]+$/', $_POST['price']))
        throw new Exception("invalid-price");
    //if (!preg_match('/^[\w\- ]+$/', $_POST['description']))
    //    throw new Exception("invalid-text");

    // Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg
    if ($_FILES["file"]["error"] == 0
        && $_FILES["file"]["type"] == "image/jpeg"
        && _mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg"
        && $_FILES["file"]["size"] < 5000000) {


        $catid = $_POST["catid"];
        $name = $_POST["name"];
        $price = $_POST["price"];
        $desc = $_POST["description"];
        $sql='INSERT INTO products VALUES (null, ?, ?, ?, ?);';
        $q = $db->prepare($sql);
        $q->bindParam(1, $catid);
        $q->bindParam(2, $name);
        $q->bindParam(3, $price);
        $q->bindParam(4, $desc);
		$q->execute();
        $lastId = $db->lastInsertId();

        // Note: Take care of the permission of destination folder (hints: current user is apache)
        if (move_uploaded_file($_FILES["file"]["tmp_name"], "images/origin/" . $lastId . ".jpg")) {
			changeSize("images/origin/" . $lastId . ".jpg");
            // redirect back to original page; you may comment it during debug
            header('Location: admin_easy.php');
            exit();
        }
    }
    // Only an invalid file will result in the execution below
    // To replace the content-type header which was json and output an error message
    header('Content-Type: text/html; charset=utf-8');
    if (_mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg"

        )
		echo 'true';
	else{
		echo _mime_content_type($_FILES["file"]["tmp_name"]);
		echo 'false';
	}
	echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
    exit();
}

