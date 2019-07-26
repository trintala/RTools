<?php
	
	//Avoid unstubbing $wgParser on setHook() too early on modern (1.12+) MW versions, as per r35980
	/*if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
	        $wgHooks['ParserFirstCallInit'][] = 'efRCodeParserInit';
	} else { // Otherwise do things the old fashioned way
	        $wgExtensionFunctions[] = 'efRCodeParserInit';
	}*/	 
	
	//$wgHooks['ParserFirstCallInit'][] = 'efRCodeParserInit';
	//$wgHooks['ParserAfterTidy'][] = 'efRCodeParserAfterTidy';
require_once(dirname(__FILE__) . '/config.php'); 

class RToolsParser {

	public $rtoolsMarkerList;	
	public $rtoolsScripts;
	public $rtoolsGeoLocates;
	
	public function __construct(){
    	$this->rtoolsMarkerList = array();
		$this->rtoolsScripts = array();
		$this->rtoolsGeoLocates = array();
		//global $wgOut;
		//$wgOut->addModules( 'ext.RTools.parser' );
    }

	/*function efRCodeParserInit() {
        global $wgParser;
       	$wgParser->setHook( 'rcode', 'efRCodeRender' );
        return true;
	}*/
	public static function efRCodeParserInit(&$parser) {
		$parser->extRTools = new self();
       	$parser->setHook( 'rcode', [ $parser->extRTools, 'efRCodeRender' ] );
		
		//global $wgOut;
		//$wgOut->addModules( 'ext.RTools.parser' );
		
		//global $wgOut;
		//$wgOut->addWikiText( "This is debug text 1" );
		
        return true;
	}
	
	public static function efRCodeMBInArray($val, $arr) {
		foreach ($arr as $v){
			if (strcmp(trim($v), trim($val)) == 0) return true;
		}	
		return false;	
	 
	}
	public function efRCodeRender( $input, $args, $parser, $frame = null ) {
		global $wgArticlePath;
		//global $rtoolsMarkerList;
		//global $rtoolsScripts;
		global $wgOut, $wgTitle;
		global $wgResourceModules;
		global $wgServer;
		global $wgScriptPath;
		
		//$wgOut->addWikiText( "This is debug text 3" );
		
		//$wgOut->addModules( 'ext.RTools.parser' );
		$parserOutput = $parser->getOutput();
		$parserOutput->addModules( 'ext.RTools.parser' );
		
		$form_class = '';
		
		$escaped_input = htmlspecialchars( $input );

		$markercount = count($this->rtoolsMarkerList);
		$id = time() + $markercount;
		
		$variables = '';
		$hidden_variables = '';
		$variable_types = '';
		
		$action = str_replace('$1','Special:R-tools',$wgArticlePath);
		
		if (! empty($args) && isset($args['label']))
			$submit_label = htmlspecialchars($args['label']);
		else
			$submit_label = wfMessage('text_run_code')->text();
		
		$form_name = 'rtools'.$markercount;
		
		//$button = '<input onclick="document.rtools'.$markercount.'.action=\''.$action.'?id=\'+Math.floor(new Date().getTime() / 1000)+\''.$markercount.'\';document.rtools'.$markercount.'.submit();" type="button" value="'.$submit_label.'"/>';

		$submit_cmd = 'mw.RTools.post_form(document.'.$form_name.')';

		if (! empty($args) && isset($args['live']) && intval($args['live']) == 1)
		{
			$button = '';
			$this->rtoolsScripts[]= $submit_cmd;
		} elseif (! empty($args) && isset($args['embed']) && intval($args['embed']) == 1)
			$button = '<input onclick="'.$submit_cmd.';return false;" type="button" value="'.$submit_label.'"/>';
		else
			$button = '<input onclick="document.rtools'.$markercount.'.submit();" type="button" value="'.$submit_label.'"/>';

		# Set hidden variables
		$hidden = '<input name="code" type="hidden" value="'.$escaped_input.'"  />';
		$hidden .= '<input name="form_name" type="hidden" value="rtools'.$markercount.'"  />';
		$hidden .= '<input name="extension_url" type="hidden" value="'.$wgServer.$wgScriptPath.'/extensions/RTools/"  />';
		$hidden .= '<input name="article_id" type="hidden" value="'.htmlspecialchars(rtWikiID.$wgTitle->getArticleID()).'"  />';
		
		if (! empty($args) && isset($args['name']))
		{
			$code_name = $args['name'];
			$hidden .= '<input name="code_name" type="hidden" value="'.htmlspecialchars($code_name).'"  />';
		}
		else
			$code_name = false;
		
		if (! empty($args) && isset($args['include']))
			$hidden .= '<input name="include" type="hidden" value="'.htmlspecialchars($args['include']).'"  />';

		if (! empty($args) && isset($args['graphics']))
			$hidden .= '<input name="graphics" type="hidden" value="'.htmlspecialchars($args['graphics']).'"  />';

		if (! empty($args) && isset($args['embed']))
		{
			$hidden .= '<input name="embed" type="hidden" value="'.htmlspecialchars($args['embed']).'"  />';
			if (intval($args['embed']) == 1)
				$form_class = 'embed';	
		}
	
		if (! empty($args) && isset($args['live']))
			$hidden .= '<input name="live" type="hidden" value="'.htmlspecialchars($args['live']).'"  />';

		if (! empty($args) && isset($args['store']))
			$hidden .= '<input name="store" type="hidden" value="'.htmlspecialchars($args['store']).'"  />';

		if (! empty($args) && isset($args['recall_inputs']))
			$hidden .= '<input name="recall_inputs" type="hidden" value="'.htmlspecialchars($args['recall_inputs']).'"  />';
		
		if (! empty($args) && isset($args['variables']))
		{
			$vars = $parser->recursiveTagParse( $args['variables'], $frame );
			$tmp = $this->efRCodeParseVariables($form_name, $code_name, $vars, false, (! empty($args) && isset($args['live']) && intval($args['live']) == 1 ? $submit_cmd : false));
			$variables .= $tmp[0];
			$variable_types .= $tmp[1];
		}
			
		if (! empty($args) && isset($args['variables']))
		{
			$vars = $parser->recursiveTagParse( $args['variables'], $frame );
			$tmp = $this->efRCodeParseVariables($form_name, $code_name, $vars, true);
			$hidden_variables .= $tmp[0];
		}

		if ($variables != '')
			$variables = '<div class="rt_varbox">'.$variables.'</div><div class="rt_newline"></div>';

		// Hidden vars and type info outside the varbox!!!
		$variables .= $hidden_variables;
		$variables .= $variable_types;
		
		$form = '<form class="'.$form_class.'" target="_blank" name="'.$form_name.'" action="'.$action.'" method="post">'.$variables.$hidden.$button.'</form>';
		
		if (! empty($args) && isset($args['showcode']) && intval($args['showcode']) == 1)
		{
			$showb_style = 'display: none;';
			$hideb_style = '';
			$block_style = '';
		}
		else
		{
			$showb_style = '';
			$hideb_style = 'display: none;';
			$block_style = 'display: none;';	        	
		}
		
		if (! empty($args) && isset($args['showcode']) && intval($args['showcode']) == -1)
			$code_block = '';
		else
		{
			$code_block = '<a id="rtools_code_show_button_'.$id.'" style="'.$showb_style.'"  href="#" onclick="mw.RTools.show_code('.$id.'); return false;">' . wfMessage('text_show_code')->text() . '</a>';
			$code_block .= '<a id="rtools_code_hide_button_'.$id.'" style="'.$hideb_style.'" href="#" onclick="mw.RTools.hide_code('.$id.'); return false;">' . wfMessage('text_hide_code')->text() . '</a>';
			$code_block .= '<div id="rtools_code_block_'.$id.'"  style="'.$block_style.'" ><pre>' . $escaped_input . '</pre></div>';
		}
		
		$marker = "xx-RTools-marker".$markercount."-xx";
		$this->rtoolsMarkerList[$markercount] = '<table class="rt_varbox_table"><tr><td>' . $form . '<p>' . $code_block . '</p></td><td><div id="rtools'.$markercount.'_results" class="rt_embed_results"></div></td></tr></table>';

		$geoloc = $this->efRCodeParseGeoLocates();

		# Add rtools javascripts to the head (generated by input functions)
		$wgOut->addHeadItem('script','<script type="text/javascript" language="javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>'."\n".'<script type="text/javascript">jQuery( document ).ready( function( $ ) {'.$geoloc.join(";\n",$this->rtoolsScripts).'});</script>');
	
		return $marker;
		//return '<pre>' . $escaped_input . '</pre><p>' . $form . '</p>';
	}
	
	public function efRCodeParseGeoLocates()
	{
		//global $rtoolsGeoLocates;
		
		if (empty($this->rtoolsGeoLocates)) return '';

		$geoloc = '';
   			
		$map_options = array(
			"zoom: 4",
			"center: new google.maps.LatLng(62.8925, 27.678333)",
			"mapTypeId: google.maps.MapTypeId.Terrain"
		);
		
		foreach($this->rtoolsGeoLocates as $id)
			$geoloc .=  "var map_".$id." = new google.maps.Map(document.getElementById('".$id."_map_canvas'),{".join(',',$map_options)."});";

		$geoloc .= "var tmp = setTimeout(function(){if (mw.RTools.geo_locating){";
		foreach ($this->rtoolsGeoLocates as $id)
			$geoloc .= "mw.RTools.cancel_locating('".$id."', map_".$id.");";				
		$geoloc .= "}}, 10000);";
	
		$geoloc .= "if(navigator.geolocation) {mw.RTools.geo_locating = true; navigator.geolocation.getCurrentPosition(function(position){mw.RTools.geo_locating = false; var pos = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);";
		foreach ($this->rtoolsGeoLocates as $id)
			$geoloc .= "mw.RTools.locate('".$id."',map_".$id.", pos);";
		$geoloc .= "}, function(err){ ";
		foreach ($this->rtoolsGeoLocates as $id)
			$geoloc .= "mw.RTools.cancel_locating('".$id."', map_".$id.");";
		$geoloc .= "},  {maximumAge:0, timeout: 10000, enableHighAccuracy:true})} else {";
		foreach ($this->rtoolsGeoLocates as $id)
			$geoloc .= "mw.RTools.cancel_locating('".$id."', map_".$id.");";
		$geoloc .= "}";

		return $geoloc;

	}
		
	// Returns the variables and the types in array
	public function efRCodeParseVariables($form_name, $code_name, $vars, $hidden = false, $live = false)
	{
		//global $rtoolsScripts, $rtoolsGeoLocates;
		global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
		
		$user_id  = $wgUser->getId();
				
		if ($user_id != 0 && $code_name)
		{
			$db =  wfGetDB( DB_SLAVE );
			$articleID = $wgTitle->getArticleID();
		}
		else
			$db = false;
		
		$extension_url = $wgServer.$wgScriptPath.'/extensions/RTools/';
		
		$pattern = '/((^ *)|(\| *))name:/';
		$ret = '';
		$vtypes = '';
		if (preg_match($pattern, $vars))
		{
			// The new way
        	$vars = explode('|',$vars);
        	$var_i = 0;
        	while ($var_i < count($vars))
        	{
	        	$attributes = array();
        		# Get all attributes for ONE variable
	        	while (isset($vars[$var_i]) and ($aname = $this->efRCodeAttributeName($vars[$var_i])) and ! in_array($aname, array_keys($attributes)))
	        	{
	        		$attributes[$aname] = $this->efRCodeAttributeValue($vars[$var_i]);
	        		$var_i ++;
	        	}
	        	if (! isset($attributes['name']))
	        		return wfMessage('error_all_variables_must_have_names')->text();
	        	else
	        		$name = $attributes['name'];
	        	! isset($attributes['type']) ? $type = 'default' : $type = trim($attributes['type']);
	        	! isset($attributes['description']) ? $description = $name : $description = trim($attributes['description']);
	        	! isset($attributes['default']) ? $default = '' : trim($default = $attributes['default']);
	        	! isset($attributes['options']) ? $options = '' : $options = trim($attributes['options']);
	        	! isset($attributes['category_conditions']) ? $cat_conditions = '' : $cat_conditions = trim($attributes['category_conditions']);
	        		
				# Fetch previous inputs or get params?
				if (isset($_GET[$code_name]) && isset($_GET[$code_name][$name]))
					$default = $_GET[$code_name][$name];
				elseif ($db)
				{	
				#	$qres = mysql_query("select input_value from rtools_inputs where user_id = $user_id and page_id = $articleID and code_name = '$name' order by time desc limit 1;",$obcWikiCon);
					$qres = $db->query("select input_value from rtools_inputs where user_id = $user_id and page_id = '$articleID' and code_name = '$code_name' and input_name = '$name' limit 1;");

				#	$qres = $db->select("rtools_inputs",array("input_value"), "user_id = $user_id and page_id = '$articleID' and code_name = '$code_name' and input_name = '$name'",__METHOD__, array('limit'=> 1));
					
				#	print_r($qres);
					
					//if (mysql_num_rows($qres->result))
					
					if ($qres->result && $qres->result->num_rows > 0)
					{
						foreach ($qres as $r)
							$default =  $r->input_value;
						//print_r($qres);
					}
				}
	        	
	        	# Unique id for every input
	        	$id = 'rtools_'.md5($name.microtime());
	        		        	
	        	if (! $hidden && isset($attributes['category']))
	        	{
	        		if ($ret != '')
	        			$ret .= '</div><div id="'.$id.'_varbox" class="rt_varbox '.(isset($attributes['br']) ? 'break_row' : '').'">';
	        		$ret .= '<strong>'.$attributes['category'].'</strong>';
	        		if ($cat_conditions != '')
	        			$ret .= $this->efRCodeParseCategoryConditions($cat_conditions, $form_name, $id);
	        	}
	        		        	
	        	if ($hidden && $type == 'hidden')
	        	{
	       			ob_start();
					include dirname(__FILE__).'/templates/inputs/hidden.php';
	    			$ret .= ob_get_clean();
	        	} elseif (! $hidden) switch($type){
    	    		case 'table':
    	    		case 'filelist':
    	    		case 'datetime':
    	    		case 'date':
	        		case 'selection':
	        		case 'slider':
	        		case 'checkbox':
	        		case 'text':
					case 'textbox':
	        		case 'location':
	        		case 'password':
	        			$ret .= '<p>'.$description.':<br/>';
	        			ob_start();
	   					include dirname(__FILE__).'/templates/inputs/'.$type.'.php';
    					$ret .= ob_get_clean();	
						$ret .= '</p>';			
	        			break;
	        		case 'default': # Default is text type!
	        			$ret .= '<p>'.$description.':<br/>';
	        			ob_start();
	   					include dirname(__FILE__).'/templates/inputs/text.php';
    					$ret .= ob_get_clean();	
						$ret .= '</p>';			
	        			break;
	        		case 'hidden':
	        			$ret .= '';
	        			break;
	        		default:
	        			$ret .= "Unknown var type";
	        	}
        		$vtypes .= '<input type="hidden" name="variable_type['.$name.']" value="'.$type.'"/>';
        	}
		}
		elseif (! $hidden)
		{
			//The old way
        	$vars = explode('|',$vars);
        	for ($i = 0; $i < count($vars); $i+=3)
	        	$ret .= $vars[$i+1].'&nbsp;('.$vars[$i].'):</br><input style="width: 293px;" type="text" name="variable['.$vars[$i].']" value="'.$vars[$i+2].'"/></br>';			
		}
		return array($ret,$vtypes);		
	}
	
	public function efRCodeParseCategoryConditions($conds, $form_name, $id)
	{
		//global $rtoolsScripts;#, $rtoolsCategoryDependencies;
		
		$conds = explode(';',$conds);
		$var_name = array_shift($conds);
		#$rtoolsScripts[] = '$(document.'.$form_name.'.'.$var_name.').change(function(){alert($(this).val());})';
		# Initially hide some var boxes
		$this->rtoolsScripts[] = 'if (jQuery.inArray($(document.forms["'.$form_name.'"].elements["variable['.$var_name.']"]).val(),["'.join('","',$conds).'"]) == -1) mw.RTools.hide_varbox("'.$id.'")';
		$this->rtoolsScripts[] = '$(document.forms["'.$form_name.'"].elements["variable['.$var_name.']"]).change(function(){if (jQuery.inArray($(this).val(),["'.join('","',$conds).'"]) >= 0) mw.RTools.show_varbox("'.$id.'"); else mw.RTools.hide_varbox("'.$id.'");})';
		#if (! isset($rtoolsCategoryDependencies[$form_name])) $rtoolsCategoryDependencies[$form_name] = array();
		#$rtoolsCategoryDependencies[$form_name][$id] = $var_name;
	}
	
	public function efRCodeAttributeName($str)
	{
		$tmp = explode(':',$str);
		return trim($tmp[0]);
	}
	
	public function efRCodeAttributeValue($str)
	{
		$tmp = explode(':',$str);
		return trim(substr($str,strlen($tmp[0])+1,strlen($str)-strlen($tmp[0])-1));
	}

	
	public static function efRCodeParserAfterTidy($parser, &$text) {
        // find markers in $text
        // replace markers with actual output
        // global $rtoolsMarkerList;
		// global $wgOut;
		
        $keys = array();
        $marker_count = count( $parser->extRTools->rtoolsMarkerList );
 
        for ($i = 0; $i < $marker_count; $i++) {
                $keys[] = 'xx-RTools-marker' . $i . '-xx';
        }
 
        $text = str_replace($keys,  $parser->extRTools->rtoolsMarkerList, $text);
		
		//global $wgOut;
		//$wgOut->addWikiText( "This is debug text 4" );
		
        return true;
	}
	
	public static function onDatabaseUpdate( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'rtools_input',
			__DIR__ . '/rtools_input.sql' );
		return true;
	}
}
