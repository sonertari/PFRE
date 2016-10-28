<?php
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

namespace View;

class Comment extends Rule
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispComment();
		$this->dispTailEditLinks($ruleNumber, $count);
	}

	/**
	 * Counts lines in the rule.
	 * 
	 * @attention Decrement once for the rule itself (already incremented in the main display loop in rules.php).
	 */
	function countLines()
	{
		return count(explode("\n", $this->rule['comment'])) - 1;
	}

	function dispComment()
	{
		?>
		<td class="comment" colspan="13">
			<?php echo nl2br(htmlentities(stripslashes($this->rule['comment']))); ?>
		</td>
		<?php
	}

	function input()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			// textarea inserts \r\n instead of just \n, which appears as ^M when saved in a rules file, so delete \r chars
			$this->rule['comment']= preg_replace('/\r/', '', filter_input(INPUT_POST, 'comment'));
		}

		$this->inputDelEmpty();
	}
	
	/**
	 * Prints edit page.
	 * 
	 * Comment rules are very simple and do not need to be generated.
	 * 
	 * @param int $ruleNumber Rule number.
	 * @param bool $modified Whether the rule is modified or not.
	 * @param bool $testResult Test result.
	 * @param bool $generateResult Rule generation result.
	 * @param string $action Current state of the edit page.
	 */
	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$editHeader= _TITLE('Edit <RULE_TYPE> Rule <RULE_NUMBER>');
		$editHeader= str_replace('<RULE_TYPE>', 'Comment', $editHeader);
		$editHeader= str_replace('<RULE_NUMBER>', $ruleNumber, $editHeader);
		?>
		<h2><?php echo $editHeader . ($modified ? ' (' . _TITLE('modified') . ')' : ''); ?></h2>
		<form id="editForm" action="<?php echo $this->href . $ruleNumber; ?>" method="post">
			<textarea cols="80" rows="5" id="comment" name="comment" placeholder="<?php echo _CONTROL('Enter comment here') ?>"><?php echo stripslashes($this->rule['comment']); ?></textarea>
			<div class="buttons">
				<input type="submit" id="apply" name="apply" value="<?php echo _CONTROL('Apply') ?>" />
				<input type="submit" id="save" name="save" value="<?php echo _CONTROL('Save') ?>" <?php echo $modified ? '' : 'disabled'; ?> />
				<input type="submit" id="cancel" name="cancel" value="<?php echo _CONTROL('Cancel') ?>" />
				<input type="checkbox" id="forcesave" name="forcesave" <?php echo $modified && !$testResult ? '' : 'disabled'; ?> />
				<label for="forcesave"><?php echo _CONTROL('Save with errors') ?></label>
				<input type="hidden" name="state" value="<?php echo $action ?>" />
			</div>
		</form>
		<?php
	}
}
?>
