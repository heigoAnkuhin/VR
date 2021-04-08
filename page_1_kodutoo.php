<?php
	$myname = "Heigo Ankuhin";
	$currenttime = date("d.m.Y H:i:s");
	$semesterbegin = new DateTime("2021-1-25");
	$semesterend = new DateTime("2021-6-30");
	$semesterduration = $semesterbegin->diff($semesterend);
	$semesterdurationdays = $semesterduration->format("%r%a");
	$semesterdurhtml = "\n <p> 2021 kevadsemestri kestus on " .$semesterdurationdays ." päeva.</p>";
	$today = new DateTime("now");
	$currentday = $today->format("N"); /* Tänasest kuupäevast nopin välja nädalapäeva numbrina */
	$days = ["Esmaspäev", "Teisipäev", "Kolmapäev", "Neljapäev", "Reede", "Laupäev", "Pühapäev"]; /* Massiiv eestikeelseteks nädalanimedeks */
	$dayofweek = $days[$currentday-1]; /* Õige nädalapäeva välja noppimine eestikeelsest massiivist */
	$timehtml = "\n <p>Lehe avamise hetkel oli: " .$dayofweek .", " .$currenttime .".</p>";
	$fromsemesterbegin = $semesterbegin->diff($today);
	$fromsemesterbegindays = $fromsemesterbegin->format("%r%a");
	$semesterprogress = "\n" .'<p>Semester edeneb: <meter min="0" max="' .$semesterdurationdays .'" value="' .$fromsemesterbegindays .'"></meter>.</p>' ."\n";

	if ($fromsemesterbegindays < 0) { /* Kõigepealt kontrollime kas semester on üldse alanud - kui on, siis kukume otse elseif-i, kui ei, siis väljastame vastava teksti */
		$semesterprogress = "\n <p>Semester pole veel alanud.</p> \n";
	}
	elseif ($fromsemesterbegindays <= $semesterdurationdays) {
		$semesterprogress = "\n" .'<p>Semester edeneb: <meter min="0" max="' .$semesterdurationdays .'" value="' .$fromsemesterbegindays .'"></meter>.</p>' ."\n";	
	}
	else {
		$semesterprogress = "\n <p>Semester on lõppenud.</p> \n";
	}
	
	//loeme piltide kataloogi sisu
	$picsdir = "../pics/";
	$allfiles = array_slice(scandir($picsdir), 2);
	//echo $allfiles[5];
	$allowedphototypes = ["image/jpeg", "image/png"];
	$picfiles = [];
	$photonums = [];
	
	foreach($allfiles as $file) {
			$fileinfo = getimagesize($picsdir .$file);
			//var_dump($fileinfo);
			if(isset($fileinfo["mime"])) {
				if(in_array($fileinfo["mime"], $allowedphototypes)) {
					array_push($picfiles, $file);
				}
			}
	}
	
	$photocount = count($picfiles);
	
	/* While-tsükkel kordumatute piltide väljanoppimiseks */
	do {
		$photonum = mt_rand(0, $photocount-1); /* Valime suvalise pildi numbri */
		if(!(in_array($photonum, $photonums))) { /* Kui pole juba massiivis, siis sobib ja jätkame - kui aga juba on, siis hakkab tsükkel otsast peale ning valitakse uus pilt ja kontrollitakse uuesti */
			array_push($photonums, $photonum); /* Lisame pildi numbri massiivi */
		}
	} while (count($photonums) < 3); /* Tsükkel käib niikaua kuni massiivi on lisatud 3 väärtust */
	
	$randomphoto = $picfiles[$photonums[0]];
	$randomphoto2 = $picfiles[$photonums[1]];
	$randomphoto3 = $picfiles[$photonums[2]];	
?>

<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
	<title>Veebirakendused ja nende loomine 2021</title>
</head>
<body>
	<h1>
	<?php
		echo $myname;
	?>
	</h1>
	<p>See leht on valminud õppetöö raames!</p>
	<?php
		echo $timehtml;
		echo $semesterdurhtml;
		echo $semesterprogress;
	?>
	<img src="<?php echo $picsdir .$randomphoto; ?>" alt="vaade Haapsalus">
	<img src="<?php echo $picsdir .$randomphoto2; ?>" alt="vaade Haapsalus 2">
	<img src="<?php echo $picsdir .$randomphoto3; ?>" alt="vaade Haapsalus 3">

	<!-- ../pics/IMG_0177.JPG" alt="vaade Haapsalus"> -->
</body>
</html>