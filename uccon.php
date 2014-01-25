<?PHP
class HTTPrequest {
	var $response = array("header", "data");
	var $cookie;
	
	/* Sends a GET request to $host for file $path.
	Optional parameter $cookie specifies the cookies to send with the request */

	function sendGET($host, $path, $cookie="") {
		//Initialize cURL, a library for communicating with servers over various protocols including HTTP and HTTPS
		$ch = curl_init($host);

		//Tell cURL what we'd like to request; concatenate the host (e.g. http://google.com) and the path (e.g. /index.html) to form the URL
		curl_setopt($ch, CURLOPT_URL, $host.$path);

		//Set the cookie if it is specified
		if (!$cookie == "") {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}

		//Misc options
		curl_setopt($ch, CURLOPT_HEADER, 1);			//Include the header of the response in the output
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	//Return the output as the return value for curl_exec, as opposed to outputting it directly
		curl_setopt($ch, CURLOPT_GET, 1);				//Specifies we want to make a GET (as opposed to, for example, POST) request
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);	//Ignore SSL certificate errors. Warning! Be careful when using this option
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);	//Ignore SSL certificate errors. Warning! Be careful when using this option
		
		//Execute the GET request, and return the header and the body (store the return value in $response)
		$response = curl_exec($ch);

		//Check for errors
		if (curl_errno($ch)) {
			die("Error: ".curl_error($ch));
		}

		//The header is separated from the body by two carriage feed + newline pairs, so we split the return value at this string
		//The third argument limits the split to produce at most two pieces, so that if the body of the response contains \r\n\r\n, we 
		//don't lose part of the response by splitting it into more than two pieces
		$response = explode("\r\n\r\n", $response, 2);

		//Set the class object's fields as appropriate
		$this->response['header'] = $response[0]."\r\n\r\n"; //Re-append the \r\n\r\n since it is technically part of the header
		$this->response['data'] = $response[1];

		//Parse cookies from the response header automatically
		//Cookies are returned as Set-Cookie headers and must be manually parsed
		$this->cookie = $this->parseCookie($this->response['header']);
	}
	
	/* Sends a POST request to $host for file $path.
	$postData specifies the POST key/value pairs in the form of a single string, e.g. var1=value1&var2=value2&...&varn=valuen
	Optional parameter $cookie specifies the cookies to send with the request.
	Works similarly to sendGET expect where otherwise specified. */
	function sendPOST($host, $URL, $postData, $cookie="") {
		$ch = curl_init($host);
		curl_setopt($ch, CURLOPT_URL, $host.$URL);
		if (!$cookie == "") {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);	//Specify the "postdata" of the request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);					//Specify the type of request as POST (as opposed to, for example, GET)
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			die("Error: ".curl_error($ch));
		}
		$a1 = explode("\r\n\r\n", $response, 2);
		$this->response['header'] = $a1[0]."\r\n";
		$this->response['data'] = $a1[1];
		$this->cookie = $this->parseCookie($this->response['header']);
	}
	
	/* Parse each cookie from a response header.
	Retrieves the cookies by examining each Set-Cookie header string from the response header.
	Updates the cookie field as necessary, replacing cookies that already exist and appending new ones as necessary. */
	function parseCookie($header) {
		//Obtain cookies from the header
		$a1 = explode("Set-Cookie: ", $header);
		for ($i=1;$i<count($a1);$i++) {
			$a2 = explode("; Path", $a1[$i]);
			$a3 = explode("=", $a2[0]);
			$a4 = explode(";", $a3[1]);
			$tempcookiename[count($tempcookiename)] = $a3[0];
			$tempcookievalue[count($tempcookievalue)] = $a4[0];
		}

		//Process existing cookies
		$a1 = explode("; ", $this->cookie);
		for ($i=0;$i<count($a1);$i++) {
			$a2 = explode("=", $a1[$i]);
			$tempcookiename[count($tempcookiename)] = $a2[0];
			$a3 = explode(";", $a2[1]);
			$tempcookievalue[count($tempcookievalue)] = $a3[0];
		}

		//Replace existing cookies and add new ones
		for ($i=0;$i<count($tempcookiename);$i++) {
			$add = true;
			for ($x=0;$x<count($cookiename);$x++) {
				if ($cookiename[$x] == $tempcookiename[$i]) {
					$add = false;
				}
			}
			if ($add == true) {
				$cookiename[count($cookiename)] = $tempcookiename[$i];
				$cookievalue[count($cookievalue)] = $tempcookievalue[$i];
			}
		}

		//Format the cookie as a single string and update the class object field
		for ($i=0;$i<count($cookiename);$i++) {
			$return .= $cookiename[$i]."=".$cookievalue[$i]."; ";
		}
		$return = substr($return, 0, (strlen($return) - 2));
		return $return.";";
	}
}
?>