<?php

	class socialMediaPkgSocialMediaSettingGroup extends coreBaseSettingGroup {
		
		public function getGroupNames() {
			return array(
				'social_media' => $this->gettext('Social media integration')
			);
		}
		
		public function getParamsTree() {
			$out = array(

			);
			
			return array('social_media' => $out);
		
		}
	}