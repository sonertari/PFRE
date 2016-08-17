<?php
define('QUICK', FALSE);

/// @attention Wait for 1 second for popups to show
/// @todo Find a better way?
define('POPUP_DISPLAY_INTERVAL', 1);

/// @attention Waiting for 1 second works around the "stale element reference" exception (?)
/// @todo Find the real cause of and solution to this issue
define('STALE_ELEMENT_INTERVAL', 1);

require_once('ConfigureWebDriver.php');
?>