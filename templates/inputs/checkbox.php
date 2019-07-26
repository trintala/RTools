<?php

		$tmp2 = '';
		
		if ($live)
			$live_cmd = 'onclick="'.$live.'"';
		else
			$live_cmd = '';
		
		# Generate the options array (name, value pairs)
		$opts = array();
		$tmp =  explode(';',$options);

		for ($ci = 0; $ci < count($tmp); $ci += 2)
			$opts[] = array('value' => trim($tmp[$ci]), 'name' => trim($tmp[$ci+1]));
		
		$d = explode(";",$default);
		
		$tmp2 .= '<input type="hidden" name="variable['.$name.'][]" value="NULL" />'."\n";
		
		foreach ($opts as $opt)
		{
			$sel = '';
			RToolsParser::efRCodeMBInArray($opt['value'],$d) ? $sel = 'checked = "checked"' : $sel = '';
			$tmp2 .= '<input '.$live_cmd.' type="checkbox" name="variable['.$name.'][]" '.$sel.' value="'.$opt['value'].'" />'.$opt['name']."<br/>\n";
		}
		
		echo $tmp2;
		
?>
