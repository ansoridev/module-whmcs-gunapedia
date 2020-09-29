<?php

namespace Gunapedia\API1;

use \Gunapedia\API1\Exceptions\Exception;
use \Gunapedia\API1\Exceptions\AuthenticationException;
use \Gunapedia\API1\Exceptions\AuthorisationException;
use \Gunapedia\API1\Exceptions\FatalAPIException;
use \Gunapedia\API1\Exceptions\UserInputAPIException;
use \Gunapedia\API1\Exceptions\HTTPException;
use \Gunapedia\API1\Exceptions\NotFoundException;

class API
{
	private $_secret;
	
	function __construct($secret)
	{
		$this->_path = dirname(__FILE__) . "/";
		require_once($this->_path . "Exceptions.php");
		
		if(!$secret) throw new AuthenticationException("No API key supplied");
		
		$this->_secret = $secret;
	}

	function __destruct()
	{
		//
	}
	
	public function call($route, $method, $url_params = [], $body = [], $options = [])
	{
		$excludedFromLogging = [];
		
		$options = array_merge(
		[
			"authenticated_request" => true
		], $options);
		
		// Get cURL resource
		$cURL = curl_init();
		
		// Set url
		curl_setopt($cURL, CURLOPT_URL, "https://api.gunapedia.com/{$route}");
		
		// Set method
		curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, $method);
		
		// Set options
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
		
		// Set headers
		$headers = [];
		
		if ($method == "PUT") {
			$headers[] = "Content-Type: application/json";
			$body = json_encode($body);
		} else {
			$headers[] = "Content-Type: application/x-www-form-urlencoded";
			$body = $body[0];
		}
		
		if($options["authenticated_request"])
		{
			$headers[] = "Key: {$this->_secret}";
		}
		
		curl_setopt($cURL, CURLOPT_HTTPHEADER, $headers);
		
		// Set body
		curl_setopt($cURL, CURLOPT_POST, 1);
		curl_setopt($cURL, CURLOPT_POSTFIELDS, $body);
		
		// Send the request & save response to $response
		$response = curl_exec($cURL);
		
		// Got a response?
		if(!$response) throw new HTTPException("No response: " . curl_error($cURL) . " - Code: " . curl_errno($cURL));
		
		// Get the body
		$response = json_decode($response, true);
		$response["http_code"] = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
		
		// Log the result
		\logModuleCall("Gunapedia", "{$method} {$route}", ["params" => $url_params, "body" => $body, "options" => $options, "headers" => $headers], null, ["response" => $response], $excludedFromLogging);
		
		// Check the response code
		$responseonseCode = (string)$response["http_code"];
		if($responseonseCode[0] == 5) throw new FatalAPIException($response);
		if($responseonseCode == 401) throw new AuthenticationException($response);
		if($responseonseCode == 403) throw new AuthorisationException($response);
		if($responseonseCode == 404) throw new NotFoundException($response);
		if($responseonseCode[0] != 2) throw new UserInputAPIException($response);
		
		// Done
		curl_close($cURL);
		
		return $response;
	}
}

