<?php
/*
Plugin Name: Ajax Spell Checker
Plugin URI: http://m0n5t3r.info/work/wordpress-plugins/ajax-spell-checker/
Description: Spell checker for Wordpress using Ajax.
Version: 0.1
Author: m0n5t3r
Author URI: http://m0n5t3r.info/
*/

//an array containing our option names and default values

function as_add_options_page(){
	$as_options = array(
		"as_personal"	=> "wp-content/plugins/ajax_spellchecker/service/dict/custom.en.pws",
		"as_repl"		=> "wp-content/plugins/ajax_spellchecker/service/dict/custom.en.prepl",
		"as_custompath"	=> "wp-content/plugins/ajax_spellchecker/service/dict",
		"as_custom"		=> false,
		"as_lang"		=> "en",
		"as_maxsug"		=> 5,
		"as_runtogether"=> true
	);

	//make sure options exist
	foreach($as_options as $k => $v){
		if(get_option($k) === false){
			add_option($k, $v);
		}
	}

	add_options_page("Ajax Spell Checker Options","Spell Checker",5,"ajax_spellchecker/options.php");
}

function as_tinymce_plugin(&$plugins){
	$plugins[] = "../../../../wp-content/plugins/ajax_spellchecker/tinymce/mcespell";
	return $plugins;
}

function as_tinymce_button(&$buttons){
	$buttons[] = "mcespell";
	return $buttons;
}

function as_fix_tinymce_plugin(){
	global $plugins;
	$tmp = explode(",", $plugins);
	for($i = 0; $i < count($tmp); $i++){
		$tmp[$i] = basename($tmp[$i]);
	}
	$plugins = implode(",", $tmp);
}

function as_rewrite_rules($rules){
	$tmp = explode("\n",$rules);
	$ret = array();
	$ig_rules = "RewriteRule wp-includes/js/tinymce/plugins/mcespell/spell-check-service wp-content/plugins/ajax_spellchecker/service/spell-check-service.php [QSA,L]\n";
	$ig_rules .= 'RewriteRule wp-includes/js/tinymce/plugins/mcespell/(.*) wp-content/plugins/ajax_spellchecker/tinymce/mcespell/$1 [L]';
	foreach($tmp as $rule){
		array_push($ret, $rule);
		if(strstr($rule,"RewriteBase") !== false)
			array_push($ret, $ig_rules);
	}
	$rules = join("\n", $ret);
	return $rules;
}

function as_quicktags() {
	?><script type="text/javascript">
		var tb = document.getElementById('ed_toolbar');
		var spell_btn = document.createElement('input');
		spell_btn.type = 'button';
		spell_btn.className = 'ed_button';
		spell_btn.id = 'ed_spellcheck';
		spell_btn.accesskey = 's';
		spell_btn.value = 'Spelling';
		spell_btn.title = 'Chech Spelling';
		spell_btn.alt = 'Chech Spelling';
		spell_btn.onclick = function() {
			var height = 400;
			var width = 500;
			var x = parseInt(screen.width / 2.0) - (width / 2.0);
			var y = parseInt(screen.height / 2.0) - (height / 2.0);
			var win = window.open("<?php echo get_option("siteurl"); ?>/wp-content/plugins/ajax_spellchecker/quicktags/spell-checker.php", "spellPopup" + new Date().getTime(), "top=" + y + ",left=" + x + ",scrollbars=no,dialog=yes,minimizable=yes,modal=yes,width=" + width + ",height=" + height + ",resizable=yes");
		}
		tb.appendChild(spell_btn);
	</script>
	<?php
}

function as_comment() {
	?><script type="text/javascript">
		var submit = document.getElementById('submit');
		var spell_btn = document.createElement('input');
		spell_btn.type = 'button';
		spell_btn.id = 'comment_spellcheck';
		spell_btn.accesskey = 's';
		spell_btn.value = 'Check spelling';
		spell_btn.title = 'Check Spelling';
		spell_btn.alt = 'Check Spelling';
		spell_btn.style.margin = '0 0.5em';
		spell_btn.onclick = function() {
			var height = 400;
			var width = 500;
			var x = parseInt(screen.width / 2.0) - (width / 2.0);
			var y = parseInt(screen.height / 2.0) - (height / 2.0);
			var win = window.open('<?php echo get_option("siteurl"); ?>/wp-content/plugins/ajax_spellchecker/comment/spell-checker.php', 'spellPopup' + new Date().getTime(), 'top=' + y + ',left=' + x + ',scrollbars=no,dialog=yes,minimizable=yes,modal=yes,width=' + width + ',height=' + height + ',resizable=yes');
		}
		submit.parentNode.appendChild(spell_btn);
	</script><?php
}

add_action("mce_options","as_fix_tinymce_plugin");
add_filter("mce_plugins","as_tinymce_plugin");
add_filter("mce_buttons","as_tinymce_button");
add_filter("mod_rewrite_rules","as_rewrite_rules");
add_action("admin_footer","as_quicktags");
add_action("comment_form","as_comment");

add_action("admin_head","as_add_options_page");

?>