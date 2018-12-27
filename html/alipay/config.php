<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016091900550464",

		//商户私钥
		'merchant_private_key' => "MIIEpAIBAAKCAQEAsR7eDenT+hzBlUgv1Jc6Ut1EAyvP3E9GtgT827/F0q9I3yHw1MXSHVRMRZTMnSiM+j72XLJZJVTEOIZu1XZAp3n47s011Lfo1IvMZNWawSkcyOqBpPC+AJXGq7kLsFcIvPPtOMzYBrqoDjX3g+YohAoYFoYTy6dwdPgUkc+MW+d9ILjDftvsmmJaddGK4chvneJvVqGDWpGkE/x/dbgn/0IYO5DuP5PomDMP77vExP47EH2XZru0HHFJ1x3bNysROW6K2D0rpDu7rBauMD4u3rMw95k5198aiu4hmd1WWE9EMQ2fFIZLXPUyn8VZ8UQc082HLbcoxMkauPBR06WS+wIDAQABAoIBABXoRXrfExL6f48hUJkw40vghksGHj2XqJ1W3JepjqRSfebrYchPd3+dL5njn7NIkrdZFku8233ckDCVoBvS9ohAc+PFigT3glrXt745FV0S4raPGt310OptnBcdWi7DdRc57Ht8CrQ6XhLz5dtwmk5KmVQf3U7xNN2i2Zh3XVr4gDbBqw6BiQBxrOETtAbv9ZrhnDgxsfGsLKc4d++sBY/T8xkzO2pEZU/xC3wK2MBqKCjFKsO4C5fnMpti/AsBsrjOXSfcdOL5H9iQMTDK+4HZzIiOiFp/mNnUZGYwf1jrxiO6BjjoARmw+OgwaPUZZGfDdHOY8FRgrlTXUZzE8FECgYEA2gyO00iYJJwGhGysMVJnQKGfFC/iV0dKUE3OBdO2Hh7YptRSHHWxinnitlEiixP1sLcX2yFio68478fKgvfycTTA5+OK7f1KcAHm/Jrk4kjMTZtA5lw+4Zq5P3eO8w9TCdb607ZTvMcFPHl1UJL0T70NLmyG8E4HdlsH9V1x+O0CgYEAz/Kw7HrXWILqq1BP8zc0UhPZBI+ZpiBZggdUls9MFwTvgktxdPRmI55lt6KRPeI1bmbfVs2Qq14JN009AMQwCyB/t9sQdN/qUl60iI83bywwVay3zMlhlc5gBDlE7ZFFhtutzFlj0vARm8ot5G75yeTriyAkSPiWerG8ctGUxocCgYEAhh6JT25snsAVxiht9dyAxCFlju4xI4wnKqPg4Haro3VHv74DT5wY+1sjVw8q1y8MxYipNNEhMhtaQyq55rsKNhXDvh6Y2vAcdC7HXIMN3B35BJdFYSxGRVB9N4ubsuevJPNFzylr8kbccqkmvsvVWKKpU+/PREpKjsNJbuPucbUCgYEAxpWjtBITuOk9JeEKmN/tTTy7EQn515YpJG3PsD3DEnCaMbnOXbxQFk/cH6RWQ9zU3dvMCV9CewTQlElkqmiw4M9maLQBYTu80HC0w3zRmY9/kTHiTOU8Fg4Bz8bJNn53ATSlIvNyyyyMBFTtwqrgTJgbAL/vJuiO/BjkOFpBU90CgYANSWNAQ+Hbr4Nx+841vBeGERAWY3dyWNbxhz6soXPayjfys2oQS/DF/du7FOYVdWeBwkX7MqO0CRCFZ6XG9DMnQgjrHptuROFnqMhFJUh4oiz8Txul8kkyGi53Gy25SfPHTyCeYi5goJxMF/OFJgF6KTaY3tRL2+LBnvKEeFfDZg==",
		
		//异步通知地址
		'notify_url' => "https://secure.s75.ierg4210.ie.uchk.edu.hk/alipay/notify_url.php",
		
		//同步跳转
		'return_url' => "https://secure.s75.ierg4210.ie.cuhk.edu.hk/alipay/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA6h/C3eRm9VpDA7XyBPXEY8ZeGgh8mrPwDB/v5bI76SuI5j1CKU9DOAh514Y6xPvkUsLn1SkxgR8jRxTyQJKjPl90HTMDnaYFpThiUDzUlio42RtsqkN40YtGKfElAAgBMc4WuxdyWoZ1nuIPWjJVq8XRDp+XqwkxwgkGeOMTMiDyBg0yHjDniQNx6bnpy1qVXO1hJMaAHx/DMeGVdq0N5S05AGRDNXVp8u4xXcDzACV0Q/sSxqEzrEjU8Nfp4kXXzmkOVt2SrqCENCZFnnejiOIq45VoKpY+7C7JPpIQ1cokWkGXSaNm+3WwdyOIV9bODh3uajPBI+DqngkagQe9MQIDAQAB",
);
