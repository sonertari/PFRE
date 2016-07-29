<?php
/* $pfre: Scrub.php,v 1.7 2016/07/27 09:15:30 soner Exp $ */

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
class Scrub extends Rule
{
	function parse($str)
	{
		$this->rule= array();
		if (strpos($str, "#")) {
			$this->rule['comment']= substr($str, strpos($str, "#") + '1');
			$str= substr($str, '0', strpos($str, "#"));
		}
		
		/*
		 * Sanitize the rule string so that we can deal with '{foo' as '{ foo' in 
		 * the code further down without any special treatment
		 */
		$str= preg_replace("/{/", " { ", $str);
		$str= preg_replace("/}/", " } ", $str);
		$str= preg_replace("/,/", " , ", $str);
		$str= preg_replace("/\(/", " ", $str);
		$str= preg_replace("/\)/", " ", $str);
		
		$words= preg_split("/[\s,\t]+/", $str, '-1', PREG_SPLIT_NO_EMPTY);
		
		for ($i= '0'; $i < count($words); $i++) {
			switch ($words[$i]) {
				case "in":
				case "out":
					$this->rule['direction']= $words[$i];
					break;
				case "all":
					$this->rule['all']= TRUE;
					break;
				case "on":
					$i++;
					if ($words[$i] != "{") {
						$this->rule['interface']= $words[$i];
					} else {
						while (preg_replace("/[\s,]+/", "", $words[++$i]) != "}") {
							$this->rule['interface'][]= $words[$i];
						}
					}
					break;
				case "from":
					$i++;
					if ($words[$i] != "{") {
						$this->rule['from']= $words[$i];
					} else {
						while (preg_replace("/[\s,]+/", "", $words[++$i]) != "}") {
							$this->rule['from'][]= $words[$i];
						}
					}
					break;
				case "to":
					$i++;
					if ($words[$i] != "{") {
						$this->rule['to']= $words[$i];
					} else {
						while (preg_replace("/[\s,]+/", "", $words[++$i]) != "}") {
							$this->rule['to'][]= $words[$i];
						}
					}
					break;
				case "no-df":
					$this->rule['no-df']= TRUE;
					break;
				case "min-ttl":
					$i++;
					$this->rule['min-ttl']= $words[$i];
					break;
				case "max-mss":
					$i++;
					$this->rule['max-mss']= $words[$i];
					break;
				case "random-id":
					$this->rule['random-id']= TRUE;
					break;
				case "reassemble":
					$i++;
					$this->rule['reassemble']= $words[$i];
					break;
			}
		}
	}

	function generate()
	{
		$str= "match";
		if ($this->rule['direction']) {
			$str.= " " . $this->rule['direction'];
		}
		if ($this->rule['interface']) {
			if (!is_array($this->rule['interface'])) {
				$str.= " on " . stripslashes($this->rule['interface']);
			} else {
				$str.= " on { ";
				foreach ($this->rule['interface'] as $interface) {
					$str.= stripslashes($interface) . ", ";
				}
				$str= rtrim($str, " ,");
				$str.= " }";
			}
		}
		if ($this->rule['all']) {
			$str.= " all";
		} else {
			if ($this->rule['from']) {
				if (!is_array($this->rule['from'])) {
					$str.= " from " . $this->rule['from'];
				} else {
					$str.= " from { ";
					foreach ($this->rule['from'] as $from) {
						$str.= $from . ", ";
					}
					$str= rtrim($str, " ,");
					$str.= " }";
				}
			}
			if ($this->rule['to']) {
				if (!is_array($this->rule['to'])) {
					$str.= " to " . stripslashes($this->rule['to']);
				} else {
					$str.= " to { ";
					foreach ($this->rule['to'] as $to) {
						$str.= stripslashes($to) . ", ";
					}
					$str= rtrim($str, ", ");
					$str.= " }";
				}
			}
		}
		$str.= " scrub";
		$opt= '';
		if ($this->rule['no-df']) {
			$opt.= " no-df";
		}
		if ($this->rule['min-ttl']) {
			$opt.= " min-ttl " . $this->rule['min-ttl'];
		}
		if ($this->rule['max-mss']) {
			$opt.= " max-mss " . $this->rule['max-mss'];
		}
		if ($this->rule['random-id']) {
			$opt.= " random-id";
		}
		if ($this->rule['reassemble']) {
			$opt.= " reassemble " . $this->rule['reassemble'];
		}
		if ($opt !== '') {
			$str.= " (" . trim($opt) . ")";
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
			<td title="Direction">
				<?php echo $this->rule['direction']; ?>
			</td>
			<td title="Interface">
				<?php $this->PrintValue($this->rule['interface']); ?>
			</td>
			<?php
			if ($this->rule['all']) {
				?>
				<td title="Source-Destination" colspan="2" class="all">
					All
				</td>
				<?php
			} else {
				?>
				<td title="Source">
					<?php $this->PrintFromTo($this->rule['from']); ?>
				</td>
				<td title="Destination">
					<?php $this->PrintFromTo($this->rule['to']); ?>
				</td>
				<?php
			}
			?>
			<td title="Min-ttl">
				<?php echo $this->rule['min-ttl']; ?>
			</td>
			<td title="Max-mss">
				<?php echo $this->rule['max-mss']; ?>
			</td>
			<td title="Parameters" colspan="6">
				<?php echo ($this->rule['no-df'] ? 'no-df<br>' : '') . ($this->rule['random-id'] ? 'random-id<br>' : '') . ($this->rule['reassemble'] ? 'reassemble ' . $this->rule['reassemble'] . '<br>' : ''); ?>
			</td>
			<td class="comment">
				<?php echo stripslashes($this->rule['comment']); ?>
			</td>
			<td class="edit">
				<?php
				$this->PrintEditLinks($rulenumber, "conf.php?sender=scrub&amp;rulenumber=$rulenumber", $count);
				?>
			</td>
		</tr>
		<?php
	}
	
	function processInput()
	{
		if (isset($_GET['dropinterface'])) {
			$this->delEntity("interface", $_GET['dropinterface']);
		}

		if (isset($_GET['dropto'])) {
			$this->delEntity("to", $_GET['dropto']);
		}

		if (isset($_GET['dropfrom'])) {
			$this->delEntity("from", $_GET['dropfrom']);
		}

		if (count($_POST)) {
			if (filter_input(INPUT_POST, 'addfrom') != '') {
				$this->addEntity("from", $_POST['addfrom']);
			}

			if (filter_input(INPUT_POST, 'addto') != '') {
				$this->addEntity("to", $_POST['addto']);
			}

			if (filter_input(INPUT_POST, 'addinterface') != '') {
				$this->addEntity("interface", $_POST['addinterface']);
			}

			$this->rule['direction']= $_POST['direction'];
			$this->rule['no-df']= ($_POST['no-df'] ? TRUE : '');
			$this->rule['random-id']= ($_POST['random-id'] ? TRUE : '');
			$this->rule['min-ttl']= $_POST['min-ttl'];
			$this->rule['max-mss']= $_POST['max-mss'];
			$this->rule['reassemble']= $_POST['reassemble'];
			$this->rule['comment']= $_POST['comment'];

			if ($_POST['all']) {
				$this->rule['all']= TRUE;
				unset($this->rule['from']);
				unset($this->rule['to']);
			} else {
				unset($this->rule['all']);
			}
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		$href= "conf.php?sender=scrub&rulenumber=$rulenumber";
		?>
		<h2>Edit Scrub Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" name="theform" action="<?php echo $href; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Direction').':' ?>
					</td>
					<td>
						<select id="direction" name="direction">
							<option value="" label=""></option>
							<option value="in" label="in" <?php echo ($this->rule['direction'] == 'in' ? 'selected' : '')?>>in</option>
							<option value="out" label="out" <?php echo ($this->rule['direction'] == 'out' ? 'selected' : '')?>>out</option>					
						</select>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Interface').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['interface'], $href, 'dropinterface');
						$this->PrintAddControls('addinterface', NULL, 'if or macro', NULL, 10, NULL, isset($this->rule['interface']));
						?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Match All').':' ?>
					</td>
					<td>
						<input type="checkbox" id="all" name="all" value="all" <?php echo ($this->rule['all'] ? 'checked' : ''); ?> onclick="document.theform.submit()" />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Source').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['from'], $href, 'dropfrom');
						$this->PrintAddControls('addfrom', NULL, 'ip, host or macro', NULL, NULL, $this->rule['all'], isset($this->rule['from']));
						?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Destination').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['to'], $href, 'dropto');
						$this->PrintAddControls('addto', NULL, 'ip, host or macro', NULL, NULL, $this->rule['all'], isset($this->rule['to']));
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Options').':' ?>
					</td>
					<td>
						<input type="checkbox" id="no-df" name="no-df" value="no-df" <?php echo ($this->rule['no-df'] ? 'checked' : '')?> />
						<label for="no-df">no-df</label>
						<br>
						<input type="checkbox" id="random-id" name="random-id" value="random-id" <?php echo ($this->rule['random-id'] ? 'checked' : '')?> />
						<label for="random-id">random-id</label>
						<br>
						<input type="checkbox" id="reassemble" name="reassemble" value="tcp" <?php echo ($this->rule['reassemble'] == 'tcp' ? 'checked' : '')?> />
						<label for="reassemble">reassemble tcp</label>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Min TTL').':' ?>
					</td>
					<td>
						<input type="text" id="min-ttl" name="min-ttl" size="4" value="<?php echo $this->rule['min-ttl']; ?>" />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Max MSS').':' ?>
					</td>
					<td>
						<input type="text" id="max-mss" name="max-mss" size="4" value="<?php echo $this->rule['max-mss']; ?>" />
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