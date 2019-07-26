<?php

require_once(dirname(__FILE__) . '/config.php');   # Configuration
//require_once(dirname(__FILE__) . '/RTools.parser.php');   # Parsers
//require_once(dirname(__FILE__).'/lib/rtools_client.class.php');

global $rtDebug;

if (isset($rtDebug) && $rtDebug)
{
	error_reporting(E_ALL);
	ini_set("display_errors", "on");
}

# gzdecode needed for opening run output files from server
if (! function_exists('gzdecode'))
{
	function gzdecode($data){
		$g=tempnam('/tmp','ff');
		@file_put_contents($g,$data);
		ob_start();
		readgzfile($g);
		$d=ob_get_clean();
		return $d;
	}
}

class SpecialRTools extends SpecialPage
{
	var $clean_output;
	var $rclient;
	
	function __construct()
	{
		parent::__construct( 'RTools' );
		$this->clean_output = false;
		$this->rclient = new RToolsClient(rtServerURI);
		//$this->getOutput()->addModules( 'ext.RTools' );
	}

	function execute( $par ) {
		global $wgRequest, $wgOut, $wgServer, $wgScriptPath, $wgUser, $wgArticle;

		//$wgOut->addModules( 'ext.RTools' ); // old style, retained for consistency
		$this->getOutput()->addModules( 'ext.RTools' ); // new style
		$this->getOutput()->addHeadItem('script','<script type="text/javascript" language="javascript" src="http://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>');

		//$wgOut->addScript('<script type="text/javascript" language="javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>');
	//	$wgOut->addScript('<script type="text/javascript" language="javascript" src="'.$wgServer.$wgScriptPath.'/extensions/RTools/scripts/scriptaculous-js-1.9.0/lib/prototype.js"></script>');
	//	$wgOut->addScript('<script type="text/javascript" language="javascript" src="'.$wgServer.$wgScriptPath.'/extensions/RTools/scripts/scriptaculous-js-1.9.0/src/scriptaculous.js"></script>');
		//$wgOut->addScript('<link rel="stylesheet" type="text/css" media="screen" href="'.$wgServer.$wgScriptPath.'/extensions/RTools/styles/screen.css"/>');
		//$wgOut->addScript('<link rel="stylesheet" type="text/css" media="print" href="'.$wgServer.$wgScriptPath.'/extensions/RTools/styles/print.css"/>');
		$this->setHeaders();
		
		$sid = $wgRequest->getText('id');
		$actn = $wgRequest->getText('actn');
		$ob = $wgRequest->getText('ob');
		if ($ob)
		 $ob = explode(',',$ob);
 
		try {
			
			// USER MUST BE LOGGED IN!!!
			/*
			if ($wgUser->getId() == 0)
			{
				$this->render_error(wfMessage('error_rtools_user_not_logged_in')->text());
				return;
			}
			*/

			// Delete run?
			if (isset($_POST['delete_run']))
			{
				$this->delete_run($_POST['delete_run']);
				$this->render_error(wfMessage('text_run_job_deleted')->text());
				return;
			}
			// Cancel run?
			elseif (isset($_POST['cancel_run']))
			{
				$this->cancel_run($_POST['cancel_run']);
				$this->render_error(wfMessage('text_run_job_canceled')->text());
				return;
			}
			
			$params = array(
				'sid' => $sid,
				'path' => $wgServer.$wgScriptPath.'/extensions/RTools/',
				'session_output' => rtRunsURI.'/'.str_replace('..','',$sid).'_output.txt.gz',
				'session_errors' => rtRunsURI.'/'.str_replace('..','',$sid).'_errors.txt',			
				'status' => $this->rclient->status($sid, 0),
				'times' => $this->rclient->times($sid),
				'ob' => $ob
				);

			if ($params['status'] == 'completed' || $params['status'] == 'canceled' || $params['status'] == 'timeout')
				$params['complete_time'] = $this->rclient->complete_time($sid);

			if ($wgRequest->getText('embed') && intval($wgRequest->getText('embed')) == 1 || $wgRequest->getText('live') && intval($wgRequest->getText('live')) == 1)
				$embed = true;
			else
				$embed = false;

			if ($code = $wgRequest->getText('code'))
			{					
				isset($_POST['article_id']) ? $aid = $_POST['article_id'] : $aid = 0;					
				isset($_POST['code_name']) ? $code_name = $_POST['code_name'] : $code_name = false;					
				isset($_POST['variable']) ? $variables = $_POST['variable'] : $variables = false;					
				isset($_POST['variable_type']) ? $variable_types = $_POST['variable_type'] : $variable_types = false;					
				isset($_POST['include']) ? $includes = $this->parse_includes($_POST['include']) : $includes = false;					
				isset($_POST['store']) ? $store = intval($_POST['store']) : $store = false;					
				
				if ($variables && $variable_types && isset($_POST['recall_inputs']) && intval($_POST['recall_inputs']) == 1)
					$this->store_inputs(str_replace(rtWikiID, '', $aid), $code_name, $variables, $variable_types);
				
				$token = $this->run_code($code, $aid, $this->is_enabled($wgRequest->getText('graphics')), $variables, $variable_types, $includes, $code_name, $store);
				
				// Redirect to result page or return token
				if ($token && $embed)
				{
					echo $token.";".$_POST['form_name'];
					exit();
				}
				elseif ($token)
				{
					header("Location: ".$wgServer.$wgScriptPath."/index.php/Special:R-tools?id=".$token);
					exit();
				}
			}

			// The actions
			switch ($actn) {
				case 'get_session_json':
					if ($embed) $params['embed'] = true;
					$this->clean_output = true;
					if ($params['status'] == 'completed')
					{						
						$params['plots'] = $this->rclient->plots($sid);
						$o = $this->render_to_string('_output',$params);
					} else
						$o = '';
					$this->output(json_encode(array('status' => $params['status'], 'result' => $o, 'form_name' => $wgRequest->getText('form_name'))));							
					break;
				case 'check_session_status':
					$this->clean_output = true;
					$this->render('_session_status',$params);
					break;
				case 'get_session_output':
					if ($embed) $params['embed'] = true;
					$params['plots'] = $this->rclient->plots($sid);
					$this->clean_output = true;
					
					if (isset($_GET['rl']) && intval($_GET['rl']) > 0)
					{
						$res = $this->render_to_string('_output',$params);
						# Render only if new output has new stuff in it !!!
						if (strlen($res) > intval($_GET['rl']))
							$this->output($res);
						else
							$this->output('');
					}
					else
						$this->render('_output',$params);						
					break;
				default:
					$params['plots'] = $this->rclient->plots($sid);
					$this->render('index', $params);			
			}
							
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			
			// Do some translating
			preg_match_all("/__[^:]*__/", $msg, $matches);
			
			foreach ($matches[0] as $match)
			{
			//	echo $match;
				$key = 'msg_'.str_replace('__','',$match);
			//	echo $key;
				$msg = str_replace($match, wfMessage($key)->text(), $msg);
			}
			$this->render_error($msg);
		}
		
	}
	
	// Render file
	function render($target, $vars=null)
	{
		global $rtDebug;
		
		if (is_array($vars) && !empty($vars)) {
			extract($vars);
		}
		
		ob_start();
		//include 'templates/helpers.php';
		include 'templates/'.$target.'.php';
		$this->output(ob_get_clean());	
	}

	// Render to text
	function render_to_string($target, $vars=null)
	{
		global $rtDebug;
		
		if (is_array($vars) && !empty($vars)) {
			extract($vars);
		}
		
		ob_start();
		include 'templates/'.$target.'.php';
		return ob_get_clean();	
	}
	
	function render_error($msg)
	{
		if ($this->clean_output)
		{
			echo $msg;
			die;
		}
		else
			$this->output('<strong>'.$msg.'</strong>');
	}
	
	function output($content)
	{
		//global $wgOut;
		if ($this->clean_output)
		{
			echo $content;
			die;
		}
		else
			$this->getOutput()->addHTML($content);
			//$this->getOutput()->prependHTML($content);
	}
	
	function store_inputs($article_id, $code_name, $vars, $var_types)
	{
		global $wgUser;
		$user_id  = $wgUser->getId();
		if ($user_id == 0 || empty($article_id) || empty($code_name)) return;
		if ($db = wfGetDB( DB_MASTER ))
			foreach ($vars as $k => $v)
				if ($var_types[$k] != 'password' && $var_types[$k] != 'table')
				{
					switch($var_types[$k])
					{
						case 'checkbox':
							$val = join(';',$v);
							break;
						case 'date':
							$val = $v['year'].'-'.$v['month'].'-'.$v['day'];
							break;
						case 'datetime':
							$val = $v['year'].'-'.$v['month'].'-'.$v['day'].' '.$v['hour'].':'.$v['min'].':'.$v['sec'];
							break;
						default:
							$val = $v;
					}
					$db->query("replace into rtools_inputs values($user_id, $article_id, '".mysqli_real_escape_string($code_name)."', '".mysqli_real_escape_string($k)."','".mysqli_real_escape_string($val)."',NULL);");
				}
	}
			
	function run_code($code, $article_id, $graphics = false, $vars = false, $var_types = false, $includes = false, $code_name = false, $store = false)
	{
		global $wgUser;

		function arrtrim($a)
		{
			return trim(stripslashes($a));
			//return trim(str_replace(array("\n","\t","\r","\v","\f"),array("","","","",""),$a));
		}
					
		$c = '';
		
		$c .= 'wiki_username <- "'.$wgUser->getName() . '"' ."\n";
		
		if ($vars)
			foreach ($vars as $key => $value)
			{	
				// Table 	
				if (isset($var_types[$key]) && $var_types[$key] == 'table')						
				{
					$rows = array();
					foreach($value as $row)
					{
						$tr = array_map('arrtrim', $row);
						// Add extra blank if last is blank, needed for R strsplit
						if($tr[count($tr)-1] == '') $tr[] = '';
						$rows[]= join('/rtt/', $tr);
					}
					$s = join('/rtn/', $rows); 
					$c .= $key . ' <- as.data.frame(t(sapply(strsplit(unlist(strsplit("'.$s.'", split = "/rtn/", fixed=TRUE, useBytes=FALSE)), split = "/rtt/", fixed=TRUE, useBytes=FALSE), c)))'." #_exclude_from_output_\n";
					//	$c .= $key . ' <- "'.preg_replace("/[\n\r]/","",$value).'"'."\n";
				//$c .= $key . ' <- as.data.frame(t(sapply(strsplit(unlist(strsplit(gsub("\t//n", "\t\t//n", "'.str_replace(array("\r","\n"), array("","//n"),$value).'"), split = "//n")), split = "\t"), c)))'."#_exclude_from_output_\n";
				//	$c .= $key . ' <-  as.data.frame(t(sapply(strsplit(unlist(strsplit("'.$value.'", "\n")), "\t"), c)))'."\n";
				}
				elseif (is_array($value))
				{
					// Datetime
					if (isset($value['day']) && isset($value['year']) && isset($value['month']) && isset($value['hour']) && isset($value['min']) && isset($value['sec']))
						$c .= $key . ' <- as.POSIXct("'.$value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['hour'].':'.$value['min'].':'.$value['sec'].'",tz="GMT")' . "\n";						
					else
					// Date
					if (isset($value['day']) && isset($value['year']) && isset($value['month']))
						$c .= $key . ' <- as.Date("'.$value['year'].'-'.$value['month'].'-'.$value['day'].'")' . "\n";						
					// Vector array
					else
						$c .= $key . ' <- c(' . join(',',$value) . ')' . "\n";
				}
				elseif (isset($var_types[$key]) && $var_types[$key] == 'location')
				// Location as vector (lat, long)
					$c .= $key . ' <- c(' . $value . ')' . "\n";
				elseif (isset($var_types[$key]) && ($var_types[$key] == 'text' || $var_types[$key] == 'textbox'))
				// Text (autoquotes)
					$c .= $key . ' <- "' . $value . '"' . "\n";
				// Password (mark to be excluded from the output)
				elseif (isset($var_types[$key]) && $var_types[$key] == 'password')
					$c .= $key . ' <- "' . $value . '"' . "#_exclude_from_output_\n";				
				// Default bulk value (for numbers etc.)
				elseif ($value != '')
					$c .= $key . ' <- ' . $value . "\n";
				// Null value
				else
					$c .= $key . ' <- NULL' . "\n"; 
			}

		// Includes?
		if ($includes)
			foreach ($includes as $include)
				$c .= "\n" . $this->include_code($include['page'], $include['name']) . "\n\n";
				
		$c .= $code;
					
		// check for  malicious javascript and such!!! Prevent XSS-attacks!!!			
		#$this->validate_code($c);

		return $this->run_on_server($c, $code_name, $article_id, $graphics, $store);
	}
	
	function run_on_server($c, $cname, $article_id, $graphics = false, $store = false)
	{
		global $wgUser;
		$username = rtServerUsername;
		$password = rtServerPassword;
		
		$wiki_user = '';
		
		if ($wgUser->getId() > 0)
			$wiki_user = $wgUser->getName();
		
		$IP = $_SERVER['REMOTE_ADDR'];
		
		$token = $this->rclient->new_run($username, $password, $wiki_user, $IP, $article_id, $cname, $c, $graphics, $store);

		return $token;
	}
	
	function delete_run($id)
	{
		global $wgUser;
		$username = rtServerUsername;
		$password = rtServerPassword;
		
		$wiki_user = '';
		
		if ($wgUser->getId() > 0)
			$wiki_user = $wgUser->getName();
		
		$IP = $_SERVER['REMOTE_ADDR'];
		
		return $this->rclient->delete_run($username, $password, $wiki_user, $IP, $id);
	}

	function cancel_run($id)
	{
		global $wgUser;
		$username = rtServerUsername;
		$password = rtServerPassword;
		
		$wiki_user = '';
		
		if ($wgUser->getId() > 0)
			$wiki_user = $wgUser->getName();
		
		$IP = $_SERVER['REMOTE_ADDR'];
		
		return $this->rclient->cancel_run($username, $password, $wiki_user, $IP, $id);
	}
	
	/*
	function run_locally($id, $c)
	{
		// Some paths needed
		$input_file = rtSessionsPath . '/' . $id . '_input.txt';
		$output_file = rtSessionsPath . '/' . $id . '_output.txt';  	
		$pid_file = rtSessionsPath . '/' . $id . '_pid.txt';
		$error_file = rtSessionsPath . '/' . $id . '_errors.txt';
		//$plots_file = rtPlotsPath . '/' . $id . '_plots.png';

		$this->write_file($input_file, $c);
		
		// Then run
		$cmd = rtRPath." --quiet --no-restore --no-save --no-readline < ".$input_file." > ".$output_file;
		
		chdir(rtSessionsPath);
		
		//system(rtRPath." --no-restore --no-save --no-readline < ".$input_file." > ".$output_file." &");

		$locale = 'en_US.UTF-8';
		setlocale(LC_ALL, $locale);
		putenv('LC_ALL='.$locale);
		$pid = shell_exec('nohup '.$cmd.' 2> '.$error_file.' & echo $!');
		
		// Store the pid			
		$this->write_file($pid_file, $pid);	
	}
	*/
/*
	function write_file($input_file, $content)
	{
		if (!$handle = fopen($input_file, 'w'))
			 throw new Exception(wfMessage('error_cannot_create_file')->text().': '.$input_file);
		if (fwrite($handle, $content) === FALSE)
		{
			fclose($handle);
			throw new Exception(wfMessage('error_cannot_write_file')->text().': '.$input_file);
		}
		fclose($handle);
	}
	*/
	/*
	function is_session_running($id)
	{
		$pid_file = rtSessionsPath . '/' . str_replace('..','',$id) . '_pid.txt';
		if (!file_exists($pid_file))
			return false;
		if (! ($pid = file_get_contents($pid_file)))
			 throw new Exception(wfMessage('error_cannot_read_file')->text().': '.$pid_file);	
		$state = array();
		exec("ps $pid", $state);
		if (count($state) >= 2)
			return true;
		else
		{
			// Remove pid file, execution has ended
			system('rm -f '.$pid_file);
			return false;
		}
	}
	*/
	function is_enabled($var)
	{
		if ($var && $var != '0' && $var != 'off' && $var != 'false' && $var != 'no')
			return true;
		else
			return false;
	}

/*
	function validate_code($input)
	{
		// List of forbidden functions and classes
		$invalids = array(
			'system',
			'system2',
			'file',
			'url',
			'gzfile',
			'bzfile',
			'xzfile',
			'unz',
			'pipe',
			'fifo',
			'socketConnection',
			'open',
			'read',
			'readLines',
			'writeLines',
			'scan',
			'write',
			'parse',
			'eval',
			'sink',
			'install'
		);
		
		foreach ($invalids as $invalid)
		{
			$iv = $invalid.'(\..*)?';
			if (preg_match_all("/(= *".$iv."|<- *".$iv."|".$iv." *->|".$iv." *\()/", $input, $matches))
				throw new Exception(wfMessage('error_invalid_code')->text().'<br/>'.wfMessage('text_forbidden_function_found')->text().': '.$invalid);
		  //throw new Exception(wfMessage('error_invalid_code')->text().'<br/>'.join(',',$matches[2]));
		}
	}
	*/
	function include_code($page, $name)
	{
		$dbw = wfGetDB( DB_SLAVE );

		$res = $dbw->select('page',array('page_id'),"page_title='{$page}' AND page_namespace=0 LIMIT 1",__METHOD__);
		foreach( $res as $row ) 
			$page_id = $row->page_id;
		
		if (! $page_id)
			return '# Could not include code: '.$page.', '.$name.'. Page not found!';
		
		$res = $dbw->select('revision',array('rev_text_id'),"rev_page={$page_id} AND rev_deleted=FALSE ORDER BY rev_timestamp DESC LIMIT 1",__METHOD__);
		foreach( $res as $row ) 
			$text_id = $row->rev_text_id;

		$res = $dbw->select('text',array('old_text'),"old_id = {$text_id} LIMIT 1",__METHOD__);
		
		foreach( $res as $row ) 
			$input = $row->old_text;    
					
		$regexp = "<rcode\s[^>]*name=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/rcode>";

		if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER))
			foreach($matches as $match)
				if (count($match) == 4 && trim($match[2]) == trim($name))
					return $match[3];

		return '# Could not include code: '.$page.', '.$name;			
	}
	
	
	// Parse includes string and return an array of code block names and pages to include
	function parse_includes($str)
	{
		// The new way
		$incs = explode('|',$str);
		$i = 0;
		$ret = array();
		while ($i < count($incs))
		{
			$attributes = array();
			# Get all attributes for ONE variable
			while (($aname = $this->attribute_name($incs[$i])) and ! in_array($aname, array_keys($attributes)))
			{
				$attributes[$aname] = $this->attribute_value($incs[$i]);
				$i ++;
			}
			$ret[] = $attributes;
		}
		return $ret;
	}
	
	function attribute_name($str)
	{
		$tmp = explode(':',$str);
		return trim($tmp[0]);
	}

	function attribute_value($str)
	{
		$tmp = explode(':',$str);
		return trim(substr($str,strlen($tmp[0])+1,strlen($str)-strlen($tmp[0])-1));
	}
	
	function validate_code($c)
	{
		$pattern = "(\"[^\"]+\"|'[^']+')";
		preg_match_all($pattern, $c, $matches);
		
		#print_r($matches);
		
		if (empty($matches)) return true;
		
		foreach($matches[0] as $m)
		{
			if (strlen($m) != strlen(strip_tags($m,'<h1><h2><h3><h4><p><strong><hr>')))
				throw new Exception('__prohibited_html_in_code__');
		}
		
	}


}



