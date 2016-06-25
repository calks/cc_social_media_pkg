<?php

	class snIntegrationPkgRuTranslation extends langPkgRuTranslation {
	
		public function getTranslations() {
		
			$parent_translations = parent::getTranslations();
			
			$translations = array(
				'Social networks integration' => 'Интеграция с социальными сетями',
				
				'Enable Login via Google' => 'Включить вход через Google',
				'Login via Google: Client ID' => 'Вход через Google: Client ID',
				'Login via Google: Client Secret' => 'Вход через Google: Client Secret',
			
				'Enable Login via Facebook' => 'Включить вход через Facebook',
				'Login via Facebook: Application ID' => 'Вход через Facebook: Application ID',
				'Login via Facebook: Application Secret' => 'Вход через Facebook: Application Secret',
			
				'Enable Login via Twitter' => 'Включить вход через Twitter',
				'Login via Twitter: Consumer Key' => 'Вход через Twitter: Consumer Key',
				'Login via Twitter: Consumer Secret' => 'Вход через Twitter: Consumer Secret',
			
			);
			
			return array_merge($parent_translations, $translations);
			
		}
		
		
	}