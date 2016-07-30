<?php
/* $pfre: Filter.php,v 1.4 2016/07/30 15:36:35 soner Exp $ */

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

class Antispoof extends Rule
{
	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keywords,
			array(
				'log' => array(
					'method' => 'setLog',
					'params' => array(),
					),
				'quick' => array(
					'method' => 'setBool',
					'params' => array(),
					),
				'for' => array(
					'method' => 'setItems',
					'params' => array('interface'),
					),
				'inet' => array(
					'method' => 'setNVP',
					'params' => array('family'),
					),
				'inet6' => array(
					'method' => 'setNVP',
					'params' => array('family'),
					),
				'label' => array(
					'method' => 'setItems',
					'params' => array('label'),
					),
				)
			);

		parent::__construct($str);
	}

	function generate()
	{
		$str= 'antispoof';
		if ($this->rule['log']) {
			if (is_array($this->rule['log'])) {
				$s= ' log ( ';
				foreach ($this->rule['log'] as $k => $v) {
					$s.= (is_bool($v) ? "$k" : "$k $v") . ', ';
				}
				$str.= rtrim($s, ', ') . ' )';
			} else {
				$str.= ' log';
			}
		}
		if ($this->rule['quick']) {
			$str.= ' quick';
		}
		if ($this->rule['interface']) {
			$str.= $this->generateItem($this->rule['interface'], 'for');
		}
		if ($this->rule['family']) {
			$str.= ' ' . $this->rule['family'];
		}
		if ($this->rule['label']) {
			$str.= ' label "' . $this->rule['label'] . '"';
		}
		if ($this->rule['comment']) {
			$str.= ' # ' . trim(stripslashes($this->rule['comment']));
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
			<td title="Interface" colspan="2">
				<?php $this->PrintValue($this->rule['interface']); ?>
			</td>
			<td title="Quick">
				<?php echo $this->rule['quick'] ? 'quick' : ''; ?>
			</td>
			<td title="Family" colspan="2">
				<?php echo $this->rule['family']; ?>
			</td>
			<td title="Log" colspan="5">
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
			<td title="Label" colspan="2">
				<?php echo $this->rule['label']; ?>
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
		if (filter_has_var(INPUT_GET, 'dropinterface')) {
			$this->delEntity('interface', filter_input(INPUT_GET, 'dropinterface'));
		}

		if (count($_POST)) {
			if (filter_input(INPUT_POST, 'addinterface') != '') {
				$this->addEntity('interface', filter_input(INPUT_POST, 'addinterface'));
			}

			$this->rule['quick']= (filter_has_var(INPUT_POST, 'quick') ? TRUE : '');
			$this->rule['family']= filter_input(INPUT_POST, 'family');

			$this->rule['log']= (filter_has_var(INPUT_POST, 'log') ? TRUE : '');
			
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

			$this->rule['label']= preg_replace('/"/', '', filter_input(INPUT_POST, 'label'));
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		?>
		<h2>Edit Antispoof Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Antispoof') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form method="post" id="theform" name="theform" action="<?php echo $this->href . $rulenumber; ?>">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Interface').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['interface'], $rulenumber, 'dropinterface');
						$this->PrintAddControls('addinterface', NULL, 'if or macro', NULL, 10);
						$this->PrintHelp('interface');
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Quick').':' ?>
					</td>
					<td>
						<input type="checkbox" id="quick" name="quick" value="quick" <?php echo ($this->rule['quick'] ? 'checked' : ''); ?> />
						<?php $this->PrintHelp('quick') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Address Family').':' ?>
					</td>
					<td>
						<select id="family" name="family">
							<option value="" label=""></option>
							<option value="inet" label="inet" <?php echo ($this->rule['family'] == 'inet' ? 'selected' : ''); ?>>inet</option>
							<option value="inet6" label="inet6" <?php echo ($this->rule['family'] == 'inet6' ? 'selected' : ''); ?>>inet6</option>
						</select>			
						<?php $this->PrintHelp('address-family') ?>
					</td>
				</tr>
				<tr class="evenline">
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
						<input type="text" id="log-to" name="log-to" value="<?php echo (isset($this->rule['log']['to']) ? $this->rule['log']['to'] : ''); ?>" <?php echo $disabled; ?> />
						<input type="checkbox" id="log-all" name="log-all" value="log-all" <?php echo (isset($this->rule['log']['all']) ? 'checked' : ''); ?> <?php echo $disabled; ?> />
						<label for="log">all</label>
						<input type="checkbox" id="log-matches" name="log-matches" value="log-matches" <?php echo (isset($this->rule['log']['matches']) ? 'checked' : ''); ?> <?php echo $disabled; ?> />
						<label for="log">matches</label>
						<input type="checkbox" id="log-user" name="log-user" value="log-user" <?php echo (isset($this->rule['log']['user']) ? 'checked' : ''); ?> <?php echo $disabled; ?> />
						<label for="log">user</label>
						<?php $this->PrintHelp('log') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Label').':' ?>
					</td>
					<td>
						<input type="text" id="label" name="label" value="<?php echo $this->rule['label']; ?>" />
						<?php $this->PrintHelp('label') ?>
					</td>
				</tr>
				<tr class="evenline">
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
