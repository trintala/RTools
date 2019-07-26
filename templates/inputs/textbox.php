<?php

	if ($live)
		$rtoolsScripts[] = '$("#'.$id.'_textbox").change(function(){'.$live.'})';
		
	echo '<textarea id="'.$id.'_textbox" style="width: 293px; height: 5em;" name="variable['.$name.']">'.$default.'</textarea>';
?>