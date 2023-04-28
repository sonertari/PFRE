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

require_once('TimeoutTest.php');

class StateTest extends TimeoutTest
{
	protected $inState= 'max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule';
	protected $ruleState= array(
		'max' => '1',
		'max-src-states' => '2',
		'max-src-nodes' => '3',
		'max-src-conn' => '4',
		'max-src-conn-rate' => '5/5',
		'sloppy' => TRUE,
		'no-sync' => TRUE,
		'pflow' => TRUE,
		'if-bound' => TRUE,
		'overload' => 'over',
		'source-track' => TRUE,
		'source-track-option' => 'rule',
		'flush' => TRUE,
		'global' => TRUE,
		);

	function __construct()
	{
		$this->rule= array_merge(
			$this->rule,
			$this->ruleState
			);

		parent::__construct();

		$this->in= 'set state-defaults ' . $this->inState . ', ' . $this->inTimeout . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>