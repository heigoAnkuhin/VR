<?php

class Upload_photo
{
	private $target_file;
	private $photo_to_upload;
	private $temp_image;
	private $size_limit;
	public $image_file_type;
	public $new_temp_image; // Hiljem kui klass hakkab kõike ise tegema, siis ilmselt "private" 
	public $upload_error;
	public $file_name;

	// funktsioon, mis käivitub klassi loomisel
	function __construct($photo_to_upload, $size_limit)
	{
		$this->photo_to_upload = $photo_to_upload;
		$this->size_limit = $size_limit;
		// ka test, kas on üldse tegemist pildiga ja kas sobib, peaks siin klassis sees olema.
		$this->test_photo($photo_to_upload, $size_limit);
		$this->temp_image = $this->create_image_from_file($this->photo_to_upload["tmp_name"], $this->image_file_type);

	}
	// funktsioon, mis käivitub kui klassi kasutav skript lõppeb
	function __destruct()
	{
		if (isset($this->new_temp_image)) {
			@imagedestroy($this->new_temp_image);
		}
		if (isset($this->temp_image)) {
			imagedestroy($this->temp_image);
		}
	}
	// Funktsioon pildi loomiseks üleslaetud failist
	private function create_image_from_file($image, $file_type)
	{
		$temp_image = null;
		if ($file_type == "jpg") {
			$temp_image = imagecreatefromjpeg($image);
		}

		if ($file_type == "png") {
			$temp_image = imagecreatefrompng($image);
		}
		return $temp_image;
	}
	// Funktsioon pildi suuruse muutmiseks
	public function resize_photo($w, $h, $keep_orig_proportion = true)
	{
		$image_w = imagesx($this->temp_image);
		$image_h = imagesy($this->temp_image);
		$new_w = $w;
		$new_h = $h;
		$cut_x = 0;
		$cut_y = 0;
		$cut_size_w = $image_w;
		$cut_size_h = $image_h;

		if ($w == $h) {
			if ($image_w > $image_h) {
				$cut_size_w = $image_h;
				$cut_x = round(($image_w - $cut_size_w) / 2);
			} else {
				$cut_size_h = $image_w;
				$cut_y = round(($image_h - $cut_size_h) / 2);
			}
		} elseif ($keep_orig_proportion) { //kui tuleb originaaproportsioone säilitada
			if ($image_w / $w > $image_h / $h) {
				$new_h = round($image_h / ($image_w / $w));
			} else {
				$new_w = round($image_w / ($image_h / $h));
			}
		} else { //kui on vaja kindlasti etteantud suurust, ehk pisut ka kärpida
			if ($image_w / $w < $image_h / $h) {
				$cut_size_h = round($image_w / $w * $h);
				$cut_y = round(($image_h - $cut_size_h) / 2);
			} else {
				$cut_size_w = round($image_h / $h * $w);
				$cut_x = round(($image_w - $cut_size_w) / 2);
			}
		}

		//loome uue ajutise pildiobjekti
		$this->new_temp_image = imagecreatetruecolor($new_w, $new_h);
		//kui on läbipaistvusega png pildid, siis on vaja säilitada läbipaistvusega
		imagesavealpha($this->new_temp_image, true);
		$trans_color = imagecolorallocatealpha($this->new_temp_image, 0, 0, 0, 127);
		imagefill($this->new_temp_image, 0, 0, $trans_color);
		imagecopyresampled($this->new_temp_image, $this->temp_image, 0, 0, $cut_x, $cut_y, $new_w, $new_h, $cut_size_w, $cut_size_h);
	}
	// Funktsioon pildi salvestamiseks uude faili
	public function save_image_to_file($target)
	{
		$notice = null;
		if ($this->image_file_type == "jpg") {
			if (imagejpeg($this->new_temp_image, $target, 90)) {
				$notice = 1;
			} else {
				$notice = 0;
			}
		}
		if ($this->image_file_type == "png") {
			if (imagepng($this->new_temp_image, $target, 6)) {
				$notice = 1;
			} else {
				$notice = 0;
			}
		}
		imagedestroy($this->new_temp_image);
		return $notice;
	}
	// Funktsioon pildile vesimärgi lisamiseks
	public function add_watermark($watermark)
	{
		$watermark_file_type = strtolower(pathinfo($watermark, PATHINFO_EXTENSION));
		$watermark_image = $this->create_image_from_file($watermark, $watermark_file_type);
		$watermark_w = imagesx($watermark_image);
		$watermark_h = imagesy($watermark_image);
		$watermark_x = imagesx($this->new_temp_image) - $watermark_w - 10;
		$watermark_y = imagesy($this->new_temp_image) - $watermark_h - 10;
		imagecopy($this->new_temp_image, $watermark_image, $watermark_x, $watermark_y, 0, 0, $watermark_w, $watermark_h);
		imagedestroy($watermark_image);
	}
	// Funktsioon üleslaetud faili/pildi parameetrite kontrollimiseks
	public function test_photo($img, $limit)
	{
		// ega pole liiga suur fail
		if ($img["size"] > $limit) {
			$this->upload_error = "Valitud fail on liiga suur! Lubatud kuni 1MiB.";
		} else {
			$check = getimagesize($img["tmp_name"]);
			if ($check !== false) {
				//kontrollime, kas aktsepteeritud faiivorming ja fikseerime laiendi.
				if ($check["mime"] == "image/jpeg") {
					$this->image_file_type = "jpg";
				} elseif ($check["mime"] == "image/png") {
					$this->image_file_type = "png";
				} else {
					$this->upload_error = "Pole sobiv failiformaat! Ainult jpg ja png on lubatud.";
				}
			} else {
				$this->upload_error = "Tegemist pole pildifailiga!";
			}
		}
		return $this->upload_error;
	}
	// Funktsioon pildile uue nime genereerimiseks
	public function generate_name($prefix, $timestamp) { // failile nime genereerimise funktsioon
		$this->file_name = $prefix . $timestamp . "." . $this->image_file_type;
		return $this->file_name;
	}
	// Funktsioon originaalpildi säilitamiseks
	public function save_original($target) {
		if(!move_uploaded_file($this->photo_to_upload["tmp_name"], $target)){
			$this->upload_error .= " Originaalfoto üleslaadimine ebaõnnestus!";
		}
	}
}
