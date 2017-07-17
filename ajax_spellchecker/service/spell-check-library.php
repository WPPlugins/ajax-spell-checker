<?php

class GenericSpellChecker {
	var $_html;
	var $_offsets;
	var $_suggestions;
	var $_chars;
	var $_lang;
	var $_runTogether;
	var $_personal;
	var $_repl;
	var $_maxSuggestions;
	var $_customDict;
	var $_customDictLocation;
	var $_charset;

	function GenericSpellChecker($text, &$options = array()){
		$this->_lang = "en";
		$this->_runTogether = true;
		$this->_maxSuggestions = 5;
		$this->_html = $text;
		$this->_offsets = array();
		$this->_suggestions = array();
		$this->_charset = "utf-8";

		foreach($options as $var => $value){
			$var = "_$var";
			$this->$var = $value;
		}

		mb_internal_encoding($this->_charset);
		mb_regex_encoding($this->_charset);
		mb_regex_set_options("z");//pcre syntax

		if($text)
			$this->_buildOffsetTable();
	}

	function checkSpelling() { /* abstract */ }

	function storeReplacement() { /* abstract */ }

	function addWord() { /* abstract */ }

	function toJSArray() {
		$js = "new Array(";
		foreach($this->_suggestions as $sug){
			$val = addslashes(join(",",$sug["value"]));
			$val = "'".str_replace(",","','",$val)."'"; //make strings javascript compatible
			$js .= "{o: $sug[o],l: $sug[l],s: $sug[s],value: new Array($val)},";
		}
		$js = (count($this->_suggestions) > 0 ? substr($js,0,strlen($js)-1) : $js).")";
		return $js;
	}

	function toPHPArray() {
		return $this->_suggestions;
	}

	function toXML(){
		$xml = '<spellresult error="0" clipped="0" charschecked="'.$this->_chars.'">';
		foreach($this->_suggestions as $sug){
			$xml .= "<c o=\"$sug[o]\" l=\"$sug[l]\" s=\"$sug[s]\">".join("\t",$sug["value"])."</c>";
		}
		$xml .= '</spellresult>';
		return $xml;
	}

	function _buildOffsetTable(){
		$offsets = array();
		$notag_offsets = array();
		$off = 0;
		$offsets[] = 0;
		mb_ereg_search_init($this->_html,"((?:<[^>]+>)+|(?:&.+;)+)");
		while(($word = @mb_ereg_search_pos())){
			$word[0] = mb_strlen(mb_strcut($this->_html,0,$word[0])); //hack for a wirdness in mb_ereg_search_pos returning bad offsets
			$off += $word[1];
			$offsets[] = $word[0] + $word[1];
			$notag_offsets[] = $word[0] + $word[1] - $off;
		}
		$off = 0;
		$notag_offsets[] = 10000;
		$this->_chars = mb_ereg_search_getpos();
		$cnt = 0;
		$oadd = -1;
		for($i = 0; $i < $this->_chars && $cnt < count($offsets); $i++){
			if($i < $notag_offsets[$cnt]){
				$oadd++;
				$this->_offsets[$i] = $oadd + $offsets[$cnt];
			} else {
				$cnt++;
				$oadd = 0;
				$this->_offsets[$i] = $offsets[$cnt];
			}
		}
	}

	function _updateOffsets() {
		for($i = 0; $i < count($this->_suggestions); $i++)
			$this->_suggestions[$i]["o"] = $this->_offsets[$this->_suggestions[$i]["o"]];
	}
}

class PspellSpellChecker extends GenericSpellChecker {
	var $_pspell;

	function PspellSpellChecker($text, &$options = array()) {
		parent::GenericSpellChecker($text, $options);

		$config = pspell_config_create($this->_lang, "", "", $this->_charset);
		pspell_config_mode($config, PSPELL_FAST);
		pspell_config_runtogether($config, $this->_runTogether);
		if($this->_personal)
			pspell_config_personal($config, $this->_personal);
		if($this->_repl)
			pspell_config_repl($config, $this->_repl);
		if($this->_customDict && function_exists("pspell_config_dict_dir"))
			pspell_config_dict_dir($this->_customDictLocation);

		$this->_pspell = pspell_new_config($config);
		if($text){
			$this->checkSpelling();
		}
	}

	function checkSpelling() {
		$text = strip_tags($this->_html);
		$text = html_entity_decode($text);
		$text = mb_ereg_replace("[~&\"#{(\[_\\^@)\]=+,.;/:!%*[:space:][:blank:]]"," ", $text);
		$words = mb_split("\s", $text);
		$off = 0;
		foreach($words as $word) {
			$l = mb_strlen($word);
			if(!pspell_check($this->_pspell, $word)) {
				$sug = array_slice(pspell_suggest($this->_pspell, $word), 0, $this->_maxSuggestions);
				$o = $off;
				$s = 0;
				for($i = 0; $i < count($sug); $i++) {
					if(levenshtein($word,$sug[$i]) == 1) {
						$s = $i + 1;
						break;
					}
				}
				$this->_suggestions[] = array("o" => $o, "l" => $l, "s" => $s, "value" => $sug);
			}
			$off += ($l+1);
		}
		$this->_updateOffsets();
	}

	function storeReplacement($wrong, $right){
		pspell_store_replacement($this->_pspell, $wrong, $right);
		pspell_save_wordlist($this->_pspell);
	}

	function addWord($word){
		pspell_add_to_personal($this->_pspell, $word);
		pspell_save_wordlist($this->_pspell);
	}
}

class AspellSpellChecker extends GenericSpellChecker {
	var $_pipes;
	var $_proc;

	function AspellSpellChecker($text, &$options = array()) {
		parent::GenericSpellChecker($text, $options);

		$cmd  = "/usr/bin/aspell -a";
		$cmd .= " --lang=" . escapeshellarg($lang);
		$cmd .= " --sug-mode=fast";
		if($this->_runTogether)
			$cmd .=" --run-together";
		if($this->_personal)
			$cmd .= " --personal=" . escapeshellarg($this->_personal);
		if($this->_repl)
			$cmd .= " --store-repl --repl=" . escapeshellarg($this->_repl);
		if($this->_customDict)
			$cmd .= " --dict-dir=" . escapeshellarg($this->_customDictLocation);

		$desc = array(	0 => array("pipe","r"),
						1 => array("pipe","w"),
						2 => array("file","/dev/null","w"));
		$this->_proc = proc_open($cmd,$desc,$this->_pipes);

		if(!is_resource($this->_proc))
			trigger_error("Problem running aspell");

		stream_set_blocking($this->_pipes[1], false);

		if($text){
			$this->checkSpelling();
			$this->_updateOffsets();
			$this->_cleanUp();
		}
	}

	function checkSpelling() {
		$text = strip_tags($this->_html);
		$text = html_entity_decode($text);
		$text = preg_replace("/[^[:alnum:]']/"," ",$text);

		$words = preg_split("/\s/", $text, -1, PREG_SPLIT_OFFSET_CAPTURE);
		foreach($words as $word){
			fwrite($this->_pipes[0], $word[0]."\n");
			fflush($this->_pipes[0]);
			stream_select($read = array($this->_pipes[1]), $write = NULL, $except = NULL, 0, 200000);
			$str = trim(fread($this->_pipes[1],8192));

			if(empty($str))
				continue;

			$o = $word[1] + 1;
			$l = strlen($word[0]);

			switch($str[0]) {
				case "#":
					$s = 0;
					$this->_suggestions[] = array("o" => $o, "l" => $l, "s" => $s, "value" => array());
					break;
				case "&":
					preg_match("/^& \w+ [0-9]+ ([0-9]+):(.+)$/", $str, $matches);
					$s = $matches[1] + 1;
					$sug = array_slice(explode(", ",$matches[2]), 0, $this->_maxSuggestions);
					$this->_suggestions[] = array("o" => $o, "l" => $l, "s" => $s, "value" => $sug);
					break;
				default:
					continue;
			}
		}
	}

	function storeReplacement($wrong, $right){
		fwrite($this->_pipes[0], "\$\$ra $wrong,$right\n#\n");
		fflush($this->_pipes[0]);
		$this->_cleanUp();
	}

	function addWord($word){
		fwrite($this->_pipes[0], "*$word\n#\n");
		fflush($this->_pipes[0]);
		$this->_cleanUp();
	}

	function _cleanUp() {
		fclose($this->_pipes[0]);
		fclose($this->_pipes[1]);
		proc_close($this->_proc);
	}
}

class GoogleSpellChecker extends GenericSpellChecker {

	function GoogleSpellChecker($text, &$options) {
		parent::GenericSpellChecker($text, $options);

		$this->checkSpelling();
		$this->_updateOffsets();
	}

	function checkSpelling() {
		$words = strip_tags($this->_html);
		$words = html_entity_decode($words);
		$words = preg_replace("/[^[:alnum:]']/"," ",$words);
		$words = "<spellrequest textalreadyclipped=\"0\" ignoredups=\"1\" ignoredigits=\"1\" ignoreallcaps=\"0\"><text>" . $words . "</text></spellrequest>";
		$server = "www.google.com";
		$port = 443;

		$path = "/tbproxy/spell?lang=".$this->_lang."&hl=".$_this->_lang;
		$host = "www.google.com";

		$url = "https://" . $server;
		$page = $path;

		$post_string = $words;

		$header= "POST ".$page." HTTP/1.0 \r\n";
		$header .= "MIME-Version: 1.0 \r\n";
		$header .= "Content-type: application/PTI26 \r\n";
		$header .= "Content-length: ".strlen($post_string)." \r\n";
		$header .= "Content-transfer-encoding: text \r\n";
		$header .= "Request-number: 1 \r\n";
		$header .= "Document-type: Request \r\n";
		$header .= "Interface-Version: Test 1.4 \r\n";
		$header .= "Connection: close \r\n\r\n";
		$header .= $post_string;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 400);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $header);

		$data = curl_exec($ch);
		if(curl_errno($ch))
			error_log(curl_error($ch));
		else
			curl_close($ch);

		$vals = array();
		$index = array();

		$xml_parser = xml_parser_create();
		xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($xml_parser,XML_OPTION_SKIP_WHITE,1);
		xml_parse_into_struct($xml_parser, $data, $vals, $index);
		xml_parser_free($xml_parser);

		foreach($index["c"] as $idx){
			$val = $vals[$idx]["value"];
			$this->_suggestions[] = array_merge($vals[$idx]["attributes"], array("value" => explode("\t",$val)));
		}
	}

	function storeReplacement($wrong, $right){ /* personal dictionaries not supported*/ }

	function addWord($word){ /* personal dictionaries not supported*/ }
}

class SpellChecker {
	var $_options;

	function SpellChecker(&$params = array()) {
		$this->_options = $params;
		if(!is_readable($params["personal"]) && is_writable(dirname($params["personal"]))) {
			$fp = fopen($params["personal"],"w");
			fwrite($fp,"personal_ws-1.1 $params[lang] 0\n");
			fclose($fp);
		}
		if(!is_readable($params["repl"]) && is_writable(dirname($params["repl"]))) {
			$fp = fopen($params["repl"],"w");
			fwrite($fp,"personal_repl-1.1 $params[lang] 0\n");
			fclose($fp);
		}
	}

	function create($text) {	//spell checker factory
		if(!is_object($this)){
			$factory = new SpellChecker();
			return $factory->create($text);
		}

		if(function_exists("pspell_config_create"))
			return new PspellSpellChecker($text, $this->_options);	// got pspell

		elseif(is_executable("/usr/bin/aspell") && function_exists("proc_open") && stristr(ini_get("disable_functions"),"proc_open") === false)
			return new AspellSpellChesker($text, $this->_options);	// there is an aspell executable and we are allowed to run it

		elseif(extension_loaded("curl") && extension_loaded("xml") && stristr(ini_get("disable_functions"),"curl") === false)
			return new GoogleSpellChecker($text, $this->_options);	// fall back to Google

		else
			return "No supported spell checker available on this system. Tried: Pspell library, aspell binary, Google spell.";
	}
}

?>