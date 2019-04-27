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

class AntispoofTest extends Rule
{
	protected $inLabel= 'label "test"';
	protected $ruleLabel= array(
		'label' => 'test',
		);

	function __construct()
	{
		$this->inInterface= 'for ' . $this->ruleInterface['interface'];

		$this->rule= array_merge(
			$this->ruleLog,
			$this->ruleQuick,
			$this->ruleAf,
			$this->ruleInterface,
			$this->ruleLabel
			);

		parent::__construct();

		$this->in= 'antispoof ' . $this->inLog . ' ' . $this->inQuick . ' ' . $this->inInterface . ' ' . $this->inAf . ' ' . $this->inLabel . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>