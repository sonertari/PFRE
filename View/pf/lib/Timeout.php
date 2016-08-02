<?php 
/* $pfre: Timeout.php,v 1.6 2016/07/31 14:19:13 soner Exp $ */

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
	function __construct($str)
	{
		$this->keywords = array(
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

		// Base should not merge keywords
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
		$this->str= '';

		if (count($this->rule['proto'])) {
			/// @attention This reset is critical if a page calls this function twice, and it does so in this case
			reset($this->rule['proto']);

			if (count($this->rule['proto']) == 1 && count(array_values($this->rule['proto'][key($this->rule['proto'])])) == 1) {
				list($proto, $kvps)= each($this->rule['proto']);
				$proto= $proto == 'all' ? '' : "$proto.";

				list($key, $val)= each($kvps);
				$this->str= "set timeout $proto$key $val";
			} else {
				$this->str= 'set timeout {';
				while (list($proto, $kvps)= each($this->rule['proto'])) {
					$proto= $proto == 'all' ? '' : "$proto.";

					if (count($kvps) == 1) {
						list($key, $val)= each($kvps);
						$this->str.= " $proto$key $val,";
					} else {
						while (list($key, $val)= each($kvps)) {
							$this->str.= " $proto$key $val,";
						}
					}
				}
				$this->str= rtrim($this->str, ',');
				$this->str.= ' }';
			}
		}

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
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
			if (count($this->rule['proto'])) {
				reset($this->rule['proto']);
				while (list($proto, $kvps)= each($this->rule['proto'])) {	
					$proto= $proto == 'all' ? '' : "$proto.";
					while (list($key, $val)= each($kvps)) {
						echo "$proto$key: $val<br>";
					}
				}
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputTimeout('frag', 'frag', 'all');
		$this->inputTimeout('interval', 'interval', 'all');
		$this->inputTimeout('src.track', 'src_track', 'all');

		$this->inputTimeout('first', 'tcp_first', 'tcp');
		$this->inputTimeout('opening', 'tcp_opening', 'tcp');
		$this->inputTimeout('established', 'tcp_established', 'tcp');
		$this->inputTimeout('closing', 'tcp_closing', 'tcp');
		$this->inputTimeout('finwait', 'tcp_finwait', 'tcp');
		$this->inputTimeout('closed', 'tcp_closed', 'tcp');

		$this->inputTimeout('first', 'udp_first', 'udp');
		$this->inputTimeout('single', 'udp_single', 'udp');
		$this->inputTimeout('multiple', 'udp_multiple', 'udp');

		$this->inputTimeout('first', 'icmp_first', 'icmp');
		$this->inputTimeout('error', 'icmp_error', 'icmp');

		$this->inputTimeout('first', 'other_first', 'other');
		$this->inputTimeout('single', 'other_single', 'other');
		$this->inputTimeout('multiple', 'other_multiple', 'other');

		$this->inputTimeout('start', 'adaptive_start', 'adaptive');
		$this->inputTimeout('end', 'adaptive_end', 'adaptive');

		$this->inputKey('comment');
		$this->inputDelEmpty(FALSE);
	}

	function inputTimeout($key, $var, $parent)
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
		$this->editSrcTrack();
		$this->editTcpTimeouts();
		$this->editUdpTimeouts();
		$this->editIcmpTimeouts();
		$this->editOtherTimeouts();
		$this->editAdaptiveTimeouts();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
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
				<?php echo _TITLE('Src track').':' ?>
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
				<?php $this->PrintHelp('tcp_timeout') ?><?php echo _TITLE('TCP').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeout('tcp', 'first');
					$this->editTimeout('tcp', 'opening');
					$this->editTimeout('tcp', 'established');
					$this->editTimeout('tcp', 'closing');
					$this->editTimeout('tcp', 'finwait');
					$this->editTimeout('tcp', 'closed');
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
				<?php $this->PrintHelp('udp_timeout') ?><?php echo _TITLE('UDP').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeout('udp', 'first');
					$this->editTimeout('udp', 'single');
					$this->editTimeout('udp', 'multiple');
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
				<?php $this->PrintHelp('icmp_timeout') ?><?php echo _TITLE('ICMP').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeout('icmp', 'first');
					$this->editTimeout('icmp', 'error');
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
				<?php $this->PrintHelp('other_timeout') ?><?php echo _TITLE('Other').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeout('other', 'first');
					$this->editTimeout('other', 'single');
					$this->editTimeout('other', 'multiple');
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
				<?php $this->PrintHelp('adaptive_timeout') ?><?php echo _TITLE('Adaptive').':' ?>
			</td>
			<td>
				<table style="width: auto;">
					<?php
					$this->editTimeout('adaptive', 'start');
					$this->editTimeout('adaptive', 'end');
					?>
				</table>
			</td>
		</tr>
		<?php
	}

	function editTimeout($proto, $key)
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