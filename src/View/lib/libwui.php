<?php
/*
 * Copyright (C) 2004-2016 Soner Tari
 *
 * This file is part of PFRE.
 *
 * PFRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PFRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PFRE.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @file
 * WUI library functions.
 */

/**
 * Common HTML footer lines.
 *
 * @todo This could be in a separate file to include, not a function.
 */
function AuthHTMLFooter()
{
	global $ADMIN, $SessionTimeout, $View;

	$_SESSION['Timeout']= time() + $SessionTimeout;
	?>
	</table>
	<table>
		<tr id="footer">
			<td class="user">
				<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?logout"><?php echo _TITLE('Logout') ?></a>

				(<label id="timeout"></label>)
				<script language="javascript" type="text/javascript">
					<!--
					// Add one to session timeout start, to LogUserOut() after redirect below (it's PHP's task)
					// Otherwise session timeout restarts from max
					var timeout= <?php echo $_SESSION['Timeout'] - time() ?> + 1;
					function countdown()
					{
						if (timeout > 0) {
							timeout-= 1;
							min= Math.floor(timeout / 60);
							sec= timeout % 60;
							// Pad left
							if (sec.toString().length < 2) {
								sec= "0" + sec;
							}
							document.getElementById("timeout").innerHTML= min + ":" + sec;
						}
						else {
							// redirect
							window.location= "/index.php";
							return;
						}
						setTimeout("countdown()", 1000);
					}
					countdown();
					// -->
				</script>

				<?php echo $_SESSION['USER'].'@'.filter_input(INPUT_SERVER, 'REMOTE_ADDR') ?>
			</td>
			<td>
				<?php echo _TITLE('Copyright') ?> (c) 2016 Soner Tari. <?php echo _TITLE('All rights reserved.') ?>
			</td>
		</tr>
	<?php
	HTMLFooter();
}

/**
 * Checks and prints a warning if the page is not active.
 *
 * $active is set during left and top menu creation according to logged in user.
 *
 * @param bool $active Whether the page was active or not.
 */
function CheckPageActivation($active)
{
	global $VIEW_PATH, $Submenu;

	if (!$active) {
		echo _TITLE('Resource not available').': '.$Submenu;

		require_once($VIEW_PATH.'/footer.php');
		pfrewui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Page not active.');
		exit(1);
	}
}

/**
 * Prints the message in a simple box, without an image.
 *
 * Used in simple info boxes on the right of components.
 * New lines are replaced with html breaks before displaying.
 *
 * @warning Checks if $msg is empty, because some automatized functions may
 * not pass a non-empth string (such as on configuration pages), thus the box
 * should not be displayed. Just take debug logs.
 *
 * @param string $msg Message to display.
 * @param int $width Box width, defaults to 300px.
 */
function PrintHelpBox($msg= '', $width= 300)
{
	global $ShowHelpBox;

	if ($ShowHelpBox) {
		if ($msg !== '') {
			?>
			<table id="helpbox" style="width: <?php echo $width ?>px;">
				<tr>
					<td class="leftbar">
					</td>
					<td>
						<?php
						echo preg_replace("/\n/", '<br />', _($msg));
						?>
					</td>
				</tr>
			</table>
			<?php
			return;
		}
		else {
			pfrewui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, '$msg empty');
		}
	}
}

/**
 * Prints the message in a box with a title bar and an image.
 *
 * Used as the main explanation box on a page.
 * New lines are replaced with html breaks before displaying.
 *
 * @warning $Width type should be string, because some functions use 'auto'.
 *
 * @param string $msg Message to display.
 * @param int $width Box width, defaults to auto.
 * @param string $type Image type to display.
 */
function PrintHelpWindow($msg, $width= 'auto', $type= 'INFO')
{
	global $IMG_PATH, $InHelpRegion, $ErrorMsg, $WarnMsg, $InfoMsg, $ShowHelpBox;

	/**
	 * Types of help boxes.
	 *
	 * @param string name Title string.
	 * @param string icon Image to display on the top-left corner of the box.
	 */
	$HelpBoxTypes = array(
		'INFO' => array(
			'name' => _TITLE('INFORMATION'),
			'icon' => 'info.png'
			),
		'ERROR' => array(
			'name' => _TITLE('ERROR'),
			'icon' => 'error.png'
			),
		'WARN' => array(
			'name' => _TITLE('WARNING'),
			'icon' => 'warning.png'
			),
	);

	$boxes= array(
		'ERROR' => 'ErrorMsg',
		'WARN' => 'WarnMsg',
		'INFO' => 'InfoMsg',
		);

	if (array_key_exists($type, $boxes)) {
		${$boxes[$type]}.= ${$boxes[$type]} ? '<br />'.$msg : $msg;
	}

	if (isset($InHelpRegion) && $InHelpRegion) {
		foreach ($boxes as $type => $msgname) {
			if (($type !== 'INFO') || $ShowHelpBox) {
				if (isset(${$msgname}) && (${$msgname} !== '')) {
					${$msgname}= preg_replace("/\n/", '<br />', ${$msgname});
					?>
					<table id="mainhelpbox" style="width: <?php echo $width ?>">
						<tr>
							<th colspan="2">
								<?php echo _($HelpBoxTypes[$type]['name']) ?>
							</th>
						</tr>
						<tr>
							<td class="image">
								<img src="<?php echo $IMG_PATH.$HelpBoxTypes[$type]['icon'] ?>" name="pfre" alt="pfre" border="0">
							</td>
							<td>
								<?php echo ${$msgname} ?>
							</td>
						</tr>
					</table>
					<?php
					// Messsage is printed now, reinitialize it
					${$msgname}= '';
				}
			}
		}
	}
}
?>
