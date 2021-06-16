<?php
	require_once("usesession.php");

/*  	session_start();
	// kas on sisse loginud
	if(!isset($_SESSION["user_id"])) {
	 header("Location: page.php");
	 exit();
	}
	// välja logimine
	if(isset($_GET["logout"])) {
		session_destroy();
		header("Location: page.php");
		exit();
	}  */
	
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
	<title>Veebirakendused ja nende loomine 2021</title>
</head>
<body>
	<h1><?php echo "Tere, " .$_SESSION["user_first_name"] ." " .$_SESSION["user_last_name"] ?></h1>
	<p>See leht on valminud õppetöö raames!</p>
	<hr>
	<ul>
	<li><p><a href="?logout=1">Logi välja</a></p></li>
	<li><p><a href="add_news.php">Uudiste lisamine</a></p></li>
	<li><p><a href="news_to_edit.php">Uudiste muutmine</a></p></li>
	<li><p><a href="show_news.php">Uudiste lugemine</a></p></li>
	<li><p><a href="upload_photo.php">Pildi üleslaadimine</a></p></li>
	<li><p><a href="show_photos.php">Piltide galerii</a></p></li>

	</ul>
</body>
</html>