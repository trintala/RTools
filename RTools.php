<?php
# OBSOLETE
# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/RTools/RTools.php" );
EOT;
        exit( 1 );
}
 
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'RTools',
	'author' => 'Einari Happonen',
	'url' => 'http://en.opasnet.org/w/SpecialPage:RTools',
	'description' => 'RTools - Opasnet R interface',
	'descriptionmsg' => 'Opasnet R interface',
	'version' => '0.0.6',
);
 
$dir = dirname(__FILE__) . '/';
 
$wgAutoloadClasses['SpecialRTools'] = $dir . 'RTools_body.php'; # Tell MediaWiki to load the extension body.
//$wgAutoloadClasses['OpasnetBaseTags'] = $dir . 'OpasnetBase.tags.php';   #implements tags
$wgExtensionMessagesFiles['RTools'] = $dir . 'RTools.i18n.php';
$wgExtensionMessagesFiles['RToolsAlias'] = $dir . 'RTools.alias.php';
$wgSpecialPages['RTools'] = 'SpecialRTools'; # Let MediaWiki know about your new special page.

$wgResourceModules['ext.RTools.parser'] = array(
        // JavaScript and CSS styles. To combine multiple file, just list them as an array.
        'position' => 'top',
        'scripts' => 'modules/parser.js',
        'styles' => 'modules/screen.css',
 
        // When your module is loaded, these messages will be available through mw.msg()
      //  'messages' => array( 'myextension-hello-world', 'myextension-goodbye-world' ),
 
        // If your scripts need code from other modules, list their identifiers as dependencies
        // and ResourceLoader will make sure they're loaded before you.
        // You don't need to manually list 'mediawiki' or 'jquery', which are always loaded.
        //'dependencies' => array( 'jquery' ),
 
        // ResourceLoader needs to know where your files are; specify your
        // subdir relative to "/extensions" (or $wgExtensionAssetsPath)
        'localBasePath' => dirname( __FILE__ ),
        'remoteExtPath' => 'RTools'
);

$wgResourceModules['ext.RTools'] = array(
        // JavaScript and CSS styles. To combine multiple file, just list them as an array.
        'position' => 'top',
        'scripts' => 'modules/index.jquery.js',
        'styles' => array('modules/screen.css'  => array( 'media' => 'screen' ),
        						'modules/print.css'  => array( 'media' => 'print' )),
        'localBasePath' => dirname( __FILE__ ),
        'remoteExtPath' => 'RTools'
);

require_once($dir . 'config.php');   # Configuration
require_once($dir . 'RTools.parser.php');   # Parsers

?>