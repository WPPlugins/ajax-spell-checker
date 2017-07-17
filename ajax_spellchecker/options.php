<div class="wrap">
<h2>Spell Checker Options</h2>
<?php
include "service/spell-check-library.php";
$spelltest = SpellChecker::create("");
if(is_string($spelltest)) {
?>
<h3>Sorry, it looks like none of my spell checking backends can work on your system!</h3>
<p><?php echo $spelltest ?></p>
<p>This means that:</p>
<ul>
<li>Pspell support is missing</li>
<li>Either there is no aspell binary on this system, or the function required to run it (proc_open) is disabled or missing</li>
<li>There is no curl, or no xml support on this system, or curl is disabled, so I can't access Google either</li>
</ul>
<?php } else { unset($spelltest); ?>
<form action="options.php" method="post">
<fieldset class="options">
<table class="editform optiontable">

<tr valign="top">
<th scope="row"><label for="as_lang" title="Spell checking language">Language:</label></th>
<td><input type="text" name="as_lang" id="as_lang" class="code" value="<?php echo get_option("as_lang") ?>" size="5" alt="Spell checking language" title="Spell checking language" /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="as_maxsug" title="Maximum number of suggestions">Max. suggestions:</label></th>
<td><input type="text" name="as_maxsug" id="as_maxsug" class="code" value="<?php echo get_option("as_maxsug") ?>" size="5" title="Maximum number of suggestions" alt="Maximum number of suggestions"/></td>
</tr>

<tr valign="top">
<td></td>
<td><label for="as_runtogether"><input type="checkbox" name="as_runtogether" id="as_runtogether"<?php checked(1,get_option("as_runtogether")) ?> value="1" /> Ignore run-together words</label>
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="as_personal" title="Personal dictionary path (relative to blog root)">Personal dictionary location:</label></th>
<td><input type="text" name="as_personal" id="as_personal" class="code" value="<?php echo get_option("as_personal") ?>" size="40" title="Personal dictionary path (relative to blog root)" alt="Personal dictionary path (relative to blog root)"/><br />
The above directory needs to be writable by the web server.
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="as_repl" title="Replacement dictionary path (relative to blog root)">Replacement dictionary location:</label></th>
<td><input type="text" name="as_repl" id="as_repl" class="code" value="<?php echo get_option("as_repl") ?>" size="40" title="Replacement dictionary path (relative to blog root)" alt="Replacement dictionary path (relative to blog root)"/><br />
The above directory needs to be writable by the web server.
</td>
</tr>

<tr valign="top">
<td></td>
<td><label for="as_custom"><input type="checkbox" name="as_custom" id="as_custom"<?php checked(1,get_option("as_custom")) ?> value="1" /> Use custom dictionary</label>
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="as_custompath" title="Custom dictionary location (relative to blog root)">Custom dictionary location:</label></th>
<td><input type="text" name="as_custompath" id="as_custompath" class="code" value="<?php echo get_option("as_custompath") ?>" size="40" title="Custom dictionary location (relative to blog root)" alt="Custom dictionary location (relative to blog root)"/></td>
</tr>

</table>
</fieldset>
<p class="submit">
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="as_lang,as_maxsug,as_runtogether,as_personal,as_repl,as_custom,as_custompath" />
<input type="submit" name="Submit" value="Update Options &raquo;" />
</p>
</form>
<?php } ?>
</div>
