<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


class CloudFlare {
	private $HOST_GW_SERVICE_URL;
	private $ENABLE_CLOBBER_UNIQUE_ID;
	private $HOST_KEY;
	public 	$returnJson = true;

	function __construct($hostKey) {
		$this->HOST_GW_SERVICE_URL = 'https://api.cloudflare.com/host-gw.html';
		$this->ENABLE_CLOBBER_UNIQUE_ID = true;
		$this->HOST_KEY=$hostKey;
	}


	function userCreate($cfEmail,$cfPass,$cfUsername='',$uniqueId='') {
	// Create a CloudFlare account for a user. Register you as the host provider.
		$params = array( "act" => "user_create" );
    		// required:
    		$params["cloudflare_email"]     = filter_var($cfEmail, FILTER_SANITIZE_EMAIL);
    		$params["cloudflare_pass"]      = $cfPass;
    		// optional:
		$params["cloudflare_username"]  = empty($cfUsername) ? NULL : filter_var($cfUsername, FILTER_SANTIZE_STRING);
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
			echo "WARNING: A connectivity error occured while contacting the service.\n";
			trigger_error(curl_error($ch));
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
