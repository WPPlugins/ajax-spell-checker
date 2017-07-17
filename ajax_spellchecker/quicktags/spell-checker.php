<?php
include "../../../../wp-config.php";
header("Content-type: text/html; charset=".get_option("blog_charset"));
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo get_option("blog_charset") ?>" />
	<title>Check Spelling</title>
	<script language="javascript" type="text/javascript" src="<?php echo get_option("siteurl"); ?>/wp-includes/js/tw-sack.js"></script>
	<script language="javascript" type="text/javascript" src="spell-checker.js"></script>
	<link rel="stylesheet" href="editor_popup.css" />
	<style type="text/css">
		#insert, #cancel, #recheck {
			font: 13px Verdana, Arial, Helvetica, sans-serif;
			height: auto;
			width: auto;
			background-color: transparent;
			background-image: url(../../../../wp-admin/images/fade-butt.png);
			background-repeat: repeat;
			border: 3px double;
			border-right-color: rgb(153, 153, 153);
			border-bottom-color: rgb(153, 153, 153);
			border-left-color: rgb(204, 204, 204);
			border-top-color: rgb(204, 204, 204);
			color: rgb(51, 51, 51);
			padding: 0.25em 0.75em;
		}

		#insert:active, #cancel:active {
			background: #f4f4f4;
			border-left-color: #999;
			border-top-color: #999;
		}

		#spellResults {
			width: 100%;
			height: 100%;
			font-family: 'Courier New',Courier,mono; font-size: 12px;
			border: thin dashed #838c93;
			clear: both;
			overflow: scroll;
		}

		#spellResults .menu {
			font-size: 1em;
			font-weight: normal;
		}

		a.error {
			background: url(../tinymce/mcespell/images/underline.gif) bottom left repeat-x;
			position: relative;
			text-decoration: none;
			color: black;
		}

		a.error:hover {
			color: black;
		}

		.menu {
			position: absolute;
			background: #d3dce3;
			border: thin solid #ccc;
			list-style-type: none;
			margin: 0;
			padding: 0;
			display: none;
			z-index: 1000;
		}

		.on {
			display: block;
		}

		.menu a {
			display: block;
			text-decoration: none;
			color: black;
			background: transparent;
			border: 1px solid #d3dce3;
			margin: 0;
			padding: 0 3px;
		}

		.menu a:hover {
			background: #838c93;
			border: 1px solid black;
			border-width: 1px 0;
		}
	</style>
</head>
<body onload="onLoadInit();" onresize="resizeInputs();">
	<form name="source" onsubmit="saveContent();" action="#">
		<div style="float: left" class="title">Check Spelling </div>

		<div id="status" class="title" style="float: right; font-size: 80%; color: #737c83;"></div>

		<div id="spellResults"></div>

		<div class="mceActionPanel">
			<div style="float: left">
				<input type="button" name="insert" value="Update" onclick="saveContent();" id="insert" />
			</div>

			<div style="float: right">
				<input type="button" name="cancel" value="Cancel" onclick="window.close();" id="cancel" />
			</div>

			<div style="text-align: center">
				<input type="button" name="cancel" value="Check spelling again" onclick="checkSpelling();" id="recheck" />
			</div>
		</div>
	</form>
</body>
</html>
