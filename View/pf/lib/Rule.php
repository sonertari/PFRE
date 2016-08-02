<?php
/* $pfre: Rule.php,v 1.7 2016/07/31 14:19:13 soner Exp $ */

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

/**
 * Base rule class.
 */
class Rule
{
	public $rule= array();
	public $cat= '';
	
	protected $str= '';
	protected $index= 0;
	protected $words= array();

	protected $keywords = array();

	protected $href= '';
	protected $rulenumber= 0;
	
	function __construct($str)
	{
		$this->cat= get_called_class();
		$this->href= 'conf.php?sender=' . strtolower(ltrim($this->cat, '_')) . '&rulenumber=';
		$this->parse($str);
	}

	function parse($str)
	{
		$this->str= $str;
		$this->deleteRules();
		$this->parseComment();
		$this->sanitize();
		$this->split();

		for ($this->index= 0; $this->index < count($this->words); $this->index++) {
			$key= $this->words[$this->index];
			if (array_key_exists($key, $this->keywords)) {
				$method= $this->keywords[$key]['method'];				
				if (is_callable($method, TRUE)) {
					call_user_method_array($method, $this, $this->keywords[$key]['params']);
				} else {
					$this->rule[]= $method;
				}
			} else {
				$this->rule[]= $key;
			}
		}
	}

	function deleteRules()
	{
		$this->rule= array();
	}

	function parseComment()
	{
		$pos= strpos($this->str, '#');
		if ($pos) {
			$this->rule['comment']= trim(substr($this->str, $pos + 1));
			$this->str= substr($this->str, 0, $pos);
		}
	}

	function sanitize()
	{
		/*
		 * Sanitize the rule string so that we can deal with '{foo' as '{ foo' in
		 * the code further down without any special treatment
		 */
		$this->str= preg_replace('/! +/', '!', $this->str);
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/\(/', ' \( ', $this->str);
		$this->str= preg_replace('/\)/', ' \) ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
		$this->str= preg_replace('/"/', ' " ', $this->str);
	}

	function split()
	{
		$this->words= preg_split('/[\s,\t]+/', $this->str, -1, PREG_SPLIT_NO_EMPTY);
	}

	function parseNVP($key)
	{
		$this->rule[$key]= $this->words[$this->index];
	}

	function parseNVPInc($key)
	{
		$this->rule[$key]= $this->words[$this->index++];
	}

	function parseNextValue()
	{
		$this->rule[$this->words[$this->index]]= preg_replace('/"/', '', $this->words[++$this->index]);
	}

	function parseNextNVP($key)
	{
		$this->rule[$key]= $this->words[++$this->index];
	}

	function parseBool()
	{
		$this->rule[$this->words[$this->index]]= TRUE;
	}
	
	function parseItems($key, $pre= '{', $post= '}')
	{
		list($this->rule[$key], $this->index)= $this->parseItem($this->words, $this->index, $pre, $post);		
	}
	
	function parseDelimitedStr($key, $delim_pre= '"', $delim_post= '"')
	{
		list($this->rule[$key], $this->index)= $this->parseString($this->words, $this->index, $delim_pre, $delim_post);		
	}

	function parseAny()
	{
		if (!isset($this->rule['from'])) {
			$this->rule['from']= 'any';
		} else {
			$this->rule['to']= 'any';
		}
	}

	function parseSrcDest($port)
	{
		if ($this->words[$this->index + 1] != 'port') {
			list($this->rule[$this->words[$this->index]], $this->index)= $this->parseItem($this->words, $this->index);
		}
		if ($this->words[$this->index + 1] == 'port') {
			list($this->rule[$port], $this->index)= $this->parsePortItem($this->words, ++$this->index);
		}
	}

	function parseOS()
	{
		$this->index++;
		unset($_data);
		if ($this->words[$this->index] != '{') {
			if ($this->words[$this->index] != '"') {
				$_data.= $this->words[$this->index++];
			} else {
				while ($this->words[++$this->index] != '"') {
					$_data.= ' ' . $this->words[$this->index];
				}
			}
			$this->rule['os']= trim($_data);
		} else {
			while (preg_replace('/[\s,]+/', '', $this->words[++$this->index]) != '}') {
				$_data= '';
				while ($this->words[++$this->index] != '"') {
					$_data.= ' ' . $this->words[$this->index];
				}
				$this->rule['os'][]= trim($_data);
			}
		}
	}

	function parseLog()
	{
		if ($this->words[$this->index + 1] == '\(') {
			list($lo, $this->index)= $this->parseItem($this->words, $this->index, '\(', '\)');
			$this->rule['log']= array();
			for ($i= 0; $i < count($lo); $i++) {
				if ($lo[$i] == 'to') {
					$this->rule['log']['to']= $lo[++$i];
				} else {
					$this->rule['log'][$lo[$i]]= TRUE;
				}
			}
		} else {
			$this->rule['log']= TRUE;
		}
	}
	
	function parseICMPType($code)
	{
		list($this->rule[$this->words[$this->index]], $this->index)= $this->parseItem($this->words, $this->index);
		if ($this->words[$this->index + 1] == 'code') {
			list($this->rule[$code], $this->index)= $this->parseItem($this->words, ++$this->index);
		}
	}

	function genKey($key)
	{
		if (isset($this->rule[$key])) {
			$this->str.= ' ' . $key;
		}
	}

	function genValue($key, $head= '', $tail= '')
	{
		if (isset($this->rule[$key])) {
			$this->str.= ' ' . $head . $this->rule[$key] . $tail;
		}
	}

	function genItems($key, $head= '', $delim_pre= '{', $delim_post= '}')
	{
		if (isset($this->rule[$key])) {
			$this->str.= $this->generateItem($this->rule[$key], $head, $delim_pre, $delim_post);
		}
	}

	function genComment()
	{
		if (isset($this->rule['comment'])) {
			$this->str.= ' # ' . trim(stripslashes($this->rule['comment']));
		}
	}

	function genLog()
	{
		if (isset($this->rule['log'])) {
			if (is_array($this->rule['log'])) {
				$s= ' log ( ';
				foreach ($this->rule['log'] as $k => $v) {
					$s.= (is_bool($v) ? "$k" : "$k $v") . ', ';
				}
				$this->str.= rtrim($s, ', ') . ' )';
			} else {
				$this->str.= ' log';
			}
		}
	}

	function dispHead($rulenumber)
	{
		?>
		<tr title="<?php echo ltrim($this->cat, '_'); ?> rule"<?php echo ($rulenumber % 2 ? ' class="oddline"' : ''); ?>>
			<td class="center">
				<?php echo $rulenumber; ?>
			</td>
			<td title="Category" class="category">
				<?php echo ltrim($this->cat, '_'); ?>
			</td>
		<?php
	}

	function dispTail($rulenumber, $count)
	{
		?>
		<td class="comment">
			<?php echo stripslashes($this->rule['comment']); ?>
		</td>
		<?php
		$this->dispTailEditLinks($rulenumber, $count);
	}

	function dispTailEditLinks($rulenumber, $count)
	{
		?>
			<td class="edit">
				<?php
				$this->PrintEditLinks($rulenumber, $count);
				?>
			</td>
		</tr>
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
			<?php $this->PrintValue($this->rule[$key]); ?>
		</td>
		<?php
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

	function inputBool($key)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$this->rule[$key]= (filter_has_var(INPUT_POST, $key) ? TRUE : '');
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
				$this->delEntity($key, filter_input(INPUT_GET, $var), $parent);
			}
		}
	}

	function inputAdd($key, $var, $parent= NULL)
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if (filter_input(INPUT_POST, $var) !== '') {
				$this->addEntity($key, preg_replace('/"/', '', filter_input(INPUT_POST, $var)), $parent);
			}
		}
	}

	function addEntity($key, $data, $parent= NULL)
	{
		$rule= &$this->rule;
		if ($parent !== NULL) {
			$rule= &$this->rule[$parent];
		}

		if (!isset($rule[$key])) {
			$rule[$key]= $data;
		} else 
			if (!is_array($rule[$key])) {
				$value= $rule[$key];
				unset($rule[$key]);
				$rule[$key][]= $value;
				$rule[$key][]= $data;
				$rule[$key]= array_unique($rule[$key]);
			} else {
				$rule[$key][]= $data;
				$rule[$key]= array_unique($rule[$key]);
			}
	}

	function delEntity($key, $data, $parent= NULL)
	{
		$rule= &$this->rule;
		if ($parent !== NULL) {
			$rule= &$this->rule[$parent];
		}

		if (is_array($rule[$key])) {
			foreach ($rule[$key] as $value) {
				if ($value != $data) {
					$nvp[]= $value;
				}
			}
			if (count($nvp) == 1) {
				$rule[$key]= $nvp[0];
			} else {
				$rule[$key]= $nvp;
			}
		} else {
			unset($rule[$key]);
		}
	}

	function inputLog()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			$this->inputBool('log');

			if ($this->rule['log'] == TRUE) {
				if (filter_has_var(INPUT_POST, 'log-all') || filter_has_var(INPUT_POST, 'log-matches') ||
					filter_has_var(INPUT_POST, 'log-user') || filter_input(INPUT_POST, 'log-to') != '') {
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
					if (filter_input(INPUT_POST, 'log-to') != '') {
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
				$array[$key]= $this->inputDelEmptyRecursive($value, $flatten);

				if (count($array[$key]) == 0) {
					// Array is empty, delete it
					unset($array[$key]);
				} elseif (count($array[$key]) == 1 && $flatten) {
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
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<input type="checkbox" id="<?php echo $key ?>" name="<?php echo $key ?>" value="<?php echo $key ?>" <?php echo ($this->rule[$key] ? 'checked' : ''); ?> />
				<?php $this->PrintHelp($key) ?>
			</td>
		</tr>
		<?php
	}

	function editText($key, $title, $help= NULL, $size= 0, $hint= '')
	{
		$help= $help === NULL ? $key : $help;
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<input type="text" id="<?php echo $key ?>" name="<?php echo $key ?>" value="<?php echo $this->rule[$key]; ?>" size="<?php echo $size ?>" placeholder="<?php echo $hint ?>" />
				<?php
				if ($help !== FALSE) {
					$this->PrintHelp($help);
				}
				?>
			</td>
		</tr>
		<?php
	}

	function editValues($key, $title, $dropname, $addname, $hint, $help= NULL, $size= 0, $disabled= FALSE)
	{
		$help= $help === NULL ? $key : $help;
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<?php
				$this->PrintDeleteLinks($this->rule[$key], $dropname);
				$this->PrintAddControls($addname, NULL, $hint, $size, $disabled);
				if ($help !== FALSE) {
					$this->PrintHelp($help);
				}
				?>
			</td>
		</tr>
		<?php
	}

	function editHead($modified)
	{
		?>
		<h2>Edit <?php echo ltrim($this->cat, '_'); ?> Rule <?php echo $this->rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp(ltrim($this->cat, '_')); ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" name="theform" action="<?php echo $this->href . $this->rulenumber; ?>" method="post">
			<table id="nvp">
			<?php
	}

	function editTail($modified, $testResult, $action)
	{
			?>
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

	function editAf()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Address Family').':' ?>
			</td>
			<td>
				<select id="af" name="af">
					<option value="" label=""></option>
					<option value="inet" label="inet" <?php echo ($this->rule['af'] == 'inet' ? 'selected' : ''); ?>>inet</option>
					<option value="inet6" label="inet6" <?php echo ($this->rule['af'] == 'inet6' ? 'selected' : ''); ?>>inet6</option>
				</select>
				<?php $this->PrintHelp('address-family') ?>
			</td>
		</tr>
		<?php
	}

	function editLog()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
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
				<?php $this->PrintHelp('log') ?>
			</td>
		</tr>
		<?php
	}

	function editComment()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Comment').':' ?>
			</td>
			<td>
				<input type="text" id="comment" name="comment" value="<?php echo stripslashes($this->rule['comment']); ?>" size="80" placeholder="enter comment, such as a description of the rule" />
			</td>
		</tr>
		<?php
	}

	/**
	 * Parse rule string.
	 *
	 * @param array $ruleArray Rule string split into an array.
	 * @param int $i The pointer to where in the ruleset the parsing is at.
	 * @return array $data,$i The parsed string and the new pointer.
	 */
	function parseString($ruleArray, $i, $delim_pre= '"', $delim_post= '"')
	{
		$i++;
		if ($ruleArray[$i] == $delim_pre) {
			$data= $ruleArray[++$i];
			while ($ruleArray[++$i] != $delim_post) {
				$data.= ' ' . $ruleArray[$i];
			}
		} else {
			$data= $ruleArray[$i];
		}
		return Array(
			$data,
			$i
		);
	}

	function parseItem($ruleArray, $i, $delimiter_pre= "{", $delimiter_post= "}")
	{
		$i++;
		//
		// Check if the rule is negated (! { $item1, $item2})
		// If it is, replace with ({ !$item1, !$item2})
		//
		if ($ruleArray[$i] == "!") {
			$ii= ++$i; // Need a new iterator for this loop
			while (preg_replace("/[\s,]+/", "", $ruleArray[++$ii]) != $delimiter_post) {
				if (($ruleArray[$ii] != $delimiter_pre) and ($ruleArray[$ii] != $delimiter_post)) {
					if ($ruleArray[$ii]{0} == "!") {
						// Delete Double negations
						$ruleArray[$ii]= substr($ruleArray[$ii], 1);
					} else {
						$ruleArray[$ii]= "!" . $ruleArray[$ii];
					}
				}
			}
		}
		if (($ruleArray[$i] == $delimiter_pre)) {
			while (preg_replace("/[\s,]+/", "", $ruleArray[++$i]) != $delimiter_post) {
				if (($ruleArray[$i] != $delimiter_pre) and ($ruleArray[$i] != $delimiter_post)) {
					if ($ruleArray[$i] == "\(") {
						while ($ruleArray[++$i] != "\)") {
							$data2[]= $ruleArray[$i];
						}
						$data[]= '(' . implode(' ', $data2) . ')';
						unset($data2);
					} else {
						$data[]= $ruleArray[$i];
					}
				}
			}
		} else {
			//
			// Check if we have for instance ($ext_if) that by now has been
			// translated to \( $ext_if \)
			//
			if ($ruleArray[$i] == "\(") {
				while ($ruleArray[++$i] != "\)") {
					$data2[]= $ruleArray[$i];
				}
				$data[]= '(' . implode(' ', $data2) . ')';
				unset($data2);
			} else {
				$data= $ruleArray[$i];
			}
		}
		return Array(
			$data,
			$i
		);
	}

	function parsePortItem($ruleArray, $i)
	{
		$i++;
		//
		// Check if the rule is negated (! { $item1, $item2})
		// If it is, replace with ({ !$item1, !$item2})
		//
		if ($ruleArray[$i] == "!") {
			$ii= ++$i; // Need a new iterator for this loop
			while (preg_replace("/[\s,]+/", "", $ruleArray[++$ii]) != "}") {
				if (($ruleArray[$ii] != "{") and ($ruleArray[$ii] != "}")) {
					if ($ruleArray[$ii]{0} == "!") {
						// Delete Double negations
						$ruleArray[$ii]= substr($ruleArray[$ii], 1);
					} else {
						$ruleArray[$ii]= "!" . $ruleArray[$ii];
					}
				}
			}
		}
		
		if ($ruleArray[$i] == "{") {
			while (preg_replace("/[\s,]+/", "", $ruleArray[++$i]) != "}") {
				$ruleArray[$i]= preg_replace("/[\s,]+/", "", $ruleArray[$i]);
				switch ($ruleArray[$i]) {
					case "}":
						break;
					case "=":
						$data[]= $ruleArray[++$i];
						break;
					case "!=":
					case "<":
					case "<=":
					case ">":
					case ">=":
						$data[]= $ruleArray[$i] . " " . $ruleArray[++$i];
						break;
					default:
						switch (preg_replace("/[\s,]+/", "", $ruleArray[$i + 1])) {
							case "<>":
							case "><":
							case ":":
								$data[]= $ruleArray[$i] . " " . $ruleArray[++$i] . " " . $ruleArray[++$i];
								break;
							default:
								$data[]= $ruleArray[$i];
								break;
						}
						break;
				}
			}
		} else {
			switch ($ruleArray[$i]) {
				case "=":
					$data= $ruleArray[++$i];
					break;
				case "!=":
				case "<":
				case "<=":
				case ">":
				case ">=":
					$data= $ruleArray[$i] . " " . $ruleArray[++$i];
					break;
				default:
					switch (preg_replace("/[\s,]+/", "", $ruleArray[$i + 1])) {
						case "<>":
						case "><":
						case ":":
							$data= $ruleArray[$i] . " " . $ruleArray[++$i] . " " . $ruleArray[++$i];
							break;
						default:
							$data= $ruleArray[$i];
							break;
					}
					break;
			}
		}
		return Array(
			$data,
			$i
		);
	}

	/**
	 * Generates output from either an array of items or a single item into a joined output string.
	 *
	 * @param array $items The item or items that should be generated
	 * @param string $head The heading for this particular item list
	 * @return string $data The parsed output with the rulestring.
	 */
	function generateItem($items, $head= '', $delim_pre= '{', $delim_post= '}')
	{
		$head= $head == '' ? '' : ' ' . trim($head);
		if (is_array($items)) {
			return $head . " $delim_pre " . implode(', ', $items) . " $delim_post";
		} else {
			return $head . ' ' . $items;
		}
	}

	function PrintValue($value, $prefix= '', $postfix= '', $count= 10)
	{
		if ($value) {
			if (!is_array($value)) {
				// Add <br> to call this function twice
				echo "$prefix$value$postfix<br>";
			} else {
				$i= 1;
				foreach ($value as $v) {
					echo "$prefix$v$postfix<br>";
					if (++$i > $count) {
						echo '+' . (count($value) - $count) . ' more entries (not displayed)<br>';
						break;
					}
				}
			}
		}
	}

	function PrintFromTo($value, $noAny= TRUE, $count= 10)
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

	function PrintEditLinks($rulenumber, $count, $up= 'up', $down= 'down', $del= 'del')
	{
		?>
		<a href="<?php echo $this->href . $rulenumber; ?>" title="Edit">e</a>
		<?php
		if ($rulenumber > 0) {
			?>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>?<?php echo $up; ?>=<?php echo $rulenumber; ?>" title="Move up">u</a>
			<?php
		} else {
			echo ' u ';
		}
		if ($rulenumber < $count) {
			?>
			<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>?<?php echo $down; ?>=<?php echo $rulenumber; ?>" title="Move down">d</a>
			<?php
		} else {
			echo ' d ';
		}
		?>
		<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>?<?php echo $del; ?>=<?php echo $rulenumber; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete <?php echo $this->cat; ?> rule number <?php echo $rulenumber; ?>?')">x</a>
		<?php
	}

	function PrintDeleteLinks($value, $name, $prefix= '', $postfix= '')
	{
		if (isset($value)) {
			if (is_array($value)) {
				foreach ($value as $v) {
					$v= htmlentities($v);
					echo "$prefix$v$postfix";
					?>
					<a href="<?php echo $this->href . $this->rulenumber; ?>&amp;<?php echo $name; ?>=<?php echo $v; ?>">delete</a><br>
					<?php
				}
			} else {
				$value= htmlentities($value);
				echo "$prefix$value$postfix";
				?>
				<a href="<?php echo $this->href . $this->rulenumber; ?>&amp;<?php echo $name; ?>=<?php echo $value; ?>">delete</a><br>
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
	function PrintAddControls($id, $label, $hint, $size= 0, $disabled= FALSE)
	{
		?>
		<input type="text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" size="<?php echo $size; ?>" placeholder="<?php echo $hint; ?>" <?php echo $disabled ? 'disabled' : ''; ?> />
		<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
		<?php
	}

	function PrintHelp($label) {
		global $IMG_PATH;
		?>
		<a target="<?php echo $label ?>" href="/pf.conf.html#<?php echo $label ?>">
			<img src="<?php echo "$IMG_PATH/help.png" ?>" name="<?php $label ?>" alt="(?)" border="0" width="12" height="12">
		</a>
		<?php
	}
}
?>
