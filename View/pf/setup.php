<?php
/* $pfre: conf.wui.php,v 1.12 2016/07/26 07:00:36 soner Exp $ */

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

/// Force https
if ($ForceHTTPs) {
	if (!isset($_SERVER['HTTPS'])) {
		header('Location: https://'.$_SERVER['SERVER_ADDR'].$_SERVER['REQUEST_URI']);
	}
}

require_once('include.php');

if ($_POST) {
	// User isset() here and allow posting empty strings to display error message
	if (isset($_POST['User'], $_POST['NewPassword'], $_POST['ReNewPassword'])) {
		if (in_array($_POST['User'], $ALL_USERS)) {
			if ($_POST['NewPassword'] === $_POST['ReNewPassword']) {
				if (preg_match('/^\w{8,}$/', $_POST['NewPassword'])) {
					if ($View->Controller($Output, 'CheckAuthentication', $_POST['User'], sha1($_POST['CurrentPassword']))) {
						if ($View->Controller($Output, 'SetPassword', $_POST['User'], sha1($_POST['NewPassword']))) {
							pfrewui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'User password changed: '.$_POST['User']);
							if ($_SESSION['USER'] == $_POST['User']) {
								// Log user out if she changes her own password, currently only admin can do that
								LogUserOut('User password changed');
							}
						}
						else {
							pfrewui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Password change failed: '.$_POST['User']);
						}
					}
					else {
						pfrewui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Password mismatch');
						// Throttle authentication failures
						exec('/bin/sleep 5');
					}
				}
				else {
					PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Not a valid password'), 'auto', 'ERROR');
					pfrewui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Password change failed: '.$_POST['User']);
				}
			}
			else {
				PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Passwords do not match'), 'auto', 'ERROR');
				pfrewui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Passwords do not match: '.$_POST['User']);
			}
		}
		else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('pfre currently supports only admin and user usernames'), 'auto', 'ERROR');
			pfrewui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Invalid user: '.$_POST['User']);
		}
	}
	else if ($_POST['LogLevel']) {
		if ($View->Controller($Output, 'SetLogLevel', $_POST['LogLevel'])) {
			pfrewui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'LogLevel set: '.$_POST['LogLevel']);
			// Reset $LOG_LEVEL to its new value
			require($ROOT.'/lib/setup.php');
		}
	}
	else {
		if ($_POST['DisableHelpBoxes']) {
			$View->Controller($Output, 'SetHelpBoxes', 'FALSE');
		}
		else if ($_POST['EnableHelpBoxes']) {
			$View->Controller($Output, 'SetHelpBoxes', 'TRUE');
		}
		else if ($_POST['SessionTimeout']) {
			$View->Controller($Output, 'SetSessionTimeout', $_POST['SessionTimeout']);
		}
		else if ($_POST['DisableForceHTTPs'] || $_POST['EnableForceHTTPs']) {
			if ($_POST['DisableForceHTTPs']) {
				$View->Controller($Output, 'SetForceHTTPs', 'FALSE');
			}
			else if ($_POST['EnableForceHTTPs']) {
				$View->Controller($Output, 'SetForceHTTPs', 'TRUE');
			}
			// Reload the page using plain HTTP to activate the change
			header('Location: http://'.$_SERVER['SERVER_ADDR'].$_SERVER['REQUEST_URI']);
		}
		// Reset defaults to their new values
		require($VIEW_PATH.'/lib/setup.php');
	}
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<tr class="oddline">
			<td class="titlegrouptop">
				<?php echo _TITLE('User').':' ?>
			</td>
			<td class="valuegrouptop">
				<input type="text" name="User" style="width: 100px;" maxlength="20" value="<?php echo $_SESSION['USER'] ?>"/>
			</td>
			<td class="none" rowspan="3">
				<?php
				PrintHelpBox(_HELPBOX('Here you can change the web administration interface passwords for admin and user. Passwords should have at least 8 alphanumeric characters.'));
				?>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegroupmiddle">
				<?php echo _TITLE('Current Password').':' ?>
			</td>
			<td class="valuegroupmiddle">
				<input type="password" name="CurrentPassword" style="width: 100px;" maxlength="20"/>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegroupmiddle">
				<?php echo _TITLE('New Password').':' ?>
			</td>
			<td class="valuegroupmiddle">
				<input type="password" name="NewPassword" style="width: 100px;" maxlength="20"/>
			</td>
		</tr>
		<tr class="oddline">
			<td class="titlegroupbottom">
				<?php echo _TITLE('New Password again').':' ?>
			</td>
			<td class="valuegroupbottom">
				<input type="password" name="ReNewPassword" style="width: 100px;" maxlength="20"/>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</td>
		</tr>
	</form>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Log level').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="LogLevel">
					<?php
					foreach ($LOG_PRIOS as $Prio) {
						$Selected= $Prio === $LOG_PRIOS[$LOG_LEVEL] ? 'selected' : '';
						?>
						<option <?php echo $Selected ?> value="<?php echo $Prio ?>"><?php echo $Prio ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This is the log level for the pfre web interface. Logs at this level and up will be recorded in WUI and Controller log files. This setting does not effect other services.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Help boxes').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<?php
				$Button= $ShowHelpBox ? 'Disable' : 'Enable';
				?>
				<input type="submit" name="<?php echo $Button ?>HelpBoxes" value="<?php echo _($Button) ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This setting enables or disables help boxes, such as this one and the help window at the bottom. Disabling help boxes does not disable error or warning help windows.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Session timeout').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="text" name="SessionTimeout" style="width: 50px;" maxlength="4" value="<?php echo $SessionTimeout ?>" />
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('User sessions expire after an idle period defined by this value. The unit is in seconds. You cannot set a value less than 10 seconds.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Force HTTPs').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<?php
				$Button= $ForceHTTPs ? 'Disable' : 'Enable';
				?>
				<input type="submit" name="<?php echo $Button ?>ForceHTTPs" value="<?php echo _($Button) ?>" onclick="return confirm('Are you sure you want to <?php echo _($Button) ?> secure HTTP?')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('If enabled, authentication pages are forced to use secure connections. Make sure you have a working SSL setup in the web server configuration, otherwise you cannot even log in to the web user interface.'));
			?>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('These defaults are permanently stored in web user interface settings, i.e. they are <em>not</em> specific to your current session only.'));
require_once($VIEW_PATH.'/footer.php');
?>
