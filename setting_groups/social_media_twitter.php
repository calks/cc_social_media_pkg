<?php

	class socialMediaPkgSocialMediaTwitterSettingGroup extends socialMediaPkgSocialMediaSettingGroup {
	
	
		public function getParamsTree() {
			
			
			$out = array(

				'twitter_login_enabled' => array(
					'type' => 'checkbox',
					'displayed_name' => $this->gettext('Enable Login via Twitter')
				),
				'twitter_login_consumer_key2' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via Twitter: Consumer Key')
				),				
				'twitter_login_consumer_secret' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via Twitter: Consumer Secret')
				)
				
			);
			
			return array('social_media' => $out);
		}
		
	}