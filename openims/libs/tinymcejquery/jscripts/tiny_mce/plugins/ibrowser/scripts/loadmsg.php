<?php
	// ================================================
	// PHP image browser - iBrowser 
	// ================================================
	// iBrowser dialog - load message
	// ================================================
	// Developed: net4visions.com
	// Copyright: net4visions.com
	// License: GPL - see readme.txt
	// (c)2005 All rights reserved.
	// ================================================
	// Revision: 1.0                   Date: 07/15/2006
	// ================================================	
	
?>
<?php
	//-------------------------------------------------------------------------
	// include configuration settings
	if (!$l) {
		include dirname(__FILE__) . '/../config/config.inc.php';
		include dirname(__FILE__) . '/../langs/lang.class.php';	
		// language settings	
		$l = (isset($_REQUEST['lang']) ? new PLUG_Lang($_REQUEST['lang']) : new PLUG_Lang($cfg['lang']));
		$l->setBlock('ibrowser');
	}
?>
<div align="center" id="dialogLoadMessage">
</div>