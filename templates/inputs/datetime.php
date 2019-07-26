<?php
		$default_date = '';
		$hour = intval(ltrim(date('G'),'0'));
		$minutes = intval(ltrim(date('i'),'0'));
		$sec = intval(ltrim(date('s'),'0'));
		
		// Resolve default datetime
		if ($default != '')
		{
			$parts = explode(' ',$default);
			if(count($parts) == 2)
			{
				$default_date = $parts[0];
				$tmp = explode(':',$parts[1]);
	
				if (count($tmp) == 3)
				{
					$hour = intval($tmp[0]);
					$minutes = intval($tmp[1]);
					$sec = intval($tmp[2]);
				}
			}
		}
		
		
		if ($live)
		{
			$rtoolsScripts[] = '$("#'.$id.'_hour").change(function(){'.$live.'})';
			$rtoolsScripts[] = '$("#'.$id.'_min").change(function(){'.$live.'})';
			$rtoolsScripts[] = '$("#'.$id.'_sec").change(function(){'.$live.'})';
		}
		
		ob_start();
	   	include dirname(__FILE__).'/date.php';
    	$date = ob_get_clean();
		
		$time = '-<span class="rtools_time"><select id="'.$id.'_hour"  name="variable['.$name.'][hour]">';
		for ($i = 0; $i < 24; $i ++)
		{
			($hour == $i) ? $sel = 'selected="selected"' : $sel = '';
			$time .= '<option '.$sel.' value="'.$i.'">' . ($i < 10 ? '0'.$i : $i) . '</option>';
		}
		$time .= '</select>:<select id="'.$id.'_min"  name="variable['.$name.'][min]">';
		for ($i = 0; $i < 60; $i ++)
		{
			($minutes == $i) ? $sel = 'selected="selected"' : $sel = '';
			$time .= '<option '.$sel.' value="'.$i.'">' . ($i < 10 ? '0'.$i : $i)  . '</option>';
		}
		$time .= '</select>:<select id="'.$id.'_sec"  name="variable['.$name.'][sec]">';
		for ($i = 0; $i < 60; $i ++)
		{
			($sec == $i) ? $sel = 'selected="selected"' : $sel = '';
			$time .= '<option '.$sel.' value="'.$i.'">' . ($i < 10 ? '0'.$i : $i) . '</option>';
		}
		$time .= '</select></span>';
		
		echo $date . $time;
?>
