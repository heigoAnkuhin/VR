<?php
	require_once "../../../conf.php";
    require_once("fnc_photo.php");
	
        $photos_html = show_photos(1);

?>

<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
	<title>Veebirakendused ja nende loomine 2021</title>
    <link rel="stylesheet" href="stiil.css">
</head>
<body>
	<h1>Piltide galerii</h1>
	<p>See leht on valminud õppetöö raames!</p>
	<hr>
    <div class="wrapper">
        <p><?php echo $photos_html; ?></p>
    </div>
</body>
</html>