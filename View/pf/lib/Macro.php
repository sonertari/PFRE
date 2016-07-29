<?php
/* $pfre: Macro.php,v 1.7 2016/07/27 09:15:30 soner Exp $ */

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
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * 3. All advertising materials mentioning features or use of this
 *    software must display the following acknowledgement: This
 *    product includes software developed by Allard Consulting
 *    and its contributors.
 *
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
class Macro extends Rule
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
		
		$str= preg_replace("/=/", " = ", $str);
		$str= preg_replace("/\"/", "", $str);

		$words= preg_split("/[\s,\t]+/", $str, '-1', PREG_SPLIT_NO_EMPTY);
		
		$i= '0';
		$this->rule['identifier']= $words[$i++];
		if ($words[++$i] != "{") {
			$this->rule['value']= $words[$i];
		} else {
			while (preg_replace("/,/", "", $words[++$i]) != "}") {
				$this->rule['value'][]= $words[$i];
			}
		}
	}

	function generate()
	{
		$str= $this->rule['identifier'] . ' = "';

		if (!is_array($this->rule['value'])) {
			$str.= $this->rule['value'];
		} else {
			$str.= '{ ' . implode(' ', $this->rule['value']) . ' }';
		}
		$str.= '"';
		
		if ($this->rule['comment']) {
			$str.= " \t# " . trim(stripslashes($this->rule['comment']));
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
				$this->PrintEditLinks($rulenumber, "conf.php?sender=macro&amp;rulenumber=$rulenumber", $count);
				?>
			</td>
		</tr>
		<?php
	}
	
	function processInput()
	{
		if (isset($_GET['dropvalue'])) {
			$this->delEntity("value", $_GET['dropvalue']);
		}

		if (count($_POST)) {
			if (filter_input(INPUT_POST, 'addvalue') != '') {
				$this->addEntity("value", $_POST['addvalue']);
			}

			$this->rule['identifier']= $_POST['identifier'];
			$this->rule['comment']= $_POST['comment'];
		}
	
		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		$href= "conf.php?sender=macro&rulenumber=$rulenumber";
		?>
		<h2>Edit Macro Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" action="<?php echo $href; ?>" method="post">
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
						$this->PrintDeleteLinks($this->rule['value'], $href, 'dropvalue');
						$this->PrintAddControls('addvalue', NULL, 'add value', NULL, 30, NULL, isset($this->rule['value']));
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