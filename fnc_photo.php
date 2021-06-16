<?php
    // FUNKTSIOON PILTIDE SUURUSE MUUTMISEKS \\
    function resize_image($image, $image_file_type, $w, $h, $toCrop) {
                    // suuruse muutmine
                    // loome pikslikogumi ehk image objekti
                    $temp_image = null;
                    if($image_file_type == "jpg") {
                        $temp_image = imagecreatefromjpeg($image);
                    }

                    if($image_file_type == "png") {
                        $temp_image = imagecreatefrompng($image);
                    }
                    // pildi originaalsed mõõtmed
                    $image_w = imagesx($temp_image);
                    $image_h = imagesy($temp_image);

                    $ratio = $image_w / $image_h;

                    if($toCrop) { // kui vaja kärpida
                        if ($image_w > $image_h) {
                            $image_w = ceil($image_w - ($image_w * abs($ratio - $image_w / $image_h)));
                        }
                        else {
                            $image_h = ceil($image_h - ($image_h * abs($ratio - $image_w / $image_h)));
                        }

                    $newW = $w;
                    $newH = $h;

                    }
                    else { // kui ei ole vaja kärpida
                        if ($w / $h > $ratio) {
                            $newW = $h * $ratio;
                            $newH = $h;
                        }
                        else {
                            $newH = $w / $ratio;
                            $newW = $w;
                        }
                    }

                    $new_temp_image = imagecreatetruecolor($newW, $newH);
                    imagecopyresampled($new_temp_image, $temp_image, 0, 0, 0, 0, $newW, $newH, $image_w, $image_h);

                    return $new_temp_image;
            }

     // FUNKTSIOON PISIPILTIDE JAOKS \\
     function resize_thumb($image, $image_file_type) {

                    $temp_image = null;
                    if($image_file_type == "jpg") {
                        $temp_image = imagecreatefromjpeg($image);
                    }

                    if($image_file_type == "png") {
                        $temp_image = imagecreatefrompng($image);
                    }
                    // pildi originaalsed mõõtmed
                    $image_w = imagesx($temp_image);
                    $image_h = imagesy($temp_image);

                    $ratio = $image_w / $image_h;

                    if ($image_w > $image_h) {
                        $image_w = ceil($image_w - ($image_w * abs($ratio - $image_w / $image_h)));
                    }
                    else {
                        $image_h = ceil($image_h - ($image_h * abs($ratio - $image_w / $image_h)));
                    }

                    $newW = 100;
                    $newH = 100;

                    $thumb = imagecreatetruecolor($newW, $newH);
                    imagecopyresampled($thumb, $temp_image, 0, 0, 0, 0, $newW, $newH, $image_w, $image_h);

                    return $thumb;
            }

        // FUNKTSIOON PILDIANDMETE SALVESTAMISEKS ANDMEBAASI \\
        function store_photo($userid, $filename, $alttext, $privacy) {
            $conn = new mysqli($GLOBALS["server_host"], $GLOBALS["server_user_name"],$GLOBALS["server_password"], $GLOBALS["database"]);
            // Valmistan ette SQL käsu..
            $stmt = $conn -> prepare("INSERT INTO vr21_photos (vr21_photos_userid, vr21_photos_filename, vr21_photos_alttext, vr21_photos_privacy) VALUES (?,?,?,?)");
            echo $conn -> error;
            // i - integer, s - string, d - decimal
            $stmt -> bind_param("issi", $userid, $filename, $alttext, $privacy);
            $stmt -> execute();
            $stmt -> close();
            $conn -> close();
        }
        
        // funktsioon üleslaetud fotode galerii tekitamiseks
        function show_photos($privacy) {

            $thumbnail_folder = "../upload_photos_thumbnail/";
    
            $conn = new mysqli($GLOBALS["server_host"], $GLOBALS["server_user_name"], $GLOBALS["server_password"], $GLOBALS["database"]);
            //määrame suhtluseks kodeeringu
            $conn -> set_charset("utf8");
            //valmistan ette SQL käsu
            $stmt = $conn -> prepare("SELECT vr21_photos.vr21_photos_id, vr21_photos.vr21_photos_filename, vr21_photos.vr21_photos_alttext, vr21_users.vr21_users_firstname, vr21_users.vr21_users_lastname FROM vr21_photos JOIN vr21_users ON vr21_photos.vr21_photos_userid = vr21_users.vr21_users_id WHERE vr21_photos.vr21_photos_privacy <= ? AND vr21_photos.vr21_photos_deleted IS NULL GROUP BY vr21_photos.vr21_photos_id");
            echo $conn -> error;
            //i - integer   s - string   d - decimal
            $stmt -> bind_result($photo_id_from_db, $photo_name_from_db, $photo_alttext_from_db, $photo_author_firstname_from_db, $photo_author_lastname_from_db);
            $stmt -> bind_param("i", $privacy); 
            $stmt -> execute();
            $raw_photos_html = null;
            while ($stmt -> fetch()) {
                $raw_photos_html .= "\n <div id='photos' class='photos'>";
                $raw_photos_html .= "\n <img src=" .$thumbnail_folder .$photo_name_from_db ." alt=" .$photo_alttext_from_db ." data-fn=" .$photo_name_from_db ." data-id=" .$photo_id_from_db .">";
                $raw_photos_html .= "\n <p> " .$photo_author_firstname_from_db ." " .$photo_author_lastname_from_db ."</p>";
                $raw_photos_html .= "\n </div>";
            }
            $stmt -> close();
            $conn -> close();
            return $raw_photos_html;
        }

        // FUNKTSIOON UUDISTE PILTIDE SALVESTAMISEKS
        function store_news_photo($userid, $filename, $alttext) {
            $conn = new mysqli($GLOBALS["server_host"], $GLOBALS["server_user_name"],$GLOBALS["server_password"], $GLOBALS["database"]);
            $conn -> set_charset("utf8");
            // Valmistan ette SQL käsu..
            $stmt = $conn -> prepare("INSERT INTO vr21_news_photo (photo_name, photo_alt_text, photo_uploader_id) VALUES (?,?,?)");
            echo $conn -> error;
            // i - integer, s - string, d - decimal
            $stmt -> bind_param("ssi", $filename, $alttext, $userid);
            $stmt -> execute();
            $photo_id = $conn->insert_id;
            $stmt -> close();
            $conn -> close();
            return $photo_id;
        }   
