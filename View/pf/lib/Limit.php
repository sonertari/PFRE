<?php 
/* $pfre: Limit.php,v 1.1 2016/07/30 00:23:57 soner Exp $ */

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
 
class Limit extends Rule
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
				case 'states':
					$this->rule['limit']['states']= $words[++$i];
					break;
				case 'frags':
					$this->rule['limit']['frags']= $words[++$i];
					break;
				case 'src-nodes':
					$this->rule['limit']['src-nodes']= $words[++$i];
					break;
				case 'tables':
					$this->rule['limit']['tables']= $words[++$i];
					break;
				case 'table-entries':
					$this->rule['limit']['table-entries']= $words[++$i];
					break;
			}
		}
	}

	function generate()
	{
		reset($this->rule['limit']);
		
		$str= '';
		if (count($this->rule['limit']) == 1) {
			list($key, $val)= each($this->rule['limit']);
			$str.= "set limit $key $val";
		} else {
			$str= 'set limit {';
			while (list($key, $val)= each($this->rule['limit'])) {
				$str.= " $key $val,";
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
			<td title="Limit" colspan="12">
				<?php
				reset($this->rule['limit']);
				while (list($key, $val)= each($this->rule['limit'])) {
					echo "$key: $val<br>";
				}
				?>
			</td>
			<td class="comment">
				<?php echo stripslashes($this->rule['comment']); ?>
			</td>
			<td class="edit">
				<?php
				$this->PrintEditLinks($rulenumber, "conf.php?sender=limit&amp;rulenumber=$rulenumber", $count);
				?>
			</td>
		</tr>
		<?php
	}		
	
	function processInput()
	{
		if (count($_POST)) {
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');

			if (filter_has_var(INPUT_POST, 'states')) {
				if (strlen(trim(filter_input(INPUT_POST, 'states')))) {
					$this->rule['limit']['states']= trim(filter_input(INPUT_POST, 'states'));
				} else {
					unset($this->rule['limit']['states']);
				}
			}
			if (filter_has_var(INPUT_POST, 'frags')) {
				if (strlen(trim(filter_input(INPUT_POST, 'frags')))) {
					$this->rule['limit']['frags']= trim(filter_input(INPUT_POST, 'frags'));
				} else {
					unset($this->rule['limit']['frags']);
				}
			}
			if (filter_has_var(INPUT_POST, 'src-nodes')) {
				if (strlen(trim(filter_input(INPUT_POST, 'src-nodes')))) {
					$this->rule['limit']['src-nodes']= trim(filter_input(INPUT_POST, 'src-nodes'));
				} else {
					unset($this->rule['limit']['src-nodes']);
				}
			}
			if (filter_has_var(INPUT_POST, 'tables')) {
				if (strlen(trim(filter_input(INPUT_POST, 'tables')))) {
					$this->rule['limit']['tables']= trim(filter_input(INPUT_POST, 'tables'));
				} else {
					unset($this->rule['limit']['tables']);
				}
			}
			if (filter_has_var(INPUT_POST, 'table-entries')) {
				if (strlen(trim(filter_input(INPUT_POST, 'table-entries')))) {
					$this->rule['limit']['table-entries']= trim(filter_input(INPUT_POST, 'table-entries'));
				} else {
					unset($this->rule['limit']['table-entries']);
				}
			}
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		$href= "conf.php?sender=limit&rulenumber=$rulenumber";
		?>
		<h2>Edit Limit Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Limit') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" action="<?php echo $href; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('States').':' ?>
					</td>
					<td>
						<input type="text" size="10" id="states" name="states" value="<?php echo $this->rule['limit']['states']; ?>" placeholder="number" />
						<?php $this->PrintHelp('states') ?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Frags').':' ?>
					</td>
					<td>
						<input type="text" size="10" id="frags" name="frags" value="<?php echo $this->rule['limit']['frags']; ?>" placeholder="number" />
						<?php $this->PrintHelp('frags') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Src Nodes').':' ?>
					</td>
					<td>
						<input type="text" size="10" id="srcnodes" name="src-nodes" value="<?php echo $this->rule['limit']['src-nodes']; ?>" placeholder="number" />
						<?php $this->PrintHelp('src-nodes') ?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Tables').':' ?>
					</td>
					<td>
						<input type="text" size="10" id="tables" name="tables" value="<?php echo $this->rule['limit']['tables']; ?>" placeholder="number" />
						<?php $this->PrintHelp('tables') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Table Entries').':' ?>
					</td>
					<td>
						<input type="text" size="10" id="table-entries" name="table-entries" value="<?php echo $this->rule['limit']['table-entries']; ?>" placeholder="number" />
						<?php $this->PrintHelp('table-entries') ?>
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