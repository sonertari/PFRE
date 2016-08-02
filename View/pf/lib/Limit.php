<?php 
/* $pfre: Limit.php,v 1.7 2016/08/02 09:54:29 soner Exp $ */

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

class Limit extends Rule
{
	function __construct($str)
	{
		$this->keywords = array(
			'states' => array(
				'method' => 'parseLimit',
				'params' => array(),
				),
			'frags' => array(
				'method' => 'parseLimit',
				'params' => array(),
				),
			'src-nodes' => array(
				'method' => 'parseLimit',
				'params' => array(),
				),
			'tables' => array(
				'method' => 'parseLimit',
				'params' => array(),
				),
			'table-entries' => array(
				'method' => 'parseLimit',
				'params' => array(),
				),
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/\(/', ' \( ', $this->str);
		$this->str= preg_replace('/\)/', ' \) ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
		$this->str= preg_replace('/"/', ' " ', $this->str);
	}

	function parseLimit()
	{
		$this->rule['limit'][$this->words[$this->index]]= $this->words[++$this->index];
	}

	function generate()
	{
		$this->str= '';

		if (count($this->rule['limit'])) {
			reset($this->rule['limit']);

			if (count($this->rule['limit']) == 1) {
				list($key, $val)= each($this->rule['limit']);
				$this->str.= "set limit $key $val";
			} else {
				$this->str= 'set limit {';
				while (list($key, $val)= each($this->rule['limit'])) {
					$this->str.= " $key $val,";
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
		$this->dispLimit();
		$this->dispTail($rulenumber, $count);
	}
	
	function dispLimit()
	{
		?>
		<td title="Limit" colspan="12">
			<?php
			if (count($this->rule['limit'])) {
				reset($this->rule['limit']);
				while (list($key, $val)= each($this->rule['limit'])) {
					echo "$key: $val<br>";
				}
			}
			?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputKey('states', 'limit');
		$this->inputKey('frags', 'limit');
		$this->inputKey('src-nodes', 'limit');
		$this->inputKey('tables', 'limit');
		$this->inputKey('table-entries', 'limit');

		$this->inputKey('comment');
		$this->inputDelEmpty(FALSE);
	}

	function edit($rulenumber, $modified, $testResult, $action)
	{
		$this->index= 0;
		$this->rulenumber= $rulenumber;

		$this->editHead($modified);

		$this->editLimit();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
	}

	function editLimit()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('States').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="states" name="states" value="<?php echo $this->rule['limit']['states']; ?>" placeholder="number" />
				<?php $this->PrintHelp('states') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Frags').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="frags" name="frags" value="<?php echo $this->rule['limit']['frags']; ?>" placeholder="number" />
				<?php $this->PrintHelp('frags') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Src Nodes').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="srcnodes" name="src-nodes" value="<?php echo $this->rule['limit']['src-nodes']; ?>" placeholder="number" />
				<?php $this->PrintHelp('src-nodes') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Tables').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="tables" name="tables" value="<?php echo $this->rule['limit']['tables']; ?>" placeholder="number" />
				<?php $this->PrintHelp('tables') ?>
			</td>
		</tr>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Table Entries').':' ?>
			</td>
			<td>
				<input type="text" size="10" id="table-entries" name="table-entries" value="<?php echo $this->rule['limit']['table-entries']; ?>" placeholder="number" />
				<?php $this->PrintHelp('table-entries') ?>
			</td>
		</tr>
		<?php
	}
}
?>