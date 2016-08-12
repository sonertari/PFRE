<?php
/* $pfre: defs.php,v 1.3 2016/08/10 04:39:43 soner Exp $ */

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

$ROOT= dirname(dirname(__FILE__));
$VIEW_PATH= $ROOT.'/View';
$MODEL_PATH= $ROOT.'/Model';

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

$PF_CONFIG_PATH= '/etc/pfre';
$TMP_PATH= '/tmp';

$TEST_ROOT_PATH= '';
$INSTALL_USER= 'root';
?>
