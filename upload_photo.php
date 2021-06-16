<?php

require_once("usesession.php");
require_once "../../../conf.php";
require_once("fnc_photo.php");
require_once "classes/Upload_photo.class.php";


$photo_upload_error = null;
$image_file_name = null;
$file_name_prefix = "vr_";
$file_size_limit = 1 * 1024 * 1024;
$image_max_w = 600;
$image_max_h = 400;
$image_thumbnail_size = 100;
$success = false;
$alt_text = null;
$watermark = "../images/vr_watermark.png";

if (isset($_POST["photo_submit"])) {
	//var_dump($_POST);
	//var_dump($_FILES);


	//Võtame kasutusele Upload_photo klassi
	$photo_upload = new Upload_photo($_FILES["file_input"], $file_size_limit);

	$imgPath = $_FILES["file_input"]["tmp_name"]; //ajutine failinimi
	//loome oma failinime
	$timestamp = microtime(1) * 10000;
	// faili nime loomine
	$image_file_name = $photo_upload->generate_name($file_name_prefix, $timestamp);
	// Kui erroreid ei ole
	if(empty($photo_upload->upload_error)) { // klassi muutuja

		$photo_upload->resize_photo($image_max_w, $image_max_h);

		// lisan vesimärgi..
		$photo_upload->add_watermark($watermark);
		// salvestame pikslikogumi faili
		$target_file = "../upload_photos_normal/" .$image_file_name;
		$result = $photo_upload->save_image_to_file($target_file);
	
		// teen pisipildi
		$photo_upload->resize_photo($image_thumbnail_size, $image_thumbnail_size, false);
		$target_file = "../upload_photos_thumbnail/" .$image_file_name;
		$result = $photo_upload->save_image_to_file($target_file);

		$target_file = "../upload_photos_orig/" .$image_file_name;
		$photo_upload->save_original($target_file);

		unset($photo_upload);

		// kui kõik edukas olnud, siis lükkame andmebaasi
		if(empty($photo_upload->upload_error)) {
			store_photo($_SESSION["user_id"], $image_file_name, $_POST["alt_text"], $_POST["privacy_input"]);
		}
	}

}


?>

<!DOCTYPE html>
<html lang="et">

<head>
	<meta charset="utf-8">
	<title>Veebirakendused ja nende loomine 2021</title>
	<script src="javascript/checkImageSize.js"></script>

</head>

<body>
	<h1>
		Fotode üleslaadimine
	</h1>
	<p>See leht on valminud õppetöö raames!</p>
	<hr>
	<p><a href="?logout=1">Logi välja</a></p>
	<p><a href="home.php">Avalehele</a></p>
	<hr>
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
		<label for="file_input">Vali foto fail!</label>
		<input id="file_input" name="file_input" type="file">
		<br>
		<label for="alt_input">Alternatiivtekst ehk pildi selgitus</label>
		<input id="alt_text" name="alt_text" type="text" placeholder="Pildil on ...">
		<br>
		<label>Privaatsustase: </label>
		<br>
		<label for="privacy_input_1">Privaatne</label>
		<input id="privacy_input_1" name="privacy_input" type="radio" value="3" checked>
		<br>
		<label for="privacy_input_2">Registreeritud kasutajatele</label>
		<input id="privacy_input_2" name="privacy_input" type="radio" value="2">
		<br>
		<label for="privacy_input_3">Avalik</label>
		<input id="privacy_input_3" name="privacy_input" type="radio" value="1">
		<br>
		<input type="submit" id="photo_submit" name="photo_submit" value="Lae pilt üles!">
	</form>
	<p id="notice"><?php echo $photo_upload_error; ?></p>
</body>

</html>