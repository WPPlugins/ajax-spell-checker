/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('mcespell', '');

function TinyMCE_mcespell_getControlHTML(control_name) {
	switch (control_name) {
		case "mcespell":
			var titleSpell = tinyMCE.getLang('lang_spell_button_title');
			var buttons = '<a href="javascript:tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceSpell\')"';
			buttons += ' target="_self" onclick="tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceSpell\');return false;">';
			buttons += '<img id="{$editor_id}_spell" src="{$pluginurl}/images/spell.gif" title="' + titleSpell + '" width="20" height="20"';
			buttons += ' class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" ';
			buttons += 'onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');" /></a>';
			return buttons;
    }

    return "";
}

function TinyMCE_mcespell_execCommand(editor_id, element, command, user_interface, value) {

	// Handle commands
	switch (command) {
		case "mceSpell":
			var template = new Array ();
			template['file'] = '../../plugins/mcespell/spell-checker.php';
			template['width'] =	parseInt(tinyMCE.getParam("spell_checker_width", 500));
			template['height'] = parseInt(tinyMCE.getParam("spell_checker_height", 400));
			tinyMCE.openWindow (template, {editor_id: editor_id, resizable: "yes", scrollbars: "no", inline:"yes"});
			return true;
	}

	// Pass to next handler in chain
	return false;
}
