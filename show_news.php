<?php
	require_once("usesession.php");
	require_once "../../../conf.php";
		
	function read_news() {
		$photo_folder = "../upload_photos_news/";
		if(isset($_POST["count_submit"])) { // kui kasutaja on valinud uudiste arvu, mida kuvada soovib
		$newsCount = $_POST['newsCount']; // kuvatavate uudiste arv sisendist
		}
		else { // kui kasutaja pole uudiste arvu valinud
			$newsCount = 3; // kuvatavate uudiste arv vaikimisi
		}
		//echo $news_title .$news_content .$news_author;
		//echo $GLOBALS["server_host"];
		//loome andmebaasis serveriga ja baasiga ühenduse
		$conn = new mysqli($GLOBALS["server_host"], $GLOBALS["server_user_name"], $GLOBALS["server_password"], $GLOBALS["database"]);
		//määrame suhtluseks kodeeringu
		$conn -> set_charset("utf8");
		//valmistan ette SQL käsu
		$stmt = $conn -> prepare("SELECT vr21_news_photo_id, vr21_news_news_title, vr21_news_news_content, vr21_news_news_author, vr21_news_added, vr21_news_photo.photo_name, vr21_news_photo.photo_alt_text FROM vr21_news LEFT JOIN vr21_news_photo ON vr21_news.vr21_news_photo_id = vr21_news_photo.photo_id ORDER BY vr21_news_id DESC LIMIT ?");
		echo $conn -> error;
		//i - integer   s - string   d - decimal
		$stmt -> bind_result($news_photo_id_from_db, $news_title_from_db, $news_content_from_db, $news_author_from_db, $news_date_from_db, $news_photo_name_from_db, $news_photo_alttext_from_db);
		$stmt -> bind_param("s", $newsCount); // edastame uudiste arvu SQL-käsule
		$stmt -> execute();
		$raw_news_html = null;
		while ($stmt -> fetch()) {
			$raw_news_html .= "\n <h2>" .$news_title_from_db ."</h2>";
			$newsDate = new DateTime($news_date_from_db); // teen andmebaasist võetud kuupäevast dateTime objekti
			$newsDate = $newsDate->format('d.m.Y'); // Teisendan dateTime objekti vajalikku formaati
			$raw_news_html .= "\n <p>Lisatud: " .$newsDate ."</p>"; // Väljastan kuupäeva
			$raw_news_html .= "\n <p>" .nl2br($news_content_from_db) ."</p>";
			$raw_news_html .= "\n <p>Edastas: ";
			if(!empty($news_author_from_db)) {
				$raw_news_html .= $news_author_from_db;
			}
			else {
				$raw_news_html .= "Tundmatu reporter";
			}
			$raw_news_html .= "</p>";
			// Kontroll, kas uudisel on ka pilt
			if($news_photo_id_from_db != 0) {
				// Kui on pilt, siis näitame seda ka
				$raw_news_html .= "\n <img src=" .$photo_folder .$news_photo_name_from_db ." alt=" .$news_photo_alttext_from_db ."width='100' height='100'" .">";
			}
		}
		$stmt -> close();
		$conn -> close();
		return $raw_news_html;
	}
	
	$news_html = read_news();
	
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
	<title>Veebirakendused ja nende loomine 2021</title>
</head>
<body>
	<h1>Uudiste lugemine</h1>
	<p>See leht on valminud õppetöö raames!</p>
	<hr>
	<p><a href="?logout=1">Logi välja</a></p>
	<p><a href="home.php">Avalehele</a></p>
	<hr>
	<form method="POST"> <!-- Vorm kuvatavate uudiste arvu määramiseks -->
	<input type="number" min="1" max="10" value="3" name="newsCount">
	<input type="submit" name="count_submit" value="Kuva uudised">
	</form>
	<p><?php echo $news_html; ?></p>
</body>
</html>