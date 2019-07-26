<?php

 #	function remote_file_exists($url)
 #	{
 #		$tmp = get_headers($url); #."?cachekiller=".time());
 #		return (strpos($tmp[0],"404") === false);
 #	}
 
    if (! isset($embed) && isset($times[1]) && ($ran_at = strtotime($times[1])))
    {
 		echo '<span class="ran_at">'.wfMessage('text_ran_at')->text().' '.strftime(wfMessage('date_format')->text(),$ran_at).'</span>';
  	}
  
  
 	function show_hide_links($id, $show = false, $show_text = false, $hide_text = false)
	{
		if (! $show_text)
			$show_text = wfMessage('text_show_code')->text();
		if (! $hide_text)
			$hide_text = wfMessage('text_hide_code')->text();
		
		$ret = '<span class="rtools_code_show_hide_links">';
		$ret .= '<a class="rtools_code_show_hide_link" id="rtools_code_show_button_'.$id.'" '.($show ? 'style="display:none;"' : '').' href="#" onclick="mw.RTools.show_code('.$id.'); return false;">' . $show_text . '</a>';
		$ret .= '<a class="rtools_code_show_hide_link" id="rtools_code_hide_button_'.$id.'" '.($show ? '' : 'style="display:none;"').' href="#" onclick="mw.RTools.hide_code('.$id.'); return false;">' . $hide_text . '</a>';
		$ret .= '</span>';
		return $ret."<br/>\n";
	}

	$errors =  @file_get_contents($session_errors);

#	if (remote_file_exists($session_errors) && ($errors = file_get_contents($session_errors)) && ! empty($errors)) {
	if ($errors !== false && ! empty($errors))
	{
		echo show_hide_links(0, (isset($ob[0]) && $ob[0] == 1), wfMessage('text_show_errors'), wfMessage('text_hide_errors'));
		echo '<div class="rt_code_block rt_errors" '.(isset($ob[0]) && $ob[0] == 1 ? '' : 'style="display:none;"').' id="rtools_code_block_0">';
		echo nl2br($errors).'</div><div class="rt_error_footer_block"></div>';
	}

 	$output_block = false;
 	
 	if ($status == 'deleted')
 		echo '<strong>'.wfMessage('text_run_job_deleted').'</strong>';
 
#	if (remote_file_exists($session_output) && ($op = gzdecode(file_get_contents($session_output))) && ! empty($op))
	$content = @file_get_contents($session_output);
	if ($content !== false && ($op = gzdecode($content)) && ! empty($op))
	{		
		$lines = explode("\n",$op);
		$i = 0;
		$cb = 1;
		(substr($lines[0],2,6) == 'bitmap') ? $draw_all = false : $draw_all = true;
		
		$prev_state = '';
		$state = '';
		
		$html_safe = false;
		$script = false;
		
		$html_safe_key = '<!-- '.md5($sid.rtHTMLSafeKey).' -->';
		$html_safe_end = '<!-- html_safe_end -->';

		foreach ($lines as $line)
		if (! empty($line) && $line != '> ' && ($draw_all || $i++ > 0 && $i < count($lines) - 3))
		{
			# First determine and set the state. Check for html_safe_key.
			$prev_state = $state;
			if (substr($line,0,2) == '> ' || substr($line,0,2) == '+ ')
			{
				$state = 'code';
				$html_safe = false;
			}
			else
			{
				$state = 'output';
				if (strpos($line, $html_safe_key) !== false) $html_safe = true; # Turn HTML safe mode on?
				if (strpos($line, $html_safe_end) !== false){
					$html_safe = false; # Turn HTML safe mode off?
					continue;
				}
				
				# Check for scripts
				if (preg_match("/< *script.*>/", $line) !== false)
					$script = true;
				elseif(preg_match("/< *\/script *>/", $line) !== false)
					$script = false;
					
			}
						
			if ($prev_state != $state)
			{
				if ($prev_state != '') echo "</div>"; # Close block if not first block
				if ($state == 'output') echo '<div class="rt_output_block" id="rtools_output_block_'.($cb-1).'">';
				if ($state == 'code'){
					if (! isset($embed)) echo show_hide_links($cb, (isset($ob[$cb]) && $ob[$cb] == 1));
					echo '<div class="rt_code_block_status" id="rtools_code_block_status_'.$cb.'"></div>';
					echo '<div class="rt_code_block" '.(isset($ob[$cb]) && $ob[$cb] == 1 ? '' : 'style="display:none;"').' id="rtools_code_block_'.$cb++.'">';				
				}
			}

			if ($state == 'code')
				echo htmlspecialchars($line)."<br/>";
			else
				// IF line does NOT include any html and line is not script, then add trailing <br> tag  
				echo ((strip_tags($line) == $line && ! $script) ? $line . '<br/>' : ($html_safe ? $line : htmlspecialchars($line)));
			
			echo "\n";
		}
			
		echo "</div>"; # All done!
	}

	if (! empty($op))
		include dirname(__FILE__).'/_plots.php';
		
	if (isset($status) && ! isset($embed))
		include dirname(__FILE__).'/_footer.php';		
 ?>

