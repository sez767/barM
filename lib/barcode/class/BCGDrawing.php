<?php
/**
 * BCGDrawing.php
 *--------------------------------------------------------------------
 *
 * Holds the drawing $im
 * You can use get_im() to add other kind of form not held into these classes.
 *
 *--------------------------------------------------------------------
 * Revision History
 * v2.1.0	8  nov	2009	Jean-Sébastien Goupil	Support DPI, Rotation
 * v2.0.1	8  mar	2009	Jean-Sébastien Goupil	Supports GIF and WBMP
 * v2.0.0	23 apr	2008	Jean-Sébastien Goupil	New Version Update
 * v1.2.3b	31 dec	2005	Jean-Sébastien Goupil	Just one barcode per drawing
 * v1.2.1	27 jun	2005	Jean-Sébastien Goupil	Font support added
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: BCGDrawing.php,v 1.10 2009/11/09 04:13:35 jsgoupil Exp $
 * PHP5-Revision: 1.12
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGBarcode.php');
include_once('drawer/BCGDrawJPG.php');
include_once('drawer/BCGDrawPNG.php');

class BCGDrawing {
	var $IMG_FORMAT_PNG = 1; // const
	var $IMG_FORMAT_JPEG = 2; // const
	var $IMG_FORMAT_GIF = 3; // const
	var $IMG_FORMAT_WBMP = 4; // const

	var $w, $h;		// int
	var $color;		// BCGColor
	var $filename;		// char *
	var $im;		// {object}
	var $barcode;		// BCGBarcode
	var $dpi;		// int
	var $rotateDegree;	// float

	/**
	 * Constructor
	 *
	 * @param int $w
	 * @param int $h
	 * @param string filename
	 * @param BCGColor $color
	 */
	function BCGDrawing($filename, &$color) {
		$this->im = null;
		$this->setFilename($filename);
		$this->color =& $color;
		$this->dpi = null;
		$this->rotateDegree = 0.0;
	}

	/**
	 * Destructor
	 */
	//public function __destruct() {
	//	$this->destroy();
	//}

	/**
	 * Sets the filename
	 *
	 * @param string $filaneme
	 */
	function setFilename($filename) {
		$this->filename = $filename;
	}

	/**
	 * Init Image and color background
	 */
	function init() {
		if($this->im === null) {
			$this->im = imagecreatetruecolor($this->w, $this->h)
			or die('Can\'t Initialize the GD Libraty');
			imagefilledrectangle($this->im, 0, 0, $this->w - 1, $this->h - 1, $this->color->allocate($this->im));
		}
	}

	/**
	 * @return resource
	 */
	function &get_im() {
		return $this->im;
	}

	/**
	 * @param resource $im
	 */
	function set_im(&$im) {
		$this->im = $im;
	}

	/**
	 * Set Barcode for drawing
	 *
	 * @param BCGBarcode $barcode
	 */
	function setBarcode(&$barcode) {
		$this->barcode =& $barcode;
	}

	/**
	 * Get the DPI for supported filetype
	 *
	 * @return int
	 */
	function getDPI() {
		return $this->dpi;
	}

	/**
	 * Set the DPI for supported filetype
	 *
	 * @param float $dpi
	 */
	function setDPI($dpi) {
		$this->dpi = $dpi;
	}

	/**
	 * Get the rotation angle in degree
	 *
	 * @return float
	 */
	function getRotationAngle() {
		return $this->rotateDegree;
	}

	/**
	 * Set the rotation angle in degree
	 *
	 * @param float $degree
	 */
	function setRotationAngle($degree) {
		$this->rotateDegree = (float)$degree;
	}

	/**
	 * Draw the barcode on the image $im
	 */
	function draw() {
		$size = $this->barcode->getMaxSize();
		$this->w = max(1, $size[0]);
		$this->h = max(1, $size[1]);
		$this->init();
		$this->barcode->draw($this->im);
	}

	/**
	 * Save $im into the file (many format available)
	 *
	 * @param int $image_style
	 * @param int $quality
	 */
	function finish($image_style = 2, $quality = 100) {
		$drawer = null;

		$im = $this->im;
		if($this->rotateDegree > 0.0) {
			$im = imagerotate($this->im, $this->rotateDegree, $this->color->allocate($this->im));
		}

		if ($image_style === $this->IMG_FORMAT_PNG) {
			$drawer =& new BCGDrawPNG($im);
			$drawer->setFilename($this->filename);
			$drawer->setDPI($this->dpi);
		} elseif ($image_style === $this->IMG_FORMAT_JPEG) {
			$drawer =& new BCGDrawJPG($im);
			$drawer->setFilename($this->filename);
			$drawer->setDPI($this->dpi);
			$drawer->setQuality($quality);
		} elseif ($image_style === $this->IMG_FORMAT_GIF) {
			imagegif($this->im, $this->filename);
		} elseif ($image_style === $this->IMG_FORMAT_WBMP) {
			imagewbmp($this->im, $this->filename);
		}

		if($drawer !== null) {
			$drawer->draw();
		}
	}

	/**
	 * Free the memory of PHP (called also by destructor)
	 */
	function destroy() {
		@imagedestroy($this->im);
	}
};
?>