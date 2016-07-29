<?php 
/* $pfre: TimeoutOptions.php,v 1.6 2016/07/26 23:08:20 soner Exp $ */

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
 
class TimeoutOptions extends Rule
{
	function parse($str)
	{
		$this->rule= array();
		if (strpos($str, "#")) {
			$this->rule['comment']= substr($str, strpos($str, "#") + '1');
			$str= substr($str, '0', strpos($str, '#'));
		}
		
		/*
		 * Sanitize the rule string so that we can deal with '{foo' as '{ foo' in 
		 * the code further down without any special treatment
		 */
		$str= preg_replace("/{/", " { ", $str);
		$str= preg_replace("/}/", " } ", $str);
		$str= preg_replace("/\(/", " \( ", $str);
		$str= preg_replace("/\)/", " \) ", $str);
		$str= preg_replace("/,/", " , ", $str);
		$str= preg_replace("/\"/", " \" ", $str);
		
		$words= preg_split("/[\s,\t\.]+/", $str, '-1', PREG_SPLIT_NO_EMPTY);
		
		for ($i= '0'; $i < count($words); $i++) {
			switch ($words[$i]) {
				case "timeout":
					break;
				case "tcp":
				case "udp":
				case "icmp":
				case "other":
				case "adaptive":
					$this->rule['proto'][$words[$i]][$words[$i + 1]]= $words[$i + 2];
					$i+= 2;
					break;
			}
		}
	}

	function generate()
	{
		/// @attention This reset is critical if a page calls this function twice
		reset($this->rule['proto']);
		
		if (count($this->rule['proto']) == 1 && count(array_values($this->rule['proto'][key($this->rule['proto'])])) == 1) {
			list($proto, $kvps)= each($this->rule['proto']);
			list($key, $val)= each($kvps);
			$str= "set timeout $proto.$key $val";
		} else {
			$str= 'set timeout {';
			while (list($proto, $kvps)= each($this->rule['proto'])) {
				if (count($kvps) == 1) {
					list($key, $val)= each($kvps);
					$str.= " $proto.$key $val,";
				} else {
					while (list($key, $val)= each($kvps)) {
						$str.= " $proto.$key $val,";
					}
				}
			}
			$str= rtrim($str, ",");
			$str.= " }";
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
				reset($this->rule['proto']);
				while (list($proto, $kvps)= each($this->rule['proto'])) {							
					while (list($key, $val)= each($kvps)) {
						echo "$proto.$key: $val<br>";
					}
				}
				?>
			</td>
			<td class="comment">
				<?php echo stripslashes($this->rule['comment']); ?>
			</td>
			<td class="edit">
				<?php
				$this->PrintEditLinks($rulenumber, "conf.php?sender=timeoutoptions&amp;rulenumber=$rulenumber", $count);
				?>
			</td>
		</tr>
		<?php
	}
	
	function processInput()
	{
		if (count($_POST)) {
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');

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
		$href= "conf.php?sender=timeoutoptions&rulenumber=$rulenumber";
		?>
		<h2>Edit Timeout Options Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" action="<?php echo $href; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
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
											<td class="optitle">first</td>
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
				<tr class="evenline">
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
											<td class="optitle">first</td>
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
				<tr class="oddline">
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
											<td class="optitle">first</td>
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
				<tr class="evenline">
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
											<td class="optitle">first</td>
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
				<tr class="oddline">
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
											<td class="optitle">start</td>
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
				<tr class="evenline">
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