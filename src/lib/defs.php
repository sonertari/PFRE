<?php
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
 * Common variables, arrays, and constants.
 */

/// Project version.
define('VERSION', '5.9');

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
