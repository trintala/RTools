<?php

		
		$tmp = explode(';',$options);
		isset($tmp[0]) ? $min = (double)$tmp[0] : $min = false;		
		isset($tmp[1]) ? $max = (double)$tmp[1] : $max = false;		
		isset($tmp[2]) ? $step = (double)$tmp[2] : $step = false;		
		
		$slider_opts = array();

		$slider_opts[] = 'slide: function( event, ui ) {$( "#'.$id.'_input" ).val( ui.value );}';

		if ($default && $default != '')
			$slider_opts[] = 'value:'.$default;

		if ($min)
			$slider_opts[] = 'min:'.$min;

		if ($max)
			$slider_opts[] = 'max:'.$max;

		if ($step)
			$slider_opts[] = 'step:'.$step;

		if ($live)
			$slider_opts[] = 'change: function( event, ui ) {'.$live.'}';

		$rtoolsScripts[] = 'mw.loader.using( "jquery.ui.slider", function () {$("#'.$id.'_slider").slider({'.join(',',$slider_opts).'})})';
		
		echo '<table style="width: 100%;"><tr><td style="padding-right: 10px; width: 4em;"><input readonly="readonly" size="4" id="'.$id.'_input" value="'.$default.'" name="variable['.$name.']"></input></td><td><div id="'.$id.'_slider"></div></td></tr></table>';
		
?>