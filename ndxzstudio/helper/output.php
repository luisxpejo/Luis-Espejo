<?php if (!defined('SITE')) exit('No direct script access allowed');


// functions used when getting data out of database

// for nicer placement in our textareas - but are we using this really?
function stripForForm($text='', $process='')
{
	if (($process == 0) || ($process == '')) 
	{
		// have we checked this yet
		if (function_exists('mb_decode_numericentity'))
		{
			return mb_decode_numericentity($text, UTF8EntConvert('1'), 'utf-8');
		}
		else
		{
			$text = htmlspecialchars($text);
			return str_replace(array("&gt;","&lt;"), array(">","<"), $text);
		}
	}
	
	if ($text) 
	{
		$out = str_replace("<p>", "", $text);
		$out = str_replace(array("<br />","<br>"),array("",""), $out);
		$out = str_replace("</p>", "", $out);
		
		if (function_exists('mb_decode_numericentity'))
		{
			$out = mb_decode_numericentity($out, UTF8EntConvert('1'), 'utf-8');
		}
		else
		{
			$out = htmlspecialchars($out);
			$out = str_replace(array("&gt;","&lt;"), array(">","<"), $out);
		}
		
		return $out;
	} 
	else 
	{
		return '';
	}
}


// this does allow ", &, < and > so be sure to be aware of them
// http://php.belnet.be/manual/en/function.mb-encode-numericentity.php
function UTF8EntConvert($out='')
{
	$f = 0xffff;

	$convmap = array(

		// %HTMLlat1;
		160,  255, 0, $f,

		// %HTMLsymbol;
		402,  402, 0, $f,  913,  929, 0, $f,  931,  937, 0, $f,
		945,  969, 0, $f,  977,  978, 0, $f,  982,  982, 0, $f,
		8226, 8226, 0, $f, 8230, 8230, 0, $f, 8242, 8243, 0, $f,
		8254, 8254, 0, $f, 8260, 8260, 0, $f, 8465, 8465, 0, $f,
		8472, 8472, 0, $f, 8476, 8476, 0, $f, 8482, 8482, 0, $f,
		8501, 8501, 0, $f, 8592, 8596, 0, $f, 8629, 8629, 0, $f,
		8656, 8660, 0, $f, 8704, 8704, 0, $f, 8706, 8707, 0, $f,
		8709, 8709, 0, $f, 8711, 8713, 0, $f, 8715, 8715, 0, $f,
		8719, 8719, 0, $f, 8721, 8722, 0, $f, 8727, 8727, 0, $f,
		8730, 8730, 0, $f, 8733, 8734, 0, $f, 8736, 8736, 0, $f,
		8743, 8747, 0, $f, 8756, 8756, 0, $f, 8764, 8764, 0, $f,
		8773, 8773, 0, $f, 8776, 8776, 0, $f, 8800, 8801, 0, $f,
		8804, 8805, 0, $f, 8834, 8836, 0, $f, 8838, 8839, 0, $f,
		8853, 8853, 0, $f, 8855, 8855, 0, $f, 8869, 8869, 0, $f,
		8901, 8901, 0, $f, 8968, 8971, 0, $f, 9001, 9002, 0, $f,
		9674, 9674, 0, $f, 9824, 9824, 0, $f, 9827, 9827, 0, $f,
		9829, 9830, 0, $f,

		// %HTMLspecial;
		// These ones are excluded to enable HTML: 34, 38, 60, 62
		// but we enable 38, 60, 62 when displaying in textarea (see below)
		338,  339, 0, $f,  352,  353, 0, $f,  376,  376, 0, $f,
		710,  710, 0, $f,  732,  732, 0, $f, 8194, 8195, 0, $f,
		8201, 8201, 0, $f, 8204, 8207, 0, $f, 8211, 8212, 0, $f,
		8216, 8218, 0, $f, 8218, 8218, 0, $f, 8220, 8222, 0, $f,
		8224, 8225, 0, $f, 8240, 8240, 0, $f, 8249, 8250, 0, $f,
		8364, 8364, 0, $f,

		// basic foreign chars

		// other symbols
		191, 191, 0, $f
		);

	if ($out == '1') 
	{
		$insert = array(38, 38, 0, $f, 60, 60, 0, $f, 62, 62, 0, $f);
		return $convmap = array_merge($insert,$convmap);
	} 
	else 
	{
		return $convmap;
	}
}


/**
 * Romanize a non-latin string
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8Romanize($string)
{
	if (utf8_isASCII($string)) return $string; //nothing to do

	$romanize = romanizeFile(NULL);

 	return strtr($string,$romanize);
}


/**
 * Checks if a string contains 7bit ASCII only
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8_isASCII($str)
{
	for ($i=0; $i<strlen($str); $i++) 
	{
		if (ord($str . $i) >127) return false;
	}

	return true;
}

	
/**
 * Replace accented UTF-8 characters by unaccented ASCII-7 equivalents
 *
 * Use the optional parameter to just deaccent lower ($case = -1) or upper ($case = 1)
 * letters. Default is to deaccent both cases ($case = 0)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function utf8Deaccent($string, $case=0)
{
	$accents = accentsFile();

	if ($case <= 0) 
	{
		$string = str_replace(
			array_keys($accents['lower']),
			array_values($accents['lower']),
			$string);
	}

	if ($case >= 0)
	{
		$string = str_replace(
			array_keys($accents['upper']),
			array_values($accents['upper']),
			$string);
	}

	return $string;
}


function accentsFile()
{
	$UTF8['lower'] = array(
	  '??' => 'a', '??' => 'o', '??' => 'd', '???' => 'f', '??' => 'e', '??' => 's', '??' => 'o', 
	  '??' => 'ss', '??' => 'a', '??' => 'r', '??' => 't', '??' => 'n', '??' => 'a', '??' => 'k', 
	  '??' => 's', '???' => 'y', '??' => 'n', '??' => 'l', '??' => 'h', '???' => 'p', '??' => 'o', 
	  '??' => 'u', '??' => 'e', '??' => 'e', '??' => 'c', '???' => 'w', '??' => 'c', '??' => 'o', 
	  '???' => 's', '??' => 'o', '??' => 'g', '??' => 't', '??' => 's', '??' => 'e', '??' => 'c', 
	  '??' => 's', '??' => 'i', '??' => 'u', '??' => 'c', '??' => 'e', '??' => 'w', '???' => 't', 
	  '??' => 'u', '??' => 'c', '??' => 'oe', '??' => 'e', '??' => 'y', '??' => 'a', '??' => 'l', 
	  '??' => 'u', '??' => 'u', '??' => 's', '??' => 'g', '??' => 'l', '??' => 'f', '??' => 'z', 
	  '???' => 'w', '???' => 'b', '??' => 'a', '??' => 'i', '??' => 'i', '???' => 'd', '??' => 't', 
	  '??' => 'r', '??' => 'ae', '??' => 'i', '??' => 'r', '??' => 'e', '??' => 'ue', '??' => 'o', 
	  '??' => 'e', '??' => 'n', '??' => 'n', '??' => 'h', '??' => 'g', '??' => 'd', '??' => 'j', 
	  '??' => 'y', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 't', '??' => 'y', '??' => 'o', 
	  '??' => 'a', '??' => 'l', '???' => 'w', '??' => 'z', '??' => 'i', '??' => 'a', '??' => 'g', 
	  '???' => 'm', '??' => 'o', '??' => 'i', '??' => 'u', '??' => 'i', '??' => 'z', '??' => 'a', 
	  '??' => 'u', '??' => 'th', '??' => 'dh', '??' => 'ae', '??' => 'u'
	);

	$UTF8['upper'] = array(
	  '??' => 'A', '??' => 'O', '??' => 'D', '???' => 'F', '??' => 'E', '??' => 'S', '??' => 'O', 
	  '??' => 'A', '??' => 'R', '??' => 'T', '??' => 'N', '??' => 'A', '??' => 'K', 
	  '??' => 'S', '???' => 'Y', '??' => 'N', '??' => 'L', '??' => 'H', '???' => 'P', '??' => 'O', 
	  '??' => 'U', '??' => 'E', '??' => 'E', '??' => 'C', '???' => 'W', '??' => 'C', '??' => 'O', 
	  '???' => 'S', '??' => 'O', '??' => 'G', '??' => 'T', '??' => 'S', '??' => 'E', '??' => 'C', 
	  '??' => 'S', '??' => 'I', '??' => 'U', '??' => 'C', '??' => 'E', '??' => 'W', '???' => 'T', 
	  '??' => 'U', '??' => 'C', '??' => 'Oe', '??' => 'E', '??' => 'Y', '??' => 'A', '??' => 'L', 
	  '??' => 'U', '??' => 'U', '??' => 'S', '??' => 'G', '??' => 'L', '??' => 'F', '??' => 'Z', 
	  '???' => 'W', '???' => 'B', '??' => 'A', '??' => 'I', '??' => 'I', '???' => 'D', '??' => 'T', 
	  '??' => 'R', '??' => 'Ae', '??' => 'I', '??' => 'R', '??' => 'E', '??' => 'Ue', '??' => 'O', 
	  '??' => 'E', '??' => 'N', '??' => 'N', '??' => 'H', '??' => 'G', '??' => 'D', '??' => 'J', 
	  '??' => 'Y', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'T', '??' => 'Y', '??' => 'O', 
	  '??' => 'A', '??' => 'L', '???' => 'W', '??' => 'Z', '??' => 'I', '??' => 'A', '??' => 'G', 
	  '???' => 'M', '??' => 'O', '??' => 'I', '??' => 'U', '??' => 'I', '??' => 'Z', '??' => 'A', 
	  '??' => 'U', '??' => 'Th', '??' => 'Dh', '??' => 'Ae'
	);

	return $UTF8;
}