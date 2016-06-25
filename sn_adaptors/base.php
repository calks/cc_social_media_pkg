<?php

	abstract class snIntegrationPkgBaseSnAdaptor {
		
		protected $settings;
		protected $authorized;
		
		public $sn_service;
				
		
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
			return true;
		}
		
		/*abstract public function query($api_method, $params);*/
		
		abstract public function getUserInfo();
		
		abstract public function getOAuthStartUrl();
		
		abstract public function getDisplayedName();
		
		abstract public function handleResponse();
		
		public function addPost($message, $name, $link) {}
	}