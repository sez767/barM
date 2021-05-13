<?php
/**
 * BCGcode39extended.barcode.php
 *--------------------------------------------------------------------
 *
 * Sub-Class - Code 39 Extended
 *
 *--------------------------------------------------------------------
 * Revision History
 * v2.0.0	23 apr	2008	Jean-S�bastien Goupil	New Version Update
 *--------------------------------------------------------------------
 * $Id: BCGcode39extended.barcode.php,v 1.3 2009/11/09 04:13:35 jsgoupil Exp $
 * PHP5-Revision: 1.3
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */
include_once('BCGcode39.barcode.php');

class BCGcode39extended extends BCGcode39 {
	var $EXTENDED_1 = 39; // const
	var $EXTENDED_2 = 40; // const
	var $EXTENDED_3 = 41; // const
	var $EXTENDED_4 = 42; // const

	var $errorText;
	var $indcheck, $data;

	/**
	 * Constructor
	 */
	function BCGcode39extended() { // public
		BCGcode39::BCGcode39();

		// We just put parenthesis around special characters.
		$this->keys[$this->EXTENDED_1] = '($)';
		$this->keys[$this->EXTENDED_2] = '(/)';
		$this->keys[$this->EXTENDED_3] = '(+)';
		$this->keys[$this->EXTENDED_4] = '(%)';

		$this->errorText = '';
	}

	/**
	 * Saves Text
	 *
	 * @param string $text
	 */
	function parse($text) {
		$this->text = $text;

		$data = array();
		$indcheck = array();

		$this->errorText = ''; // Reset Error
		$c = strlen($this->text);
		for($i = 0; $i < $c; $i++) {
			$pos = array_search($this->text[$i], $this->keys);
			if($pos === false) {
				// Search in extended?
				$extended = $this->getExtendedVersion($this->text[$i]);
				if($extended === false) {
					$this->errorText .= 'Char \'' . $this->text[$i] . '\' not allowed.'."\n";
				} else {
					$extc = strlen($extended);
					for($j = 0; $j < $extc; $j++) {
						$v = $extended[$j];
						if($v === '$') {
							$indcheck[] = $this->EXTENDED_1;
							$data[] = $this->code[$this->EXTENDED_1];
						} elseif($v === '%') {
							$indcheck[] = $this->EXTENDED_2;
							$data[] = $this->code[$this->EXTENDED_2];
						} elseif($v === '/') {
							$indcheck[] = $this->EXTENDED_3;
							$data[] = $this->code[$this->EXTENDED_3];
						} elseif($v === '+') {
							$indcheck[] = $this->EXTENDED_4;
							$data[] = $this->code[$this->EXTENDED_4];
						} else {
							$pos2 = array_search($v, $this->keys);
							$indcheck[] = $pos2;
							$data[] = $this->code[$pos2];
						}
					}
				}
			} else {
				$indcheck[] = $pos;
				$data[] = $this->code[$pos];
			}
		}

		$this->setData(array($indcheck, $data));
	}

	/**
	 * Draws the barcode
	 *
	 * @param resource $im
	 */
	function draw(&$im) {
		if(!empty($this->errorText)) {
			$error = explode("\n", trim($this->errorText));
			$c = count($error);
			for($i = 0; $i < $c; $i++) {
				$this->drawError($im, $error[$i]);
			}
		} else {
			$c = count($this->data);
			if($c === 0) {
				$this->drawError($im, 'No text has been entered.');
			} else {
				// Starting *
				$this->drawChar($im, $this->code[$this->starting], true);
				for($i = 0; $i < $c; $i++) {
					$this->drawChar($im, $this->data[$i], true);
				}
				// Checksum (rarely used)
				if ($this->checksum === true) {
					$this->drawChar($im, $this->code[$this->checksumValue % 43], true);
				}
				// Ending *
				$this->drawChar($im, $this->code[$this->ending], true);
				$this->drawText($im);
			}
		}
	}

	/**
	 * Returns the maximal size of a barcode
	 *
	 * @return int[]
	 */
	function getMaxSize() {
		$p = BCGBarcode1D::getMaxSize();

		$textlength = 13 * count($this->data) * $this->scale;
		$startlength = 13 * $this->scale;
		$checksumlength = 0;
		if ($this->checksum === true) {
			$checksumlength = 13 * $this->scale;
		}
		$endlength = 13 * $this->scale;
		return array($p[0] + $startlength + $textlength + $checksumlength + $endlength, $p[1]);
	}

	/**
	 * Overloaded method to calculate checksum
	 */
	function calculateChecksum() {
		$this->checksumValue = 0;
		$c = count($this->indcheck);
		for ($i = 0; $i < $c; $i++) {
			$this->checksumValue += $this->indcheck[$i];
		}
		$this->checksumValue = $this->checksumValue % 43;
	}

	/**
	 * Saves data into the classes.
	 *
	 * This method will save data, calculate real column number
	 * (if -1 was selected), the real error level (if -1 was
	 * selected)... It will add Padding to the end and generate
	 * the error codes.
	 *
	 * @param array $data
	 */
	function setData($data) {
		$this->indcheck = $data[0];
		$this->data = $data[1];
		$this->calculateChecksum();
	}

	function getExtendedVersion($char) {
		$o = ord($char);
		if($o === 0)
			return '%U';
		elseif($o >= 1 && $o <= 26)
			return '$' . chr($o + 64);
		elseif(($o >= 33 && $o <= 44) || $o === 47 || $o === 48)
			return '/' . chr($o + 32);
		elseif($o >= 97 && $o <= 122)
			return '+' . chr($o - 32);
		elseif($o >= 27 && $o <= 31)
			return '%' . chr($o + 38);
		elseif($o >= 59 && $o <= 63)
			return '%' . chr($o + 11);
		elseif($o >= 91 && $o <= 95)
			return '%' . chr($o - 16);
		elseif($o >= 123 && $o <= 127)
			return '%' . chr($o - 43);
		elseif($o === 64)
			return '%V';
		elseif($o === 96)
			return '%W';
		elseif($o > 127)
			return false;
		else
			return $char;
	}
};
?>