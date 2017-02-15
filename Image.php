<?php

class Image {
   
	protected $image;
	protected $imageType;
 
	function __construct($filename) {
		$image_info = getimagesize($filename);
		$this->imageType = $image_info[2];
		if( $this->imageType == IMAGETYPE_JPEG ) {
			$this->image = imagecreatefromjpeg($filename);
		} elseif( $this->imageType == IMAGETYPE_GIF ) {
			$this->image = imagecreatefromgif($filename);
		} elseif( $this->imageType == IMAGETYPE_PNG ) {
			$this->image = imagecreatefrompng($filename);
		}
	}

	public function save($filename, $imageType=IMAGETYPE_JPEG) {
		if( $imageType == IMAGETYPE_JPEG ) {
			imagejpeg($this->image,$filename);
		} elseif( $imageType == IMAGETYPE_GIF ) {
			imagegif($this->image,$filename);			
		} elseif( $imageType == IMAGETYPE_PNG ) {
			imagepng($this->image,$filename);
		}	
	}

	public function output($imageType=IMAGETYPE_JPEG) {
		if( $imageType == IMAGETYPE_JPEG ) {
			imagejpeg($this->image);
		} elseif( $imageType == IMAGETYPE_GIF ) {
			imagegif($this->image);			
		} elseif( $imageType == IMAGETYPE_PNG ) {
			imagepng($this->image);
		}	
	}

	public function getWidth() {
		return imagesx($this->image);
	}

	public function getHeight() {
		return imagesy($this->image);
	}

	public function resizeToHeight($height, $force = true) {
		if (!$force && $this->getHeight() <= $height) return;
		$ratio = $height / $this->getHeight();
		$width = ceil($this->getWidth() * $ratio);
		$this->resize($width, $height);
	}

	public function resizeToWidth($width, $force = true) {
		if (!$force && $this->getWidth() <= $width) return;
		$ratio = $width / $this->getWidth();
		$height = ceil($this->getHeight() * $ratio);
		$this->resize($width, $height);
	}

	public function scale($scale) {
		$width = $this->getWidth() * $scale / 100;
		$height = $this->getHeight() * $scale / 100; 
		$this->resize($width, $height);
	}

	// ako je fill onda ce se resizat/cropati da popuni box
	public function fitTo($width, $height, $fill = false) {
		$xRatio = $width / $this->getWidth();
		$yRatio = $height / $this->getHeight();

		if ($fill) {
			// manje slike ce povecati da manja stranica dosegne rub boxa, a veca ispadne van boxa i onda cropati
			// vece slike ce smanjiti da manja stranica dosegne rub boxa a veca ostane vani i onda cropati
			$stretch = ($this->getWidth() <= $width || $this->getHeight() <= $height);
			if ($xRatio * $this->getHeight() < $height) {
				$this->resizeToHeight($height, $stretch);
			} else {
				$this->resizeToWidth($width, $stretch);
			}
			// crop
			$this->crop($width, $height);

		} else {
			// manje slike nece povecati, ako je po jednoj dimenziji van boxa napravit ce smanjenje po toj dim, i sacuvati aspekt
			if ($this->getWidth() <= $width && $this->getHeight() <= $height) {
				return;
			} else if ($xRatio * $this->getHeight() < $height) {
				$this->resizeToWidth($width);
			} else {
				$this->resizeToHeight($height);
			}
		}
	}

	protected function crop($width, $height) {
		$newImage = imagecreatetruecolor($width, $height);
		imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $width, $height);
		$this->image = $newImage;	
	}

	protected function resize($width, $height) {
		$newImage = imagecreatetruecolor($width, $height);
		imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $newImage;	
	}		
}
