<?php

	class socialMediaPkgsnIntegrationFacebookSettingGroup extends socialMediaPkgSnIntegrationSettingGroup {
	
	
		public function getParamsTree() {
			
			
			$out = array(

				'facebook_login_enabled' => array(
					'type' => 'checkbox',
					'displayed_name' => $this->gettext('Enable Login via Facebook')
				),				
				'facebook_login_application_id' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via Facebook: Application ID')
				),				
				'facebook_login_application_secret' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via Facebook: Application Secret')
				)
											
			);
			
			return array('sn_integration' => $out);
		}
		
	}