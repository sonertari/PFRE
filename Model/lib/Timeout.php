<?php 
/* $pfre: Timeout.php,v 1.2 2016/08/04 16:59:15 soner Exp $ */

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

class Timeout extends Rule
{
	protected $arr= array();

	protected $keyTimeout= array(
		'frag' => array(
			'method' => 'parseAll',
			'params' => array(),
			),
		'interval' => array(
			'method' => 'parseAll',
			'params' => array(),
			),
		'src' => array(
			'method' => 'parseSrcTrack',
			'params' => array(),
			),
		'tcp' => array(
			'method' => 'parseTimeout',
			'params' => array(),
			),
		'udp' => array(
			'method' => 'parseTimeout',
			'params' => array(),
			),
		'icmp' => array(
			'method' => 'parseTimeout',
			'params' => array(),
			),
		'other' => array(
			'method' => 'parseTimeout',
			'params' => array(),
			),
		'adaptive' => array(
			'method' => 'parseTimeout',
			'params' => array(),
			),
		);

	protected $typeTimeout= array(
		'timeout' => array(
			'values' => array(
				'all' => array(
					'values' => array(
						'frag' => array(
							'func' => 'IsNumber',
							),
						'interval' => array(
							'func' => 'IsNumber',
							),
						'src.track' => array(
							'func' => 'IsNumber',
							),
						),
					),
				'tcp' => array(
					'values' => array(
						'first' => array(
							'func' => 'IsNumber',
							),
						'opening' => array(
							'func' => 'IsNumber',
							),
						'established' => array(
							'func' => 'IsNumber',
							),
						'closing' => array(
							'func' => 'IsNumber',
							),
						'finwait' => array(
							'func' => 'IsNumber',
							),
						'closed' => array(
							'func' => 'IsNumber',
							),
						),
					),
				'udp' => array(
					'values' => array(
						'first' => array(
							'func' => 'IsNumber',
							),
						'single' => array(
							'func' => 'IsNumber',
							),
						'multiple' => array(
							'func' => 'IsNumber',
							),
						),
					),
				'icmp' => array(
					'values' => array(
						'first' => array(
							'func' => 'IsNumber',
							),
						'error' => array(
							'func' => 'IsNumber',
							),
						),
					),
				'other' => array(
					'values' => array(
						'first' => array(
							'func' => 'IsNumber',
							),
						'single' => array(
							'func' => 'IsNumber',
							),
						'multiple' => array(
							'func' => 'IsNumber',
							),
						),
					),
				'adaptive' => array(
					'values' => array(
						'start' => array(
							'func' => 'IsNumber',
							),
						'end' => array(
							'func' => 'IsNumber',
							),
						),
					),
				),
			),
		);

	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keywords,
			$this->keyTimeout
			);

		$this->typedef = array_merge(
			$this->typedef,
			$this->typeTimeout,
			$this->typeComment
			);

		parent::__construct($str);
	}

	function split()
	{
		// @attention Cannot split at dots, otherwise IP addresses are split too, so split as usual
		//$this->words= preg_split('/[\s,\t\.]+/', $this->str, -1, PREG_SPLIT_NO_EMPTY);
		parent::split();

		// Split timeout keys
		// @todo Find a better way
		// @attention Do not use foreach here, we modify the list we loop on
		for ($index= 0; $index < count($this->words); $index++) {
			if (preg_match('/(src|tcp|udp|icmp|other|adaptive)\.(.+)/', $this->words[$index], $match)) {
				$head= array_slice($this->words, 0, $index);
				$tail= array_slice($this->words, $index + 1);
				$this->words= array_merge($head, array($match[1], $match[2]), $tail);
			}
		}
	}

	function parseAll()
	{
		$this->rule['timeout']['all'][$this->words[$this->index]]= $this->words[++$this->index];
	}

	function parseSrcTrack()
	{
		if ($this->words[$this->index + 1] == 'track') {
			$this->rule['timeout']['all']['src.track']= $this->words[$this->index + 2];
			$this->index+= 2;
		}
	}

	function parseTimeout()
	{
		$this->rule['timeout'][$this->words[$this->index]][$this->words[$this->index + 1]]= $this->words[$this->index + 2];
		$this->index+= 2;
	}

	function generate()
	{
		$this->str= '';
		$this->genTimeout();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genTimeout()
	{
		if (count($this->rule['timeout'])) {
			$this->arr= array();

			$this->genTimeoutOpts();

			if (count($this->arr)) {
				$this->str= 'set timeout ';
				$this->str.= count($this->arr) > 1 ? '{ ' : '';
				$this->str.= implode(', ', $this->arr);
				$this->str.= count($this->arr) > 1 ? ' }' : '';
			}
		}
	}
	
	function genTimeoutOpts()
	{
		// Check if timeout is set again, this method is used elsewhere too
		if (count($this->rule['timeout'])) {
			/// @attention This reset is critical if a page calls this function twice, and it does so in this case
			reset($this->rule['timeout']);

			if (count($this->rule['timeout']) == 1 && count(array_values($this->rule['timeout'][key($this->rule['timeout'])])) == 1) {
				list($timeout, $kvps)= each($this->rule['timeout']);
				$timeout= $timeout == 'all' ? '' : "$timeout.";

				list($key, $val)= each($kvps);
				$this->arr[]= "$timeout$key $val";
			} else {
				while (list($timeout, $kvps)= each($this->rule['timeout'])) {
					$timeout= $timeout == 'all' ? '' : "$timeout.";

					if (count($kvps) == 1) {
						list($key, $val)= each($kvps);
						$this->arr[]= "$timeout$key $val";
					} else {
						while (list($key, $val)= each($kvps)) {
							$this->arr[]= "$timeout$key $val";
						}
					}
				}
			}
		}
	}
}
?>