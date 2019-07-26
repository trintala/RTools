<?php


	// Debug or not to debug?
	define('rtDebug',true);

	// Server URI
	define('rtServerURI', 'http://'.''.'/rtools_server/index.php'); // modify to the appropriate OpasnetRTools server url
	define('rtServerUsername', "");
	define('rtServerPassword', "");

	// Path to sessions
	define('rtRunsURI', 'http://'.''.'/rtools_server/runs'); // modify to the appropriate OpasnetRTools server url

	// Path to plots
	define('rtPlotsURI', rtRunsURI);
	
	// Wiki prefix
	define('rtWikiID','test');


	// HTML Safe Key
	define('rtHTMLSafeKey', ''); // shared secret that matches the OpasnetRTools server configuration

?>
