<?php
/* $pfre: Anchor.php,v 1.5 2016/07/30 15:36:35 soner Exp $ */

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

class Anchor extends Filter
{	
	function __construct($str)
	{
		$this->keywords = array(
			'anchor' => array(
				'method' => 'setAnchor',
				'params' => array(),
				),
			);

		parent::__construct($str, TRUE);
	}

	function setAnchor()
	{
		$this->rule['type']= $this->words[$this->index++];
		if ($this->words[$this->index] == '"') {
			$this->rule['identifier']= $this->words[++$this->index];
			if ($this->words[$this->index + 1] == '"') {
				$this->index++;
			}
		} else {
			$this->rule['identifier']= $this->words[$this->index];
		}
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
			<td title="Id" class="<?php echo $this->rule['type']; ?>" nowrap="nowrap">
				<?php echo $this->rule['type'] . ' ' . $this->rule['identifier']; ?>
			</td>
			<td title="Direction">
				<?php echo $this->rule['direction']; ?>
			</td>
			<td title="Interface">
				<?php $this->PrintValue($this->rule['interface']); ?>
			</td>
			<td title="Log">
				<?php echo $this->rule['log'] ? 'log' : ''; ?>
			</td>
			<td title="Quick">
				<?php echo $this->rule['quick'] ? 'quick' : ''; ?>
			</td>
			<td title="Proto">
				<?php $this->PrintValue($this->rule['proto']); ?>
			</td>
			<?php
			if ($this->rule['all']) {
				?>
				<td title="Source->Destination" colspan="4" class="all">
					All
				</td>
				<?php
			} else {
				?>
				<td title="Source">
					<?php $this->PrintFromTo($this->rule['from']); ?>
				</td>
				<td title="Source Port">
					<?php $this->PrintFromTo($this->rule['fromport']); ?>
				</td>
				<td title="Destination">
					<?php $this->PrintFromTo($this->rule['to']); ?>
				</td>
				<td title="Destination Port">
					<?php $this->PrintFromTo($this->rule['port']); ?>
				</td>
				<?php
			}
			?>
			<td title="State">
				<?php echo $this->rule['state']; ?>
			</td>
			<td title="Queue">
				<?php echo isset($this->rule['queue']) ? (!is_array($this->rule['queue']) ? $this->rule['queue'] : $this->rule['queue'][0] . '<br>' . $this->rule['queue'][1]) : ''; ?>
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
		if (filter_has_var(INPUT_GET, 'dropfrom')) {
			$this->delEntity("from", filter_input(INPUT_GET, 'dropfrom'));
		}

		if (filter_has_var(INPUT_GET, 'dropfromport')) {
			$this->delEntity("fromport", filter_input(INPUT_GET, 'dropfromport'));
		}

		if (filter_has_var(INPUT_GET, 'dropto')) {
			$this->delEntity("to", filter_input(INPUT_GET, 'dropto'));
		}

		if (filter_has_var(INPUT_GET, 'dropport')) {
			$this->delEntity("port", filter_input(INPUT_GET, 'dropport'));
		}

		if (filter_has_var(INPUT_GET, 'dropinterface')) {
			$this->delEntity("interface", filter_input(INPUT_GET, 'dropinterface'));
		}

		if (filter_has_var(INPUT_GET, 'dropproto')) {
			$this->delEntity("proto", filter_input(INPUT_GET, 'dropproto'));
		}

		if (count($_POST)) {
			if (filter_has_var(INPUT_POST, 'addfrom') != '') {
				$this->addEntity("from", filter_input(INPUT_POST, 'addfrom'));
			}

			if (filter_has_var(INPUT_POST, 'addfromport') != '') {
				$this->addEntity("fromport", filter_input(INPUT_POST, 'addfromport'));
			}

			if (filter_has_var(INPUT_POST, 'addto') != '') {
				$this->addEntity("to", filter_input(INPUT_POST, 'addto'));
			}

			if (filter_has_var(INPUT_POST, 'addport') != '') {
				$this->addEntity("port", filter_input(INPUT_POST, 'addport'));
			}

			if (filter_has_var(INPUT_POST, 'addinterface') != '') {
				$this->addEntity("interface", filter_input(INPUT_POST, 'addinterface'));
			}

			if (filter_has_var(INPUT_POST, 'addproto') != '') {
				$this->addEntity("proto", filter_input(INPUT_POST, 'addproto'));
			}

			$this->rule['type']= "anchor";
			$this->rule['identifier']= preg_replace("/\"/", "", filter_input(INPUT_POST, 'identifier'));
			$this->rule['direction']= filter_input(INPUT_POST, 'direction');
			$this->rule['family']= filter_input(INPUT_POST, 'family');

			if (filter_has_var(INPUT_POST, 'all')) {
				$this->rule['all']= TRUE;
				unset($this->rule['from']);
				unset($this->rule['fromport']);
				unset($this->rule['to']);
				unset($this->rule['port']);
			} else {
				unset($this->rule['all']);
			}

			$this->rule['comment']= filter_input(INPUT_POST, 'comment');
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		?>
		<h2>Edit Anchor Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Anchor') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" name="theform" action="<?php echo $this->href . $rulenumber; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Identifier').':' ?>
					</td>
					<td>
						<input type="text" id="identifier" name="identifier" size="20" value="<?php echo $this->rule['identifier']; ?>" />
						<?php $this->PrintHelp('anchor-id') ?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Direction').':' ?>
					</td>
					<td>
						<select id="direction" name="direction">
							<option value="" label=""></option>
							<option value="in" label="in" <?php echo ($this->rule['direction'] == 'in' ? 'selected' : ''); ?>>in</option>
							<option value="out" label="out" <?php echo ($this->rule['direction'] == 'out' ? 'selected' : ''); ?>>out</option>
						</select>
						<?php $this->PrintHelp('direction') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Interface').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['interface'], $rulenumber, 'dropinterface');
						$this->PrintAddControls('addinterface', 'add interface', 'if or macro', NULL, 10);
						$this->PrintHelp('interface');
						?>
					</td>
				</tr>
				<tr class="evenline">
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
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Protocol').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['proto'], $rulenumber, 'dropproto');
						$this->PrintAddControls('addproto', NULL, 'protocol', NULL, 10);
						$this->PrintHelp('proto');
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Match All').':' ?>
					</td>
					<td>
						<input type="checkbox" id="all" name="all" value="all" <?php echo ($this->rule['all'] ? 'checked' : ''); ?> onclick="document.theform.submit()" />
						<?php $this->PrintHelp('match-all') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Source').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['from'], $rulenumber, 'dropfrom');
						$this->PrintAddControls('addfrom', NULL, 'ip, host or macro', NULL, NULL, $this->rule['all']);
						$this->PrintHelp('src-dst');
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Source Port').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['fromport'], $rulenumber, 'dropfromport');
						$this->PrintAddControls('addfromport', NULL, 'number, name, table or macro', NULL, NULL, $this->rule['all']);
						?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Destination').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['to'], $rulenumber, 'dropto');
						$this->PrintAddControls('addto', NULL, 'ip, host, table or macro', NULL, NULL, $this->rule['all']);
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Destination Port').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['port'], $rulenumber, 'dropport');
						$this->PrintAddControls('addport', NULL, 'number, name or macro', NULL, NULL, $this->rule['all']);
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
