<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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

class LoadAnchorTest extends Rule
{
	protected $inAnchor= 'anchor test';
	protected $ruleAnchor= array(
		'anchor' => 'test',
		);

	protected $inFile= 'from "/etc/pfre/include.conf"';
	protected $ruleFile= array(
		'file' => '/etc/pfre/include.conf',
		);

	function __construct()
	{
		$this->rule= array_merge(
			$this->ruleAnchor,
			$this->ruleFile
			);

		parent::__construct();

		$this->in= 'load ' . $this->inAnchor . ' ' . $this->inFile . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>