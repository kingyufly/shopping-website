<?php
	require_once("config.php");
	require_once 'pagepay/service/AlipayTradeService.php';


	$arr=$_GET;
	$alipaySevice = new AlipayTradeService($config); 
	$result = $alipaySevice->check($arr);

	/* 实际验证过程建议商户添加以下校验。
	1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
	2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
	3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
	4、验证app_id是否为该商户本身。
	*/
	if($result) {//验证成功
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//请在这里加上商户的业务逻辑程序代码
		
		//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
		//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

		//商户订单号
		$out_trade_no = htmlspecialchars($_GET['out_trade_no']);

		//支付宝交易号
		$trade_no = htmlspecialchars($_GET['trade_no']);
		$app_id = htmlspecialchars($_GET['app_id']);
        $seller_id = htmlspecialchars($_GET['seller_id']);
		
		if($app_id != 2016091900550464 || $seller_id != 2088102176386745)
			//验证失败
			echo "Auth falied";

		$db = new PDO('sqlite:/var/www/orders.db');
		$db->query("PRAGMA foreign_keys = ON;");
		$sql="UPDATE orders SET txid = ? WHERE orderid = ?;";
		$q = $db->prepare($sql);
		
		$q->bindParam(1, $trade_no);
		$q->bindParam(2, $out_trade_no);
		$q->execute();
	}
	else {
		//验证失败
		echo "Auth falied";
	}
?>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Alipay Payment Success</title>
	</head>
    <body>
		<p> 
			Payment Success!<br>
			Order number: <?php echo $out_trade_no;?><br>
			transaction number: <?php echo $trade_no;?>
		</p>
		<span id="show"></span>
		<script>
			var storage = window.localStorage;
			var url = storage.getItem("url");
			storage.removeItem("url");
			storage.removeItem("shop_cart");
			
			var t = 3;
			setInterval("refer()",1000);
			function refer(){
				if(t == 0)
					location = url;
				document.getElementById('show').innerHTML = "Redirect to the original page in " + t + " seconds";
				t--;
			}
		</script>
    </body>
</html>
