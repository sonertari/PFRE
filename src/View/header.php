<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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
 * Page header.
 */

require_once('lib/vars.php');

$PageActivated= FALSE;
if (isset($TopMenu)) {
	if (in_array($_SESSION['USER'], $Menu[$TopMenu]['Perms'])) {
		$PageActivated= TRUE;
	}
}

if (!$PageActivated) {
	wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Page not active: $TopMenu");
	header('Location: conf.editor.php');
	exit(1);
}

// Start sending the page
HTMLHeader();

define('ACTIVE_LI_STYLE', ' style="background: #01466b;"');
define('ACTIVE_A_STYLE', ' style="color: white;"');
?>
<table id="menu">
	<tr >
		<td nowrap>
			<div id="wrapper">
				<nav id="nav">
					<ul id="navigation">
						<li>
							<a class="menuwithimage" href="#">
								<span class="menuwithimage">PFRE</span>
								<img class="menuwithimage" src="/images/menu.png" name="Menu" alt="Menu" align="absmiddle">
							</a>
						</li>
						<?php
						foreach ($Menu as $TopMenuName => $TopMenuConf) {
							if (in_array($_SESSION['USER'], $TopMenuConf['Perms'])) {
								$LiStyle= '';
								$AStyle= '';
								if ($TopMenu == $TopMenuName) {
									$LiStyle= ACTIVE_LI_STYLE;
									$AStyle= ACTIVE_A_STYLE;
								}
								?>
								<li<?php echo $LiStyle ?>>
									<a href="<?php echo $TopMenuName ?>.php"<?php echo $AStyle ?>><?php echo _($TopMenuConf['Name']) ?></a>
								</li>
								<?php
							}
						}
						?>
						<li id="rightmenu">
							<a class="menuwithimage" href="#">
								<?php $_SESSION['Timeout']= time() + $SessionTimeout; ?>
								<span id="timeout"></span>
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
								<img class="menuwithimage" src="/images/rightmenu.png" name="Right Menu" alt="Right Menu" align="absmiddle">
								<span class="menuwithimage"><?php echo exec('/bin/date "+%d.%m.%Y %H:%M"') ?></span>
							</a>
							<ul>
								<li>
									<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?logout"><?php echo _MENU('Logout') ?> <?php echo $_SESSION['USER'].'@'.filter_input(INPUT_SERVER, 'REMOTE_ADDR') ?></a>
								</li>
								<li id="languagemenu">
									<a href="#"><?php echo _MENU('Language') ?></a>
									<ul>
										<?php
										foreach ($LOCALES as $Locale => $Conf) {
											$LiStyle= '';
											$AStyle= '';
											if ($_SESSION['Locale'] == $Locale) {
												$LiStyle= ACTIVE_LI_STYLE;
												$AStyle= ACTIVE_A_STYLE;
											}
											if ($_SESSION['Locale'] !== 'en_EN') {
												$LocaleDisplayName= _($Conf['Name']).' ('.$Conf['Name'].')';
											}
											else {
												$LocaleDisplayName= _($Conf['Name']);
											}
											?>
											<li<?php echo $LiStyle ?>>
												<a href="<?php echo $_SERVER['PHP_SELF'] ?>?locale=<?php echo $Locale ?>"<?php echo $AStyle ?>><?php echo $LocaleDisplayName ?></a>
											</li>
											<?php
										}
										?>
									</ul>				
								</li>
							</ul>
						</li>
					</ul>
				</nav>
			</div>
		</td>
	</tr>
</table>
<?php
$InHelpRegion= TRUE;
PrintHelpWindow('', 'auto', 'PRINT');
$InHelpRegion= FALSE;
?>
