<?php
/************************************************************************/
/* QChecker (former AChecker)											*/
/* AChecker - https://github.com/inclusive-design/AChecker				*/
/************************************************************************/
/* Inclusive Design Institute, Copyright (c) 2008 - 2015                */
/* RELEASE Group And PT Innovation, Copyright (c) 2015 - 2016			*/
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

/**
* QChecker Default Theme
* @author Achecker
* @author Joel Carvalho
* @version 1.0 2015.03.16
*/

if (!defined('AC_INCLUDE_PATH')) { exit; }

global $languageManager, $_my_uri;

if($languageManager->getNumEnabledLanguages() > 1) {
?>

<div align="center" id="lang" style="clear: both;"><br />
<?php

	if ($languageManager->getNumEnabledLanguages() > 5) {
		echo '<form method="get" action="'.htmlspecialchars($_my_uri, ENT_QUOTES).'">';
		echo '<label for="lang" style="display:none;">'._AC('translate_to').' </label>';
		$languageManager->printDropdown($_SESSION['lang'], 'lang', 'lang');
		echo ' <input type="submit" name="submit_language" class="button" value="'._AC('translate').'" />';
		echo '</form>';
	} else {
		echo '<small><label for="lang">'._AC('translate_to').' </label></small>';
		$languageManager->printList($_SESSION['lang'], 'lang', 'lang', htmlspecialchars($_my_uri));
	}
?>
</div><br /><br />
<?php } ?>

</div> <!--  end center-content div -->

<div class="bottom" style="padding-top:20px">
	<small>
		<span style="padding-top:10px"><?php echo _AC("qchecker_copyright"); ?></span>
	</small><!--  bottom for liquid-round theme -->
</div> <!-- end liquid-round div -->

<script language="javascript" type="text/javascript">
//<!--
var selected;
function rowselect(obj) {
	obj.className = 'selected';
	if (selected && selected != obj.id)
		document.getElementById(selected).className = '';
	selected = obj.id;
}
function rowselectbox(obj, checked, handler) {
	var functionDemo = new Function(handler + ";");
	functionDemo();

	if (checked)
		obj.className = 'selected';
	else
		obj.className = '';
}
//-->
</script>
<div align="center" style="margin-top:10px;clear:both;margin-left:auto; width:15em;margin-right:auto;">
	<a href="documentation/web_service_api.php" title="<?php echo _AC("web_service_api"); ?>" target="_new"><?php echo _AC('web_service_api'); ?></a>
</div>
</body>
</html>

<?php
// Timer, calculate how much time to load the page
// starttime is in include/header.inc.php
global $starttime;
$mtime = microtime(); 
$mtime = explode(" ", $mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; 
$totaltime = ($endtime - $starttime); 

if (defined('AC_DEVEL') && AC_DEVEL) {
	debug(TABLE_PREFIX, 'TABLE_PREFIX');
	debug(DB_NAME, 'DB_NAME');
	debug($totaltime. ' seconds.', "TIME USED"); 
	debug($_SESSION);
}
// Timer Ends

?>
