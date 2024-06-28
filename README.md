# UPS_Liverates

1. Installation Manually

	1. Prepare UPS_Liverates Module
	2. Copy UPS_Liverates Module to app/code
	3. Enable the Module
		>> php bin/magento module:enable UPS_Liverates
	4. RUN all commands
		>> php bin/magento setup:upgrade
		>> php bin/magento setup:di:compile
		>> php bin/magento setup:static-content:deploy
		>> php bin/magento cache:flush
		
1. Installation using composer
		
	1. Goto root DIR from command line.
	2. RUN composer require command	
		>> composer require ups/liverates
		
	3. Enable the Module
		>> php bin/magento module:enable UPS_Liverates
	4. RUN all commands
		>> php bin/magento setup:upgrade
		>> php bin/magento setup:di:compile
		>> php bin/magento setup:static-content:deploy
		>> php bin/magento cache:flush