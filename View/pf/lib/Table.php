<?php
/* $pfre: Table.php,v 1.4 2016/07/30 15:36:35 soner Exp $ */

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
class Table extends Rule
{
	function __construct($str)
	{
		$this->keywords = array(
			'table' => array(
				'method' => 'setNextNVP',
				'params' => array('identifier'),
				),
			'persist' => array(
				'method' => 'setBool',
				'params' => array(),
				),
			'const' => array(
				'method' => 'setBool',
				'params' => array(),
				),
			'counters' => array(
				'method' => 'setBool',
				'params' => array(),
				),
			'file' => array(
				'method' => 'setFile',
				'params' => array(),
				),
			'{' => array(
				'method' => 'setData',
				'params' => array(),
				),
			);

		// Base should not merge keywords
		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
	}

	function setFile()
	{
		$filename= preg_replace('/"/', '', $this->words[++$this->index]);
		if (!$this->rule['file']) {
			$this->rule['file']= $filename;
		} else {
			if (!is_array($this->rule['file'])) {
				$_temp= $this->rule['file'];
				unset($this->rule['file']);
				$this->rule['file'][]= $_temp;
			}
			$this->rule['file'][]= $filename;
		}
	}

	function setData()
	{
		while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}') {
			$this->rule['data'][]= $this->words[$this->index];
		}
	}

	function generate()
	{
		$str= 'table ' . $this->rule['identifier'];
		if ($this->rule['persist']) {
			$str.= ' persist';
		}
		if ($this->rule['const']) {
			$str.= ' const';
		}
		if ($this->rule['counters']) {
			$str.= ' counters';
		}
		if ($this->rule['file']) {
			if (!is_array($this->rule['file'])) {
				$str.= ' file "' . $this->rule['file'] . '"';
			} else {
				foreach ($this->rule['file'] as $file) {
					$str.= ' file "' . $file . '"';
				}
			}
		}
		if ($this->rule['data']) {
			$str.= ' { ';
			if (!is_array($this->rule['data'])) {
				$str.= $this->rule['data'];
			} else {
				$str.= implode(', ', $this->rule['data']);
			}
			$str.= ' }';
		}
		
		if ($this->rule['comment']) {
			$str.= ' # ' . trim(stripslashes($this->rule['comment']));
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
			<td title="Id">
				<?php echo htmlentities($this->rule['identifier']); ?>
			</td>
			<td title="Flags" colspan="5">
				<?php
				echo $this->rule['const'] ? 'const<br>' : '';
				echo $this->rule['persist'] ? 'persist<br>' : '';
				echo $this->rule['counters'] ? 'counters' : '';
				?>
			</td>
			<td title="Values" colspan="6">
				<?php
				$this->PrintValue($this->rule['data']);
				$this->PrintValue($this->rule['file'], 'file "', '"');
				?>
			</td>
			<td class="comment">
				<?php echo stripslashes($this->rule['comment']); ?>
			</td>
			<td class="edit">
				<?php
				$this->PrintEditLinks($rulenumber, $count);
				?>
			</td>
		</tr>
		<?php
	}
	
	function processInput()
	{
		if (filter_has_var(INPUT_GET, 'dropvalue')) {
			$this->delEntity("data", filter_input(INPUT_GET, 'dropvalue'));
		}

		if (filter_has_var(INPUT_GET, 'dropfile')) {
			$this->delEntity("file", filter_input(INPUT_GET, 'dropfile'));
		}

		if (count($_POST)) {
			if (filter_input(INPUT_POST, 'addvalue') != '') {
				foreach (preg_split("/[\s,]+/", filter_input(INPUT_POST, 'addvalue')) as $value) {
					$this->addEntity("data", trim($value));
				}
			}

			if (filter_input(INPUT_POST, 'addfile') != '') {
				$this->addEntity("file", preg_replace("/\"/", "", filter_input(INPUT_POST, 'addfile')));
			}

			$this->rule['identifier']= "<" . preg_replace("/[<>]/", "", filter_input(INPUT_POST, 'identifier')) . ">";
			$this->rule['const']= (filter_has_var(INPUT_POST, 'const') ? TRUE : '');
			$this->rule['persist']= (filter_has_var(INPUT_POST, 'persist') ? TRUE : '');
			$this->rule['counters']= (filter_has_var(INPUT_POST, 'counters') ? TRUE : '');
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		?>
		<h2>Edit Table Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Table') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" action="<?php echo $this->href . $rulenumber; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Identifier').':' ?>
					</td>
					<td>
						<input type="text" id="identifier" name="identifier" size="20" value="<?php echo $this->rule['identifier']; ?>" />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Flags').':' ?>
					</td>
					<td>
						<input type="checkbox" id="const" name="const" value="const" <?php echo $this->rule['const'] ? 'checked' : ''; ?> />
						<label for="const">const</label>
						<?php $this->PrintHelp('const') ?>
						<br>
						<input type="checkbox" id="persist" name="persist" value="persist" <?php echo $this->rule['persist'] ? 'checked' : ''; ?> />
						<label for="persist">persist</label>
						<?php $this->PrintHelp('persist') ?>
						<br>
						<input type="checkbox" id="counters" name="counters" value="counters" <?php echo $this->rule['counters'] ? 'checked' : ''; ?> />
						<label for="counters">counters</label>
						<?php $this->PrintHelp('counters') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Values').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['data'], $rulenumber, 'dropvalue');
						$this->PrintDeleteLinks($this->rule['file'], $rulenumber, 'dropfile', 'file "', '"');
						$this->PrintAddControls('addfile', 'add file', 'filename', NULL, 30);
						?>
						<br />
						<textarea id="addvalue" name="addvalue" cols="30" rows="5" placeholder="hosts or networks separated by comma, space or newline"></textarea>
						<label for="addvalue">add hosts or networks</label>
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