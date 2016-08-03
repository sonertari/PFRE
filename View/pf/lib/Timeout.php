<?php 
/* $pfre: Timeout.php,v 1.8 2016/08/02 12:01:08 soner Exp $ */

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

	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keywords,
			array(
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
				)
			);

		parent::__construct($str);
	}

	function split()
	{
		$this->words= preg_split('/[\s,\t\.]+/', $this->str, -1, PREG_SPLIT_NO_EMPTY);
	}

	function parseAll()
	{
		$this->rule['proto']['all'][$this->words[$this->index]]= $this->words[++$this->index];
	}

	function parseSrcTrack()
	{
		if ($this->words[$this->index + 1] == 'track') {
			$this->rule['proto']['all']['src.track']= $this->words[$this->index + 2];
			$this->index+= 2;
		}
	}

	function parseTimeout()
	{
		$this->rule['proto'][$this->words[$this->index]][$this->words[$this->index + 1]]= $this->words[$this->index + 2];
		$this->index+= 2;
	}

	function generate()
	{
		$this->str= 'set timeout ';
		$this->genTimeout();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genTimeout()
	{
		if (count($this->rule['proto'])) {
			$this->arr= array();

			$this->genTimeoutOpts();

			if (count($this->arr)) {
				$this->str.= count($this->arr) > 1 ? '{ ' : '';
				$this->str.= implode(', ', $this->arr);
				$this->str.= count($this->arr) > 1 ? ' }' : '';
			}
		}
	}
	
	function genTimeoutOpts()
	{
		/// @attention This reset is critical if a page calls this function twice, and it does so in this case
		reset($this->rule['proto']);

		if (count($this->rule['proto']) == 1 && count(array_values($this->rule['proto'][key($this->rule['proto'])])) == 1) {
			list($proto, $kvps)= each($this->rule['proto']);
			$proto= $proto == 'all' ? '' : "$proto.";

			list($key, $val)= each($kvps);
			$this->arr[]= "$proto$key $val";
		} else {
			while (list($proto, $kvps)= each($this->rule['proto'])) {
				$proto= $proto == 'all' ? '' : "$proto.";

				if (count($kvps) == 1) {
					list($key, $val)= each($kvps);
					$this->arr[]= "$proto$key $val";
				} else {
					while (list($key, $val)= each($kvps)) {
						$this->arr[]= "$proto$key $val";
					}
				}
			}
		}
	}
	
	function display($rulenumber, $count)
	{
		$this->dispHead($rulenumber);
		$this->dispTimeout();
		$this->dispTail($rulenumber, $count);
	}
	
	function dispTimeout()
	{
		?>
		<td title="Timeout" colspan="12">
			<?php
			$this->arr= array();
			$this->dispTimeoutOpts();
			echo implode('<br>', $this->arr);
			?>
		</td>
		<?php
	}

	function dispTimeoutOpts()
	{
		if (count($this->rule['proto'])) {
			reset($this->rule['proto']);
			while (list($proto, $kvps)= each($this->rule['proto'])) {	
				$proto= $proto == 'all' ? '' : "$proto.";
				while (list($key, $val)= each($kvps)) {
					$this->arr[]= "$proto$key: $val";
				}
			}
		}
	}

	function input()
	{
		$this->inputTimeoutOpt('frag', 'frag', 'all');
		$this->inputTimeoutOpt('interval', 'interval', 'all');

		$this->inputTimeout();

		$this->inputKey('comment');
		$this->inputDelEmpty(FALSE);
	}

	function inputTimeout()
	{
		$this->inputTimeoutOpt('src.track', 'src_track', 'all');

		$this->inputTimeoutOpt('first', 'tcp_first', 'tcp');
		$this->inputTimeoutOpt('opening', 'tcp_opening', 'tcp');
		$this->inputTimeoutOpt('established', 'tcp_established', 'tcp');
		$this->inputTimeoutOpt('closing', 'tcp_closing', 'tcp');
		$this->inputTimeoutOpt('finwait', 'tcp_finwait', 'tcp');
		$this->inputTimeoutOpt('closed', 'tcp_closed', 'tcp');

		$this->inputTimeoutOpt('first', 'udp_first', 'udp');
		$this->inputTimeoutOpt('single', 'udp_single', 'udp');
		$this->inputTimeoutOpt('multiple', 'udp_multiple', 'udp');

		$this->inputTimeoutOpt('first', 'icmp_first', 'icmp');
		$this->inputTimeoutOpt('error', 'icmp_error', 'icmp');

		$this->inputTimeoutOpt('first', 'other_first', 'other');
		$this->inputTimeoutOpt('single', 'other_single', 'other');
		$this->inputTimeoutOpt('multiple', 'other_multiple', 'other');

		$this->inputTimeoutOpt('start', 'adaptive_start', 'adaptive');
		$this->inputTimeoutOpt('end', 'adaptive_end', 'adaptive');
	}

	function inputTimeoutOpt($key, $var, $parent)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$this->rule['proto'][$parent][$key]= trim(filter_input(INPUT_POST, $var), '" ');
		}
	}

	function edit($rulenumber, $modified, $testResult, $action)
	{
		$this->index= 0;
		$this->rulenumber= $rulenumber;

		$this->editHead($modified);

		$this->editFragment();
		$this->editInterval();

		$this->editTimeout();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
	}

	function editTimeout()
	{
		$this->editSrcTrack();
		$this->editTcpTimeouts();
		$this->editUdpTimeouts();
		$this->editIcmpTimeouts();
		$this->editOtherTimeouts();
		$this->editAdaptiveTimeouts();
	}

	function editFragment()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Fragment').':' ?>
			</td>
			<td>
				<input type="text" id="frag" name="frag" size="10" value="<?php echo $this->rule['proto']['all']['frag']; ?>" placeholder="number" />
				<?php $this->PrintHelp('frag') ?>
			</td>
		</tr>
		<?php
	}

	function editInterval()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Interval').':' ?>
			</td>
			<td>
				<input type="text" id="interval" name="interval" size="10" value="<?php echo $this->rule['proto']['all']['interval']; ?>" placeholder="number" />
				<?php $this->PrintHelp('interval') ?>
			</td>
		</tr>
		<?php
	}

	function editSrcTrack()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Source track timeout').':' ?>
			</td>
			<td>
				<input type="text" id="src_track" name="src_track" size="10" value="<?php echo $this->rule['proto']['all']['src.track']; ?>" placeholder="number" />
				<?php $this->PrintHelp('src.track') ?>
			</td>
		</tr>
		<?php
	}

	function editTcpTimeouts()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php $this->PrintHelp('tcp_timeout') ?><?php echo _TITLE('TCP timeouts').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeoutOpt('tcp', 'first');
					$this->editTimeoutOpt('tcp', 'opening');
					$this->editTimeoutOpt('tcp', 'established');
					$this->editTimeoutOpt('tcp', 'closing');
					$this->editTimeoutOpt('tcp', 'finwait');
					$this->editTimeoutOpt('tcp', 'closed');
					?>
				</table>
			</td>
		</tr>
		<?php
	}

	function editUdpTimeouts()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php $this->PrintHelp('udp_timeout') ?><?php echo _TITLE('UDP timeouts').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeoutOpt('udp', 'first');
					$this->editTimeoutOpt('udp', 'single');
					$this->editTimeoutOpt('udp', 'multiple');
					?>
				</table>
			</td>
		</tr>
		<?php
	}

	function editIcmpTimeouts()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php $this->PrintHelp('icmp_timeout') ?><?php echo _TITLE('ICMP timeouts').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeoutOpt('icmp', 'first');
					$this->editTimeoutOpt('icmp', 'error');
					?>
				</table>
			</td>
		</tr>
		<?php
	}

	function editOtherTimeouts()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php $this->PrintHelp('other_timeout') ?><?php echo _TITLE('Other timeouts').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeoutOpt('other', 'first');
					$this->editTimeoutOpt('other', 'single');
					$this->editTimeoutOpt('other', 'multiple');
					?>
				</table>
			</td>
		</tr>
		<?php
	}

	function editAdaptiveTimeouts()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php $this->PrintHelp('adaptive_timeout') ?><?php echo _TITLE('Adaptive timeouts').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeoutOpt('adaptive', 'start');
					$this->editTimeoutOpt('adaptive', 'end');
					?>
				</table>
			</td>
		</tr>
		<?php
	}

	function editTimeoutOpt($proto, $key)
	{
		?>
		<tr>
			<td class="ifs">
				<input type="text" size="10" id="<?php echo $proto ?>_<?php echo $key ?>" name="<?php echo $proto ?>_<?php echo $key ?>" value="<?php echo $this->rule['proto'][$proto][$key]; ?>" placeholder="number" />
			</td>
			<td class="optitle"><?php echo $key ?></td>
		</tr>
		<?php
	}
}
?>