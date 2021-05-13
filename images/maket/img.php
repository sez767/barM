<?php
Header("Content-type: image/png");
class textPNG 
{
	var $font = './arialbd.ttf'; //default font. directory relative to script directory.
	var $msg = "no text"; // default text to display.
	var $size = 24; // default font size.
	var $rot = 0; // rotation in degrees.
	var $pad = 0; // padding.
	var $transparent = 1; // transparency set to on.
	var $red = 0; // black text...
	var $grn = 0;
	var $blu = 0;
	var $bg_red = 255; // on white background.
	var $bg_grn = 255;
	var $bg_blu = 255;
	
	function draw() 
	{
		$width = 0;
		$height = 0;
		$offset_x = 0;
		$offset_y = 0;
		$bounds = array();
		$image = "";
	
		// get the font height.
		$bounds = ImageTTFBBox($this->size, $this->rot, $this->font, "W");
		if ($this->rot < 0) 
		{
			$font_height = abs($bounds[7]-$bounds[1]);		
		} 
		else if ($this->rot > 0) 
		{
		$font_height = abs($bounds[1]-$bounds[7]);
		} 
		else 
		{
			$font_height = abs($bounds[7]-$bounds[1]);
		}
		// determine bounding box.
		$bounds = ImageTTFBBox($this->size, $this->rot, $this->font, $this->msg);
		if ($this->rot < 0) 
		{
			$width = abs($bounds[4]-$bounds[0]);
			$height = abs($bounds[3]-$bounds[7]);
			$offset_y = $font_height;
			$offset_x = 0;
		} 
		else if ($this->rot > 0) 
		{
			$width = abs($bounds[2]-$bounds[6]);
			$height = abs($bounds[1]-$bounds[5]);
			$offset_y = abs($bounds[7]-$bounds[5])+$font_height;
			$offset_x = abs($bounds[0]-$bounds[6]);
		} 
		else
		{
			$width = abs($bounds[4]-$bounds[6]);
			$height = abs($bounds[7]-$bounds[1]);
			$offset_y = $font_height;;
			$offset_x = 0;
		}
		
		$image = imagecreate($width+($this->pad*2)+1,$height+($this->pad*2)+1);
		$background = ImageColorAllocate($image, $this->bg_red, $this->bg_grn, $this->bg_blu);
		$foreground = ImageColorAllocate($image, $this->red, $this->grn, $this->blu);
	
		if ($this->transparent) ImageColorTransparent($image, $background);
		ImageInterlace($image, false);
	
		// render the image
		ImageTTFText($image, $this->size, $this->rot, $offset_x+$this->pad, $offset_y+$this->pad, $foreground, $this->font, $this->msg);
	
		// output PNG object.
		imagePNG($image);
		}
	}

	$text = new textPNG;

	if (isset($_GET['text'])) $text->msg = $_GET['text']; // text to display
	if (isset($font)) $text->font = './arialbd.ttf'; // font to use (include directory if needed).
	if (isset($size)) $text->size = '20'; // size in points
	if (isset($rot)) $text->rot = '0'; // rotation
	if (isset($pad)) $text->pad = '0'; // padding in pixels around text.
        $colors = array("red" => array('255','0','0'),"black" => array('0','0','0'),"white" => array('255','255','255'),"green"=>array('0','255','0'));
	if(isset($_GET['color'])){
                 //var_dump($_GET['color'][1]); die;
		 $text->red = $colors[$_GET['color']][0]; // text color
	 	 $text->grn = $colors[$_GET['color']][1]; // ..
	 	 $text->blu = $colors[$_GET['color']][2]; // ..
	}
	if(isset($_GET['background'])){
		$text->bg_red = $colors[$_GET['color']][0]; // background color.
		$text->bg_grn = $colors[$_GET['color']][1]; // ..
		$text->bg_blu = $colors[$_GET['color']][2]; // ..
	}
	if (isset($tr)) $text->transparent = false; // transparency flag (boolean).

	$text->draw(); // GO!!!!!
?>