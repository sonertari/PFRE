<?php
/* $pfre: Rule.php,v 1.29 2016/08/06 23:48:36 soner Exp $ */

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

class Rule
{
	public $cat= '';
	public $rule= array();

	protected $href= '';
	protected $ruleNumber= 0;
	
	protected $arr= array();
	protected $editIndex= 0;

	function __construct()
	{
		$this->cat= get_called_class();
		$this->href= 'conf.php?sender=' . strtolower(ltrim($this->cat, '_')) . '&amp;rulenumber=';
	}

	function dispHead($ruleNumber)
	{
		?>
		<tr title="<?php echo ltrim($this->cat, '_'); ?> rule"<?php echo ($ruleNumber % 2 ? ' class="oddline"' : ''); ?>>
			<td class="center">
				<?php echo $ruleNumber; ?>
			</td>
			<td title="Category" class="category">
				<?php echo ltrim($this->cat, '_'); ?>
			</td>
		<?php
	}

	function dispTail($ruleNumber, $count)
	{
		?>
		<td class="comment">
			<?php echo stripslashes($this->rule['comment']); ?>
		</td>
		<?php
		$this->dispTailEditLinks($ruleNumber, $count);
	}

	function dispTailEditLinks($ruleNumber, $count)
	{
		?>
			<td class="edit">
				<?php
				$this->dispEditLinks($ruleNumber, $count);
				?>
			</td>
		</tr>
		<?php
	}

	function dispEditLinks($ruleNumber, $count, $up= 'up', $down= 'down', $del= 'del')
	{
		?>
		<a href="<?php echo $this->href . $ruleNumber; ?>" title="Edit">e</a>
		<?php
		if ($ruleNumber > 0) {
			?>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>?<?php echo $up; ?>=<?php echo $ruleNumber; ?>" title="Move up">u</a>
			<?php
		} else {
			echo ' u ';
		}
		if ($ruleNumber < $count) {
			?>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>?<?php echo $down; ?>=<?php echo $ruleNumber; ?>" title="Move down">d</a>
			<?php
		} else {
			echo ' d ';
		}
		?>
		<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>?<?php echo $del; ?>=<?php echo $ruleNumber; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete <?php echo $this->cat; ?> rule number <?php echo $ruleNumber; ?>?')">x</a>
		<?php
	}

	function dispKey($key, $title)
	{
		?>
		<td title="<?php echo $title; ?>">
			<?php echo $this->rule[$key] ? $key : ''; ?>
		</td>
		<?php
	}

	function dispValue($key, $title)
	{
		?>
		<td title="<?php echo $title; ?>">
			<?php $this->printValue($this->rule[$key]); ?>
		</td>
		<?php
	}

	function dispValues($key, $title)
	{
		?>
		<td title="<?php echo $title; ?>">
			<?php $this->printHostPort($this->rule[$key], TRUE); ?>
		</td>
		<?php
	}

	function dispInterface()
	{
		?>
		<td title="Interface">
			<?php $this->printValue($this->rule['interface']); ?>
		</td>
		<?php
	}

	function printValue($value, $pre= '', $post= '', $count= 10)
	{
		if ($value) {
			if (!is_array($value)) {
				// Add <br> to call this function twice
				echo "$pre$value$post<br>";
			} else {
				$i= 1;
				foreach ($value as $v) {
					echo "$pre$v$post<br>";
					if (++$i > $count) {
						echo '+' . (count($value) - $count) . ' more entries (not displayed)<br>';
						break;
					}
				}
			}
		}
	}

	function dispLog($colspan= 1)
	{
		?>
		<td title="Log" colspan="<?php echo $colspan; ?>">
			<?php
			if ($this->rule['log']) {
				if (is_array($this->rule['log'])) {
					$s= 'log ';
					foreach ($this->rule['log'] as $k => $v) {
						$s.= (is_bool($v) ? "$k" : "$k=$v") . ', ';
					}
					echo trim($s, ', ');
				} else {
					echo 'log';
				}
			}
			?>
		</td>
		<?php
	}

	function printHostPort($value, $noAny= TRUE, $count= 10)
	{
		if (!is_array($value)) {
			echo $value || $noAny ? htmlentities($value) : 'any';
		} else {
			$i= 1;
			foreach ($value as $v) {
				echo htmlentities($v) . '<br>';
				if (++$i > $count) {
					echo '+' . (count($value) - $count) . ' more entries (not displayed)<br>';
					break;
				}
			}
		}
	}

	function inputKey($key, $parent= NULL)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$rule= &$this->rule;
			if ($parent !== NULL) {
				$rule= &$this->rule[$parent];
			}

			//$rule[$key]= preg_replace('/"/', '', filter_input(INPUT_POST, $key));
			$rule[$key]= trim(filter_input(INPUT_POST, $key), "\" \t\n\r\0\x0B");
		}
	}

	function inputBool($key, $parent= NULL)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$rule= &$this->rule;
			if ($parent !== NULL) {
				$rule= &$this->rule[$parent];
			}

			$rule[$key]= (filter_has_var(INPUT_POST, $key) ? TRUE : '');
		}
	}

	function inputKeyIfHasVar($key, $var)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if (filter_has_var(INPUT_POST, $var)) {
				$this->rule[$key]= filter_input(INPUT_POST, $key);
			}
		}
	}

	function inputDel($key, $var, $parent= NULL)
	{
		if (count($_GET)) {
			if (filter_has_var(INPUT_GET, $var)) {
				$this->inputDelValue($key, filter_input(INPUT_GET, $var), $parent);
			}
		}
	}

	function inputDelValue($key, $value, $parent= NULL)
	{
		$rule= &$this->rule;
		if ($parent !== NULL) {
			$rule= &$this->rule[$parent];
		}

		if (is_array($rule[$key])) {
			$index= array_search($value, $rule[$key]);
			if ($index !== FALSE) {
				unset($rule[$key][$index]);
				/// @todo Should we also update the keys?
			}

			FlattenArray($rule[$key]);
		} else {
			unset($rule[$key]);
		}
	}

	function inputAdd($key, $var, $parent= NULL)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if (filter_has_var(INPUT_POST, $var) && filter_input(INPUT_POST, $var) !== '') {
				$this->inputAddValue($key, preg_replace('/"/', '', filter_input(INPUT_POST, $var)), $parent);
			}
		}
	}

	function inputAddValue($key, $value, $parent= NULL)
	{
		$rule= &$this->rule;
		if ($parent !== NULL) {
			$rule= &$this->rule[$parent];
		}

		if (!isset($rule[$key])) {
			$rule[$key]= $value;
		} else { 
			if (!is_array($rule[$key])) {
				// Make array
				$tmp= $rule[$key];
				unset($rule[$key]);
				$rule[$key][]= $tmp;
			}
			$rule[$key][]= $value;
			$rule[$key]= array_unique($rule[$key]);
		}
	}

	function inputInterface()
	{
		$this->inputDel('interface', 'delInterface');
		$this->inputAdd('interface', 'addInterface');
	}

	function inputLog()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$this->inputBool('log');

			if ($this->rule['log'] == TRUE) {
				if (filter_has_var(INPUT_POST, 'log-all') || filter_has_var(INPUT_POST, 'log-matches') ||
					filter_has_var(INPUT_POST, 'log-user') || (filter_has_var(INPUT_POST, 'log-to') && filter_input(INPUT_POST, 'log-to') !== '')) {
					$this->rule['log']= array();
					if (filter_has_var(INPUT_POST, 'log-all')) {
						$this->rule['log']['all']= TRUE;
					}
					if (filter_has_var(INPUT_POST, 'log-matches')) {
						$this->rule['log']['matches']= TRUE;
					}
					if (filter_has_var(INPUT_POST, 'log-user')) {
						$this->rule['log']['user']= TRUE;
					}
					if (filter_has_var(INPUT_POST, 'log-to') && filter_input(INPUT_POST, 'log-to') !== '') {
						$this->rule['log']['to']= filter_input(INPUT_POST, 'log-to');
					}
				}
			}
		}
	}

	function inputDelEmpty($flatten= TRUE)
	{
		/// @todo Check why we cannot combine inputDelEmpty() with inputDelEmptyRecursive()
		$this->rule= $this->inputDelEmptyRecursive($this->rule, $flatten);
	}

	function inputDelEmptyRecursive($array, $flatten)
	{
		foreach ($array as $key => $value) {
			if ($value == '') {
				unset($array[$key]);
			} elseif (is_array($value)) {
				/// @todo Is there a better way? Passing $flatten=FALSE down from Timeout and Limit objects does not work, Filter objects need TRUE
				/// @attention Do not flatten timeout and limit options
				$array[$key]= $this->inputDelEmptyRecursive($value, in_array($key, array('timeout', 'limit', 'log')) ? FALSE : $flatten);

				if (count($array[$key]) == 0) {
					// Array is empty, delete it
					unset($array[$key]);
				} elseif (count($array[$key]) == 1 && $flatten && !in_array($key, array('timeout', 'limit', 'log'))) {
					// Array has only one element, convert from array to simple NVP
					list($k, $v)= each($array[$key]);
					unset($array[$key]);
					$array[$key]= $v;
				}
			}
		}
		return $array;
	}

	function editCheckbox($key, $title)
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<input type="checkbox" id="<?php echo $key ?>" name="<?php echo $key ?>" value="<?php echo $key ?>" <?php echo ($this->rule[$key] ? 'checked' : ''); ?> />
				<?php $this->editHelp($key) ?>
			</td>
		</tr>
		<?php
	}

	function editText($key, $title, $help= NULL, $size= 0, $hint= '')
	{
		$help= $help === NULL ? $key : $help;
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<input type="text" id="<?php echo $key ?>" name="<?php echo $key ?>" value="<?php echo $this->rule[$key]; ?>" size="<?php echo $size ?>" placeholder="<?php echo $hint ?>" />
				<?php
				if ($help !== FALSE) {
					$this->editHelp($help);
				}
				?>
			</td>
		</tr>
		<?php
	}

	function editValues($key, $title, $delName, $addName, $hint, $help= NULL, $size= 0, $disabled= FALSE)
	{
		$help= $help === NULL ? $key : $help;
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<?php
				$this->editDeleteValueLinks($this->rule[$key], $delName);
				$this->editAddValueBox($addName, NULL, $hint, $size, $disabled);
				if ($help !== FALSE) {
					$this->editHelp($help);
				}
				?>
			</td>
		</tr>
		<?php
	}

	function editHead($modified)
	{
		global $ruleStr;
		?>
		<h2>Edit <?php echo ltrim($this->cat, '_'); ?> Rule <?php echo $this->ruleNumber . ($modified ? ' (modified)' : ''); ?><?php $this->editHelp(ltrim($this->cat, '_')); ?></h2>
		<h4><?php echo str_replace("\t", "<code>\t</code><code>\t</code>", str_replace("\n", '<br>', htmlentities($ruleStr))); ?></h4>
		<form id="theform" name="theform" action="<?php echo $this->href . $this->ruleNumber; ?>" method="post">
			<table id="nvp">
			<?php
	}

	function editTail($modified, $testResult, $generateResult, $action)
	{
			?>
			</table>
			<div class="buttons">
				<input type="submit" id="apply" name="apply" value="Apply" />
				<input type="submit" id="save" name="save" value="Save" <?php echo $modified ? '' : 'disabled'; ?> />
				<input type="submit" id="cancel" name="cancel" value="Cancel" />
				<input type="checkbox" id="forcesave" name="forcesave" <?php echo $modified && !$testResult ? '' : 'disabled'; ?> />
				<label for="forcesave">Save with errors</label>
				<input type="checkbox" id="forcegenerate" name="forcegenerate" <?php echo !$generateResult ? '' : 'disabled'; ?> <?php echo filter_has_var(INPUT_POST, 'forcegenerate') ? 'checked' : ''; ?> />
				<label for="forcegenerate">Generate with errors</label>
				<input type="hidden" name="state" value="<?php echo $action; ?>" />
			</div>
		</form>
		<?php
	}

	function editInterface()
	{
		$this->editValues('interface', 'Interface', 'delInterface', 'addInterface', 'if or macro', NULL, 10);
	}

	function editAf()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Address Family').':' ?>
			</td>
			<td>
				<select id="af" name="af">
					<option value="" label=""></option>
					<option value="inet" label="inet" <?php echo ($this->rule['af'] == 'inet' ? 'selected' : ''); ?>>inet</option>
					<option value="inet6" label="inet6" <?php echo ($this->rule['af'] == 'inet6' ? 'selected' : ''); ?>>inet6</option>
				</select>
				<?php $this->editHelp('address-family') ?>
			</td>
		</tr>
		<?php
	}

	function editLog()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Logging').':' ?>
			</td>
			<td>
				<input type="checkbox" id="log" name="log" value="log" <?php echo (isset($this->rule['log']) ? 'checked' : ''); ?> />
				<label for="log">Log</label>
				<?php
				$disabled= isset($this->rule['log']) ? '' : 'disabled';
				?>
				<label for="log">to:</label>
				<input type="text" id="log-to" name="log-to" value="<?php echo (isset($this->rule['log']['to']) ? $this->rule['log']['to'] : ''); ?>" placeholder="logging interface" <?php echo $disabled; ?> />
				<input type="checkbox" id="log-all" name="log-all" value="log-all" <?php echo (isset($this->rule['log']['all']) ? 'checked' : ''); ?> <?php echo $disabled; ?> />
				<label for="log">all</label>
				<input type="checkbox" id="log-matches" name="log-matches" value="log-matches" <?php echo (isset($this->rule['log']['matches']) ? 'checked' : ''); ?> <?php echo $disabled; ?> />
				<label for="log">matches</label>
				<input type="checkbox" id="log-user" name="log-user" value="log-user" <?php echo (isset($this->rule['log']['user']) ? 'checked' : ''); ?> <?php echo $disabled; ?> />
				<label for="log">user</label>
				<?php $this->editHelp('log') ?>
			</td>
		</tr>
		<?php
	}

	function editComment()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Comment').':' ?>
			</td>
			<td>
				<input type="text" id="comment" name="comment" value="<?php echo stripslashes($this->rule['comment']); ?>" size="80" placeholder="enter comment, such as a description of the rule" />
			</td>
		</tr>
		<?php
	}

	function editDeleteValueLinks($value, $name, $prefix= '', $postfix= '')
	{
		if (isset($value)) {
			if (is_array($value)) {
				foreach ($value as $v) {
					$v= htmlentities($v);
					echo "$prefix$v$postfix";
					?>
					<a href="<?php echo $this->href . $this->ruleNumber; ?>&amp;<?php echo $name; ?>=<?php echo $v; ?>">delete</a><br>
					<?php
				}
			} else {
				$value= htmlentities($value);
				echo "$prefix$value$postfix";
				?>
				<a href="<?php echo $this->href . $this->ruleNumber; ?>&amp;<?php echo $name; ?>=<?php echo $value; ?>">delete</a><br>
				<?php
			}
			?>
			<hr style="border: 0; border-bottom: 1px solid #CCC;" />
			<?php
		}
	}

	/** Prints add value controls.
	 *
	 * @param[in]	$id		string	Id of the input
	 * @param[in]	$label	string	Label
	 * @param[in]	$hint	string	Hint text
	 * @param[in]	$value	string	Value instead of hint
	 * @param[in]	$size	int		Size of the input
	 * @param[in]	$disabled	bool	Condition to disable the input
	 */
	function editAddValueBox($id, $label, $hint, $size= 0, $disabled= FALSE)
	{
		?>
		<input type="text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" size="<?php echo $size; ?>" placeholder="<?php echo $hint; ?>" <?php echo $disabled ? 'disabled' : ''; ?> />
		<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
		<?php
	}

	function editHelp($label) {
		global $IMG_PATH;
		?>
		<a target="<?php echo $label ?>" href="/pf.conf.html#<?php echo $label ?>">
			<img src="<?php echo "$IMG_PATH/help.png" ?>" name="<?php $label ?>" alt="(?)" border="0" width="12" height="12">
		</a>
		<?php
	}
}
?>
