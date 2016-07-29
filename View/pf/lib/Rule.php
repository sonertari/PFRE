<?php
/* $pfre: Rule.php,v 1.6 2016/07/27 15:08:56 soner Exp $ */

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
	
	function __construct($str)
	{
		$this->cat= get_called_class();
		$this->parse($str);
	}

	function deleteEmptyEntries()
	{
		/// @todo Implement deeper delete than just one level?
		foreach ($this->rule as $key => $value) {
			if ($value == '') {
				unset($this->rule[$key]);
			} elseif (is_array($value) && count($value) == 0) {
				unset($this->rule[$key]);
			}
		}
	}
	
	/**
	 * Adds an entity to the rule, used for things that can hold more than one thing.
	 *
	 * @param string $type What we are adding the entity to.
	 * @param string $data The data to add to the $type.
	 * @return void
	 */
	function addEntity($type, $data)
	{
		if (!isset($this->rule[$type])) {
			$this->rule[$type]= $data;
		} else 
			if (!is_array($this->rule[$type])) {
				$_temp= $this->rule[$type];
				unset($this->rule[$type]);
				$this->rule[$type][]= $_temp;
				$this->rule[$type][]= $data;
				$this->rule[$type]= array_unique($this->rule[$type]);
			} else {
				$this->rule[$type][]= $data;
				$this->rule[$type]= array_unique($this->rule[$type]);
			}
	}

	/**
	 * Deletes an entity from the rule, used for things that can hold more than one thing.
	 *
	 * @param string $type What we are deleting the entity from.
	 * @param string $data The data to delete from the $type.
	 * @return void
	 */
	function delEntity($type, $data)
	{
		if (is_array($this->rule[$type])) {
			foreach ($this->rule[$type] as $entity) {
				if ($entity != $data) {
					$_new_data[]= $entity;
				}
			}
			if (count($_new_data) == '1') {
				$this->rule[$type]= $_new_data['0'];
			} else {
				$this->rule[$type]= $_new_data;
			}
		} else {
			unset($this->rule[$type]);
		}
	}

	/**
	 * Parse rule string.
	 *
	 * @param array $ruleArray Rule string split into an array.
	 * @param int $i The pointer to where in the ruleset the parsing is at.
	 * @return array $data,$i The parsed string and the new pointer.
	 */
	function parseString($ruleArray, $i)
	{
		$i++;
		if ($ruleArray[$i] == "\"") {
			$data= $ruleArray[++$i];
			while ($ruleArray[++$i] != "\"") {
				$data.= " " . $ruleArray[$i];
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
	 * @param string $heading The heading for this particular item list
	 * @return string $data The parsed output with the rulestring.
	 */
	function generateItem($items, $heading= '')
	{
		$heading= $heading == '' ? '' : ' ' . trim($heading);
		if (is_array($items)) {
			return $heading . ' { ' . implode(' ', $items) . ' }';
		} else {
			return $heading . ' ' . $items;
		}
	}
	
	function PrintValue($value, $prefix= '', $postfix= '', $count= 10)
	{
		if ($value) {
			if (!is_array($value)) {
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
	
	function PrintEditLinks($rulenumber, $href, $count, $up= 'up', $down= 'down', $del= 'del')
	{
		?>
		<a href="<?php echo $href; ?>" title="Edit">e</a>
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
	
	function PrintDeleteLinks($value, $href, $name, $prefix= '', $postfix= '')
	{
		if (isset($value)) {
			if (is_array($value)) {
				foreach ($value as $v) {
					$v= htmlentities($v);
					echo "$prefix$v$postfix";
					?>
					<a href="<?php echo $href; ?>&amp;<?php echo $name; ?>=<?php echo $v; ?>">delete</a><br>
					<?php
				}
			} else {
				$value= htmlentities($value);
				echo "$prefix$value$postfix";
				?>
				<a href="<?php echo $href; ?>&amp;<?php echo $name; ?>=<?php echo $value; ?>">delete</a><br>
				<?php
			}
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
	 * @param[in]	$div	bool	Whether to surround with a div
	 */
	function PrintAddControls($id, $label, $hint, $value= NULL, $size= 0, $disabled= FALSE, $div= FALSE)
	{
		$value= ($value == NULL) ? '' : $value;

		if ($div) {
			?>
			<div class="add">
			<?php
		}
		?>
		<input type="text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" size="<?php echo $size; ?>" value="<?php echo $value; ?>"
			placeholder="<?php echo $hint; ?>" <?php echo $disabled ? 'disabled' : ''; ?> />
		<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
		<?php
		if ($div) {
			?>
			</div>
			<?php
		}
	}
}
?>
