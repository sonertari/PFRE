<?php 
/* $pfre: Timeout.php,v 1.2 2016/07/30 03:37:37 soner Exp $ */

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

/*
 * Copyright (c) 2004 Allard Consulting.  All rights reserved.
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
 *    product includes software developed by Allard Consulting
 *    and its contributors.
 * 4. Neither the name of Allard Consulting nor the names of
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
				'method' => 'setAll',
				'params' => array(),
				),
			'interval' => array(
				'method' => 'setAll',
				'params' => array(),
				),
			'src' => array(
				'method' => 'setSrcTrack',
				'params' => array(),
				),
			'tcp' => array(
				'method' => 'setTimeout',
				'params' => array(),
				),
			'udp' => array(
				'method' => 'setTimeout',
				'params' => array(),
				),
			'icmp' => array(
				'method' => 'setTimeout',
				'params' => array(),
				),
			'other' => array(
				'method' => 'setTimeout',
				'params' => array(),
				),
			'adaptive' => array(
				'method' => 'setTimeout',
				'params' => array(),
				),
			);

		// Base should not merge keywords
		parent::__construct($str, FALSE);
	}

	function split()
	{
		$this->words= preg_split('/[\s,\t\.]+/', $this->str, -1, PREG_SPLIT_NO_EMPTY);
	}

	function setAll()
	{
		$this->rule['proto']['all'][$this->words[$this->index]]= $this->words[++$this->index];
	}

	function setSrcTrack()
	{
		if ($this->words[$this->index + 1] == 'track') {
			$this->rule['proto']['all']['src.track']= $this->words[$this->index + 2];
			$this->index+= 2;
		}
	}

	function setTimeout()
	{
		$this->rule['proto'][$this->words[$this->index]][$this->words[$this->index + 1]]= $this->words[$this->index + 2];
		$this->index+= 2;
	}

	function generate()
	{
		$str= '';
		if (count($this->rule['proto'])) {
			/// @attention This reset is critical if a page calls this function twice, and it does in this case
			reset($this->rule['proto']);

			if (count($this->rule['proto']) == 1 && count(array_values($this->rule['proto'][key($this->rule['proto'])])) == 1) {
				list($proto, $kvps)= each($this->rule['proto']);
				$proto= $proto == 'all' ? '' : "$proto.";

				list($key, $val)= each($kvps);
				$str= "set timeout $proto$key $val";
			} else {
				$str= 'set timeout {';
				while (list($proto, $kvps)= each($this->rule['proto'])) {
					$proto= $proto == 'all' ? '' : "$proto.";

					if (count($kvps) == 1) {
						list($key, $val)= each($kvps);
						$str.= " $proto$key $val,";
					} else {
						while (list($key, $val)= each($kvps)) {
							$str.= " $proto$key $val,";
						}
					}
				}
				$str= rtrim($str, ",");
				$str.= " }";
			}
		}

		if ($this->rule['comment']) {
			$str.= " # " . trim(stripslashes($this->rule['comment']));
		}
		$str.= "\n";
		return $str;
	}
	
	function display($rulenumber, $count, $class)
	{
		?>
		<tr title="<?php echo $this->cat; ?> rule"<?php echo $class; ?>>
			<td class="center">
				<?php echo $rulenumber; ?>
			</td>
			<td title="Category" class="category">
				<?php echo $this->cat; ?>
			</td>
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
			<td class="comment">
				<?php echo stripslashes($this->rule['comment']); ?>
			</td>
			<td class="edit">
				<?php
				$this->PrintEditLinks($rulenumber, "conf.php?sender=timeout&amp;rulenumber=$rulenumber", $count);
				?>
			</td>
		</tr>
		<?php
	}
	
	function processInput()
	{
		if (count($_POST)) {
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');

			if (filter_has_var(INPUT_POST, 'frag')) {
				if (strlen(trim(filter_input(INPUT_POST, 'frag')))) {
					$this->rule['proto']['all']['frag']= trim(filter_input(INPUT_POST, 'frag'));
				} else {
					unset($this->rule['proto']['all']['frag']);
				}
			}
			if (filter_has_var(INPUT_POST, 'interval')) {
				if (strlen(trim(filter_input(INPUT_POST, 'interval')))) {
					$this->rule['proto']['all']['interval']= trim(filter_input(INPUT_POST, 'interval'));
				} else {
					unset($this->rule['proto']['all']['interval']);
				}
			}
			/// @attention POST cannot handle dots in keys: src.track, use src_track instead
			if (filter_has_var(INPUT_POST, 'src_track')) {
				if (strlen(trim(filter_input(INPUT_POST, 'src_track')))) {
					$this->rule['proto']['all']['src.track']= trim(filter_input(INPUT_POST, 'src_track'));
				} else {
					unset($this->rule['proto']['all']['src.track']);
				}
			}
			if (filter_has_var(INPUT_POST, 'tcp_first')) {
				if (strlen(trim(filter_input(INPUT_POST, 'tcp_first')))) {
					$this->rule['proto']['tcp']['first']= trim(filter_input(INPUT_POST, 'tcp_first'));
				} else {
					unset($this->rule['proto']['tcp']['first']);
				}
			}
			if (filter_has_var(INPUT_POST, 'tcp_opening')) {
				if (strlen(trim(filter_input(INPUT_POST, 'tcp_opening')))) {
					$this->rule['proto']['tcp']['opening']= trim(filter_input(INPUT_POST, 'tcp_opening'));
				} else {
					unset($this->rule['proto']['tcp']['opening']);
				}
			}
			if (filter_has_var(INPUT_POST, 'tcp_established')) {
				if (strlen(trim(filter_input(INPUT_POST, 'tcp_established')))) {
					$this->rule['proto']['tcp']['established']= trim(filter_input(INPUT_POST, 'tcp_established'));
				} else {
					unset($this->rule['proto']['tcp']['established']);
				}
			}
			if (filter_has_var(INPUT_POST, 'tcp_closing')) {
				if (strlen(trim(filter_input(INPUT_POST, 'tcp_closing')))) {
					$this->rule['proto']['tcp']['closing']= trim(filter_input(INPUT_POST, 'tcp_closing'));
				} else {
					unset($this->rule['proto']['tcp']['closing']);
				}
			}
			if (filter_has_var(INPUT_POST, 'tcp_finwait')) {
				if (strlen(trim(filter_input(INPUT_POST, 'tcp_finwait')))) {
					$this->rule['proto']['tcp']['finwait']= trim(filter_input(INPUT_POST, 'tcp_finwait'));
				} else {
					unset($this->rule['proto']['tcp']['finwait']);
				}
			}
			if (filter_has_var(INPUT_POST, 'tcp_closed')) {
				if (strlen(trim(filter_input(INPUT_POST, 'tcp_closed')))) {
					$this->rule['proto']['tcp']['closed']= trim(filter_input(INPUT_POST, 'tcp_closed'));
				} else {
					unset($this->rule['proto']['tcp']['closed']);
				}
			}
			if (filter_has_var(INPUT_POST, 'udp_first')) {
				if (strlen(trim(filter_input(INPUT_POST, 'udp_first')))) {
					$this->rule['proto']['udp']['first']= trim(filter_input(INPUT_POST, 'udp_first'));
				} else {
					unset($this->rule['proto']['udp']['first']);
				}
			}
			if (filter_has_var(INPUT_POST, 'udp_single')) {
				if (strlen(trim(filter_input(INPUT_POST, 'udp_single')))) {
					$this->rule['proto']['udp']['single']= trim(filter_input(INPUT_POST, 'udp_single'));
				} else {
					unset($this->rule['proto']['udp']['single']);
				}
			}
			if (filter_has_var(INPUT_POST, 'udp_multiple')) {
				if (strlen(trim(filter_input(INPUT_POST, 'udp_multiple')))) {
					$this->rule['proto']['udp']['multiple']= trim(filter_input(INPUT_POST, 'udp_multiple'));
				} else {
					unset($this->rule['proto']['udp']['multiple']);
				}
			}
			if (filter_has_var(INPUT_POST, 'icmp_first')) {
				if (strlen(trim(filter_input(INPUT_POST, 'icmp_first')))) {
					$this->rule['proto']['icmp']['first']= trim(filter_input(INPUT_POST, 'icmp_first'));
				} else {
					unset($this->rule['proto']['icmp']['first']);
				}
			}
			if (filter_has_var(INPUT_POST, 'icmp_error')) {
				if (strlen(trim(filter_input(INPUT_POST, 'icmp_error')))) {
					$this->rule['proto']['icmp']['error']= trim(filter_input(INPUT_POST, 'icmp_error'));
				} else {
					unset($this->rule['proto']['icmp']['error']);
				}
			}
			if (filter_has_var(INPUT_POST, 'other_first')) {
				if (strlen(trim(filter_input(INPUT_POST, 'other_first')))) {
					$this->rule['proto']['other']['first']= trim(filter_input(INPUT_POST, 'other_first'));
				} else {
					unset($this->rule['proto']['other']['first']);
				}
			}
			if (filter_has_var(INPUT_POST, 'other_single')) {
				if (strlen(trim(filter_input(INPUT_POST, 'other_single')))) {
					$this->rule['proto']['other']['single']= trim(filter_input(INPUT_POST, 'other_single'));
				} else {
					unset($this->rule['proto']['other']['single']);
				}
			}
			if (filter_has_var(INPUT_POST, 'other_multiple')) {
				if (strlen(trim(filter_input(INPUT_POST, 'other_multiple')))) {
					$this->rule['proto']['other']['multiple']= trim(filter_input(INPUT_POST, 'other_multiple'));
				} else {
					unset($this->rule['proto']['other']['multiple']);
				}
			}
			if (filter_has_var(INPUT_POST, 'adaptive_start')) {
				if (strlen(trim(filter_input(INPUT_POST, 'adaptive_start')))) {
					$this->rule['proto']['adaptive']['start']= trim(filter_input(INPUT_POST, 'adaptive_start'));
				} else {
					unset($this->rule['proto']['adaptive']['start']);
				}
			}
			if (filter_has_var(INPUT_POST, 'adaptive_end')) {
				if (strlen(trim(filter_input(INPUT_POST, 'adaptive_end')))) {
					$this->rule['proto']['adaptive']['end']= trim(filter_input(INPUT_POST, 'adaptive_end'));
				} else {
					unset($this->rule['proto']['adaptive']['end']);
				}
			}
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		$href= "conf.php?sender=timeout&rulenumber=$rulenumber";
		?>
		<h2>Edit Timeout Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Timeout') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" action="<?php echo $href; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Fragment').':' ?>
					</td>
					<td>
						<input type="text" id="frag" name="frag" size="10" value="<?php echo $this->rule['proto']['all']['frag']; ?>" />
						<?php $this->PrintHelp('frag') ?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Interval').':' ?>
					</td>
					<td>
						<input type="text" id="interval" name="interval" size="10" value="<?php echo $this->rule['proto']['all']['interval']; ?>" />
						<?php $this->PrintHelp('interval') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Src track').':' ?>
					</td>
					<td>
						<input type="text" id="src_track" name="src_track" size="10" value="<?php echo $this->rule['proto']['all']['src.track']; ?>" />
						<?php $this->PrintHelp('src.track') ?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('TCP').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="tcp_first" name="tcp_first" value="<?php echo $this->rule['proto']['tcp']['first']; ?>" />
											</td>
											<td class="optitle">first<?php $this->PrintHelp('tcp_timeout') ?></td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="tcp_opening" name="tcp_opening" value="<?php echo $this->rule['proto']['tcp']['opening']; ?>" />
											</td>
											<td class="optitle">opening</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="tcp_established" name="tcp_established" value="<?php echo $this->rule['proto']['tcp']['established']; ?>" />
											</td>
											<td class="optitle">established</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="tcp_closing" name="tcp_closing" value="<?php echo $this->rule['proto']['tcp']['closing']; ?>" />
											</td>
											<td class="optitle">closing</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="tcp_finwait" name="tcp_finwait" value="<?php echo $this->rule['proto']['tcp']['finwait']; ?>" />
											</td>
											<td class="optitle">fin wait</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="tcp_closed" name="tcp_closed" value="<?php echo $this->rule['proto']['tcp']['closed']; ?>" />
											</td>
											<td class="optitle">closed</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('UDP').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="udp_first" name="udp_first" value="<?php echo $this->rule['proto']['udp']['first']; ?>" />
											</td>
											<td class="optitle">first<?php $this->PrintHelp('udp_timeout') ?></td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="udp_single" name="udp_single" value="<?php echo $this->rule['proto']['udp']['single']; ?>" />
											</td>
											<td class="optitle">single</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="udp_multiple" name="udp_multiple" value="<?php echo $this->rule['proto']['udp']['multiple']; ?>" />
											</td>
											<td class="optitle">multiple</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('ICMP').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="icmp_first" name="icmp_first" value="<?php echo $this->rule['proto']['icmp']['first']; ?>" />
											</td>
											<td class="optitle">first<?php $this->PrintHelp('icmp_timeout') ?></td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="icmp_error" name="icmp_error" value="<?php echo $this->rule['proto']['icmp']['error']; ?>" />
											</td>
											<td class="optitle">error</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Other').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="other_first" name="other_first" value="<?php echo $this->rule['proto']['other']['first']; ?>" />
											</td>
											<td class="optitle">first<?php $this->PrintHelp('other_timeout') ?></td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="other_single" name="other_single" value="<?php echo $this->rule['proto']['other']['single']; ?>" />
											</td>
											<td class="optitle">single</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="other_multiple" name="other_multiple" value="<?php echo $this->rule['proto']['other']['multiple']; ?>" />
											</td>
											<td class="optitle">multiple</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Adaptive').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="adaptive_start" name="adaptive_start" value="<?php echo $this->rule['proto']['adaptive']['start']; ?>" />
											</td>
											<td class="optitle">start<?php $this->PrintHelp('adaptive_timeout') ?></td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" size="10" id="adaptive_end" name="adaptive_end" value="<?php echo $this->rule['proto']['adaptive']['end']; ?>" />
											</td>
											<td class="optitle">end</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Comment').':' ?>
					</td>
					<td>
						<input type="text" id="comment" name="comment" value="<?php echo stripslashes($this->rule['comment']); ?>" size="80" />
					</td>
				</tr>
			</table>
			<div class="buttons">
				<input type="submit" id="apply" name="apply" value="Apply" />
				<input type="submit" id="save" name="save" value="Save" <?php echo $modified ? '' : 'disabled'; ?> />
				<input type="submit" id="cancel" name="cancel" value="Cancel" />
				<input type="checkbox" id="forcesave" name="forcesave" <?php echo $modified && !$testResult ? '' : 'disabled'; ?> />
				<label for="forcesave">Save with errors</label>
				<input type="hidden" name="state" value="<?php echo $action; ?>" />
			</div>
		</form>
		<?php
	}
}
?>