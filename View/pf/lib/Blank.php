<?php
/* $pfre: Blank.php,v 1.8 2016/08/04 14:42:52 soner Exp $ */

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

class Blank extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispBlank();
		$this->dispTailEditLinks($ruleNumber, $count);
	}
	
	function dispBlank()
	{
		?>
		<td class="blank" colspan="13">
			<?php echo nl2br($this->rule['blank']); ?>
		</td>
		<?php
	}

	function input()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			// Untaint: convert all into blank lines
			$blank= '';
			foreach (explode("\n", filter_input(INPUT_POST, 'blank')) as $line) {
				$blank.= "\n";
			}
			$this->rule['blank']= $blank;
		}

		$this->inputDelEmpty();
	}
	
	function edit($ruleNumber, $modified, $testResult, $action)
	{
		$count= count(explode("\n", $this->rule['blank'])) - 1;
		?>
		<h2>Edit Blank <?php echo $ruleNumber . ($modified ? ' (modified)' : ''); ?></h2>
		<form id="theform" action="<?php echo $this->href . $ruleNumber; ?>" method="post">
			<?php echo _('Number of lines') . ': ' . $count; ?><br>
			<textarea cols="80" rows="5" id="blank" name="blank" placeholder="Enter blank lines here"><?php echo $this->rule['blank']; ?></textarea>
			<div class="buttons">
				<input type="submit" id="apply" name="apply" value="Apply" />
				<input type="submit" id="save" name="save" value="Save" <?php echo $modified ? '' : 'disabled'; ?> />
				<input type="submit" id="cancel" name="cancel" value="Cancel" />
				<input type="hidden" name="state" value="<?php echo $action; ?>" />
			</div>
		</form>
		<?php
	}
}
?>