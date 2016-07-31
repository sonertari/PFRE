<?php 
/* $pfre: Option.php,v 1.6 2016/07/31 10:33:34 soner Exp $ */

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

class Option extends Rule
{
	function __construct($str)
	{
		$this->keywords = array(
			'loginterface' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'block-policy' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'state-policy' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'optimization' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'ruleset-optimization' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'debug' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'hostid' => array(
				'method' => 'parseOption',
				'params' => array(),
				),
			'skip' => array(
				'method' => 'parseSkip',
				'params' => array(),
				),
			'fingerprints' => array(
				'method' => 'parseFingerprints',
				'params' => array(),
				),
			);

		// Base should not merge keywords
		parent::__construct($str);
	}

	function parseOption()
	{
		$this->rule['option'][$this->words[$this->index]]= $this->words[++$this->index];
	}

	function parseSkip()
	{
		list($this->rule['option']['skip'], $this->index)= $this->parseItem($this->words, ++$this->index);
	}

	function parseFingerprints()
	{
		// File name is in quotes, skip the quote
		$this->index+= 2;
		$this->rule['option']['fingerprints']= $this->words[$this->index];
	}

	function generate()
	{
		$this->str= '';

		$this->genOption('loginterface');
		$this->genOption('optimization');
		$this->genOption('ruleset-optimization');
		$this->genOption('block-policy');
		$this->genOption('state-policy');
		$this->genOption('debug');
		$this->genOption('fingerprints', '"', '"');
		$this->genSkip();
		
		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genOption($key, $head= '', $tail= '')
	{
		if (isset($this->rule['option'][$key])) {
			$this->str.= "set $key " . $head . preg_replace('/"/', '', $this->rule['option'][$key]) . $tail;
		}
	}

	function genSkip()
	{
		if (isset($this->rule['option']['skip'])) {
			if (!is_array($this->rule['option']['skip'])) {
				$this->genOption('skip', 'on ');
			} else {
				$this->str.= 'set skip on { ' . implode(' ', $this->rule['option']['skip']) . ' }';
			}
		}
	}

	function display($rulenumber, $count)
	{
		$this->dispHead($rulenumber);
		$this->dispOption();
		$this->dispTail($rulenumber, $count);
	}
	
	function dispOption()
	{
		?>
		<td title="Option" colspan="12">
			<?php
			$option= key($this->rule['option']);
			$value= $this->rule['option'][$option];
			if (in_array($option, array('loginterface', 'optimization', 'ruleset-optimization', 'block-policy', 'state-policy', 'debug', 'fingerprints'))) {
				echo "$option: $value";
			} elseif ($option == 'skip') {
				if (!is_array($value)) {
					echo "skip on $value";
				} else {
					foreach ($value as $skip) {
						echo "skip on $skip<br>";
					}
				}
			}
			?>
		</td>
		<?php
	}

	function processInput()
	{
		if (count($_POST)) {
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');

			if (filter_has_var(INPUT_POST, 'block-policy')) {
				$this->rule['option']['block-policy']= filter_input(INPUT_POST, 'block-policy');
			}
			if (filter_has_var(INPUT_POST, 'optimization')) {
				$this->rule['option']['optimization']= filter_input(INPUT_POST, 'optimization');
			}
			if (filter_has_var(INPUT_POST, 'ruleset-optimization')) {
				$this->rule['option']['ruleset-optimization']= filter_input(INPUT_POST, 'ruleset-optimization');
			}
			if (filter_has_var(INPUT_POST, 'state-policy')) {
				$this->rule['option']['state-policy']= filter_input(INPUT_POST, 'state-policy');
			}
			if (filter_has_var(INPUT_POST, 'debug')) {
				$this->rule['option']['debug']= filter_input(INPUT_POST, 'debug');
			}
			if (filter_has_var(INPUT_POST, 'fingerprints')) {
				$this->rule['option']['fingerprints']= trim(preg_replace("/\"/", "", filter_input(INPUT_POST, 'fingerprints')));
			}

			if (filter_has_var(INPUT_POST, 'loginterface')) {
				if (strlen(trim(filter_input(INPUT_POST, 'loginterface')))) {
					$this->rule['option']['loginterface']= trim(filter_input(INPUT_POST, 'loginterface'));
				} else {
					$this->rule['option']['loginterface']= '';
				}
			}
			if (filter_has_var(INPUT_POST, 'skip')) {
				if (strlen(trim(filter_input(INPUT_POST, 'skip')))) {
					if (count(preg_split("/[\s,\t]+/", trim(filter_input(INPUT_POST, 'skip')))) > 1) {
						unset($this->rule['option']['skip']);
						foreach (preg_split("/[\s,\t]+/", trim(filter_input(INPUT_POST, 'skip'))) as $skip) {
							$this->rule['option']['skip'][]= $skip;
						}
					} else {
						$this->rule['option']['skip']= trim(filter_input(INPUT_POST, 'skip'));
					}
				} else {
					$this->rule['option']['skip']= '';
				}
			}
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		// XXX: Fix this
		if (count($_POST)) {
			$type= filter_input(INPUT_POST, 'type');
		}
		if (isset($this->rule['option']) && count($this->rule['option'])) {
			$type= key($this->rule['option']);
		}
		?>
		<h2>Edit Option Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Option') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" action="<?php echo $this->href . $rulenumber; ?>" method="post">
			<table id="nvp">
				<?php
				if (!isset($type)) {
					?>
					<tr class="oddline">
						<td class="title">
							<?php echo _TITLE('Select Option Type').':' ?>
						</td>
						<td>
							<select id="type" name="type">
								<option value="block-policy" <?php echo ($type == 'block-policy' ? 'selected' : ''); ?>>block-policy</option>
								<option value="optimization" <?php echo ($type == 'optimization' ? 'selected' : ''); ?>>optimization</option>
								<option value="ruleset-optimization" <?php echo ($type == 'ruleset-optimization' ? 'selected' : ''); ?>>ruleset-optimization</option>
								<option value="state-policy" <?php echo ($type == 'state-policy' ? 'selected' : ''); ?>>state-policy</option>
								<option value="fingerprints" <?php echo ($type == 'fingerprints' ? 'selected' : ''); ?>>fingerprints</option>
								<option value="loginterface" <?php echo ($type == 'loginterface' ? 'selected' : ''); ?>>loginterface</option>
								<option value="debug" <?php echo ($type == 'debug' ? 'selected' : ''); ?>>debug</option>
								<option value="skip" <?php echo ($type == 'skip' ? 'selected' : ''); ?>>skip</option>
							</select>
						</td>
					</tr>
					<?php
				}
				?>
				<tr class="oddline">
					<?php
					if (isset($this->rule['option']['block-policy']) || $type == 'block-policy') {
						?>
						<td class="title">
							<?php echo _TITLE('Block Policy').':' ?>
						</td>
						<td>
							<select id="block-policy" name="block-policy">
								<option value="drop" label="drop" <?php echo ($this->rule['option']['block-policy'] == 'drop' ? 'selected' : ''); ?>>drop</option>
								<option value="return" label="return" <?php echo ($this->rule['option']['block-policy'] == 'return' ? 'selected' : ''); ?>>return</option>
							</select>
							<?php $this->PrintHelp('block-policy') ?>
						</td>
						<?php
					}
					if (isset($this->rule['option']['optimization']) || $type == 'optimization') {
						?>
						<td class="title">
							<?php echo _TITLE('Optimization').':' ?>
						</td>
						<td>
							<select id="optimization" name="optimization">
								<option value="normal" <?php echo ($this->rule['option']['optimization'] == 'normal' ? 'selected' : ''); ?>>normal</option>
								<option value="high-latency" <?php echo ($this->rule['option']['optimization'] == 'high-latency' ? 'selected' : ''); ?>>high-latency</option>
								<option value="satellite" <?php echo ($this->rule['option']['optimization'] == 'satellite' ? 'selected' : ''); ?>>satellite</option>
								<option value="aggressive" <?php echo ($this->rule['option']['optimization'] == 'aggressive' ? 'selected' : ''); ?>>aggressive</option>
								<option value="conservative" <?php echo ($this->rule['option']['optimization'] == 'conservative' ? 'selected' : ''); ?>>conservative</option>
							</select>
							<?php $this->PrintHelp('optimization') ?>
						</td>
						<?php
					}
					if (isset($this->rule['option']['ruleset-optimization']) || $type == 'ruleset-optimization') {
						?>
						<td class="title">
							<?php echo _TITLE('Ruleset Optimization').':' ?>
						</td>
						<td>
							<select id="ruleset-optimization" name="ruleset-optimization">
								<option value="none" <?php echo ($this->rule['option']['ruleset-optimization'] == 'none' ? 'selected' : ''); ?>>none</option>
								<option value="basic" <?php echo ($this->rule['option']['ruleset-optimization'] == 'basic' ? 'selected' : ''); ?>>basic</option>
								<option value="profile" <?php echo ($this->rule['option']['ruleset-optimization'] == 'profile' ? 'selected' : ''); ?>>profile</option>
							</select>
							<?php $this->PrintHelp('ruleset-optimization') ?>
						</td>
						<?php
					}
					if (isset($this->rule['option']['state-policy']) || $type == 'state-policy') {
						?>
						<td class="title">
							<?php echo _TITLE('State Policy').':' ?>
						</td>
						<td>
							<select id="state-policy" name="state-policy">
								<option value="if-bound" <?php echo ($this->rule['option']['state-policy'] == 'if-bound' ? 'selected' : ''); ?>>if-bound</option>
								<option value="floating" <?php echo ($this->rule['option']['state-policy'] == 'floating' ? 'selected' : ''); ?>>floating</option>
							</select>
							<?php $this->PrintHelp('state-policy') ?>
						</td>
						<?php
					}
					if (isset($this->rule['option']['fingerprints']) || $type == 'fingerprints') {
						?>
						<td class="title">
							<?php echo _TITLE('Fingerprints File').':' ?>
						</td>
						<td>
							<input type="text" size="10" id="fingerprints" name="fingerprints" value="<?php echo $this->rule['option']['fingerprints']; ?>" placeholder="filename"/>
							<?php $this->PrintHelp('fingerprints') ?>
						</td>
						<?php
					}
					if (isset($this->rule['option']['loginterface']) || $type == 'loginterface') {
						?>
						<td class="title">
							<?php echo _TITLE('Log Interface').':' ?>
						</td>
						<td>
							<input type="text" size="10" id="loginterface" name="loginterface" value="<?php echo $this->rule['option']['loginterface']; ?>"  placeholder="interface"/>
							<?php $this->PrintHelp('loginterface') ?>
						</td>
						<?php
					}
					if (isset($this->rule['option']['debug']) || $type == 'debug') {
						?>
						<td class="title">
							<?php echo _TITLE('Debug').':' ?>
						</td>
						<td>
							<select id="debug" name="debug">
								<option value="emerg" <?php echo ($this->rule['option']['debug'] == 'emerg' ? 'selected' : ''); ?>>emerg</option>
								<option value="alert" <?php echo ($this->rule['option']['debug'] == 'alert' ? 'selected' : ''); ?>>alert</option>
								<option value="crit" <?php echo ($this->rule['option']['debug'] == 'crit' ? 'selected' : ''); ?>>crit</option>
								<option value="err" <?php echo ($this->rule['option']['debug'] == 'err' ? 'selected' : ''); ?>>err</option>
								<option value="warning" <?php echo ($this->rule['option']['debug'] == 'warning' ? 'selected' : ''); ?>>warning</option>
								<option value="notice" <?php echo ($this->rule['option']['debug'] == 'notice' ? 'selected' : ''); ?>>notice</option>
								<option value="info" <?php echo ($this->rule['option']['debug'] == 'info' ? 'selected' : ''); ?>>info</option>
								<option value="debug" <?php echo ($this->rule['option']['debug'] == 'debug' ? 'selected' : ''); ?>>debug</option>
							</select>
							<?php $this->PrintHelp('debug') ?>
						</td>
						<?php
					}
					if (isset($this->rule['option']['skip']) || $type == 'skip') {
						?>
						<td class="title">
							<?php echo _TITLE('Skip Interfaces').':' ?>
						</td>
						<td>
							<input type="text" size="40" id="skip" name="skip" value="<?php echo (is_array($this->rule['option']['skip']) ? implode(' ', $this->rule['option']['skip']) : $this->rule['option']['skip']); ?>"
								placeholder="comma or space separated list of interfaces"/>
							<?php $this->PrintHelp('skip') ?>
						</td>
						<?php
					}
					?>
				</tr>
				<?php
				if (isset($type)) {
					?>
					<tr class="evenline">
						<td class="title">
							<?php echo _TITLE('Comment').':' ?>
						</td>
						<td>
							<input type="text" id="comment" name="comment" value="<?php echo stripslashes($this->rule['comment']); ?>" size="80" />
						</td>
					</tr>
					<?php
				}
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
}
?>