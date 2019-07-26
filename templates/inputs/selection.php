<?php

		if ($live)
			$rtoolsScripts[] = '$("#'.$id.'_select").change(function(){'.$live.'})';
		
		$tmp2 = '<select id="'.$id.'_select" name="variable['.$name.']">';
		
		# Generate the options array (name, value pairs)
		$opts = array();
		$tmp =  explode(';',$options);
		for ($ci = 0; $ci < count($tmp); $ci += 2)
			$opts[] = array('value' => trim($tmp[$ci]), 'name' => trim($tmp[$ci+1]));
		
		foreach ($opts as $opt)
		{
			$default == $opt['value'] ? $sel = 'selected = "selected"' : $sel = '';
			$tmp2 .= '<option '.$sel.' value="'.$opt['value'].'">'.$opt['name'].'</option>'."\n";
		}
		
		echo $tmp2 . "</select>";
?>
