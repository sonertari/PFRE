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
 * Prints top menu.
 */

require_once('lib/vars.php');

HTMLHeader();
?>
<tr id="top">
	<td>
		<table class="topmenu">
			<tr>
				<td>
					<?php
					$FileName= basename(filter_input(INPUT_SERVER, 'PHP_SELF'));					
					$PageActivated= FALSE;
					if (in_array($_SESSION['USER'], $SubMenus[$Submenu]['Perms'])) {
						$_SESSION['submenu']= $Submenu;
						$PageActivated= TRUE;
					}

					if (isset($Submenu)) {
						?>
						<form method="post" id="languageForm" name="languageForm" action="<?php echo preg_replace("/&/", "&amp;", $_SERVER['REQUEST_URI'], -1) ?>">
							<div id="menu">
								<b><?php echo _MENU('PF RULE EDITOR') ?></b>
								<ul id="tabs">
								<?php
								foreach ($SubMenus as $Name => $SubMenuConf) {
									if (in_array($_SESSION['USER'], $SubMenuConf['Perms'])) {
										?>
										<li<?php echo ($Submenu == $Name ? ' class="active"' : '') ?>><a href="?submenu=<?php echo $Name ?>"><?php echo _($SubMenuConf['Name']) ?></a></li>
										<?php
									}
								}
								?>
								</ul>

								<?php echo _MENU('Language').': ' ?>
								<select id="Locale" name="Locale" onchange="document.languageForm.submit()">
								<?php
								foreach ($LOCALES as $Locale => $Conf) {
									$Selected= ($_SESSION['Locale'] == $Locale) ? 'selected' : '';
									if ($_SESSION['Locale'] !== 'en_EN') {
										$LocaleDisplayName= _($Conf['Name']).' ('.$Conf['Name'].')';
									}
									else {
										$LocaleDisplayName= _($Conf['Name']);
									}
									?>
									<option value="<?php echo $Locale ?>" <?php echo $Selected ?>><?php echo $LocaleDisplayName ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</form>
						<?php
					}
					?>
					<div id="menuunderline">
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
