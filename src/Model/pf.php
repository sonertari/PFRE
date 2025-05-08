<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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
 * Contains Pf class to run pf tasks.
 */

use Model\RuleSet;

require_once($MODEL_PATH.'/model.php');

class Pf extends Model
{
	use Rules;

	public $ConfPath= '/etc/pfre';
	public $ConfFile= '/etc/pf.conf';

	public $ReloadCmd= "/sbin/pfctl -f <FILE> 2>&1";

	function __construct()
	{
		parent::__construct();
		$this->registerRulesCommands();
	}

	function getTestRulesCmd($rulesStr, &$tmpFile)
	{
		return "/bin/echo '$rulesStr' | /sbin/pfctl -nf - 2>&1";
	}
}
?>
