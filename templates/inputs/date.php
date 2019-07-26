<?php
	
	if (isset($options) && $options != '')
		$opts = explode(';',$options);
	else
		$opts = false;
		
	if (isset($opts[0]))
		$min = $opts[0];
	else
		$min = strftime('%Y-%m-%d',strtotime('-10 years'));

	if (isset($opts[1]))
		$max = $opts[1];
	else
		$max = strftime('%Y-%m-%d',strtotime('+10 years'));
	
	$min = explode('-',$min);
	$max = explode('-',$max);

	$datepicker_opts = array(
		'showOn: "button"',
		'buttonImage: "'.$extension_url.'images/calendar.png"',
		'buttonImageOnly: true',
		'minDate: new Date('.$min[0].','.($min[1]-1).','.$min[2].')',
		'maxDate: new Date('.$max[0].','.($max[1]-1).','.$max[2].')',
		'dateFormat: "mm/dd/yy"'
	);

	$rtoolsScripts[] = 'mw.loader.using( "jquery.ui.datepicker", function () {$("#'.$id.'_hidden_date").datepicker({'.join(',',$datepicker_opts).'})})';
	$rtoolsScripts[] = '$("#'.$id.'_hidden_date").change(function(){mw.RTools.set_date($("#'.$id.'_hidden_date").val(), "'.$id.'");})';

	if ($live)
	{
		$rtoolsScripts[] = '$("#'.$id.'_hidden_date").change(function(){'.$live.'})';
		$rtoolsScripts[] = '$("#'.$id.'_day").change(function(){'.$live.'})';
		$rtoolsScripts[] = '$("#'.$id.'_month").change(function(){'.$live.'})';
		$rtoolsScripts[] = '$("#'.$id.'_year").change(function(){'.$live.'})';
	}
	
	if ($default == '')
	{
		$day = date('j');
		$month = date('n');
		$year = date('Y');
	}
	else
	{
		$parts = explode('-',$default);
		if (count($parts) == 3)
		{
			$year = intval($parts[0]);
			$month = intval($parts[1]);
			$day = intval($parts[2]);
		}
	}
	$dret = '<select id="'.$id.'_day" name="variable['.$name.'][day]">';
	for ($i = 1; $i <= 31; $i ++)
	{
		($day == $i) ? $sel = 'selected="selected"' : $sel = '';
		$dret .= '<option '.$sel.' value="'.$i.'">' . $i . '</option>';
	}
	$dret .= '</select>.<select id="'.$id.'_month"  name="variable['.$name.'][month]">';
	for ($i = 1; $i <= 12; $i ++)
	{
		($month == $i) ? $sel = 'selected="selected"' : $sel = '';
		$dret .= '<option '.$sel.' value="'.$i.'">' . $i . '</option>';
	}
	$dret .= '</select>.<select id="'.$id.'_year"  name="variable['.$name.'][year]">';
		
	for ($i =$max[0]; $i >= $min[0]; $i --)
	{
		($year == $i) ? $sel = 'selected="selected"' : $sel = '';
		$dret .= '<option '.$sel.' value="'.$i.'">' . $i . '</option>';
	}
	$dret .= '</select>';
	
	$dret .= '<input type="hidden" id="'.$id.'_hidden_date"/>';
	echo $dret;
?>
