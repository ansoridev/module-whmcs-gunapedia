<?php

function gunapedia_getAPI($params)
{
	require_once(dirname(__FILE__) . "/API.class.php");
	return new \Gunapedia\API1\API($params["API_Secret"]);
}

function gunapedia_getConfigArray()
{
	return
	[
		"FriendlyName" =>
		[
			"Type" => "System",
			"Value" => "GunaPedia - Reseller Domain"
		],
		
		"API_Secret" =>
		[
			"Type" => "text",
			"Size" => "40",
			"Description" => "Enter your API secret here"
		]
	];
}

function gunapedia_GetNameservers($params)
{
	$API = gunapedia_getAPI($params);

	try
	{
		$hasilnya = $API->call("domain/data/info", "POST", [], ["domain={$params['domainname']}"])["data"];
		$nameservernya = [
			"ns1" => $hasilnya['nameserver1'],
			"ns2" => $hasilnya['nameserver2'],
			"ns3" => $hasilnya['nameserver3'],
			"ns4" => $hasilnya['nameserver4'],
			"ns5" => ""
		];
		return $nameservernya;
	}
	catch(\Gunapedia\API1\Exceptions\Exception $e)
	{
		return ["error" => get_class($e) . " - " . $e->getMessage()];
	}
}

function gunapedia_SaveNameservers($params)
{	
	$API = gunapedia_getAPI($params);

	try
	{
		$payload = [
			"domain" => $params["domainname"],
			"ns1" => $params["ns1"],
			"ns2" => $params["ns2"],
			"ns3" => $params["ns3"],
			"ns4" => $params["ns4"],
			"ns5" => ""
		];
		$API->call("domain/update/nameserver", "PUT", [], $payload);
		return ["error" => ""];
	}
	catch(\Gunapedia\API1\Exceptions\Exception $e)
	{
		return ["error" => get_class($e) . " - " . $e->getMessage()];
	}
}

function gunapedia_GetRegistrarLock($params)
{
	$API = gunapedia_getAPI($params);

	try
	{
		return ($API->call("domain/data/info", "POST", [], ["domain={$params['domainname']}"])["data"]["is_locked"]) ? "locked" : "unlocked";
	}
	catch(\Gunapedia\API1\Exceptions\Exception $e)
	{
		return ["error" => get_class($e) . " - " . $e->getMessage()];
	}
}

function gunapedia_SaveRegistrarLock($params)
{
	$API = gunapedia_getAPI($params);

	try
	{
		$API->call("domain/update/locked", "PUT", [], ["domain" => $params['domainname']]);
		return ["error" => ""];
	}
	catch(\Gunapedia\API1\Exceptions\Exception $e)
	{
		return ["error" => get_class($e) . " - " . $e->getMessage()];
	}
}

function gunapedia_ReleaseDomain($params)
{
	return ["error" => "This function still under development!"];
}

function gunapedia_RegisterDomain($params)
{
	$API = gunapedia_getAPI($params);

	try
	{
		$API->call("purchase/domain", "POST", [], ["domain={$params['domainname']}"]);
		
		return ["error" => ""];
	}
	catch(\Gunapedia\API1\Exceptions\Exception $e)
	{
		return ["error" => get_class($e) . " - " . $e->getMessage()];
	}
}

function gunapedia_TransferDomain($params)
{
	return ["error" => "This function still under development!"];
}

function gunapedia_RenewDomain($params)
{
	return ["error" => "This function still under development!"];
}

function gunapedia_GetContactDetails($params)
{
	$API = gunapedia_getAPI($params);

	try
	{
		$hasilnya = $API->call("domain/data/info", "POST", [], ["domain={$params['domainname']}"])["data"];
		$contactnya = [
			"First Name" => ($hasilnya['nameFirst']) ? $hasilnya['nameFirst'] : "GunaPedia",
			"Last Name" => ($hasilnya['nameLast']) ? $hasilnya['nameLast'] : "Domains",
			"Company Name" => $hasilnya['nameFirst'] . ' ' . $hasilnya['nameLast'],
			"Email Address" => "privacy@icann.org",
			"Address 1" => ($hasilnya['address1']) ? $hasilnya['address1'] : "Indonesia",
			"Address 2" => $hasilnya['address2'],
			"City" => ($hasilnya['city']) ? $hasilnya['city'] : "Jakarta",
			"State" => ($hasilnya['state']) ? $hasilnya['state'] : "Jakarta",
			"Postcode" => $hasilnya['zip'],
			"Country" => $hasilnya['country'],
			"Phone Number" => $hasilnya['phone'],
			"Fax Number" => $hasilnya['phone'],
		];
		return ["Registrant" => $contactnya, "Technical" => $contactnya, "Billing" => $contactnya, "Admin" => $contactnya];
	}
	catch(\Gunapedia\API1\Exceptions\Exception $e)
	{
		return ["error" => get_class($e) . " - " . $e->getMessage()];
	}
}

function gunapedia_SaveContactDetails($params)
{
	$API = gunapedia_getAPI($params);

	try
	{
		$contactDetails = $params['contactdetails']['Registrant'];

		$API->call("domain/update/contact", "PUT", [], [
			"domain" => $params['domainname'],
			"nameFirst" => $contactDetails['First Name'],
			"nameLast" => $contactDetails['Last Name'],
			"address1" => $contactDetails['Address 1'],
			"address2" => $contactDetails['Address 2'],
			"city" => $contactDetails['City'],
			"state" => $contactDetails['State'],
			"country" => $contactDetails['Country'],
			"zip" => $contactDetails['Postcode'],
			"phone" => $contactDetails['Phone Number']
		]);

		return ["error" => ""];
	}
	catch(\Gunapedia\API1\Exceptions\Exception $e)
	{
		return ["error" => get_class($e) . " - " . $e->getMessage()];
	}
}

function gunapedia_GetEPPCode($params)
{
	$API = gunapedia_getAPI($params);

	try
	{
		$hasilnya = $API->call("domain/data/info", "POST", [], ["domain={$params['domainname']}"])["data"];
		return ["eppcode" => $hasilnya["eppcode"], "error" => ""];
	}
	catch(\Gunapedia\API1\Exceptions\Exception $e)
	{
		return ["error" => get_class($e) . " - " . $e->getMessage()];
	}
}

function gunapedia_TransferSync($params)
{
	return ["error" => "This function still under development!"];
}

function gunapedia_Sync($params)
{
	$API = gunapedia_getAPI($params);

	try
	{
		$hasilnya = $API->call("domain/data/info", "POST", [], ["domain={$params['domainname']}"])["data"];
		
		$values = [];
		
		// Add in the expiry if we have it
		$values["expirydate"] = $hasilnya["expired"];
		
		if($hasilnya['status'])
		{
			$values["active"] = true;
			$values["expired"] = false;
		}
		else
		{
			$values["expired"] = true;
			$values["active"] = false;
		}
		
		return $values;
	}
	catch(\Gunapedia\API1\Exceptions\Exception $e)
	{
		return ["error" => get_class($e) . " - " . $e->getMessage()];
	}
}