<?php

	
	class snIntegrationPkgTwitterSnAdaptor extends snIntegrationPkgBaseSnAdaptor {
		protected $oauth_token;
		protected $oauth_token_secret;		
		protected $user_id;
		protected $screen_name;
		
		protected $user_object;
		
		protected static $sdk_code_placed = false;
		
		public function __construct($settings) {			
			parent::__construct($settings);			
		}
		
		public function getDisplayedName() {
			return "Twitter";
		}
		
		
		protected function generateSignature($method, $url, $data) {
			
			$url_parts = parse_url($url);
			$base_url = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path']; 
			$url_params = isset($url_parts['query']) ? $url_parts['query'] : '';
			
			$url_params_nvp = array();
			parse_str($url_params, $url_params_nvp);			
			$params_to_code = $url_params_nvp ? $url_params_nvp : array();
			
			$params_to_code = array_merge($params_to_code, $data);
			$tmp = array();
			foreach($params_to_code as $k=>$v) {
				$tmp[rawurlencode($k)] = rawurlencode($v);	
			}
			$params_to_code = $tmp;

			ksort($params_to_code);
			
			$params_encoded = array();
			foreach($params_to_code as $k=>$v) {
				$params_encoded[] = "$k=$v";
			}
			$params_encoded = implode('&', $params_encoded);			
						
			$base_string = strtoupper($method) . '&' . rawurlencode($base_url) . '&' . rawurlencode($params_encoded);
			$signing_key = rawurlencode($this->settings['consumer_secret']) . '&' . rawurlencode($this->oauth_token_secret);
			
			$signature = base64_encode(hash_hmac('sha1', $base_string, $signing_key, true));

			return $signature;
		}
		
		protected function getAuthorizationHeader($method, $url, $data=array(), $include_oauth_callback = false, $test=false) {
			
			$header_data['oauth_consumer_key'] = $this->settings['consumer_key'];
			$header_data['oauth_nonce'] = md5(uniqid());
			$header_data['oauth_signature_method'] = 'HMAC-SHA1';
			$header_data['oauth_timestamp'] = time();
			$header_data['oauth_version'] = '1.0';
			
			if ($test) {
				$header_data['oauth_nonce'] = '99bc1533081193899210f449979f2d51';
				$header_data['oauth_timestamp'] = '1459177010';
				$this->oauth_token = '485750895-eEjFYyDaM2zhLGMzqLEJKHPOJODFOa0dNTGoSH2u';	
				$this->settings['consumer_secret'] = 'gNCL7yx6VncQGgFGXXziNvjWuHO5aVzNqEsQPXcC7AUQSjD8Up';		
			}

			/*
			 * From Twitter Dev Docs:
			 * (https://dev.twitter.com/oauth/reference/post/oauth/request_token)
			 * For OAuth 1.0a compliance this parameter is required.
			 * 
			 * So, this param MUST  be used for request_token request
			 * and MUST NOT be used for other request.
			 * Otherways API will return "Could not authorize you" error in both cases 
			 */
			if ($include_oauth_callback) {
				$header_data['oauth_callback'] = $this->settings['return_url'];
			}			

			if ($this->oauth_token) {
				$header_data['oauth_token'] = $this->oauth_token;	
			}
			
			$header_data['oauth_signature'] = $this->generateSignature($method, $url, array_merge($header_data, $data));
			
			$out = array();
			foreach($header_data as $k=>$v) {
				$out[] = $k . '="' . rawurlencode($v) . '"';	
			}
			
			$header = 'Authorization: OAuth ' . implode(', ', $out); 
			return $header;
		}
		
		public function getOAuthStartUrl() {
						
			$post_url = 'https://api.twitter.com/oauth/request_token';
			
			/* Clear old oauth token if any
			 * to avoid "could not authenticate you" error
			 */
			$this->oauth_token = null;
			
			
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $post_url,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,				
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => array(),
				CURLOPT_HTTPHEADER => array($this->getAuthorizationHeader('POST', $post_url, array(), true)) 
			));
			
			$response = curl_exec($ch);
			
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
			curl_close($ch);						
			if ($http_code != 200) return '';
			
			$response_nvp = array();
			parse_str($response, $response_nvp);
			
			$oauth_callback_confirmed = isset($response_nvp['oauth_callback_confirmed']) ? $response_nvp['oauth_callback_confirmed'] : ''; 
			if ($oauth_callback_confirmed != 'true') return '';
			
			$oauth_token = isset($response_nvp['oauth_token']) ? $response_nvp['oauth_token'] : '';
			
			$session_key = $this->getSessionKey();
			$_SESSION[$session_key] = $oauth_token;

			return "https://api.twitter.com/oauth/authenticate?oauth_token=$oauth_token";
						
		}
		

		
		protected function getSessionKey() {
			return get_class($this) . 'SessionData';
		}
		
				
		protected function obtainToken($oauth_token, $oauth_verifier) {
			
			$post_url = 'https://api.twitter.com/oauth/access_token';
			
			$post_data['oauth_verifier'] = $oauth_verifier;			

			/*
			 * Setting temporary token to make it appear in the authorization header
			 * during request (header is produced by getAuthorizationHeader() function)
			 * 
			 */
			$this->oauth_token = $oauth_token;
			
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $post_url,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,				
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $post_data,
				CURLOPT_HTTPHEADER => array($this->getAuthorizationHeader('POST', $post_url, $post_data)) 
			));
			
			/*
			 * Now removing temporary token.
			 * After auth process finish, permanent access token will be placed here
			 *  
			 */			
			$this->oauth_token = null;
			$this->oauth_token_secret = null;
			
			$response = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
			curl_close($ch);
				
			if ($http_code != 200) return '';
			
			$response_nvp = array();
			parse_str($response, $response_nvp);
				
			if (!isset($response_nvp['oauth_token'])) return false;
			
			$this->oauth_token = $response_nvp['oauth_token'];
			$this->oauth_token_secret = $response_nvp['oauth_token_secret'];
			$this->user_id = $response_nvp['user_id'];
			$this->screen_name = $response_nvp['screen_name'];
			$this->authorized = true;

			return true;
		}


		public function handleResponse() { 
			$this->authorized = false;
			$this->oauth_token = null;
			$this->oauth_token_secret = null;
			$this->screen_name = null;
			$this->user_id = null;

			$oauth_token = isset($_REQUEST['oauth_token']) ? $_REQUEST['oauth_token'] : '';
			$oauth_verifier = isset($_REQUEST['oauth_verifier']) ? $_REQUEST['oauth_verifier'] : '';

			$session_key = $this->getSessionKey();			
			$stored_oauth_token = isset($_SESSION[$session_key]) ? $_SESSION[$session_key] : 'null';

			if ($oauth_token != $stored_oauth_token) return false;
			
			return $this->obtainToken($oauth_token, $oauth_verifier);
		}
	
		
		public function getUserInfo() { 
			if (!$this->authorized) return null;
			
			if(!$this->user_object) {
			
				$params[] = "user_id=$this->user_id";
				$get_url = 'https://api.twitter.com/1.1/users/lookup.json?' . implode('&', $params);
							
				$ch = curl_init();
				curl_setopt_array($ch, array(
					CURLOPT_URL => $get_url,
					CURLOPT_HEADER => 0,
					CURLOPT_RETURNTRANSFER => 1,									
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_HTTPHEADER => array($this->getAuthorizationHeader('GET', $get_url))												
				));
				
				$response = curl_exec($ch);
				
				
				curl_close($ch);
				
				if (!$response) return null;
				
				$response = json_decode($response);
				
				$response = $response[0];
				
				$user = new stdClass();
				$user->uid = $response->id;
				
				$name_parts = explode(' ', $response->name);				
				$user->first_name = @array_shift($name_parts);
				$user->last_name = implode(' ', $name_parts);
				$user->avatar = $response->profile_image_url;
				$user->profile_url = "http://twitter.com/$response->screen_name";
				$user->email = null;
				
				$this->user_object = $user;
			}
			
						
			return $this->user_object;			
			
		}		
		
	}
	
