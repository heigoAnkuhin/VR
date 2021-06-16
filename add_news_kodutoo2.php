<?php
		
require_once "../../../conf.php";
$news_input_error = null;		
//var_dump($_POST); //on olemas ka $_GET
$titleSave = null; // pealkirja meeldejätmiseks - väärtus vaikimisi
$contentSave = null; // sisu meeldejätmiseks - väärtus vaikimisi
$authorSave = null; // autori meeldejätmiseks - väärtus vaikimisi
if(isset($_POST["news_submit"])) {
	if(empty($_POST["news_title_input"])) {
		$news_input_error = "Uudise pealkiri on puudu! ";
		$contentSave = (isset($_POST['news_content_input']) ? $_POST['news_content_input'] : ''); // väärtus, mis salvestatakse pealkirja meeldejätmiseks
		$authorSave = (isset($_POST['news_author_input']) ? $_POST['news_author_input'] : ''); // väärtus, mis salvestatakse autori meeldejätmiseks
	}
	if(empty($_POST["news_content_input"])) {
	$news_input_error .= "Uudise tekst on puudu!";
	$titleSave = (isset($_POST['news_title_input']) ? $_POST['news_title_input'] : ''); // väärtus, mis salvestatakse sisu meeldejätmiseks
	$authorSave = (isset($_POST['news_author_input']) ? $_POST['news_author_input'] : ''); // väärtus, mis salvestatakse autori meeldejätmiseks
	}
	if(empty($news_input_error)) {
		// valideerime sisendandmed
		$news_title_input = test_input($_POST["news_title_input"]);
		$news_content_input = test_input($_POST["news_content_input"]);
		$news_author_input = test_input($_POST["news_author_input"]);
		// salvestame andmebaasi
		store_news("news_title_input", "news_content_input", "news_author_input");
	}
}
function store_news() {
	//echo $news_title, $news_content, $news_author;
	//echo $GLOBALS["server_host"];
	// Loome andmebaasiserveriga ja baasiga ühenduse..
	$conn = new mysqli($GLOBALS["server_host"], $GLOBALS["server_user_name"],$GLOBALS["server_password"], $GLOBALS["database"]);
	// Valmistan ette SQL käsu..
	$stmt = $conn -> prepare("INSERT INTO vr21_news (vr21_news_news_title, vr21_news_news_content, vr21_news_news_author) VALUES (?,?,?)");
	echo $conn -> error;
	// i - integer, s - string, d - decimal
	$stmt -> bind_param("sss", $news_title, $news_content, $news_author);
	$stmt -> execute();
	$stmt -> close();
	$conn -> close();
}

function test_input($input) { // sisendandmete valideerimise funktsioon
  $data = trim($input);
  $data = stripslashes($input);
  $data = htmlspecialchars($input);
  return $input;
}
	
?>

<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
	<title>Veebirakendused ja nende loomine 2021</title>
</head>
<body>
	<h1>
		Uudiste lisamine
	</h1>
	<p>See leht on valminud õppetöö raames!</p>
	<hr>
	<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
		<label for="news_title_input">Uudise pealkiri</label>
		<br>
		<input type="text" id="news_title_input" name="news_title_input" placeholder="Pealkiri" value="<?php echo $titleSave; ?>">
		<br>
		<label for="news_content_input">Uudise tekst</label>
		<br>
		<textarea id="news_content_input" name="news_content input" placeholder="Uudise tekst" rows="6" cols="40"><?php echo $contentSave; ?></textarea>
		<br>
		<label for="news_author_input">Uudise autor</label>
		<br>
		<input type="text" id="news_author_input" name="news_author_input" placeholder="Autor" value="<?php echo $authorSave; ?>">
		<br>
		<input type="submit" name="news_submit" value="Salvesta uudis!">
	</form>
	<p><?php echo $news_input_error; ?></p>
</body>
</html>