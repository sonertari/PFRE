<?php
/* $pfre: vars.php,v 1.7 2016/07/31 10:33:34 soner Exp $ */

/*
 * Copyright (c) 2016 Soner Tari.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this
 *    software must display the following acknowledgement: This
 *    product includes software developed by Soner Tari
 *    and its contributors.
 * 4. Neither the name of Soner Tari nor the names of
 *    its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written
 *    permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/** @file
 * Required includes and vars.
 * 
 * @attention TAB size throughout this source code is 4 spaces.
 * @bug	There is partial PHP support in Doxygen, thus there are many issues.
 */

/// Root directory of the project tree obtained from Apache.
$VIEW_PATH= filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');

require_once($VIEW_PATH.'/lib/setup.php');

/// PF module absolute path.
$PF_PATH= $VIEW_PATH.'/pf';

// Include these before session start,
// because we save instances of these in the session
require_once($PF_PATH.'/lib/RuleSet.php');
require_once($PF_PATH.'/lib/Rule.php');
require_once($PF_PATH.'/lib/FilterBase.php');
require_once($PF_PATH.'/lib/Filter.php');
require_once($PF_PATH.'/lib/Antispoof.php');
require_once($PF_PATH.'/lib/Anchor.php');
require_once($PF_PATH.'/lib/NatBase.php');
require_once($PF_PATH.'/lib/NatTo.php');
require_once($PF_PATH.'/lib/BinatTo.php');
require_once($PF_PATH.'/lib/RdrTo.php');
require_once($PF_PATH.'/lib/AfTo.php');
require_once($PF_PATH.'/lib/DivertTo.php');
require_once($PF_PATH.'/lib/Route.php');
require_once($PF_PATH.'/lib/Macro.php');
require_once($PF_PATH.'/lib/Table.php');
require_once($PF_PATH.'/lib/Queue.php');
require_once($PF_PATH.'/lib/Scrub.php');
require_once($PF_PATH.'/lib/Option.php');
require_once($PF_PATH.'/lib/Timeout.php');
require_once($PF_PATH.'/lib/Limit.php');
require_once($PF_PATH.'/lib/LoadAnchor.php');
require_once($PF_PATH.'/lib/Include.php');
require_once($PF_PATH.'/lib/Comment.php');
require_once($PF_PATH.'/lib/Blank.php');

require_once($VIEW_PATH.'/lib/libauth.php');

if ($_SESSION['Timeout']) {
	if ($_SESSION['Timeout'] <= time()) {
		LogUserOut('Session expired');
	}
} else {
	$_SESSION['Timeout']= time() + $SessionTimeout;
}

if (!isset($_SESSION['USER']) || $_SESSION['USER'] == 'loggedout') {
	header('Location: http://'.filter_input(INPUT_SERVER, 'SERVER_ADDR').'/index.php');
	exit;
}

$ROOT= dirname(dirname(dirname(__FILE__)));

require_once($ROOT.'/lib/defs.php');
require_once($ROOT.'/lib/lib.php');
require_once($ROOT.'/lib/setup.php');

/** Sub menu configuration, caption and permissions.
 */
$SubMenus = array(
	'rules' => array(
		'Name' => _MENU('Rules'),
		'Perms' => $ALL_USERS,
		),
	'displayinstall' => array(
		'Name' => _MENU('Display & Install'),
		'Perms' => $ALL_USERS,
		),
	'loadsave' => array(
		'Name' => _MENU('Load & Save'),
		'Perms' => $ALL_USERS,
		),
	'setup' => array(
		'Name' => _MENU('Setup'),
		'Perms' => $ADMIN,
		),
);

/// Path to image files used in help boxes and links.
$IMG_PATH= '/images/';

require_once($VIEW_PATH.'/lib/libwui.php');
require_once($VIEW_PATH.'/lib/view.php');
?>
