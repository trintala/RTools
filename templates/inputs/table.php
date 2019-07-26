<?php
		if ($live)
			$rtoolsScripts[] = '$( document ).on("change","input.tablecell'.$id.'", function(){'.$live.'})';
		
		$str = '<textarea onclick="this.value=\'\'" id="'.$id.'_input" onpaste="mw.RTools.build_table(this, \''.$id.'\',\''.$name.'\')" class="rt_table_input">'.wfMessage('text_paste_table_here')->text().'</textarea>';
		$str .= '<div class="rt_table_preview" id="'.$id.'_table_preview"></div>';
		$str .= '<div class="rt_cancel_table_wrapper"><div onclick="mw.RTools.cancel_table(\''.$id.'\')" class="rt_cancel_table" id="'.$id.'_cancel_table">'.wfMessage('Cancel')->text().'</div></div>';
		echo $str;
	
?>
