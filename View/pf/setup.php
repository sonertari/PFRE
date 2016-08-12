<?php
/* $pfre: setup.php,v 1.9 2016/08/11 18:29:21 soner Exp $ */

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
	if (!filter_has_var(INPUT_SERVER, 'HTTPS')) {
		header('Location: https://'.filter_input(INPUT_SERVER, 'SERVER_ADDR').filter_input(INPUT_SERVER, 'REQUEST_URI'));
		exit;
	}
}

require_once('pf.php');

if (count($_POST)) {
	// Allow posting empty strings to display an error message
	if (filter_has_var(INPUT_POST, 'User') && filter_has_var(INPUT_POST, 'NewPassword') && filter_has_var(INPUT_POST, 'ReNewPassword')) {
		if (in_array(filter_input(INPUT_POST, 'User'), $ALL_USERS)) {
			if (filter_input(INPUT_POST, 'NewPassword') === filter_input(INPUT_POST, 'ReNewPassword')) {
				if (preg_match('/^\w{8,}$/', filter_input(INPUT_POST, 'NewPassword'))) {
					/// @attention Admin can change other users' passwords without needing to know their passwords
					if (($_SESSION['USER'] == 'admin' && filter_input(INPUT_POST, 'User') != 'admin') ||
						$View->Controller($Output, 'CheckAuthentication', filter_input(INPUT_POST, 'User'), sha1(filter_input(INPUT_POST, 'CurrentPassword')))) {
						if ($View->Controller($Output, 'SetPassword', filter_input(INPUT_POST, 'User'), sha1(filter_input(INPUT_POST, 'NewPassword')))) {
							PrintHelpWindow(_NOTICE('User password changed') . ': ' . filter_input(INPUT_POST, 'User'));
							pfrewui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'User password changed: '.filter_input(INPUT_POST, 'User'));
							if ($_SESSION['USER'] == filter_input(INPUT_POST, 'User')) {
								// Log user out if she changes her own password, currently only admin can do that
								LogUserOut('User password changed');
							}
						}
						else {
							pfrewui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Password change failed: '.filter_input(INPUT_POST, 'User'));
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
					pfrewui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Password change failed: '.filter_input(INPUT_POST, 'User'));
				}
			}
			else {
				PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('Passwords do not match'), 'auto', 'ERROR');
				pfrewui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Passwords do not match: '.filter_input(INPUT_POST, 'User'));
			}
		}
		else {
			PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('pfre currently supports only admin and user usernames'), 'auto', 'ERROR');
			pfrewui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Invalid user: '.filter_input(INPUT_POST, 'User'));
		}
	}
	else if (filter_has_var(INPUT_POST, 'LogLevel')) {
		if ($View->Controller($Output, 'SetLogLevel', filter_input(INPUT_POST, 'LogLevel'))) {
			pfrewui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'LogLevel set: '.filter_input(INPUT_POST, 'LogLevel'));
			// Reset $LOG_LEVEL to its new value
			require($ROOT.'/lib/setup.php');
		}
	}
	else if (filter_has_var(INPUT_POST, 'MaxAnchorNesting')) {
		if ($View->Controller($Output, 'SetMaxAnchorNesting', filter_input(INPUT_POST, 'MaxAnchorNesting'))) {
			pfrewui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'MaxAnchorNesting set: '.filter_input(INPUT_POST, 'MaxAnchorNesting'));
			// Reset $MaxAnchorNesting to its new value
			require($ROOT.'/lib/setup.php');
		}
	}
	else if (filter_has_var(INPUT_POST, 'PfctlTimeout')) {
		if ($View->Controller($Output, 'SetPfctlTimeout', filter_input(INPUT_POST, 'PfctlTimeout'))) {
			pfrewui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'PfctlTimeout set: '.filter_input(INPUT_POST, 'PfctlTimeout'));
			// Reset $PfctlTimeout to its new value
			require($ROOT.'/lib/setup.php');
		}
	}
	else {
		if (filter_has_var(INPUT_POST, 'DisableHelpBoxes')) {
			$View->Controller($Output, 'SetHelpBox', 'FALSE');
		}
		else if (filter_has_var(INPUT_POST, 'EnableHelpBoxes')) {
			$View->Controller($Output, 'SetHelpBox', 'TRUE');
		}
		else if (filter_has_var(INPUT_POST, 'SessionTimeout')) {
			$View->Controller($Output, 'SetSessionTimeout', filter_input(INPUT_POST, 'SessionTimeout'));
		}
		else if (filter_input(INPUT_POST, 'DisableForceHTTPs') || filter_input(INPUT_POST, 'EnableForceHTTPs')) {
			if (filter_has_var(INPUT_POST, 'DisableForceHTTPs')) {
				$View->Controller($Output, 'SetForceHTTPs', 'FALSE');
			}
			else if (filter_has_var(INPUT_POST, 'EnableForceHTTPs')) {
				$View->Controller($Output, 'SetForceHTTPs', 'TRUE');
			}
			// Reload the page using plain HTTP to activate the change
			header('Location: http://'.filter_input(INPUT_SERVER, 'SERVER_ADDR').filter_input(INPUT_SERVER, 'REQUEST_URI'));
			exit;
		}
		// Reset defaults to their new values
		require($VIEW_PATH.'/lib/setup.php');
	}
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
		<tr class="oddline">
			<td class="titlegrouptop">
				<?php echo _TITLE('User').':' ?>
			</td>
			<td class="valuegrouptop">
				<input type="text" name="User" style="width: 100px;" maxlength="20" value="<?php echo $_SESSION['USER'] ?>"/>
			</td>
			<td class="none" rowspan="3">
				<?php
				PrintHelpBox(_HELPBOX('Here you can change the web administration interface passwords for admin and user. Passwords should have at least 8 alphanumeric characters.

Admin can change the user password without knowing the current user password. But if you forget the admin password, you should run the following on the command line to set the password to soner123:
<code>/usr/local/bin/htpasswd -b -s /var/www/conf/.htpasswd admin $(/bin/echo -n soner123 | sha1 -)</code>'));
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
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
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
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
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
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
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
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<?php
				$Button= $ForceHTTPs ? 'Disable' : 'Enable';
				?>
				<input type="submit" name="<?php echo $Button ?>ForceHTTPs" value="<?php echo _($Button) ?>" onclick="return confirm('Are you sure you want to <?php echo _($Button) ?> secure HTTP?')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('If enabled, authentication pages are forced to use secure connections. Make sure you have a working SSL setup in the web server configuration, otherwise you cannot even log in to the web user interface. It is advised to use secure HTTP.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Max anchor nesting').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<input type="text" name="MaxAnchorNesting" style="width: 50px;" maxlength="2" value="<?php echo $MaxAnchorNesting ?>" />
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Inline anchor rules can be nested. It is advised to limit the number of nesting allowed. Parsing and validation stop at this many number of nesting.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Pfctl Timeout').':' ?>
		</td>
		<td>
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
				<input type="text" name="PfctlTimeout" style="width: 50px;" maxlength="2" value="<?php echo $PfctlTimeout ?>" />
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Pfctl commands are executed in a separate process, which returns pfctl output in a message. Parent process times out waiting for an output message after this many seconds. This approach is necessary in case pfctl is stuck or taking too long (and it is on certain cases).

<b>Setting this timeout to 0 may fail the execution of all pfctl commands, effectively disabling rule tests.<b>'));
			?>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('These defaults are permanently stored in web user interface settings, i.e. they are <em>not</em> specific to your current session only.'));
require_once($VIEW_PATH.'/footer.php');
?>
