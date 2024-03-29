<?php
	require_once "usesession.php";
	require_once "../../../conf.php";
	require_once "fnc_general.php";
	require_once "fnc_photo.php";
	require_once "classes/Upload_photo.class.php";
	$news_photo_result = 0;
	$news_input_error = null;
	$news_title = null;
	$news_content = null;
	$news_author = null;
	// uudise pildiga seotud muutujad:
	$file_size_limit = 1 * 1024 * 1024;
	$file_name_prefix = "vr_";
	$image_max_w = 600;
	$image_max_h = 400;
	$get_news_id = null; // kui ei ole uudise id-d, siis on ta lihtsalt "null", et vältida veateateid.

	if(isset($_GET["news_id"])){ // kui kindla uudise id on olemas, siis määrame selle globaalsele muutujale väärtuseks
		$get_news_id = $_GET["news_id"];
		$_SESSION['news_id'] = $get_news_id; // hoiustan uudise id sessioonimuutujasse, muidu kaob ära, kui POST['news_submit'] toimub

	}
	
	$news_content_html = get_news_content($_SESSION['news_id']);




	if(isset($_POST["news_submit"])){
		if(empty($_POST["news_title_input"])){
			$news_input_error = "Uudise pealkiri on puudu! ";
		} else {
			$news_title = test_input($_POST["news_title_input"]);
		}
		if(empty($_POST["news_content_input"])){
			$news_input_error .= "Uudise tekst on puudu!";
		} else {
			$news_content = test_input($_POST["news_content_input"]);
		}
		if(!empty($_POST["news_author_input"])){
			$news_author = test_input($_POST["news_author_input"]);
		}
		
		if(empty($news_input_error)){
			if($_FILES["file_input"]['size'] > 0) { // kui pildifail on valitud
				$timestamp = microtime(1) * 10000;
				$photo_upload = new Upload_photo($_FILES["file_input"], $file_size_limit);
				$image_file_name = $photo_upload->generate_name($file_name_prefix, $timestamp);
				$target_file = "../upload_photos_news/" .$image_file_name;
				if(empty($photo_upload->upload_error)) {
					$photo_upload->resize_photo($image_max_w, $image_max_h);			
					$result = $photo_upload->save_image_to_file($target_file);
					echo $photo_upload->upload_error;
					//salvestame andmebaasi pildi
					$news_photo_result = store_news_photo($_SESSION["user_id"], $image_file_name, $_POST["alt_text"]);
				}
			}
			// uuendame uudise andmebaasi kirjet
			update_news($news_title, $news_content, $news_author, $news_photo_result, $_SESSION['news_id']);
			// Kui uudis on uuendatud, siis suuname tagasi muudetava uudise valimise lehele
			header('Location: news_to_edit.php');
		}
	}

	function update_news($news_title, $news_content, $news_author, $photo_id, $news_id){
		$conn = new mysqli($GLOBALS["server_host"], $GLOBALS["server_user_name"], $GLOBALS["server_password"], $GLOBALS["database"]);
		$conn -> set_charset("utf8");
		$stmt = $conn -> prepare("UPDATE vr21_news SET vr21_news_photo_id=?, vr21_news_news_title=?, vr21_news_news_content=?, vr21_news_news_author=? WHERE vr21_news_id=?");
		echo $conn -> error;
		//i - integer   s - string   d - decimal
		$stmt -> bind_param("isssi", $photo_id, $news_title, $news_content, $news_author, $news_id);
		$stmt -> execute();
		$stmt -> close();
		$conn -> close();
		$GLOBALS["news_input_error"] = null;
		$GLOBALS["news_title"] = null;
		$GLOBALS["news_content"] = null;
		$GLOBALS["news_author"] = null;
	}

	//	Funktsioon valitud uudise sisu lugemiseks andmebaasist
	function get_news_content($news_id) { 
		$photo_folder = "../upload_photos_news/";
		$conn = new mysqli($GLOBALS["server_host"], $GLOBALS["server_user_name"], $GLOBALS["server_password"], $GLOBALS["database"]);
		$conn -> set_charset("utf8");
		$stmt = $conn -> prepare("SELECT vr21_news_news_title, vr21_news_news_content, vr21_news_news_author, vr21_news_added, vr21_news_photo.photo_name, vr21_news_photo.photo_alt_text FROM vr21_news LEFT JOIN vr21_news_photo ON vr21_news.vr21_news_photo_id = vr21_news_photo.photo_id WHERE vr21_news_id = ?");
		echo $conn -> error;
		$stmt -> bind_result($news_title_from_db, $news_content_from_db, $news_author_from_db, $news_added_from_db, $news_photo_name_from_db, $news_photo_alttext_from_db);
		$stmt -> bind_param("i", $news_id);
		$stmt -> execute();
		$raw_news_html = null;
		while ($stmt -> fetch()) {

			$raw_news_html .= "\n <label for='news_title_input'>Uudise pealkiri</label>";
		 	$raw_news_html .= "\n <br> <input type='text' id='news_title_input' name='news_title_input' placeholder='Pealkiri' value='" .$news_title_from_db ."'>";		
			$raw_news_html .= "\n <br> <label for='news_content_input'>Uudise tekst</label>";
			$raw_news_html .= "\n <br> <textarea id='news_content_input' name='news_content_input' placeholder='Uudise tekst' rows='6' cols='40'>" .$news_content_from_db ."</textarea>";
			$raw_news_html .= "\n <br> <label for='news_author_input'>Uudise lisaja nimi</label>";
			$raw_news_html .= "\n <br> <input type='text' id='news_author_input' name='news_author_input' placeholder='Nimi' value='" .$news_author_from_db ."'>";
			$raw_news_html .= "\n <br> <label for='alt_input'>Alternatiivtekst ehk pildi selgitus</label>";
			$raw_news_html .= "\n <input id='alt_text' name='alt_text' type='text' placeholder='Pildil on ...' value='" .$news_photo_alttext_from_db ."'>";
			// Kontroll, kas uudisel on ka pilt
			if(!empty($news_photo_name_from_db)) {
					// Kui on pilt, siis näitame seda ka
					$raw_news_html .= "\n <br> <label for='file_input'>Uudise pildina hetkel kasutusel: " .$news_photo_name_from_db;
					$raw_news_html .= "\n <br> <img src=" .$photo_folder .$news_photo_name_from_db ." alt=" .$news_photo_alttext_from_db ."width='100' height='100'" .">";
					$raw_news_html .= "\n <br> Muuda uudise pilti: </label>";
				}
			else { // kui pole pilti, siis väljastame vastava sõnumi
				$raw_news_html .= "\n <br> <label for='file_input'>Uudisel hetkel pilt puudub. <br> Lisa uudisele pilt: </label>";
			}

		}
		
		$stmt->close();
		$conn->close();

		return $raw_news_html; // tagastame funktsioonist html-i
	}
	


?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
	<title>Veebirakendused ja nende loomine 2021</title>
</head>
<body>
	<h1>Uudiste lisamine</h1>
	<p>See leht on valminud õppetöö raames!</p>
	<hr>
	<p><a href="?logout=1">Logi välja</a></p>
	<p><a href="home.php">Avalehele</a></p>
	<hr>
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" enctype="multipart/form-data">
    <p><?php echo $news_content_html; ?></p>
		<input id="file_input" name="file_input" type="file">
		<br>
		<br>
		<input type="submit" name="news_submit" value="Muuda uudist!">
	</form>
	<p><?php echo $news_input_error; ?></p>
</body>
</html>