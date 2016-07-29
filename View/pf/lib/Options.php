<?php 
/* $pfre: Options.php,v 1.8 2016/07/27 09:15:30 soner Exp $ */

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
 
class Options extends Rule
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
		
		/*
		 * Need to handle fingerprints differently since we're
		 * expecting a dot (.) in the filename
		 */
		$words= preg_split("/[\s,\t]+/", $str, '-1', PREG_SPLIT_NO_EMPTY);
		
		for ($i= '0'; $i < count($words); $i++) {
			switch ($words[$i]) {
				case "loginterface":
					$this->rule['option']['loginterface']= $words[++$i];
					break;
				case "block-policy":
					$this->rule['option']['block-policy']= $words[++$i];
					break;
				case "state-policy":
					$this->rule['option']['state-policy']= $words[++$i];
					break;
				case "fingerprints":
					$this->rule['option']['fingerprints']= $words['3'];
					break;
				case "optimization":
					$this->rule['option']['optimization']= $words[++$i];
					break;
				case "ruleset-optimization":
					$this->rule['option']['ruleset-optimization']= $words[++$i];
					break;
				case "debug":
					$this->rule['option']['debug']= $words[++$i];
					break;
				case "states":
					$this->rule['option']['states']= $words[++$i];
					break;
				case "frags":
					$this->rule['option']['frags']= $words[++$i];
					break;
				case "src-nodes":
					$this->rule['option']['src-nodes']= $words[++$i];
					break;
				case "tables":
					$this->rule['option']['tables']= $words[++$i];
					break;
				case "table-entries":
					$this->rule['option']['table-entries']= $words[++$i];
					break;
				case "skip":
					list($this->rule['option']['skip'], $i)= $this->parseItem($words, ++$i);
					break;
			}
		}
	}

	function generate()
	{
		$str= '';
		if (isset($this->rule['option']['loginterface'])) {
			$str.= "set loginterface " . $this->rule['option']['loginterface'];
		} elseif (isset($this->rule['option']['optimization'])) {
			$str.= "set optimization " . $this->rule['option']['optimization'];
		} elseif (isset($this->rule['option']['ruleset-optimization'])) {
			$str.= "set ruleset-optimization " . $this->rule['option']['ruleset-optimization'];
		} elseif (isset($this->rule['option']['block-policy'])) {
			$str.= "set block-policy " . $this->rule['option']['block-policy'];
		} elseif (isset($this->rule['option']['state-policy'])) {
			$str.= "set state-policy " . $this->rule['option']['state-policy'];
		} elseif (isset($this->rule['option']['debug'])) {
			$str.= "set debug " . $this->rule['option']['debug'];
		} elseif (isset($this->rule['option']['fingerprints'])) {
			$str.= "set fingerprints \"" . preg_replace("/\"/", "", $this->rule['option']['fingerprints']) . "\"";
		} elseif (isset($this->rule['option']['states'])) {
			$str.= "set limit states " . $this->rule['option']['states'];
		} elseif (isset($this->rule['option']['frags'])) {
			$str.= "set limit frags " . $this->rule['option']['frags'];
		} elseif (isset($this->rule['option']['src-nodes'])) {
			$str.= "set limit src-nodes " . $this->rule['option']['src-nodes'];
		} elseif (isset($this->rule['option']['tables'])) {
			$str.= "set limit tables " . $this->rule['option']['tables'];
		} elseif (isset($this->rule['option']['table-entries'])) {
			$str.= "set limit table-entries " . $this->rule['option']['table-entries'];
		} elseif (isset($this->rule['option']['skip'])) {
			if (!is_array($this->rule['option']['skip'])) {
				$str.= "set skip on " . $this->rule['option']['skip'];
			} else {
				$str.= 'set skip on { ' . implode(' ', $this->rule['option']['skip']) . ' }';
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
			<td title="Option" colspan="12">
				<?php
				$option= key($this->rule['option']);
				$value= $this->rule['option'][$option];
				if (in_array($option, array('loginterface', 'optimization', 'ruleset-optimization', 'block-policy', 'state-policy', 'debug', 'fingerprints'))) {
					echo "$option: $value";
				} elseif (in_array($option, array('states', 'frags', 'src-nodes', 'tables', 'table-entries'))) {
					echo "$option limit: $value";
				} elseif ($option == 'skip') {
					if (!is_array($value)) {
						echo "skip on $value";
					} else {
						foreach ($value as $skip) {
							echo "skip on $skip<br>";
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
				$this->PrintEditLinks($rulenumber, "conf.php?sender=options&amp;rulenumber=$rulenumber", $count);
				?>
			</td>
		</tr>
		<?php
	}		
	
	function processInput()
	{
		if (count($_POST)) {
			$this->rule['comment']= $_POST['comment'];

			if (isset($_POST['block-policy'])) {
				$this->rule['option']['block-policy']= $_POST['block-policy'];
			}
			if (isset($_POST['optimization'])) {
				$this->rule['option']['optimization']= $_POST['optimization'];
			}
			if (isset($_POST['ruleset-optimization'])) {
				$this->rule['option']['ruleset-optimization']= $_POST['ruleset-optimization'];
			}
			if (isset($_POST['state-policy'])) {
				$this->rule['option']['state-policy']= $_POST['state-policy'];
			}
			if (isset($_POST['debug'])) {
				$this->rule['option']['debug']= $_POST['debug'];
			}
			if (isset($_POST['fingerprints'])) {
				$this->rule['option']['fingerprints']= trim(preg_replace("/\"/", "", $_POST['fingerprints']));
			}
			if (isset($_POST['states'])) {
				$this->rule['option']['states']= trim($_POST['states']);
			}
			if (isset($_POST['frags'])) {
				$this->rule['option']['frags']= trim($_POST['frags']);
			}
			if (isset($_POST['src-nodes'])) {
				$this->rule['option']['src-nodes']= trim($_POST['srcnodes']);
			}
			if (isset($_POST['tables'])) {
				$this->rule['option']['tables']= trim($_POST['tables']);
			}
			if (isset($_POST['table-entries'])) {
				$this->rule['option']['table-entries']= trim($_POST['table-entries']);
			}

			if (isset($_POST['loginterface'])) {
				if (strlen(trim($_POST['loginterface']))) {
					$this->rule['option']['loginterface']= trim($_POST['loginterface']);
				} else {
					$this->rule['option']['loginterface']= '';
				}
			}
			if (isset($_POST['skip'])) {
				if (strlen(trim($_POST['skip']))) {
					if (count(preg_split("/[\s,\t]+/", trim($_POST['skip']))) > 1) {
						unset($this->rule['option']['skip']);
						foreach (preg_split("/[\s,\t]+/", trim($_POST['skip'])) as $skip) {
							$this->rule['option']['skip'][]= $skip;
						}
					} else {
						$this->rule['option']['skip']= trim($_POST['skip']);
					}
				} else {
					$this->rule['option']['skip']= '';
				}
			}
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		// XXX: Fix this
		if (count($_POST)) {
			$type= $_POST['type'];
		}
		if (isset($this->rule['option']) && count($this->rule['option'])) {
			$type= key($this->rule['option']);
		}

		$href= "conf.php?sender=options&rulenumber=$rulenumber";
		?>
		<h2>Edit Options Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" action="<?php echo $href; ?>" method="post">
			<table id="nvp">
				<?php
				if (!isset($type)) {
					?>
					<tr class="oddline">
						<td class="title">
							<?php echo _TITLE('Select Option Type').':' ?>
						</td>
						<td>
							<select id="type" name="type">
								<option value="block-policy" <?php echo ($type == 'block-policy' ? 'selected' : ''); ?>>block-policy</option>
								<option value="optimization" <?php echo ($type == 'optimization' ? 'selected' : ''); ?>>optimization</option>
								<option value="ruleset-optimization" <?php echo ($type == 'ruleset-optimization' ? 'selected' : ''); ?>>ruleset-optimization</option>
								<option value="state-policy" <?php echo ($type == 'state-policy' ? 'selected' : ''); ?>>state-policy</option>
								<option value="fingerprints" <?php echo ($type == 'fingerprints' ? 'selected' : ''); ?>>fingerprints</option>
								<option value="loginterface" <?php echo ($type == 'loginterface' ? 'selected' : ''); ?>>loginterface</option>
								<option value="debug" <?php echo ($type == 'debug' ? 'selected' : ''); ?>>debug</option>
								<option value="skip" <?php echo ($type == 'skip' ? 'selected' : ''); ?>>skip</option>
								<option value="states" <?php echo ($type == 'states' ? 'selected' : ''); ?>>states</option>
								<option value="frags" <?php echo ($type == 'frags' ? 'selected' : ''); ?>>frags</option>
								<option value="srcnodes" <?php echo ($type == 'srcnodes' ? 'selected' : ''); ?>>srcnodes</option>
								<option value="tables" <?php echo ($type == 'tables' ? 'selected' : ''); ?>>tables</option>
								<option value="table-entries" <?php echo ($type == 'table-entries' ? 'selected' : ''); ?>>table-entries</option>
							</select>
						</td>
					</tr>
					<?php
				}
				?>
				<tr class="oddline">
					<?php
					if (isset($this->rule['option']['block-policy']) || $type == 'block-policy') {
						?>
						<td class="title">
							<?php echo _TITLE('Block Policy').':' ?>
						</td>
						<td>
							<select id="block-policy" name="block-policy">
								<option value="drop" label="drop" <?php echo ($this->rule['option']['block-policy'] == 'drop' ? 'selected' : ''); ?>>drop</option>
								<option value="return" label="return" <?php echo ($this->rule['option']['block-policy'] == 'return' ? 'selected' : ''); ?>>return</option>
							</select>
						</td>
						<?php
					}
					if (isset($this->rule['option']['optimization']) || $type == 'optimization') {
						?>
						<td class="title">
							<?php echo _TITLE('Optimization').':' ?>
						</td>
						<td>
							<select id="optimization" name="optimization">
								<option value="normal" <?php echo ($this->rule['option']['optimization'] == 'normal' ? 'selected' : ''); ?>>normal</option>
								<option value="high-latency" <?php echo ($this->rule['option']['optimization'] == 'high-latency' ? 'selected' : ''); ?>>high-latency</option>
								<option value="satellite" <?php echo ($this->rule['option']['optimization'] == 'satellite' ? 'selected' : ''); ?>>satellite</option>
								<option value="aggressive" <?php echo ($this->rule['option']['optimization'] == 'aggressive' ? 'selected' : ''); ?>>aggressive</option>
								<option value="conservative" <?php echo ($this->rule['option']['optimization'] == 'conservative' ? 'selected' : ''); ?>>conservative</option>
							</select>
						</td>
						<?php
					}
					if (isset($this->rule['option']['ruleset-optimization']) || $type == 'ruleset-optimization') {
						?>
						<td class="title">
							<?php echo _TITLE('Ruleset Optimization').':' ?>
						</td>
						<td>
							<select id="ruleset-optimization" name="ruleset-optimization">
								<option value="none" <?php echo ($this->rule['option']['ruleset-optimization'] == 'none' ? 'selected' : ''); ?>>none</option>
								<option value="basic" <?php echo ($this->rule['option']['ruleset-optimization'] == 'basic' ? 'selected' : ''); ?>>basic</option>
								<option value="profile" <?php echo ($this->rule['option']['ruleset-optimization'] == 'profile' ? 'selected' : ''); ?>>profile</option>
							</select>
						</td>
						<?php
					}
					if (isset($this->rule['option']['state-policy']) || $type == 'state-policy') {
						?>
						<td class="title">
							<?php echo _TITLE('State Policy').':' ?>
						</td>
						<td>
							<select id="state-policy" name="state-policy">
								<option value="if-bound" <?php echo ($this->rule['option']['state-policy'] == 'if-bound' ? 'selected' : ''); ?>>if-bound</option>
								<option value="floating" <?php echo ($this->rule['option']['state-policy'] == 'floating' ? 'selected' : ''); ?>>floating</option>
							</select>
						</td>
						<?php
					}
					if (isset($this->rule['option']['fingerprints']) || $type == 'fingerprints') {
						?>
						<td class="title">
							<?php echo _TITLE('Fingerprints File').':' ?>
						</td>
						<td>
							<input type="text" size="10" id="fingerprints" name="fingerprints" value="<?php echo $this->rule['option']['fingerprints']; ?>" placeholder="filename"/>
						</td>
						<?php
					}
					if (isset($this->rule['option']['loginterface']) || $type == 'loginterface') {
						?>
						<td class="title">
							<?php echo _TITLE('Log Interface').':' ?>
						</td>
						<td>
							<input type="text" size="10" id="loginterface" name="loginterface" value="<?php echo $this->rule['option']['loginterface']; ?>"  placeholder="interface"/>
						</td>
						<?php
					}
					if (isset($this->rule['option']['debug']) || $type == 'debug') {
						?>
						<td class="title">
							<?php echo _TITLE('Debug').':' ?>
						</td>
						<td>
							<select id="debug" name="debug">
								<option value="emerg" <?php echo ($this->rule['option']['debug'] == 'emerg' ? 'selected' : ''); ?>>emerg</option>
								<option value="alert" <?php echo ($this->rule['option']['debug'] == 'alert' ? 'selected' : ''); ?>>alert</option>
								<option value="crit" <?php echo ($this->rule['option']['debug'] == 'crit' ? 'selected' : ''); ?>>crit</option>
								<option value="err" <?php echo ($this->rule['option']['debug'] == 'err' ? 'selected' : ''); ?>>err</option>
								<option value="warning" <?php echo ($this->rule['option']['debug'] == 'warning' ? 'selected' : ''); ?>>warning</option>
								<option value="notice" <?php echo ($this->rule['option']['debug'] == 'notice' ? 'selected' : ''); ?>>notice</option>
								<option value="info" <?php echo ($this->rule['option']['debug'] == 'info' ? 'selected' : ''); ?>>info</option>
								<option value="debug" <?php echo ($this->rule['option']['debug'] == 'debug' ? 'selected' : ''); ?>>debug</option>
							</select>
						</td>
						<?php
					}
					if (isset($this->rule['option']['skip']) || $type == 'skip') {
						?>
						<td class="title">
							<?php echo _TITLE('Skip Interfaces').':' ?>
						</td>
						<td>
							<input type="text" size="40" id="skip" name="skip" value="<?php echo (is_array($this->rule['option']['skip']) ? implode(' ', $this->rule['option']['skip']) : $this->rule['option']['skip']); ?>"
								placeholder="comma or space separated list of interfaces"/>
						</td>
						<?php
					}
					if (isset($this->rule['option']['states']) || $type == 'states') {
						?>
						<td class="title">
							<?php echo _TITLE('Limit States').':' ?>
						</td>
						<td>
							<input type="text" size="10" id="states" name="states" value="<?php echo $this->rule['option']['states']; ?>" placeholder="number" />
						</td>
						<?php
					}
					if (isset($this->rule['option']['frags']) || $type == 'frags') {
						?>
						<td class="title">
							<?php echo _TITLE('Limit Frags').':' ?>
						</td>
						<td>
							<input type="text" size="10" id="frags" name="frags" value="<?php echo $this->rule['option']['frags']; ?>" placeholder="number" />
						</td>
						<?php
					}
					if (isset($this->rule['option']['srcnodes']) || $type == 'srcnodes') {
						?>
						<td class="title">
							<?php echo _TITLE('Limit Src-Nodes').':' ?>
						</td>
						<td>
							<input type="text" size="10" id="srcnodes" name="srcnodes" value="<?php echo $this->rule['option']['src-nodes']; ?>" placeholder="number" />
						</td>
						<?php
					}
					if (isset($this->rule['option']['tables']) || $type == 'tables') {
						?>
						<td class="title">
							<?php echo _TITLE('Limit Tables').':' ?>
						</td>
						<td>
							<input type="text" size="10" id="tables" name="tables" value="<?php echo $this->rule['option']['tables']; ?>" placeholder="number" />
						</td>
						<?php
					}
					if (isset($this->rule['option']['table-entries']) || $type == 'table-entries') {
						?>
						<td class="title">
							<?php echo _TITLE('Limit Table-Entries').':' ?>
						</td>
						<td>
							<input type="text" size="10" id="table-entries" name="table-entries" value="<?php echo $this->rule['option']['table-entries']; ?>" placeholder="number" />
						</td>
						<?php
					}
					?>
				</tr>
				<?php
				if (isset($type)) {
					?>
					<tr class="evenline">
						<td class="title">
							<?php echo _TITLE('Comment').':' ?>
						</td>
						<td>
							<input type="text" id="comment" name="comment" value="<?php echo stripslashes($this->rule['comment']); ?>" size="80" />
						</td>
					</tr>
					<?php
				}
				?>
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