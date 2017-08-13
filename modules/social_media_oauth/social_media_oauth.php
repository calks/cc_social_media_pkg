<?php

	class socialMediaPkgSocialMediaOauthModule extends coreBaseModule {
		
		protected function getSocialMediaService() {
			$social_media_service_class = coreResourceLibrary::getEffectiveClass('service', 'social_media');
			return $social_media_service_class ? new $social_media_service_class() : null;
		}
		
		
		protected function taskStart($params=array()) {			
			try {
				$social_media_service = $this->getSocialMediaService();
				$social_media_service->logout();
				$network_name = @array_shift($params);
				
				$popup_link = $social_media_service->getOAuthStartUrl($network_name);
				
				if ($popup_link) {
					header("Location: $popup_link", 302);
				}				
				
			} catch (Exception $e) {
				echo $e->getMessage();				
			}
			
			die();
		}
		

		protected function taskReturn($params=array()) {
				
			try {
				$social_media_service = $this->getSocialMediaService();
				
				if (isset($_REQUEST['logout'])) {
					$social_media_service->logout();
					die();	
				}
				
				$social_media_service->processOauthResponse();	
				$social_media_service->renderResponsePage($this->getResponsePageData($social_media_service));
				
			} catch (Exception $e) {
				echo $e->getMessage();
			}
			
			die();
					
		}
		
		protected function taskError($params=array()) {
			$error_message = Request::get('error_messge');
			$social_media_service = $this->getSocialMediaService();
			if ($error_message) {
				$social_media_service->setErrorMessage($error_message);
			}
			$social_media_service->renderResponsePage($this->getResponsePageData($social_media_service));
			die();
		}		
		
		protected function getResponsePageData($social_media_service) {
			return null;
		}
		
	}