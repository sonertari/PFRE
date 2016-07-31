<?php
/* $pfre: Macro.php,v 1.5 2016/07/30 20:38:08 soner Exp $ */

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

class Macro extends Rule
{
	function parse($str)
	{
		$this->str= $str;
		$this->deleteRules();
		$this->parseComment();
		$this->sanitize();
		$this->split();

		$this->index= 0;
		$this->rule['identifier']= $this->words[$this->index++];
		if ($this->words[++$this->index] != '{') {
			$this->rule['value']= $this->words[$this->index];
		} else {
			while (preg_replace('/,/', '', $this->words[++$this->index]) != '}') {
				$this->rule['value'][]= $this->words[$this->index];
			}
		}
	}

	function sanitize()
	{
		$this->str= preg_replace("/{/", " { ", $this->str);
		$this->str= preg_replace("/}/", " } ", $this->str);
		$this->str= preg_replace("/\(/", " \( ", $this->str);
		$this->str= preg_replace("/\)/", " \) ", $this->str);
		$this->str= preg_replace("/,/", " , ", $this->str);
		$this->str= preg_replace("/\"/", " \" ", $this->str);
		
		$this->str= preg_replace("/=/", " = ", $this->str);
		$this->str= preg_replace("/\"/", "", $this->str);
	}

	function generate()
	{
		$this->str= $this->rule['identifier'] . ' = "';

		if (!is_array($this->rule['value'])) {
			$this->str.= $this->rule['value'];
		} else {
			$this->str.= '{ ' . implode(', ', $this->rule['value']) . ' }';
		}
		$this->str.= '"';
		
		$this->genComment();
		$this->str.= "\n";
		return $this->str;
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
				<?php echo $this->rule['identifier']; ?>
			</td>
			<td title="Value" colspan="11">
				<?php $this->PrintValue($this->rule['value']); ?>
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
			$this->delEntity("value", filter_input(INPUT_GET, 'dropvalue'));
		}

		if (count($_POST)) {
			if (filter_input(INPUT_POST, 'addvalue') != '') {
				$this->addEntity("value", filter_input(INPUT_POST, 'addvalue'));
			}

			$this->rule['identifier']= filter_input(INPUT_POST, 'identifier');
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');
		}
	
		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		?>
		<h2>Edit Macro Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Macro') ?></h2>
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
						<?php echo _TITLE('Value').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['value'], $rulenumber, 'dropvalue');
						$this->PrintAddControls('addvalue', NULL, 'add value', NULL, 30);
						?>
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