<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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

class NatToTest extends NatBase
{
	// Override action, just for NatTo rules
	protected $inAction= 'match';
	protected $ruleAction= array(
		'action' => 'match',
		);

	protected $inStaticPort= 'static-port';
	protected $ruleStaticPort= array(
		'static-port' => TRUE,
		);

	protected $ruleType= array(
		'type' => 'nat-to',
		);

	function __construct()
	{
		$this->inType= 'nat-to ' . $this->inRedirHost . ' ' . $this->inRedirPort;

		$this->rule= $this->ruleStaticPort;

		parent::__construct();

		$this->in= $this->inFilterHead . ' ' . $this->inFilterOpts . ' ' . $this->inType . ' ' . $this->inPoolType . ' ' . $this->inStaticPort . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>