<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class CloudFlare {
	// Built by po@poism.com based on https://www.cloudflare.com/docs/host-api/
	private $HOST_GW_SERVICE_URL;
	private $HOST_KEY;
	public 	$ENABLE_CLOBBER_UNIQUE_ID;
	public 	$actions;
	public	$fields;
	public 	$returnJson = true;

	function __construct() {
		$this->HOST_GW_SERVICE_URL = 'https://api.cloudflare.com/host-gw.html';
		$this->ENABLE_CLOBBER_UNIQUE_ID = true;
		$this->HOST_KEY = false;

		// List of API Actions
		$this->actions=array(
			"user_create"=>array(
				"title"=>"Create User",
				"description"=>"Create a CloudFlare account for a user. Register you as the host provider.",
				"required"=>array("cloudflare_email","cloudflare_pass"),
				"optional"=>array("cloudflare_username","unique_id")
			),
			"user_lookup"=>array(
				"title"=>"Lookup User",
				"description"=>"Lookup a user by 'cloudflare_email' or by a previously assigned 'unique_id'.",
				"optional"=>array("cloudflare_email","unique_id")
			),
			"user_auth"=>array(
				"title"=>"Authorize User",
				"description"=>"Authorize you as the host provider for an existing user.",
				"required"=>array("cloudflare_email","cloudflare_pass"),
				"optional"=>array("unique_id")
			),
			"zone_list"=>array(
				"title"=>"List Zones",
				"description"=>"List the domains currently active on Cloudflare for the given host.",
				"optional"=>array("zone_name")
			),
			"zone_lookup"=>array(
				"title"=>"Lookup Zone",
				"description"=>"Lookup a Zone by 'user_key' and 'zone_name'.",
				"required"=>array("user_key","zone_name")
			),
			"zone_set"=>array(
				"title"=>"Setup Zone",
				"description"=>"Add a Zone using the CNAME method. Can use your own nameservers.",
				"required"=>array("user_key","zone_name","resolve_to","subdomains")
			),
			"full_zone_set"=>array(
				"title"=>"Setup Zone Fully",
				"description"=>"Add a Zone using the full setup method. Requires Cloudflare nameservers.",
				"required"=>array("user_key","zone_name")
			),
			"zone_delete"=>array(
				"title"=>"Delete Zone",
				"description"=>"Delete a Zone by 'user_key' and 'zone_name'.",
				"required"=>array("user_key","zone_name")
			)
		);


		// List of API Field Definitions
		$this->fields=array(
			"cloudflare_username"=>array(
				"label"=>"Cloudflare Username",
				"type"=>"text"
			),
			"cloudflare_email"=>array(
				"label"=>"Cloudflare Email",
				"type"=>"email"
			),
			"cloudflare_pass"=>array(
				"label"=>"Cloudflare Password",
				"type"=>"password"
			),
			"unique_id"=>array(
				"label"=>"Unique ID",
				"tooltip"=>"A unique string identifying the user, serves as an alias to their Cloudflare account for your system.",
				"type"=>"text"
			),
			"user_key"=>array(
				"label"=>"User Key",
				"tooltip"=>"Unique 32 hex char auth string identifying the Cloudflare user account. Generated from user_create or user_auth.",
				"type"=>"password"
			),
			"zone_name"=>array(
				"label"=>"Zone Name",
				"tooltip"=>"The zone you wish to run CNAMES through, eg. somedomain.com",
				"type"=>"text"
			),
			"resolve_to"=>array(
				"label"=>"Resolve To",
				"tooltip"=>"CNAME to resolve filtered traffic to, eg. cloudflare-resolve-to.somedomain.com",
				"type"=>"text"
			),
			"subdomains"=>array(
				"label"=>"Subdomains",
				"tooltip"=>"Comma-separated string of subdomains for Cloudflare to host eg. www,blog,wordpress",
				"type"=>"text"
			)
		);
		

	}
	function setHostKey($hostKey) {
		$this->HOST_KEY=$hostKey;
	}

	function submitAction($action, $data) {
		// This function replaces all of the other userCreate, userLookup, etc.

		//$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

		if (! array_key_exists($action, $this->actions)) {
			return $this->processResponse(FALSE, "Requested ".$action." action not supported.");
		}

		$required = isset($this->actions[$action]['required']) ? $this->actions[$action]['required'] : [];
		$optional = isset($this->actions[$action]['optional']) ? $this->actions[$action]['optional'] : [];
		//$requiredVals=array_intersect_key($data,$required);
		//$optionalVals=array_intersect_key($data,$optional);

		$fields = array_merge($required, $optional);
		$params = array( 'act'=>$action );
		foreach ($data as $d=>$v) {
			if (!in_array($d, $fields)) continue; //skip irrelevant data
			if (@$this->fields[$d]['type'] == 'email') $params[$d] = filter_var($v, FILTER_SANITIZE_EMAIL);
			elseif (@$this->fields[$d]['type'] == 'text') $params[$d] = filter_var($v, FILTER_SANITIZE_STRING);
			elseif (@$this->fields[$d]['type'] == 'password') $params[$d] = $v;

			if (empty($params[$d])) unset($params[$d]);
		}

		if ( $action=="user_lookup" && empty($required) && count($params)<=1) {
			//user_lookup for instance has no required fields, but at least needs something
			return $this->processResponse(FALSE, "Insufficient fields for ".$action." provided."); 
		}
		if ( !empty( array_diff($required, array_keys($params)) ) ) {
			return $this->processResponse(FALSE, "Required fields for ".$action." were missing.");
		}

		foreach ($optional as $o) { //doubt we need to send null on undefined optional values, but doing it since the sample api did so
			if (!isset($params[$o])) $params[$o] = NULL;
		}
		
		return $this->processResponse( $this->performRequest($params) );
	}

	//
	// FIXME: all of the following API actions are replaced by the previous sendAction function
	//

	function userCreate($cfEmail,$cfPass,$cfUsername='',$uniqueId='') {
	// Create a CloudFlare account for a user. Register you as the host provider.
		$params = array( "act" => "user_create" );
    		// required:
    		$params["cloudflare_email"]     = filter_var($cfEmail, FILTER_SANITIZE_EMAIL);
    		$params["cloudflare_pass"]      = $cfPass;
    		// optional:
		$params["cloudflare_username"]  = empty($cfUsername) ? NULL : filter_var($cfUsername, FILTER_SANITIZE_STRING);
    		$params["unique_id"]            = empty($uniqueId) ? NULL : filter_var($uniqueId, FILTER_SANITIZE_STRING);

		return $this->processResponse( $this->performRequest($params) );
	}

	function userLookup($cfEmail='',$uniqueId='') {
	// Look a user by 'cloudflare_email' or by a previosly assigned 'unique_id'.
		$params = array( "act" => "user_lookup" );
		if (!empty($cfEmail)) {
			$params["cloudflare_email"] = filter_var($cfEmail, FILTER_SANITIZE_EMAIL);
		}
		elseif (!empty($uniqueId)) {
			$params["unique_id"] = filter_var($uniqueId, FILTER_SANITIZE_STRING);
		}
		else {
			return $this->processResponse(FALSE, "Invalid lookup type, must be cloudflare_email or unique_id");
		}

		return $this->processResponse( $this->performRequest($params) );
	}

	function userAuth($cfEmail,$cfPass,$uniqueId=''){
	// Authorize you as the host provider for an existing user.
		$params = array( "act" => "user_auth" );
		$params["cloudflare_email"]     = filter_var($cfEmail, FILTER_SANITIZE_EMAIL);
		$params["cloudflare_pass"]      = $cfPass;
		// optional:
		$params["unique_id"]            = empty($uniqueId) ? NULL : filter_var($uniqueId, FILTER_SANITIZE_STRING);

		return $this->processResponse( $this->performRequest($params) );
	}

	function zoneSet($userKey,$zoneName,$resolveTo,$subdomains) {
	// Setup a zone
		$params = array( "act" => "zone_set" );
		$params["user_key"]             = $userKey;
		$params["zone_name"]            = $zoneName;
		$params["resolve_to"]           = $resolveTo;
		$params["subdomains"]           = $subdomains;

		return $this->processResponse( $this->performRequest($params) );
	}

	function zoneLookup($userKey,$zoneName) {
	// Lookup a Zone by 'user_key' and 'zone_name'.
		$params = array( "act" => "zone_lookup" );
		$params["user_key"]             = $userKey;
		$params["zone_name"]            = $zoneName;

		return $this->processResponse( $this->performRequest($params) );
	}

	function zoneDelete($userKey,$zoneName) {
	// Lookup a Zone by 'user_key' and 'zone_name'.
		$params = array( "act" => "zone_delete" );
		$params["user_key"]             = $userKey;
		$params["zone_name"]            = $zoneName;

		return $this->processResponse( $this->performRequest($params) );
	}

	private function performRequest(& $data, $headers=NULL) {
	// Contact the service. Returns FALSE on error.

		if ( !$this->HOST_KEY ) return FALSE; //fixme add better fail messages

		$data["host_key"] = $this->HOST_KEY;
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->HOST_GW_SERVICE_URL);
		
		if ($headers) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($data) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
		curl_setopt($ch, CURLOPT_TIMEOUT, 20); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_AUTOREFERER,    TRUE);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		
		if (($http_result = curl_exec($ch)) === FALSE) {
			//echo "WARNING: A connectivity error occured while contacting the service.\n";
			//trigger_error(curl_error($ch));
			return FALSE;
		}
		
		curl_close($ch);
		return $http_result;
	}

	private function processResponse($response, $exception='') {
		$result = array();
		if (!empty($exception)) {
			$result["result"] = "error";
			$result["msg"] = $exception;
		}
		else if ($response === FALSE) {
			$result["result"] = "error";
			$result["msg"] = "Failed to get response from service.";
		}
		else {
			$result = json_decode($response);
		}
		return $this->returnJson ? json_encode($result,JSON_PRETTY_PRINT) : $result;
	}
}


?>
