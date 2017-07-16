<?php

	class socialMediaPkgSnIntegrationSettingGroup extends coreBaseSettingGroup {
		
		public function getGroupNames() {
			return array(
				'sn_integration' => $this->gettext('Social networks integration')
			);
		}
		
		public function getParamsTree() {
			$out = array(

			);
			
			return array('sn_integration' => $out);
		
		}
	}