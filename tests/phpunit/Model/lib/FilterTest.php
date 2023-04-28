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

require_once('FilterBase.php');

class FilterTest extends FilterBase
{
	protected $inFilterHead= '';

	protected $inAction= 'pass';
	protected $ruleAction= array(
		'action' => 'pass',
		);

	protected $ruleType= array(
		);

	protected $inRedirHost= '192.168.0.1';
	protected $ruleRedirHost= array(
		'redirhost' => '192.168.0.1',
		);

	protected $inPoolType= 'source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address';
	protected $rulePoolType= array(
		'source-hash' => TRUE,
		'source-hash-key' => '09f1cbe02e2f4801b433ba9fab728903',
		'sticky-address' => TRUE,
		);

	protected $inDivertPort= 'port ssh';
	protected $ruleDivertPort= array(
		'divertport' => 'ssh',
		);

	/// @todo Test rdomain
	function __construct()
	{
		$this->rule= array_merge(
			$this->rule,
			$this->ruleAction,
			$this->ruleLog,
			$this->ruleQuick,
			$this->ruleType
			);

		parent::__construct();

		$this->inFilterHead= $this->inAction . ' ' . $this->inDirection . ' ' . $this->inLog . ' ' . $this->inQuick . ' ' . $this->inInterface . ' ' . $this->inAf . ' ' . $this->inProto . ' ' . $this->inSrcDest;

		$this->in= $this->inFilterHead . ' ' . $this->inFilterOpts . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>