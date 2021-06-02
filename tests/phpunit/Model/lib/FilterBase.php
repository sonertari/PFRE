<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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

require_once('StateTest.php');

class FilterBase extends StateTest
{
	protected $inDirection= 'in';
	protected $ruleDirection= array(
		'direction' => 'in',
		);

	protected $inProto= 'proto tcp';
	protected $ruleProto= array(
		'proto' => 'tcp',
		);

	protected $inSrcDest= 'from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh';
	protected $ruleSrcDest= array(
		'from' => '192.168.0.1',
		'fromport' => array(
			'ssh',
			'2222',
			),
		'os' => 'openbsd',
		'to' => '192.168.0.2',
		'toport' => 'ssh',
		);

	protected $inFilterOpts= 'user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set delay 1000 set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state';
	protected $ruleFilterOpts= array(
		'user' => 'root',
		'group' => 'wheel',
		'flags' => 'S/SA',
		'tos' => '1',
		'state-filter' => 'keep',
		'allow-opts' => TRUE,
		'once' => TRUE,
		'label' => 'test',
		'tag' => 'test',
		'tagged' => 'test',
		'not-tagged' => TRUE,
		'set-delay' => '1000',
		'set-prio' => '2',
		'queue' => array(
			'std',
			'service',
			),
		'rtable' => '3',
		'max-pkt-rate' => '100/10',
		'probability' => '10%',
		'prio' => '4',
		'set-tos' => '5',
		'received-on' => 'em0',
		'not-received-on' => TRUE,
		);

	function __construct()
	{
		$this->rule= array_merge(
			$this->rule,
			$this->ruleDirection,
			$this->ruleInterface,
			$this->ruleAf,
			$this->ruleProto,
			$this->ruleSrcDest,
			$this->ruleFilterOpts
			);

		parent::__construct();

		$this->inFilterOpts.= ' ( ' . $this->inState . ', ' . $this->inTimeout . ' )';
	}
}
?>