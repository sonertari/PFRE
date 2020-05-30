<?php
/*
 * Copyright (C) 2004-2020 Soner Tari
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

class LimitTest extends Rule
{
	protected $inLimit= 'states 1, frags 2, src-nodes 3, tables 4, table-entries 5';
	protected $ruleLimit= array(
		'limit' => array(
			'states' => '1',
			'frags' => '2',
			'src-nodes' => '3',
			'tables' => '4',
			'table-entries' => '5',
			),
		);

	function __construct()
	{
		$this->rule= $this->ruleLimit;

		parent::__construct();

		$this->in= 'set limit { ' . $this->inLimit . ' }' . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>