<?php
/* $pfre: Queue.php,v 1.7 2016/07/27 03:10:47 soner Exp $ */

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
class Queue extends Rule
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
		$str= preg_replace("/\(/", " ( ", $str);
		$str= preg_replace("/\)/", " ) ", $str);
		$str= preg_replace("/,/", " , ", $str);
		
		$words= preg_split("/[\s,\t]+/", $str, '-1', PREG_SPLIT_NO_EMPTY);
		
		$this->rule['name']= $words['1'];
		for ($i= '2'; $i < count($words); $i++) {
			switch ($words[$i]) {
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
				case "parent":
					$i++;
					$this->rule['parent']= $words[$i];
					break;
				case "bandwidth":
					$this->rule['bandwidth']= $words[++$i];
					/// @todo Fix this possible off-by-N error
					if ($words[$i + 1] == 'burst') {
						$i+= 2;
						$this->rule['bw-burst']= $words[$i];
					}
					if ($words[$i + 1] == 'for') {
						$i+= 2;
						$this->rule['bw-time']= $words[$i];
					}
					break;
				case "min":
					$this->rule['min']= $words[++$i];
					/// @todo Fix this possible off-by-N error
					if ($words[$i + 1] == 'burst') {
						$i+= 2;
						$this->rule['min-burst']= $words[$i];
					}
					if ($words[$i + 1] == 'for') {
						$i+= 2;
						$this->rule['min-time']= $words[$i];
					}
					break;
				case "max":
					$this->rule['max']= $words[++$i];
					/// @todo Fix this possible off-by-N error
					if ($words[$i + 1] == 'burst') {
						$i+= 2;
						$this->rule['max-burst']= $words[$i];
					}
					if ($words[$i + 1] == 'for') {
						$i+= 2;
						$this->rule['max-time']= $words[$i];
					}
					break;
				case "qlimit":
					$this->rule['qlimit']= $words[++$i];
					break;
				case "default":
					$this->rule['default']= TRUE;
					break;
			}
		}
	}

	function generate()
	{
		$str= "queue " . $this->rule['name'];
		if ($this->rule['interface']) {
			if (!is_array($this->rule['interface'])) {
				$str.= " on " . $this->rule['interface'];
			} else {
				$str.= " { ";
				foreach ($this->rule['interface'] as $interface) {
					$str.= $interface . ", ";
				}
				$str= rtrim($str, ", ");
				$str.= " }";
			}
		}
		if ($this->rule['parent']) {
			$str.= " parent " . $this->rule['parent'];
		}
		if ($this->rule['bandwidth']) {
			$str.= " bandwidth " . $this->rule['bandwidth'] . ($this->rule['bw-burst'] ? ' burst ' . $this->rule['bw-burst'] : '') . ($this->rule['bw-time'] ? ' for ' . $this->rule['bw-time'] : '');
		}
		if ($this->rule['min']) {
			$str.= " min " . $this->rule['min'] . ($this->rule['min-burst'] ? ' burst ' . $this->rule['min-burst'] : '') . ($this->rule['min-time'] ? ' for ' . $this->rule['min-time'] : '');
		}
		if ($this->rule['max']) {
			$str.= " max " . $this->rule['max'] . ($this->rule['max-burst'] ? ' burst ' . $this->rule['max-burst'] : '') . ($this->rule['max-time'] ? ' for ' . $this->rule['max-time'] : '');
		}
		if ($this->rule['qlimit']) {
			$str.= " qlimit " . $this->rule['qlimit'];
		}
		if ($this->rule['default']) {
			$str.= " default";
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
			<td title="Name">
				<?php echo $this->rule['name']; ?>
			</td>
			<td title="Interface">
				<?php $this->PrintValue($this->rule['interface']); ?>
			</td>
			<td title="Parent">
				<?php echo $this->rule['parent']; ?>
			</td>
			<td title="Bandwidth" colspan="2">
				<?php echo $this->rule['bandwidth'] . ($this->rule['bw-burst'] ? '<br>burst: ' . $this->rule['bw-burst'] : '') . ($this->rule['bw-time'] ? '<br>time: ' . $this->rule['bw-time'] : ''); ?>
			</td>
			<td title="Min" colspan="2">
				<?php echo $this->rule['min'] . ($this->rule['min-burst'] ? '<br>burst: ' . $this->rule['min-burst'] : '') . ($this->rule['min-time'] ? '<br>time: ' . $this->rule['min-time'] : ''); ?>
			</td>
			<td title="Max" colspan="2">
				<?php echo $this->rule['max'] . ($this->rule['max-burst'] ? '<br>burst: ' . $this->rule['max-burst'] : '') . ($this->rule['max-time'] ? '<br>time: ' . $this->rule['max-time'] : ''); ?>
			</td>
			<td title="Qlimit" colspan="2">
				<?php echo $this->rule['qlimit']; ?>
			</td>
			<td title="Default">
				<?php echo $this->rule['default']; ?>
			</td>
			<td class="comment">
				<?php echo stripslashes($this->rule['comment']); ?>
			</td>
			<td class="edit">
				<?php
				$this->PrintEditLinks($rulenumber, "conf.php?sender=queue&amp;rulenumber=$rulenumber", $count);
				?>
			</td>
		</tr>
		<?php
	}
	
	function processInput()
	{
		if (count($_POST)) {
			$this->rule['name']= filter_input(INPUT_POST, 'name');
			$this->rule['interface']= filter_input(INPUT_POST, 'interface');
			$this->rule['parent']= filter_input(INPUT_POST, 'parent');
			$this->rule['bandwidth']= filter_input(INPUT_POST, 'bandwidth');
			$this->rule['bw-burst']= filter_input(INPUT_POST, 'bw-burst');
			$this->rule['bw-time']= filter_input(INPUT_POST, 'bw-time');
			$this->rule['min']= filter_input(INPUT_POST, 'min');
			$this->rule['min-burst']= filter_input(INPUT_POST, 'min-burst');
			$this->rule['min-time']= filter_input(INPUT_POST, 'min-time');
			$this->rule['max']= filter_input(INPUT_POST, 'max');
			$this->rule['max-burst']= filter_input(INPUT_POST, 'max-burst');
			$this->rule['max-time']= filter_input(INPUT_POST, 'max-time');
			$this->rule['qlimit']= filter_input(INPUT_POST, 'qlimit');
			$this->rule['default']= (filter_has_var(INPUT_POST, 'default') ? TRUE : '');
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		$href= "conf.php?sender=queue&rulenumber=$rulenumber";
		?>
		<h2>Edit Queue Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" action="<?php echo "conf.php?sender=queue&rulenumber=$rulenumber"; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Name').':' ?>
					</td>
					<td>
						<input type="text" id="name" name="name" size="10" value="<?php echo $this->rule['name']; ?>" />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Interface').':' ?>
					</td>
					<td>
						<input type="text" id="interface" name="interface" size="10" value="<?php echo $this->rule['interface']; ?>" />
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Parent').':' ?>
					</td>
					<td>
						<input type="text" id="parent" name="parent" size="10" value="<?php echo $this->rule['parent']; ?>" />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Bandwidth').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" id="bandwidth" name="bandwidth" size="10" value="<?php echo $this->rule['bandwidth']; ?>" />
											</td>
											<td class="optitle">bandwidth</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="bw-burst" name="bw-burst" size="10" value="<?php echo $this->rule['bw-burst']; ?>" />
											</td>
											<td class="optitle">burst</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="bw-time" name="bw-time" size="10" value="<?php echo $this->rule['bw-time']; ?>" />
											</td>
											<td class="optitle">time</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Min').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" id="min" name="min" size="10" value="<?php echo $this->rule['min']; ?>" />
											</td>
											<td class="optitle">bandwidth</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="min-burst" name="min-burst" size="10" value="<?php echo $this->rule['min-burst']; ?>" />
											</td>
											<td class="optitle">burst</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="min-time" name="min-time" size="10" value="<?php echo $this->rule['min-time']; ?>" />
											</td>
											<td class="optitle">time</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Max').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" id="max" name="max" size="10" value="<?php echo $this->rule['max']; ?>" />
											</td>
											<td class="optitle">bandwidth</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="max-burst" name="max-burst" size="10" value="<?php echo $this->rule['max-burst']; ?>" />
											</td>
											<td class="optitle">burst</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="max-time" name="max-time" size="10" value="<?php echo $this->rule['max-time']; ?>" />
											</td>
											<td class="optitle">time</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Qlimit').':' ?>
					</td>
					<td>
						<input type="text" id="qlimit" name="qlimit" size="10" value="<?php echo $this->rule['qlimit']; ?>" />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Default').':' ?>
					</td>
					<td>
						<input type="checkbox" id="default" name="default" <?php echo ($this->rule['default']) ? 'checked' : '' ; ?> />
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