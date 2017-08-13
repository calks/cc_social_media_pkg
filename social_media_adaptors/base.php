<?php

	abstract class socialMediaPkgBaseSocialMediaAdaptor extends coreResourceObjectLibrary {
		
		protected $settings;
		protected $authorized;
		protected $listener;
		
		public $social_media_service;
				
		
		public function __construct(array $settings) {
			$this->settings = $settings;
			$this->authorized = false;
		}
		
		public function isAuthorized() {
			return $this->authorized;
		}
		
		public function isUpToDate() {
			return $this->isAuthorized();
		}
		
		public function logout() {
			
		}	

		public function setListener($listener) {
			if (!isset($this->settings['return_url'])) return false;			
			$old_url = $this->settings['return_url'];
			$old_url = preg_replace('/&listener=(?:[a-z0-9\-_]+)/is', '', $old_url);			
			$new_url = "$old_url&listener=$listener";
			$this->settings['return_url'] = $new_url; 
			$this->listener = $listener;
			return true;
		}
		
		abstract public function getUserInfo();
		
		abstract public function getOAuthStartUrl();
		
		abstract public function getDisplayedName();
		
		abstract public function handleResponse();
		
		public function isLoginViaEnabled() {
			$setteing_param_name = $this->getResourceName() . '_login_enabled';
			echo " $setteing_param_name " . coreSettingsLibrary::get("social_media/$setteing_param_name") . ' ';  
			return coreSettingsLibrary::get("social_media/$setteing_param_name");
		}
		
		public function getAuthPopupUrl() {
			return $this->social_media_service->getAuthPopupUrl($this->getResourceName());
		}
		
		public function addPost($message, $name, $link) {}
	}