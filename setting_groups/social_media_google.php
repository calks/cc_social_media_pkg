<?php

	class socialMediaPkgSocialMediaGoogleSettingGroup extends socialMediaPkgSocialMediaSettingGroup {
	
	
		public function getParamsTree() {
			
			
			$out = array(

				'google_login_enabled' => array(
					'type' => 'checkbox',
					'displayed_name' => $this->gettext('Enable Login via Google')
				),
				'google_login_client_id' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via Google: Client ID')
				),				
				'google_login_client_secret' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via Google: Client Secret')
				)
								
			);
			
			return array('social_media' => $out);
		}
		
	}