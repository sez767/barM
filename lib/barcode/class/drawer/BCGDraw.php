<?php
/**
 * BCGDraw.php
 *--------------------------------------------------------------------
 *
 * Base class to draw images
 *
 *--------------------------------------------------------------------
 * Revision History
 * v2.1.0	8  nov	2009	Jean-Sébastien Goupil
 *--------------------------------------------------------------------
 * $Id: BCGDraw.php,v 1.1 2009/11/09 04:15:10 jsgoupil Exp $
 * PHP5-Revision: 1.1
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
class BCGDraw { // abstract
	var $im;
	var $filename;

	/**
	 * Constructor
	 *
	 * @param resource $im
	 */
	function BCGDraw(&$im) {
		$this->im = $im;
	}

	/**
	 * Sets the filename
	 *
	 * @param string $filename
	 */
	function setFilename($filename) {
		$this->filename = $filename;
	}

	/**
	 * Method needed to draw the image based on its specification (JPG, GIF, etc.)
	 */
	function draw() {} // abstract public 
}
?>