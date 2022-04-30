<?php
/*
 * Copyright (C) 2004-2022 Soner Tari
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

require_once('FilterTest.php');

class RouteTest extends FilterTest
{
	protected $ruleType= array(
		'type' => 'route-to',
		);

	protected $inRouteHost= '{ 192.168.0.1, 192.168.0.2 }';
	protected $ruleRouteHost= array(
		'routehost' => array(
			'192.168.0.1',
			'192.168.0.2',
			)
		);

	function __construct()
	{
		$this->inType= 'route-to ' . $this->inRouteHost;

		$this->rule= array_merge(
			$this->ruleRouteHost,
			$this->rulePoolType
			);

		parent::__construct();

		$this->in= $this->inFilterHead . ' ' . $this->inFilterOpts . ' ' . $this->inType . ' ' . $this->inPoolType . $this->inComment;
		$this->out= $this->in . "\n";
	}
}
?>