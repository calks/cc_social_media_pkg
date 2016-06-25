<?php

	class snIntegrationPkgSnIntegrationSettingGroup extends coreBaseSettingGroup {
		
		public function getGroupNames() {
			return array(
				'sn_integration' => $this->gettext('Social networks integration')
			);
		}
		
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
				),
				
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
				),

				'twitter_login_enabled' => array(
					'type' => 'checkbox',
					'displayed_name' => $this->gettext('Enable Login via Twitter')
				),
				'twitter_login_consumer_key' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via Twitter: Consumer Key')
				),				
				'twitter_login_consumer_secret' => array(
					'type' => 'string',
					'displayed_name' => $this->gettext('Login via Twitter: Consumer Secret')
				),
				
				
			);
			
			return array('sn_integration' => $out);
		
		}
	}