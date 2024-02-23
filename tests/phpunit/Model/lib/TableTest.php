<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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

require_once('Rule.php');

class TableTest extends Rule
{
	protected $inTable= 'table <test> persist const counters file "/etc/pf.restrictedips1" file "/etc/pf.restrictedips2" { 192.168.0.1, 192.168.0.2 }';
	protected $ruleTable= array(
		'identifier' => 'test',
		'persist' => TRUE,
		'const' => TRUE,
		'counters' => TRUE,
		'file' => array(
			'/etc/pf.restrictedips1',
			'/etc/pf.restrictedips2',
			),
		'data' => array(
			'192.168.0.1',
			'192.168.0.2',
			),
		);

	function __construct()
	{
		$this->rule= $this->ruleTable;

		parent::__construct();

		$this->in= $this->inTable . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>