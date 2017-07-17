// EN lang variables

if (navigator.userAgent.indexOf('Mac OS') != -1) {
// Mac OS browsers use Ctrl to hit accesskeys
	var metaKey = 'Ctrl';
}
else {
	var metaKey = 'Alt';
}

tinyMCE.addToLang('',{
lang_spell_button_title: 'Check Spelling',
lang_spell_popup_title: 'Spell Checking Results',
lang_spell_button_recheck: 'Check spelling again'
});
