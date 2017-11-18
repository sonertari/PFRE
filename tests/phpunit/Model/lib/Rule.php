<?php
/*
 * Copyright (C) 2004-2017 Soner Tari
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

require_once('RuleBase.php');

class Rule extends RuleBase
{
	protected $inInterface= 'on em0';
	protected $ruleInterface= array(
		'interface' => 'em0',
		);

	protected $inAf= 'inet';
	protected $ruleAf= array(
		'af' => 'inet',
		);

	protected $inLog= 'log ( all, matches, user, to pflog0 )';
	protected $ruleLog= array(
		'log' => array(
			'all' => TRUE,
			'matches' => TRUE,
			'user' => TRUE,
			'to' => 'pflog0',
			),
		);

	protected $inQuick= 'quick';
	protected $ruleQuick= array(
		'quick' => TRUE,
		);

	protected $inComment= ' # Test';
	protected $ruleComment= array(
		'comment' => 'Test',
		);

	function __construct()
	{
		$this->rule= array_merge(
			$this->rule,
			$this->ruleComment
			);

		parent::__construct();
	}
}
?>