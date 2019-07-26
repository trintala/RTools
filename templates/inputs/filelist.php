<?php
	$dbr =& wfGetDB(DB_SLAVE);
    $res = $dbr->select(
        array('image'),
        array('img_name','img_media_type','img_user_text','img_description', 'img_size',
              'img_timestamp','img_major_mime','img_minor_mime'),
        '',
        '',
        array('ORDER BY' => 'img_timestamp')
        );
    if ($res === false)
        return array();

    // Convert the results list into an array.
    $list = array();
    #$prefix = get_prefix_from_page_name($pagename);
    #$exam_page = pagename_is_exam_page($prefix);
    while ($x = $dbr->fetchObject($res)) {
		if (! $options || $options == '')
        	$list[] = $x;
        else
        {
        	$m = preg_match('/'.$options.'/i', $x->img_name);
        	if (! empty($m))
        		$list[] = $x;
    	}
    }

    // Free the results.
    $dbr->freeResult($res);

	$tmp = array();
	foreach ($list as $li)
	{
		$image = wfFindFile($li->img_name);
		$url_parts = explode('/', $image->getUrl());
		if ($ii = array_search('images', $url_parts))
			$url = join('/',array_slice($url_parts, $ii+1));
		else
			$url = $image->getUrl();
		$tmp[] = "'".$url."'";
		$tmp []= $li->img_name;
	}

	if ($live)
		$rtoolsScripts[] = '$("#'.$id.'_filelist").change(function(){'.$live.'})';
	
	$tmp2 = '<select id="'.$id.'_filelist" name="variable['.$name.']">';
	
	# Generate the options array (name, value pairs)
	$opts = array();

	for ($ci = 0; $ci < count($tmp); $ci += 2)
		$opts[] = array('value' => $tmp[$ci], 'name' => $tmp[$ci+1]);
	
	foreach ($opts as $opt)
	{
		$default == $opt['value'] ? $sel = 'selected = "selected"' : $sel = '';
		$tmp2 .= '<option '.$sel.' value="'.$opt['value'].'">'.$opt['name'].'</option>'."\n";
	}
	
	echo $tmp2 . "</select>";
?>
