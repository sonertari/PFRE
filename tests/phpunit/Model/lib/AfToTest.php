<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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

require_once('FilterTest.php');

class AfToTest extends FilterTest
{
	protected $inAfto= 'af-to inet from 192.168.0.1 to 192.168.0.2';
	protected $ruleAfto= array(
		'rediraf' => 'inet',
		'redirhost' => '192.168.0.1',
		'toredirhost' => '192.168.0.2'
		);

	function __construct()
	{
		$this->rule= array_merge(
			$this->ruleAfto,
			// Redirhost in AfTo has a leading 'from', hence handled in $ruleAfto instead
			//$this->ruleRedirHost,
			$this->rulePoolType
			);

		parent::__construct();

		$this->in= $this->inFilterHead . ' ' . $this->inFilterOpts . ' ' . $this->inAfto . ' ' . $this->inPoolType . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>