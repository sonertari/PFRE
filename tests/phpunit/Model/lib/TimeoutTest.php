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

require_once('Rule.php');

class TimeoutTest extends Rule
{
	protected $inTimeout= 'frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19';
	protected $ruleTimeout= array(
		'timeout' => array(
			'all' => array(
				'frag' => '1',
				'interval' => '2',
				'src.track' => '3',
				),
			'tcp' => array(
				'first' => '4',
				'opening' => '5',
				'established' => '6',
				'closing' => '7',
				'finwait' => '8',
				'closed' => '9',
				),
			'udp' => array(
				'first' => '10',
				'single' => '11',
				'multiple' => '12',
				),
			'icmp' => array(
				'first' => '13',
				'error' => '14',
				),
			'other' => array(
				'first' => '15',
				'single' => '16',
				'multiple' => '17',
				),
			'adaptive' => array(
				'start' => '18',
				'end' => '19',
				),
			),
		);

	function __construct()
	{
		$this->rule= array_merge(
			$this->rule,
			$this->ruleTimeout
			);

		parent::__construct();

		$this->in= 'set timeout { ' . $this->inTimeout . ' }' . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>