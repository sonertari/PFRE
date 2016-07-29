<?php
/* $pfre: libwui.php,v 1.84 2016/07/11 17:31:39 soner Exp $ */

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
 * WUI library functions.
 */

/** Common HTML Header lines.
 *
 * @attention This function should be called by all pages, because it enforces authentication.
 *
 * @param[in]	$reloadrate	Page reload rate, defaults to 0 (no reload)
 */
function AuthHTMLHeader()
{
	if (isset($_GET['logout'])) {
		LogUserOut();
	}

	HTMLHeader();
}

/** Common HTML footer lines.
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
				<a href="<?php echo $_SERVER['PHP_SELF'] ?>?logout"><?php echo _TITLE('Logout') ?></a>

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

				<?php echo $_SESSION['USER'].'@'.$_SERVER['REMOTE_ADDR'] ?>
			</td>
			<?php
			if (in_array($_SESSION['USER'], $ADMIN)) {
				$file= $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'];
				if ($View->Controller($output, 'GetFileCvsTag', $file)) {
					$cvstag= $output[0];
				}
				else {
					$cvstag= _TITLE2('CVS Tag');
				}
				?>
				<td class="cvs">
					<?php echo $cvstag ?>
				</td>
				<?php
			}
			?>
			<td>
				<?php echo _TITLE2('Copyright') ?> (c) 2016 Soner Tari. <?php echo _TITLE2('All rights reserved.') ?>
			</td>
		</tr>
	<?php
	HTMLFooter();
}

/** Checks and prints a warning if the page is not active.
 *
 * $active is set during left and top menu creation according to logged in user.
 *
 * @param[in]	$active	boolean Whether the page was active
 */
function CheckPageActivation($active)
{
	global $VIEW_PATH, $Submenu;

	if (!$active) {
		echo _TITLE2('Resource not available').': '.$Submenu;

		require_once($VIEW_PATH.'/footer.php');
		pfrewui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Page not active.');
		exit(1);
	}
}

/** Prints the message in a simple box, without an image.
 *
 * Used in simple info boxes on the right of components.
 * New lines are replaced with html breaks before displaying.
 *
 * @param[in]	$msg	Message string
 * @param[in]	$width	Box width, defaults to 300px
 *
 * @warning Checks if $msg is empty, because some automatized functions may
 * not pass a non-empth string (such as on configuration pages), thus the box
 * should not be displayed. Just take debug logs.
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

/** Prints the message in a box with a title bar and an image.
 *
 * Used as the main explanation box on a page.
 * New lines are replaced with html breaks before displaying.
 *
 * @param[in]	$msg	Message string
 * @param[in]	$width	Box width, defaults to auto
 * @param[in]	$type	Image type to display
 *
 * @warning $Width type should be string, because some functions use 'auto'.
 */
function PrintHelpWindow($msg, $width= 'auto', $type= 'INFO')
{
	global $InHelpRegion, $ErrorMsg, $WarnMsg, $InfoMsg, $ShowHelpBox;

	/// Path to image files used in help boxes.
	$IMG_PATH= '/images/';

	/** Types of help boxes.
	 *
	 * @param name	Title string
	 * @param icon	Image to display on top-left corner
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
