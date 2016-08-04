<?php
/* $pfre: FilterBase.php,v 1.12 2016/08/04 02:16:13 soner Exp $ */

/*
 * Copyright (c) 2016 Soner Tari.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this
 *    software must display the following acknowledgement: This
 *    product includes software developed by Soner Tari
 *    and its contributors.
 * 4. Neither the name of Soner Tari nor the names of
 *    its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written
 *    permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class FilterBase extends State
{
	protected $keyDirection= array(
		'in' => array(
			'method' => 'parseNVP',
			'params' => array('direction'),
			),
		'out' => array(
			'method' => 'parseNVP',
			'params' => array('direction'),
			),
		);

	protected $keyProto= array(
		'proto' => array(
			'method' => 'parseItems',
			'params' => array('proto'),
			),
		);

	protected $keySrcDest= array(
		'any' => array(
			'method' => 'parseAny',
			'params' => array(),
			),
		'all' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'from' => array(
			'method' => 'parseSrcDest',
			'params' => array('fromport'),
			),
		'to' => array(
			'method' => 'parseSrcDest',
			'params' => array('port'),
			),
		);

	protected $keyFilterOpts= array(
		'user' => array(
			'method' => 'parseItems',
			'params' => array('user'),
			),
		'group' => array(
			'method' => 'parseItems',
			'params' => array('group'),
			),
		'flags' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'icmp-type' => array(
			'method' => 'parseICMPType',
			'params' => array('icmp-code'),
			),
		'icmp6-type' => array(
			'method' => 'parseICMPType',
			'params' => array('icmp6-code'),
			),
		'tos' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'no' => array(
			'method' => 'parseNVPInc',
			'params' => array('state-filter'),
			),
		'keep' => array(
			'method' => 'parseNVPInc',
			'params' => array('state-filter'),
			),
		'modulate' => array(
			'method' => 'parseNVPInc',
			'params' => array('state-filter'),
			),
		'synproxy' => array(
			'method' => 'parseNVPInc',
			'params' => array('state-filter'),
			),
		'fragment' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'allow-opts' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'once' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'divert-reply' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'label' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('label'),
			),
		'tag' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('tag'),
			),
		'tagged' => array(
			'method' => 'parseDelimitedStr',
			'params' => array('tagged'),
			),
		'!tagged' => array(
			'method' => 'parseNotTagged',
			'params' => array(),
			),
		// "set prio" and "set tos"
		'set' => array(
			'method' => 'parseSet',
			'params' => array(),
			),
		'queue' => array(
			'method' => 'parseItems',
			'params' => array('queue', '(', ')'),
			),
		'rtable' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'probability' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'prio' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'received-on' => array(
			'method' => 'parseItems',
			'params' => array('received-on', '(', ')'),
			),
		'!received-on' => array(
			'method' => 'parseNotReceivedOn',
			'params' => array(),
			),
		'os' => array(
			'method' => 'parseOS',
			'params' => array(),
			),
		);

	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keyDirection,
			$this->keyInterface,
			$this->keyAf,
			$this->keyProto,
			$this->keySrcDest,
			$this->keyFilterOpts,
			$this->keywords
			);

		parent::__construct($str);
	}

	function parseSet()
	{
		if ($this->words[$this->index + 1] === 'prio') {
			$this->index++;
			$this->parseItems('set-prio', '(', ')');
		} elseif ($this->words[$this->index + 1] === 'tos') {
			$this->index++;
			$this->parseNextNVP('set-tos');
		}
	}

	function parseNotTagged()
	{
		$this->parseDelimitedStr('tagged');
		$this->rule['not-tagged']= TRUE;
	}

	function parseNotReceivedOn()
	{
		$this->parseItems('received-on', '(', ')');
		$this->rule['not-received-on']= TRUE;
	}

	function genFilterHead()
	{
		$this->genValue('direction');
		$this->genLog();
		$this->genKey('quick');
		$this->genInterface();
		$this->genValue('af');
		$this->genItems('proto', 'proto');
		$this->genSrcDest();
	}
	
	function genFilterOpts()
	{
		$this->genItems('user', 'user');
		$this->genItems('group', 'group');
		$this->genValue('flags', 'flags ');
		$this->genIcmpType();
		$this->genIcmp6Type();
		$this->genValue('tos', 'tos ');
		$this->genValue('state-filter', NULL, ' state');
		$this->genState();
		$this->genKey('fragment');
		$this->genKey('allow-opts');
		$this->genKey('once');
		$this->genKey('divert-reply');
		$this->genValue('label', 'label "', '"');
		$this->genValue('tag', 'tag "', '"');
		$this->genTagged();
		$this->genItems('set-prio', 'set prio', '(', ')');
		$this->genQueue();
		$this->genValue('rtable', 'rtable ');
		$this->genValue('probability', 'probability ');
		$this->genValue('prio', 'prio ');
		$this->genValue('set-tos', 'set tos ');
		$this->genReceivedOn();
	}
	
	function genSrcDest()
	{
		if (isset($this->rule['all'])) {
			$this->str.= ' all';
		} else {
			if (isset($this->rule['from']) || isset($this->rule['fromport'])) {
				$this->str.= ' from';
				$this->genItems('from');
				$this->genItems('fromport', 'port');
			}

			if (isset($this->rule['os'])) {
				$this->genItems('os', 'os');
			}
			
			if (isset($this->rule['to']) || isset($this->rule['port'])) {
				$this->str.= ' to';
				$this->genItems('to');
				$this->genItems('port', 'port');
			}
		}
	}

	function genState()
	{
		if (isset($this->rule['state-filter'])) {
			$this->arr= array();
			$this->genStateOpts();
			if (count($this->arr)) {
				$this->str.= ' ( ';
				$this->str.= implode(', ', $this->arr);
				$this->str.= ' )';
			}
		}
	}

	function genIcmpType()
	{
		if (($this->rule['af'] === 'inet') &&
			((isset($this->rule['proto']) && $this->rule['proto'] === 'icmp') ||
			 (is_array($this->rule['proto']) && in_array('icmp', $this->rule['proto'])))) {
			if (isset($this->rule['icmp-type'])) {
				$this->str.= $this->generateItem($this->rule['icmp-type'], 'icmp-type');
				if (isset($this->rule['icmp-code'])) {
					$this->str.= $this->generateItem($this->rule['icmp-code'], 'code');
				}
			}
		}
	}

	function genIcmp6Type()
	{
		if (($this->rule['af'] === 'inet6') &&
			((isset($this->rule['proto']) && $this->rule['proto'] === 'icmp6') ||
			 (is_array($this->rule['proto']) && in_array('icmp6', $this->rule['proto'])))) {
			if (isset($this->rule['icmp6-type'])) {
				$this->str.= $this->generateItem($this->rule['icmp6-type'], 'icmp6-type');
				if (isset($this->rule['icmp6-code'])) {
					$this->str.= $this->generateItem($this->rule['icmp6-code'], 'code');
				}
			}
		}
	}

	function genQueue()
	{
		if (isset($this->rule['queue'])) {
			if (!is_array($this->rule['queue'])) {
				$this->str.= ' set queue ' . $this->rule['queue'];
			} else {
				$this->str.= ' set queue (' . $this->rule['queue'][0] . ', ' . $this->rule['queue'][1] . ')';
			}
		}
	}

	function genTagged()
	{
		if (isset($this->rule['tagged'])) {
			$not= '';
			if (isset($this->rule['not-tagged']) && $this->rule['not-tagged'] === TRUE) {
				$not= '!';
			}
			$this->str.= " ${not}tagged \"" . $this->rule['tagged'] . '"';
		}
	}
	
	function genReceivedOn()
	{
		if (isset($this->rule['received-on'])) {
			$not= '';
			if (isset($this->rule['not-received-on']) && $this->rule['not-received-on'] === TRUE) {
				$not= '!';
			}
			$this->str.= " ${not}received-on " . $this->rule['received-on'];
		}
	}
}
?>
