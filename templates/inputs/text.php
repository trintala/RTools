<?php

	if ($live)
		$rtoolsScripts[] = '$("#'.$id.'_text").change(function(){'.$live.'})';
		
	echo '<input id="'.$id.'_text" style="width: 293px;" type="text" name="variable['.$name.']" value="'.$default.'"/>';
?>