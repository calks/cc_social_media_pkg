<?php

	class socialMediaPkgFacebookSnAdaptor extends socialMediaPkgBaseSnAdaptor {
		protected $access_token;
		protected $token_created;
		protected $expires_in;
		protected $user_id;
		
		protected $user_object;
		
		protected $application_id;
		protected $application_secret;
		
		protected static $sdk_code_placed = false;		
		
		public function __construct($settings) {			
			parent::__construct($settings);
			$this->application_id = coreSettingsLibrary::get('sn_integration/facebook_login_application_id');
			$this->application_secret = coreSettingsLibrary::get('sn_integration/facebook_login_application_secret');
		}
		
		public function getDisplayedName() {
			return "Facebook";
		}
		
		public function getOAuthStartUrl() {
			$params[] ='client_id=' . $this->application_id;
			$params[] ='redirect_uri=' . urlencode($this->settings['return_url']);
			$params[] ='display=popup';
			$params[] ='scope=email,public_profile';
			
			return 'https://www.facebook.com/dialog/oauth?' . implode('&', $params);			
		}
		
		protected function obtainToken($code) {
						
			$params[] ='client_id=' . $this->application_id;
			$params[] ='client_secret=' . $this->application_secret;
			$params[] ='redirect_uri=' . urlencode($this->settings['return_url']);
			$params[] ='code=' . urlencode($code);  
				
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => 'https://graph.facebook.com/oauth/access_token?' . implode('&', $params),
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,				
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0			
			));
			
			$response = curl_exec($ch);
			curl_close($ch);
						
			if (!$response) return false;
			
			parse_str($response, $response_arr);
									
			if (!isset($response_arr['access_token'])) return false;
			
			$this->access_token = $response_arr['access_token'];
			$this->expires_in = $response_arr['expires'];
			$this->authorized = true;
			$this->token_created = time();

			return true;
		}
		
		public function handleResponse() {
			$this->authorized = false;
			$this->access_token = null;
			$this->user_id = null;
			$this->expires_in = null;
						
			$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
			if ($code) {
				return $this->obtainToken($code);				
			} 
			else {
				return false;
			}
		}
		
		
		public function getUserInfo() {
			
			if (!$this->authorized) return null;
			
			if(!$this->user_object) {
				
				$ch = curl_init();
				curl_setopt_array($ch, array(
					CURLOPT_URL => "https://graph.facebook.com/me/picture?width=1000&access_token=$this->access_token",
					CURLOPT_HEADER => 1,
					CURLOPT_RETURNTRANSFER => 1,				
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0
				));
				
				
				
				preg_match('/location:\s+(?P<avatar_url>.*)\s/isU', curl_exec($ch), $matches);
				$avatar_url = isset($matches['avatar_url']) ? $matches['avatar_url'] : '';

				
				curl_setopt_array($ch, array(
					CURLOPT_URL => "https://graph.facebook.com/me?fields=id,hometown,link,email,first_name,last_name&access_token=$this->access_token",
					CURLOPT_HEADER => 0,
					CURLOPT_RETURNTRANSFER => 1,				
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0
				));
				
				$facebook_user_raw = curl_exec($ch);
				
				curl_close($ch);
				
     			$facebook_user = json_decode($facebook_user_raw);
     							
				$user = new stdClass();
				$user->uid = @$facebook_user->id;
				$user->first_name = @$facebook_user->first_name;
				$user->last_name = @$facebook_user->last_name;
				$user->avatar = $avatar_url;
				$user->profile_url = @$facebook_user->link;
				$user->email = @$facebook_user->email;
				
				$this->user_object = $user;
			}
			
			return $this->user_object;			
			
		}
		
	
	}