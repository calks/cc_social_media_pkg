<?php

	class socialMediaPkgSocialMediaService extends coreResourceObjectLibrary {
	
		protected $settings;
		protected $domain;
		protected $return_page_url;
		protected $client_script_url;
		protected $error_message;
		protected $listener_name = 'social_media_connect';
				
		protected $authorized_network_name;
		protected $authorized_adaptor;

		
		public function getSocialAccountsTable() {
			return 'user_social_accounts';
		}
		
		public function getListenerName() {
			return $this->listener_name;
		}
		
		public function getListenerInitCode() {
			return '
				<script type="text/javascript">
					var '.$this->getListenerName().' = new SocialMediaOAuth();
				</script>
			';
		}	
		
		public function getListenerJsUrl() {
			return Application::getSiteUrl() . coreResourceLibrary::getStaticPath('/js/social_media_oauth.js');
		}
		
		protected function buildSettings() {
			$settings = array(
				'general' => array(
					'start_page' => Application::getSiteUrl() . Application::getSeoUrl("/social_media_oauth/start"),
					'return_page' => Application::getSiteUrl() . Application::getSeoUrl("/social_media_oauth/return"),
					'client_script_url' => $this->getListenerJsUrl(),
					'domain' => Application::getHost(),
					'page_encoding' => 'utf-8'
				)				
			);

			return $settings;
		}
		
		public function __construct() {
			
			$this->settings = $this->buildSettings();						
			$this->return_page_url = $this->settings['general']['return_page'];
			$this->client_script_url = $this->settings['general']['client_script_url'];

			$this->restoreState();
		}
		
		
		public function logout() {
			if (!$this->isAuthorized()) return;
			$authorized_adaptor = $this->getAdaptor($this->authorized_network_name);
			$authorized_adaptor->logout();
			
			$this->authorized_adaptor = null;
			$this->authorized_network_name = null;
		}
		
		
		public function setErrorMessage($message) {
			$this->error_message = $message;
		}
		
		public function __destruct() {
			$this->saveState();
		}
		
		protected function getSessionName() {
			return "{$this->getName()} Data";
		}
		
		protected function restoreState() {
			if (!session_id()) session_start();
			$session_name = $this->getSessionName();
			
			$this->authorized_network_name = null;
			$this->authorized_adaptor = null;
			
			if (!isset($_SESSION[$session_name]['authorized_adaptor'])) return;
			$this->authorized_network_name = $_SESSION[$session_name]['authorized_network_name'];
			if (!$this->authorized_network_name) return;

						
			$this->authorized_adaptor = unserialize($_SESSION[$session_name]['authorized_adaptor']);
			$this->authorized_adaptor->social_media_service = $this;

			if (!$this->authorized_adaptor->isUpToDate()) {
				$this->authorized_network_name = null;
				$this->authorized_adaptor = null;				
			}			
		}
		
		
		protected function saveState() {
			if (!session_id()) session_start();
			$session_name = $this->getSessionName();
			
			$_SESSION[$session_name] = array(
				'authorized_network_name' => $this->authorized_network_name,
				'authorized_adaptor' => serialize($this->authorized_adaptor)
			);
			
		}
		
		
		protected function getSettings($network_name) {
			return isset($this->settings[$network_name]) ? $this->settings[$network_name] : array(); 
		}
		
		
		protected function getAdaptor($network_name) {
			
			if ($network_name == $this->authorized_network_name) return $this->authorized_adaptor;
			
			$adaptor_class = coreResourceLibrary::getEffectiveClass('social_media_adaptor', $network_name);
			
			if (!$adaptor_class) return null;
			
			$settings = $this->getSettings($network_name);
			$settings['return_url'] = $this->getReturnPageUrl($network_name);
			
			$adaptor = new $adaptor_class($settings); 
			$adaptor->social_media_service = $this;
			return $adaptor;
		}
		
		public function getReturnPageUrl($network_name) {						
			return $this->return_page_url . '?network_name=' . $network_name;
		}
		
		public function getLogoutUrl() {
			return $this->return_page_url . '?logout=1';
		}
		
		
		public function getAuthPopupUrl($network_name) {
			return Application::getSeoUrl("/social_media_oauth/start/$network_name");			
		}
		
		public function getOAuthStartUrl($network_name) {
			$adaptor = $this->getAdaptor($network_name);
			$adaptor->setListener($this->listener_name);
			if (!$adaptor) return '';
			return $adaptor->getOAuthStartUrl();
		}
		
		
		public function processOauthResponse() {
			$network_name = isset($_GET['network_name']) ? $_GET['network_name'] : '';		
			if (!$network_name) die();
			
			$listener = isset($_GET['listener']) ? $_GET['listener'] : '';
			if (!$listener) die();

			$this->authorized_network_name = null;
			$this->authorized_adaptor = null;
			
			$adaptor = $this->getAdaptor($network_name);
			$adaptor->setListener($listener);
			$adaptor->handleResponse();
			
			$user = null;
			if ($adaptor->isAuthorized()) {				
				$this->authorized_network_name = $network_name;
				$this->authorized_adaptor = $adaptor;
				$this->saveState();
			}			
		}
		
		public function renderResponsePage($data=null) {
			
			$response = new stdClass();
			
			if ($data) {
				$response->data = $data;
			}			
			$listener = isset($_GET['listener']) ? $_GET['listener'] : '';
			$user = $this->getAuthorizedUserInfo();			
			if ($user) {
				$response->status = 'ok';
				$response->user = clone $user;
				if ($this->settings['general']['page_encoding'] != 'utf-8') {
					foreach ($response->user as $k=>$v) {
						if(is_object($v)) continue;
						$response->user->$k = iconv($this->settings['general']['page_encoding'], 'UTF-8', $v);										
					}
				}
			}
			else {
				$response->status = 'failed';
				$response->user = null;				
				$response->error = $this->error_message;
			}
			
			$response = json_encode($response);
			
			include 'response_page.php';
		}
		
		public function isAuthorized() {
			return is_object($this->authorized_adaptor);
		}
		
		public function getAuthorizedUserInfo() {
			if (!$this->isAuthorized()) return null;
			
			$adaptor_user_info = $this->authorized_adaptor->getUserInfo();
			if (!$adaptor_user_info) {
				$this->logout();
				return null;
			}
			
			$user_info = clone $adaptor_user_info;
			
			if ($this->settings['general']['page_encoding'] != 'utf-8') {
				foreach ($user_info as $k=>$v) {
					if(is_object($v)) continue;
					$user_info->$k = iconv('UTF-8', $this->settings['general']['page_encoding'], $v);										
				}
			}
			
			
			if ($user_info) {
				$user_info->network_name = $this->authorized_network_name;
				$user_info->network_displayed_name = $this->authorized_adaptor->getDisplayedName();
				$user_info->signature = md5($user_info->network_name . 'saLt sTriNg hERE' . $user_info->uid);
				$user_info->can_post = isset($this->settings[$this->authorized_network_name]['can_post']) ? $this->settings[$this->authorized_network_name]['can_post'] : 0; 
			}
			
			return $user_info;
		}
		
		
		public function getLoginViaEnabledAdaptors() {
			$networks = coreResourceLibrary::findEffective('social_media_adaptor');
			
			$out = array();
			
			foreach ($networks as $network_name=>$resource_data) {				
				if($network_name == 'base') continue;
				$adaptor = $this->getAdaptor($network_name);
				if ($adaptor->isLoginViaEnabled()) {
					$out[$network_name] = $adaptor;
				}
			}
			
			return $out;
					
		
		}
		

		public function addNetworkForSiteUser($user_id, $network_name, $network_uid, $profile_link) {
			$db = Application::getDb();								
			$coupling_table = $this->getSocialAccountsTable(); 
			
			$user_id = (int)$user_id;
			$network_name = addslashes($network_name);
			$network_uid = addslashes($network_uid);
			$profile_link = addslashes($profile_link);
			
			$db->execute("
				INSERT INTO $coupling_table (
					user_id,
					social_network_name,
					uid,
					profile_link
				) VALUES (
					$user_id,
					'$network_name',
					'$network_uid',
					'$profile_link'
				)
			");
				
		}
		
		
		public function findSiteUser($network_name, $network_uid) {
			$db = Application::getDb();
			
			$network_name = addslashes($network_name);
			$network_uid = addslashes($network_uid);
			$user = Application::getEntityInstance('user');
			$coupling_table = $this->getSocialAccountsTable(); 
			$existing_user_id = $db->executeScalar("
				SELECT user_id
				FROM $coupling_table
				WHERE
					social_network_name = '$network_name' and
					uid = '$network_uid'	
			");
			
			if (!$existing_user_id) return null;
			
			return $user->load($existing_user_id);		
		}
		

		
		/*public function addPost($message, $name, $link) {
			if (!$this->isAuthorized()) return null;
			
			if ($this->settings['general']['page_encoding'] != 'utf-8') {
				$message = iconv($this->settings['general']['page_encoding'], 'UTF-8', $message);
				$name = iconv($this->settings['general']['page_encoding'], 'UTF-8', $name);
			}			
			
			return $this->authorized_adaptor->addPost($message, $name, $link);
		}
		
		public function getLikeButton($network_name, $url) {
			if ($network_name == 'odnoklassniki') $network_name = 'mailru';
			$adaptor = $this->getAdaptor($network_name);
			if (method_exists($adaptor, getLikeButton)) {
				return $adaptor->getLikeButton($url);
			}
			return null;			
		}*/
	}