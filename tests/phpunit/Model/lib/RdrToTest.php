<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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

namespace ModelTest;

require_once('NatBase.php');

class RdrToTest extends NatBase
{
	protected $ruleType= array(
		'type' => 'rdr-to',
		);

	function __construct()
	{
		$this->inType= 'rdr-to ' . $this->inRedirHost . ' ' . $this->inRedirPort;

		parent::__construct();

		$this->out= $this->in . "\n";
	}
}
?>