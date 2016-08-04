<?php
/* $pfre: Table.php,v 1.9 2016/08/02 12:01:08 soner Exp $ */

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

class Table extends Rule
{
	function __construct($str)
	{
		$this->keywords = array(
			'table' => array(
				'method' => 'parseDelimitedStr',
				'params' => array('identifier', '<', '>'),
				),
			'persist' => array(
				'method' => 'parseBool',
				'params' => array(),
				),
			'const' => array(
				'method' => 'parseBool',
				'params' => array(),
				),
			'counters' => array(
				'method' => 'parseBool',
				'params' => array(),
				),
			'file' => array(
				'method' => 'parseFile',
				'params' => array(),
				),
			'{' => array(
				'method' => 'parseData',
				'params' => array(),
				),
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/</', ' < ', $this->str);
		$this->str= preg_replace('/>/', ' > ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
	}

	function parseFile()
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

	function parseData()
	{
		while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}') {
			$this->rule['data'][]= $this->words[$this->index];
		}
	}

	function generate()
	{
		$this->str= 'table <' . $this->rule['identifier'] . '>';
		$this->genKey('persist');
		$this->genKey('const');
		$this->genKey('counters');
		$this->genFiles();
		$this->genData();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genFiles()
	{
		if (isset($this->rule['file'])) {
			if (!is_array($this->rule['file'])) {
				$this->str.= ' file "' . $this->rule['file'] . '"';
			} else {
				foreach ($this->rule['file'] as $file) {
					$this->str.= ' file "' . $file . '"';
				}
			}
		}
	}

	function genData()
	{
		if (isset($this->rule['data'])) {
			$this->str.= ' { ';
			if (!is_array($this->rule['data'])) {
				$this->str.= $this->rule['data'];
			} else {
				$this->str.= implode(', ', $this->rule['data']);
			}
			$this->str.= ' }';
		}
	}

	function display($rulenumber, $count)
	{
		$this->dispHead($rulenumber);
		$this->dispId();
		$this->dispKey('const', 'Flag');
		$this->dispKey('persist', 'Flag');
		$this->dispKey('counters', 'Flag');
		$this->dispValues();
		$this->dispTail($rulenumber, $count);
	}

	function dispId()
	{
		?>
		<td title="Id">
			<?php echo htmlentities($this->rule['identifier']); ?>
		</td>
		<?php
	}

	function dispValues()
	{
		?>
		<td title="Values" colspan="8">
			<?php
			$this->printValue($this->rule['data']);
			$this->printValue($this->rule['file'], 'file "', '"');
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('identifier');
		$this->inputBool('const');
		$this->inputBool('persist');
		$this->inputBool('counters');
		$this->inputDel('data', 'dropvalue');
		$this->inputAdd('data', 'addvalue');
		$this->inputDel('file', 'dropfile');
		$this->inputAdd('file', 'addfile');

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($rulenumber, $modified, $testResult, $action)
	{
		$this->index= 0;
		$this->rulenumber= $rulenumber;

		$this->editHead($modified);

		$this->editText('identifier', 'Identifier', FALSE, NULL, 'string');
		$this->editFlags();
		$this->editValues();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
	}

	function editFlags()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Flags').':' ?>
			</td>
			<td>
				<input type="checkbox" id="const" name="const" value="const" <?php echo $this->rule['const'] ? 'checked' : ''; ?> />
				<label for="const">const</label>
				<?php $this->editHelp('const') ?>
				<br>
				<input type="checkbox" id="persist" name="persist" value="persist" <?php echo $this->rule['persist'] ? 'checked' : ''; ?> />
				<label for="persist">persist</label>
				<?php $this->editHelp('persist') ?>
				<br>
				<input type="checkbox" id="counters" name="counters" value="counters" <?php echo $this->rule['counters'] ? 'checked' : ''; ?> />
				<label for="counters">counters</label>
				<?php $this->editHelp('counters') ?>
			</td>
		</tr>
		<?php
	}

	function editValues()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Values').':' ?>
			</td>
			<td>
				<?php
				$this->editDeleteValueLinks($this->rule['data'], 'dropvalue');
				$this->editDeleteValueLinks($this->rule['file'], 'dropfile', 'file "', '"');
				$this->editAddValueBox('addvalue', 'add host or network', 'host or network', 30);
				echo '<br />';
				$this->editAddValueBox('addfile', 'add file', 'filename', 30);
				?>
			</td>
		</tr>
		<?php
	}
}
?>