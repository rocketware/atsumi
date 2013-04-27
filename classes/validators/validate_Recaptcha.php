<?php
class validate_Recaptcha extends validate_AbstractValidator {
	private $privateKey;
	public function __construct($privateKey) {
		$this->privateKey = $privateKey;
	}
	public function getUserIp () {
	
		// cloudflare
		if (array_key_exists('HTTP_CF_CONNECTING_IP', $_SERVER))
			return $_SERVER['HTTP_CF_CONNECTING_IP'];
		
		// proxy
		elseif  (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		
		// direct IP
		elseif  (array_key_exists('REMOTE_ADDR', $_SERVER))
			return $_SERVER['REMOTE_ADDR'];
		
		else return false;
		
	}
	public function validate($data) {
		// We don't care about the data variable, check for...
		if(!isset($_POST['recaptcha_challenge_field']) || !isset($_POST['recaptcha_response_field'])) {
			throw new Exception('Missing required reCAPTCHA field');
		}

		$answer = $this->recaptcha_check_answer($this->privateKey,
							$this->getUserIp(),
							$_POST["recaptcha_challenge_field"], 
							$_POST["recaptcha_response_field"]
						);
		if($answer->is_valid) {
			return true;
		}
		throw new Exception('Invalid CAPTCHA answer');
	}	

	/*
	 * Hacked up and class-ified functions from the recaptchalib.php written by 
	 * 	Mike Crawford
	 * 	Ben Maurer
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 */
	
	/**
	 * The reCAPTCHA server URL's
	 */
	const RECAPTCHA_VERIFY_SERVER = 'www.google.com';

	/**
	 * Encodes the given data into a query string format
	 * @param $data - array of string elements to be encoded
	 * @return string - encoded request
	 */
	function _recaptcha_qsencode ($data) {
	        $req = "";
	        foreach ( $data as $key => $value )
	                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';
	
	        // Cut the last '&'
	        $req=substr($req,0,strlen($req)-1);
	        return $req;
	}
	
		
	/**
	 * Submits an HTTP POST to a reCAPTCHA server
	 * @param string $host
	 * @param string $path
	 * @param array $data
	 * @param int port
	 * @return array response
	 */
	function _recaptcha_http_post($host, $path, $data, $port = 80) {
	        $req = $this->_recaptcha_qsencode ($data);
	        $http_request  = "POST $path HTTP/1.0\r\n";
	        $http_request .= "Host: $host\r\n";
	        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
	        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
	        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
	        $http_request .= "\r\n";
	        $http_request .= $req;

	        $response = '';
	        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
	                die ('Could not open socket');
	        }

	        fwrite($fs, $http_request);

	        while ( !feof($fs) )
	                $response .= fgets($fs, 1160); // One TCP-IP packet
	        fclose($fs);
	        $response = explode("\r\n\r\n", $response, 2);
	
	        return $response;
	}


	/**
	  * Calls an HTTP POST function to verify if the user's guess was correct
	  * @param string $privkey
	  * @param string $remoteip
	  * @param string $challenge
	  * @param string $response
	  * @param array $extra_params an array of extra variables to post to the server
	  * @return ReCaptchaResponse
	  */
	function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $extra_params = array()) {
		if ($privkey == null || $privkey == '') {
			die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
		}
		if ($remoteip == null || $remoteip == '') {
			die ("For security reasons, you must pass the remote ip to reCAPTCHA");
		}

	        //discard spam submissions
	        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
	                $recaptcha_response = new ReCaptchaResponse();
	                $recaptcha_response->is_valid = false;
	                $recaptcha_response->error = 'incorrect-captcha-sol';
	                return $recaptcha_response;
	        }
	        $response = $this->_recaptcha_http_post (self::RECAPTCHA_VERIFY_SERVER, "/recaptcha/api/verify",
	                                          array (
	                                                 'privatekey' => $privkey,
	                                                 'remoteip' => $remoteip,
	                                                 'challenge' => $challenge,
	                                                 'response' => $response
	                                                 ) + $extra_params
	                                          );

	        $answers = explode ("\n", $response [1]);
	        $recaptcha_response = new ReCaptchaResponse();
	        if (trim ($answers [0]) == 'true') {
	                $recaptcha_response->is_valid = true;
	        } else {
	                $recaptcha_response->is_valid = false;
	                $recaptcha_response->error = $answers [1];
	        }
	        return $recaptcha_response;
	}
}


/**
 * A ReCaptchaResponse is returned from recaptcha_check_answer()
 */
class ReCaptchaResponse {
        var $is_valid;
        var $error;
}
?>
