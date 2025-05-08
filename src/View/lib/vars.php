<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @file
 * Required includes and vars.
 * 
 * @attention TAB size throughout this source code is 4 spaces.
 * @bug	There is partial PHP support in Doxygen, thus there are many issues.
 */

$ROOT= dirname(dirname(dirname(dirname(__FILE__))));
$SRC_ROOT= dirname(dirname(dirname(__FILE__)));

require_once($SRC_ROOT . '/lib/defs.php');
require_once($VIEW_PATH . '/pf/include.php');

require_once($VIEW_PATH . '/lib/libauth.php');

if (isset($_SESSION['Timeout'])) {
	if ($_SESSION['Timeout'] <= time()) {
		LogUserOut('Session expired');
	}
} elseif (isset($_SESSION['USER']) && in_array($_SESSION['USER'], $ALL_USERS)) {
	$_SESSION['Timeout']= time() + $SessionTimeout;
}

if (!isset($_SESSION['USER']) || $_SESSION['USER'] == 'loggedout') {
	header('Location: /index.php');
	exit;
}

/// Path to image files used in help boxes and links.
$IMG_PATH= '/images/';

require_once($VIEW_PATH.'/lib/libwui.php');
require_once($VIEW_PATH.'/lib/view.php');

/** Submenu configuration, caption and permissions.
 */
$Menu = array(
	'conf.editor' => array(
		'Name' => _MENU('Rules'),
		'Perms' => $ALL_USERS,
		),
	'conf.write' => array(
		'Name' => _MENU('Display & Install'),
		'Perms' => $ALL_USERS,
		),
	'conf.files' => array(
		'Name' => _MENU('Load & Save'),
		'Perms' => $ALL_USERS,
		),
	'conf.setup' => array(
		'Name' => _MENU('Setup'),
		'Perms' => $ADMIN,
		),
);
?>
