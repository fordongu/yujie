<?php 
	//echo 'hello world'; 

	include('phpqrcode.php');

	$data = 'http://www.baidu.com';

	$filename = 'web_url.png';
	
	$errorCorrectionLevel = 'L';

	$matrixPointSize = 4;
	
	QRcode::png($data,$filename,$errorCorrectionLevel,$matrixPointSize,2);

	QRcode::png($data);

?> 
