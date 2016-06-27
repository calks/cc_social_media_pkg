<?php

	class snIntegrationPkgsnIntegrationVkSettingGroup extends snIntegrationPkgSnIntegrationSettingGroup {
	
	
		public function getParamsTree() {
			
			
			$out = array(

				
				'vk_login_enabled' => array(
					'type' => 'checkbox',
					'displayed_name' => $this->gettext('Enable Login via VK')
				),
				'vk_login_application_id' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via VK: Application ID')
				),				
				'vk_login_secure_key' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via VK: Secure key')
				)
							
			);
			
			return array('sn_integration' => $out);
		}
		
	}