<?php
require_once(dirname(__FILE__).'/xmlrpc.inc');

class RToolsClient {

	private $server;

    function __construct($server_url){
    	$this->server = $server_url;
    }
    
    function new_run($username, $password, $wiki_user, $IP, $article_id, $cname, $code, $graphics = false, $store = false)
    {
		$client = new xmlrpc_client($this->server);
		$client->return_type = 'phpvals';
		$client->request_charset_encoding = 'UTF-8';
		//$message = new xmlrpcmsg("examples.Foo", array(new xmlrpcval(23, "int")));
		$params = array(
			new xmlrpcval($username, "string"),
			new xmlrpcval(md5($password), "string"),
			new xmlrpcval($wiki_user, "string"),
			new xmlrpcval($IP, "string"),
			new xmlrpcval($article_id, "string"),
			new xmlrpcval($cname, "string"),
			new xmlrpcval($code, "string"),
			new xmlrpcval($graphics, "boolean"),
			new xmlrpcval($store, "boolean")
		);
		$message = new xmlrpcmsg("rtools.new_run", $params);
		$resp = $client->send($message);
		if ($resp->faultCode()) 
			throw new Exception('__server_error__:<br/>'.$resp->faultString());
		else
			return $resp->value();
    }
    
    function delete_run($username, $password, $wiki_user, $IP, $id)
    {    	
		$client = new xmlrpc_client($this->server);
		$client->return_type = 'phpvals';
		$client->request_charset_encoding = 'UTF-8';
		//$message = new xmlrpcmsg("examples.Foo", array(new xmlrpcval(23, "int")));
		$params = array(
			new xmlrpcval($username, "string"),
			new xmlrpcval(md5($password), "string"),
			new xmlrpcval($wiki_user, "string"),
			new xmlrpcval($IP, "string"),
			new xmlrpcval($id, "string"),
		);
		$message = new xmlrpcmsg("rtools.delete_run", $params);
		$resp = $client->send($message);
		if ($resp->faultCode()) 
			throw new Exception('__server_error__:<br/>'.$resp->faultString());
		else
			return $resp->value();
    }

    function cancel_run($username, $password, $wiki_user, $IP, $id)
    {    	
		$client = new xmlrpc_client($this->server);
		$client->return_type = 'phpvals';
		$client->request_charset_encoding = 'UTF-8';
		//$message = new xmlrpcmsg("examples.Foo", array(new xmlrpcval(23, "int")));
		$params = array(
			new xmlrpcval($username, "string"),
			new xmlrpcval(md5($password), "string"),
			new xmlrpcval($wiki_user, "string"),
			new xmlrpcval($IP, "string"),
			new xmlrpcval($id, "string"),
		);
		$message = new xmlrpcmsg("rtools.cancel_run", $params);
		$resp = $client->send($message);
		if ($resp->faultCode()) 
			throw new Exception('__server_error__:<br/>'.$resp->faultString());
		else
			return $resp->value();
    }
        
   function status($token, $line_count)
    {
		$client = new xmlrpc_client($this->server);
		$client->return_type = 'phpvals';
		$client->request_charset_encoding = 'UTF-8';
		$params = array(
			new xmlrpcval($token, "string"),
			new xmlrpcval($line_count, "int"),
		);
		$message = new xmlrpcmsg("rtools.status", $params);
		$resp = $client->send($message);
		if ($resp->faultCode()) 
			throw new Exception('__server_error__:<br/>'.$resp->faultString());
		else
			return $resp->value();
    }
    
   function plots($token)
   {
		$client = new xmlrpc_client($this->server);
		$client->return_type = 'phpvals';
		$client->request_charset_encoding = 'UTF-8';
		$params = array(
			new xmlrpcval($token, "string")
		);
		$message = new xmlrpcmsg("rtools.plots", $params);
		$resp = $client->send($message);
		if ($resp->faultCode()) 
			throw new Exception('__server_error__:<br/>'.$resp->faultString());
		else
			return $resp->value();
    }
    
   function complete_time($token)
   {
		$client = new xmlrpc_client($this->server);
		$client->return_type = 'phpvals';
		$client->request_charset_encoding = 'UTF-8';
		$params = array(
			new xmlrpcval($token, "string")
		);
		$message = new xmlrpcmsg("rtools.complete_time", $params);
		$resp = $client->send($message);
		if ($resp->faultCode()) 
			throw new Exception('__server_error__:<br/>'.$resp->faultString());
		else
			return $resp->value();
    }
    
    // Returns times for job
    // 0 => queued_at
    // 1 => ran_at
    // 2 => end_at
   function times($token)
   {
		$client = new xmlrpc_client($this->server);
		$client->return_type = 'phpvals';
		$client->request_charset_encoding = 'UTF-8';
		$params = array(
			new xmlrpcval($token, "string")
		);
		$message = new xmlrpcmsg("rtools.times", $params);
		$resp = $client->send($message);
		if ($resp->faultCode()) 
			throw new Exception('__server_error__:<br/>'.$resp->faultString());
		else
			return $resp->value();
    }
    
}
?>