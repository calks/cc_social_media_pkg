<?php

	
	class socialMediaPkgVkSocialMediaAdaptor extends socialMediaPkgBaseSocialMediaAdaptor {
		protected $access_token;
		protected $token_created;
		protected $expires_in;
		protected $user_id;
		protected $user_email;
		
		protected $user_object;
		
		protected $application_id;
		protected $secure_key;
		
		protected static $sdk_code_placed = false;
		
		public function __construct($settings) {			
			parent::__construct($settings);	
			$this->application_id = coreSettingsLibrary::get('social_media/vk_login_application_id');
			$this->secure_key = coreSettingsLibrary::get('social_media/vk_login_secure_key');		
		}
		
		public function getDisplayedName() {
			return "VK";
		}
		
		public function isUpToDate() {
			if (!$this->isAuthorized()) return false;
			return (time()-$this->token_created) < $this->expires_in;
		}
		
		public function getOAuthStartUrl() {
			$params[] = 'client_id=' . $this->application_id;
			$params[] = 'display=popup';
			$params[] = 'response_type=code';
			$params[] = 'redirect_uri=' . urlencode($this->settings['return_url']);
			$params[] = 'v=5.52';
			$params[] = 'scope=email'; 
			
			
			return 'https://oauth.vk.com/authorize?' . implode('&', $params);			
		}
		
		protected function obtainToken($code) {
						
			$params[] = 'client_id=' . $this->application_id;
			$params[] = 'client_secret=' . $this->secure_key;
			$params[] = 'redirect_uri=' . urlencode($this->settings['return_url']);			
			$params[] = 'code=' . urlencode($code);  
						
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => 'https://oauth.vk.com/access_token?' . implode('&', $params),
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,				
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0			
			));
			
			$response = curl_exec($ch);
			
			curl_close($ch);
			
			if (!$response) {
				$this->social_media_service->setErrorMessage($this->gettext('Empty response on token request'));
				return false;
			}
			
			$response = json_decode($response);
									
			$error = isset($response->error) ? $response->error : null;
			if ($error) {
				$error_description = isset($response->error_description) ? $response->error_description : null;
				if ($error_description) {
					$error .= ': ' . $error_description;
				}
				$this->social_media_service->setErrorMessage($error);
			} 
			
			if (!isset($response->access_token)) return false;
			
			$this->access_token = $response->access_token;
			$this->expires_in = $response->expires_in;
			$this->user_id = $response->user_id;
			$this->authorized = true;
			$this->user_email = isset($response->email) ? $response->email : null; 
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
			
				$post_data['access_token'] = $this->access_token;
				$post_data['uid'] = $this->user_id;
				$post_data['fields'] = 'photo,photo_big,photo_rec,photo_medium';
				  
							
				$ch = curl_init();
				curl_setopt_array($ch, array(
					CURLOPT_URL => 'https://api.vk.com/method/users.get',
					CURLOPT_HEADER => 0,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_POST => 1,				
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_POSTFIELDS => $post_data							
				));
				
				$response = curl_exec($ch);
				curl_close($ch);
				
				if (!$response) return null;
				
				$response = json_decode($response);
				
				$response = $response->response[0];
				
				$user = new stdClass();
				$user->uid = $response->uid;
				$user->firstname = $response->first_name;
				$user->lastname = $response->last_name;
				$user->avatar = $response->photo_big;
				$user->profile_url = "http://vkontakte.ru/id{$user->uid}";
				$user->email = $this->user_email;
				
				$this->user_object = $user;
			}
			
			
			
			return $this->user_object;			
			
		}
		
		
		public function getLikeButton($url) {
			$out = "";
			if (!self::$sdk_code_placed) {
				$app_id = $this->application_id;			
				$out .= '
					<script type="text/javascript" src="http://userapi.com/js/api/openapi.js?49"></script>

					<script type="text/javascript">
					  VK.init({apiId: '.$app_id.', onlyWidgets: true});
					</script>
				';
				self::$sdk_code_placed = true;
			}
			
			$out .= '
				<div id="vk_like_btn"></div>
				<script type="text/javascript">					
					VK.Widgets.Like("vk_like_btn", {type:"button", pageUrl:"'.$url.'"});
				</script>			
			';
			
			return $out;
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
