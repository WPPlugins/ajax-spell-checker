<?php
include "../../../../wp-config.php";
include "spell-check-library.php";

function code2utf($num){
	if($num<128)
		return chr($num);
	if($num<1024)
		return chr(($num>>6)+192).chr(($num&63)+128);
	if($num<32768)
		return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
	if($num<2097152)
		return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
	return '';
}

function unescape($strIn, $iconv_to = 'UTF-8') {
	$strOut = '';
	$iPos = 0;
	$len = strlen ($strIn);
	while ($iPos < $len) {
		$charAt = substr ($strIn, $iPos, 1);
		if ($charAt == '%') {
			$iPos++;
			$charAt = substr ($strIn, $iPos, 1);
			if ($charAt == 'u') {
				// Unicode character
				$iPos++;
				$unicodeHexVal = substr ($strIn, $iPos, 4);
				$unicode = hexdec ($unicodeHexVal);
				$strOut .= code2utf($unicode);
				$iPos += 4;
			}
			else {
				// Escaped ascii character
				$hexVal = substr ($strIn, $iPos, 2);
				if (hexdec($hexVal) > 127) {
					// Convert to Unicode
					$strOut .= code2utf(hexdec ($hexVal));
				}
				else {
					$strOut .= chr (hexdec ($hexVal));
				}
				$iPos += 2;
			}
		}
		else {
			$strOut .= $charAt;
			$iPos++;
		}
	}
	if ($iconv_to != "UTF-8") {
		$strOut = mb_convert_encoding($strOut, $iconv_to);
	}
	return $strOut;
}

$action = "";
$content = "";
$as_options = array(
	"lang"					=> get_option("as_lang"),
	"runTogether"			=> get_option("as_runtogether"),
	"personal"				=> ABSPATH . get_option("as_personal") . "/custom." . get_option("as_lang") . ".pws",
	"repl"					=> ABSPATH . get_option("as_repl") . "/custom." . get_option("as_lang") . ".prepl",
	"maxSuggestions"		=> get_option("as_maxsug"),
	"customDict"			=> get_option("as_custom"),
	"customDictLocation"	=> ABSPATH . get_option("as_custompath"),
	"charset"				=> get_option("blog_charset")
);

$factory = new SpellChecker($as_options);

switch($_SERVER["REQUEST_METHOD"]){
	case "GET":
		$action = $_GET["do"];
		$content = preg_replace("/[0-9]/", " ", unescape($_GET["content"])); //hack for a strange segfault
		break;
	case "POST":
		$action = $_POST["do"];
		$content = preg_replace("/[0-9]/", " ", unescape($_POST["content"])); //hack for a strange segfault
		break;
	default:
		die("Request not understood");
}

switch($action) {
	case "check":
		$spell = $factory->create($content);
		header("Content-type: text/javascript; charset=$as_options[charset]");
		echo "updateDisplay(" . $spell->toJSArray() . ")";
		break;
	case "store":
		$pair = explode(":", $content);
		$spell = $factory->create("");
		$spell->storeReplacement($pair[0], $pair[1]);
		header("Content-type: text/javascript; charset=$as_options[charset]");
		echo "checkSpelling()";
		break;
	case "add":
		$spell = $factory->create("");
		$spell->addWord($content);
		header("Content-type: text/javascript; charset=$as_options[charset]");
		echo "checkSpelling()";
		break;
	default:
		die("I wish you humans would leave me alone!");
}

?>