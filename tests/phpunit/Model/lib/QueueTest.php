<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
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

class QueueTest extends Rule
{
	protected $inQueue= 'queue test on em0 parent rootq bandwidth 20M burst 90M for 100ms min 5M burst 10M for 50ms max 100M burst 1M for 10ms flows 1024 quantum 1 qlimit 100 default';
	protected $ruleQueue= array(
		'name' => 'test',
		'interface' => 'em0',
		'parent' => 'rootq',
		'bandwidth' => '20M',
		'bw-burst' => '90M',
		'bw-time' => '100ms',
		'min' => '5M',
		'min-burst' => '10M',
		'min-time' => '50ms',
		'max' => '100M',
		'max-burst' => '1M',
		'max-time' => '10ms',
		'flows' => '1024',
		'quantum' => '1',
		'qlimit' => '100',
		'default' => TRUE,
		);

	function __construct()
	{
		$this->rule= $this->ruleQueue;

		parent::__construct();

		$this->in= $this->inQueue . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>