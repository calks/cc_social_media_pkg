<?php


	require_once Application::getSitePath() . '/vendor/autoload.php';
	
	
	class socialMediaPkgGoogleSocialMediaAdaptor extends socialMediaPkgBaseSocialMediaAdaptor {
		protected $access_token;
				
		protected $user_object;
		protected static $api_client;
		
		protected static $sdk_code_placed = false;
		
		protected $client_id;
		protected $client_secret;
		
		
		
		public function __construct($settings) {			
			parent::__construct($settings);	
			$this->client_id = coreSettingsLibrary::get('social_media/google_login_client_id');
			$this->client_secret = coreSettingsLibrary::get('social_media/google_login_client_secret');
		}
		
		public function getDisplayedName() {
			return "Google";
		}
		
		
		public function getOAuthStartUrl() {
			$client = $this->getApiClient();
			return $client->createAuthUrl();
		}
		
		public function handleResponse() {
			$this->authorized = false;
			$this->access_token = null;			
									
			$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
			if ($code) {
				$client = $this->getApiClient();
				
				try {
					$client->authenticate($code);					 
					$this->access_token = $client->getAccessToken();
				}
				catch (Exception $e) {
					$this->social_media_service->setErrorMessage($e->getMessage());
				}
				
				$this->authorized = !empty($this->access_token);
				return $this->authorized;				
			} 
			else {
				return false;
			}
		}
	
		
		public function getUserInfo() {
			if (!$this->authorized) return null;

			if(!$this->user_object) {
			
				try {				
					$client = $this->getApiClient();
					$plus_service = new Google_Service_Plus($client);
					$user_info = $plus_service->people->get("me");
					
					
					$user = new stdClass();
		
					$user->first_name = $user_info->getName()->getGivenName();
					$user->last_name = $user_info->getName()->getFamilyName();
					$user->avatar = $user_info->getImage()->getUrl();
					// google returns small 50x50 avatar and
					// it looks like there is no way to specify desired profile picture size
					// so we'll try to modify an url to make it bigger
					$user->avatar = str_replace('sz=50', 'sz=1000', $user->avatar);
					$emails = $user_info->getEmails();
					
					$user->email = $emails ? $emails[0]->getValue() : null;
					$user->uid = $user_info->getId(); 
					$user->profile_url = $user_info->getUrl();
						
					$this->user_object = $user;
				}
				catch (Exception $e) {
					$error = $e->getMessage();					
					$this->social_media_service->setErrorMessage($error);
					$this->user_object = null;
				}
			
			}
			
			
			return $this->user_object;			
			
		}
		
		
		
		protected function getApiClient() {
			if (!self::$api_client) {
				self::$api_client = new Google_Client();
				
				self::$api_client->addScope(array(
					'https://www.googleapis.com/auth/plus.me',
					'https://www.googleapis.com/auth/userinfo.email',
					'https://www.googleapis.com/auth/userinfo.profile'			
				));

			    self::$api_client->setClientId(trim($this->client_id));
			    self::$api_client->setClientSecret(trim($this->client_secret));
			    self::$api_client->setRedirectUri($this->settings['return_url']);
			}
			
			return self::$api_client;
		}
		
		
	}
