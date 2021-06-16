<?php

	function sign_up($name,$surname, $gender, $birth_date, $email, $password) {
		
		$notice = 0;
		$conn = new mysqli($GLOBALS["server_host"], $GLOBALS["server_user_name"],$GLOBALS["server_password"], $GLOBALS["database"]);
		// KONTROLL, KAS KASUTAJA JUBA OLEMAS \\
		$chk = $conn -> prepare("SELECT vr21_users_id FROM vr21_users WHERE vr21_users_email = ?");
		$chk -> bind_param("s", $email);
		$chk -> execute();
		if($chk -> fetch()) {
			$chk -> close();
			$conn -> close();
			$notice = 2;
		}
		else {
			$chk -> close();
		
			$stmt = $conn->prepare("INSERT INTO vr21_users (vr21_users_firstname, vr21_users_lastname, vr21_users_birthdate, vr21_users_gender, vr21_users_email, vr21_users_password) VALUES (?,?,?,?,?,?)");
			echo $conn->error;
			
			//krüpteerime parooli
			$options = ["cost" => 12, "salt" => substr(sha1(rand()), 0, 22)];
			$pwd_hash = password_hash($password, PASSWORD_BCRYPT, $options);
			
			$stmt -> bind_param("sssiss", $name, $surname, $birth_date, $gender, $email, $pwd_hash);
			
			if($stmt -> execute()) {
				$notice = 1;
			}
			$stmt -> close();
			$conn -> close();
		}
		return $notice;
		
		
	}
	
	function sign_in($email, $password) {
		$notice = 0;
		$conn = new mysqli($GLOBALS["server_host"], $GLOBALS["server_user_name"],$GLOBALS["server_password"], $GLOBALS["database"]);
		// KONTROLL, KAS PAROOL OLEMAS JA KLAPIB \\
		$pwchk = $conn -> prepare("SELECT vr21_users_password FROM vr21_users WHERE vr21_users_email = ?");
		$pwchk -> bind_param("s", $email);
		$pwchk -> bind_result($password_from_db);
		$pwchk -> execute();
		if($pwchk -> fetch()) {
			if(password_verify($password, $password_from_db)) {
				$notice = 0;
				$pwchk -> close();
				$stmt = $conn -> prepare("SELECT vr21_users_id, vr21_users_firstname, vr21_users_lastname FROM vr21_users WHERE vr21_users_email = ?");
				echo $conn -> error;
				$stmt -> bind_result($id_from_db, $first_name_from_db, $last_name_from_db);
				$stmt -> bind_param("s", $email);
				$stmt -> execute();
				while ($stmt -> fetch()) {
					$_SESSION["user_id"] = $id_from_db;
					$_SESSION["user_first_name"] = $first_name_from_db;
					$_SESSION["user_last_name"] = $last_name_from_db;
				}
				$stmt -> close();
				$conn -> close();
				header("Location: home.php");
				return $notice;
				exit();				
			}
			else {
				 $notice = 2;

			}
		}
		else {
			$notice = 2;
		}
		$pwchk -> close();
		$conn -> close();
		return $notice;	
	}
