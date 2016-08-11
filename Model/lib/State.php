<?php 
/* $pfre: State.php,v 1.4 2016/08/06 09:43:30 soner Exp $ */

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

namespace Model;

class State extends Timeout
{
	protected $keyState= array(
		'max' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-src-states' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-src-nodes' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-src-conn' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'max-src-conn-rate' => array(
			'method' => 'parseNextValue',
			'params' => array(),
			),
		'sloppy' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'no-sync' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'pflow' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'if-bound' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'floating' => array(
			'method' => 'parseBool',
			'params' => array(),
			),
		'overload' => array(
			'method' => 'parseOverload',
			'params' => array(),
			),
		'source-track' => array(
			'method' => 'parseSourceTrack',
			'params' => array(),
			),
		);

	protected $typeState= array(
		'max' => array(
			'regex' => RE_NUM,
			),
		'max-src-states' => array(
			'regex' => RE_NUM,
			),
		'max-src-nodes' => array(
			'regex' => RE_NUM,
			),
		'max-src-conn' => array(
			'regex' => RE_NUM,
			),
		'max-src-conn-rate' => array(
			'regex' => RE_CONNRATE,
			),
		'sloppy' => array(
			'regex' => RE_BOOL,
			),
		'no-sync' => array(
			'regex' => RE_BOOL,
			),
		'pflow' => array(
			'regex' => RE_BOOL,
			),
		'if-bound' => array(
			'regex' => RE_BOOL,
			),
		'floating' => array(
			'regex' => RE_BOOL,
			),
		'overload' => array(
			'regex' => RE_ID,
			),
		'flush' => array(
			'regex' => RE_BOOL,
			),
		'global' => array(
			'regex' => RE_BOOL,
			),
		'source-track' => array(
			'regex' => RE_BOOL,
			),
 		'source-track-option' => array(
			'regex' => RE_SOURCETRACKOPTION,
			),
		);

	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keywords,
			$this->keyState
			);

		$this->typedef = array_merge(
			$this->typedef,
			$this->typeState
			);

		parent::__construct($str);
		
		unset($this->keywords['frag']);
		unset($this->keywords['interval']);
	}

	function parseOverload()
	{
		$this->rule['overload']= rtrim(ltrim($this->words[++$this->index], '<'), '>');

		if ($this->words[$this->index + 1] == 'flush') {
			$this->index++;
			$this->rule['flush']= TRUE;

			if ($this->words[$this->index + 1] == 'global') {
				$this->index++;
				$this->rule['global']= TRUE;
			}
		}
	}
	
	function parseSourceTrack()
	{
		$this->rule['source-track']= TRUE;

		if ($this->words[$this->index + 1] == 'rule' || $this->words[$this->index + 1] == 'global') {
			$this->rule['source-track-option']= $this->words[++$this->index];
		}
	}
	
	function generate()
	{
		$this->str= '';
		$this->genState();
		
		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genState()
	{
		$this->arr= array();
		$this->genStateOpts();
		if (count($this->arr)) {
			$this->str= 'set state-defaults ';
			$this->str.= implode(', ', $this->arr);
		}
	}

	function genStateOpts()
	{
		$this->genText('max');
		$this->genText('max-src-states');
		$this->genText('max-src-nodes');
		$this->genText('max-src-conn');
		$this->genText('max-src-conn-rate');

		$this->genBool('sloppy');
		$this->genBool('no-sync');
		$this->genBool('pflow');

		$this->genBool('if-bound');
		$this->genBool('floating');

		$this->genOverload();
		$this->genSourceTrack();

		$this->genTimeoutOpts();
	}

	function genText($key)
	{
		if (isset($this->rule[$key])) {
			$this->arr[]= "$key " . $this->rule[$key];
		}
	}
	
	function genBool($key)
	{
		if (isset($this->rule[$key])) {
			$this->arr[]= $key;
		}
	}

	function genOverload()
	{
		if (isset($this->rule['overload'])) {
			$str= 'overload <' . $this->rule['overload'] . '>';
			if (isset($this->rule['flush'])) {
				$str.= ' flush';
				if (isset($this->rule['global'])) {
					$str.= ' global';
				}
			}
			$this->arr[]= $str;
		}
	}
	
	function genSourceTrack()
	{
		if (isset($this->rule['source-track'])) {
			$str= 'source-track';
			if (isset($this->rule['source-track-option'])) {
				$str.= ' ' . $this->rule['source-track-option'];
			}
			$this->arr[]= $str;
		}
	}
}
?>