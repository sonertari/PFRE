<?php
/* $pfre: libauth.php,v 1.33 2016/07/26 23:08:20 soner Exp $ */

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
 * Authentication and related library functions.
 */

require_once($VIEW_PATH.'/lib/setup.php');

if (!isset($_SESSION)) {
	session_name('pfre');
	session_start();
}

/** Wrapper for syslog().
 *
 * Web interface related syslog messages.
 * A global $LOG_LEVEL is set in setup.php.
 *
 * @param[in]	$prio	Log priority checked against $LOG_LEVEL
 * @param[in]	$file	Source file the function is in
 * @param[in]	$func	Function where the log is taken
 * @param[in]	$line	Line number within the function
 * @param[in]	$msg	Log message
 */
function pfrewui_syslog($prio, $file, $func, $line, $msg)
{
	global $LOG_LEVEL, $LOG_PRIOS;

	try {
		openlog('pfrewui', LOG_PID, LOG_LOCAL0);
		
		if ($prio <= $LOG_LEVEL) {
			$useratip= $_SESSION['USER'].'@'.filter_input(INPUT_SERVER, 'REMOTE_ADDR');
			$func= $func == '' ? 'NA' : $func;
			$log= "$LOG_PRIOS[$prio] $useratip $file: $func ($line): $msg\n";
			if (!syslog($prio, $log)) {
				if (!fwrite(STDERR, $log)) {
					echo $log;
				}
			}
		}
		closelog();
	}
	catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
		echo "pfrewui_syslog() failed: $prio, $file, $func, $line, $msg\n";
		// No need to closelog(), it is optional
	}
}

/** Logs user out by setting session USER var to loggedout.
 *
 * Redirects to the main index page, which asks for re-authentication.
 *
 * @param[in]	$reason	string Reason for log message
 */
function LogUserOut($reason= 'User logged out')
{
	pfrewui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, $reason);
	$_SESSION['USER']= 'loggedout';
	/// @warning Relogin page should not time out
	$_SESSION['Timeout']= -1;
	session_write_close();

	header('Location: /index.php');
}

/** Authenticates session user with the password supplied.
 *
 * Passwords are sha1 encrypted before passed to Controller,
 * so the password string is never passed around plain text.
 * This means double encryption in the password file,
 * because Model encrypts again while storing into the file.
 *
 * @param[in]	$passwd	string Password submitted by user
 */
function Authentication($passwd)
{
	global $ALL_USERS, $SessionTimeout, $View;

	pfrewui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Login attempt');

	if (!in_array($_SESSION['USER'], $ALL_USERS)) {
		pfrewui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Not a valid user');
		// Throttle authentication failures
		exec('/bin/sleep 5');
		LogUserOut('Authentication failed');
	}

	if (!$View->Controller($Output, 'CheckAuthentication', $_SESSION['USER'], sha1($passwd))) {
		pfrewui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Password mismatch');
		// Throttle authentication failures
		exec('/bin/sleep 5');
		LogUserOut('Authentication failed');
	}
	pfrewui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Authentication succeeded');

	// Update session timeout now, otherwise in the worst case scenario, vars.php may log user out on very close session timeout
	$_SESSION['Timeout']= time() + $SessionTimeout;
	
	header("Location: /pf/index.php");
}

/** HTML Header without authentication.
 *
 * Called by AuthHTMLHeader() after logout check, and also by Login page.
 * Separate from AuthHTMLHeader() because Login page should not check logout naturally.
 *
 * @param[in]	$reloadrate	Page reload rate, defaults to 0 (no reload)
 * @param[in]	$color		Page background, Login page uses gray
 */
function HTMLHeader($color= 'white')
{
// Unindent these html lines, against the project style guidelines, otherwise they are indented in page source too
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo _MENU('PF Rule Editor') ?></title>
		<meta http-equiv="content-type" content="text/html" />
		<meta name="description" content="PF Rule Editor" />
		<meta name="author" content="Soner Tari"/>
		<meta name="keywords" content="PF, Rule, Editor, :)" />
		<link rel="stylesheet" href="../pfre.css" type="text/css" media="screen" />
	</head>
	<body style="background: <?php echo $color ?>;">
		<table>
		<?php
}

function HTMLFooter()
{
		?>
		</table>
	</body>
</html>
<?php
}

/** Sets session submenu variable.
 *
 * @param[in]	$default	string Default submenu selected
 * @return string Selected submenu
 */
function SetSubmenu($default)
{
	global $View, $SubMenus;

	if (filter_has_var(INPUT_GET, 'submenu') && array_key_exists(filter_input(INPUT_GET, 'submenu'), $SubMenus)) {
		$submenu= filter_input(INPUT_GET, 'submenu');
	} elseif ($_SESSION['submenu']) {
		$submenu= $_SESSION['submenu'];
	} else {
		$submenu= $default;
	}

	return $submenu;
}
?>
