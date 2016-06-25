<?php

	class snIntegrationPkgSnOauthModule extends coreBaseModule {
		
		protected function getSnService() {
			$sn_service_class = coreResourceLibrary::getEffectiveClass('service', 'sn_integration');
			return $sn_service_class ? new $sn_service_class() : null;
		}
		
		
		protected function taskStart($params=array()) {			
			try {
				$sn = $this->getSnService();
				$sn->logout();
				$network_name = @array_shift($params);
				
				$popup_link = $sn->getOAuthStartUrl($network_name);
				
				if ($popup_link) {
					header("Location: $popup_link", 302);
				}				
				
			} catch (Exception $e) {
				echo $e->getMessage();				
			}
			
			die();
		}
		

		protected function taskReturn($params=array()) {;
				
			try {
				$sn = $this->getSnService();
				
				if (isset($_REQUEST['logout'])) {
					$sn->logout();
					die();	
				}
				
				$sn->processOauthResponse();	
				$sn->renderResponsePage($this->getResponsePageData($sn));
				
			} catch (Exception $e) {
				echo $e->getMessage();
			}
			
			die();
					
		}
		
		
		protected function getResponsePageData($sn) {
			return null;
		}
		
	}