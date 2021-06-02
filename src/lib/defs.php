<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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
 * Common variables, arrays, and constants.
 */

/// Project version.
define('VERSION', '6.8');

$ROOT= dirname(dirname(dirname(__FILE__)));
$SRC_ROOT= dirname(dirname(__FILE__));

$VIEW_PATH= $SRC_ROOT . '/View';
$MODEL_PATH= $SRC_ROOT . '/Model';

/// Syslog priority strings.
$LOG_PRIOS= array(
	'LOG_EMERG',	// system is unusable
	'LOG_ALERT',	// action must be taken immediately
	'LOG_CRIT',		// critical conditions
	'LOG_ERR',		// error conditions
	'LOG_WARNING',	// warning conditions
	'LOG_NOTICE',	// normal, but significant, condition
	'LOG_INFO',		// informational message
	'LOG_DEBUG',	// debug-level message
	);

/// Superuser
$ADMIN= array('admin');
/// Unprivileged user who can modify any configuration
$USER= array('user');
/// All valid users
$ALL_USERS= array_merge($ADMIN, $USER);

/**
 * Locale definitions used by both View and Controller.
 *
 * It is recommended that all translations use UTF-8 codeset.
 *
 * @param string Name Title string
 * @param string Codeset Locale codeset
 */
$LOCALES = array(
    'en_EN' => array(
        'Name' => _('English'),
        'Codeset' => 'UTF-8'
		),
    'tr_TR' => array(
        'Name' => _('Turkish'),
        'Codeset' => 'UTF-8'
		),
	);

$PF_CONFIG_PATH= '/etc/pfre';
$TMP_PATH= '/tmp';

$TEST_DIR_PATH= '';
/// @attention Necessary to set to '/pfre' instead of '' to fix $ROOT . $TEST_DIR_SRC in model.php
$TEST_DIR_SRC= '/pfre';
$INSTALL_USER= 'root';
?>
