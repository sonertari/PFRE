<?php
/* $pfre: Nat.php,v 1.5 2016/07/30 20:38:08 soner Exp $ */

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

class AfTo extends Filter
{
	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keywords,
			array(
				'af-to' => array(
					'method' => 'parseAfto',
					'params' => array(),
					),
				)
			);

		parent::__construct($str);
	}

	function parseAfto()
	{
		$this->rule['rediraf']= $this->words[++$this->index];

		if ($this->words[$this->index + 1] === 'from') {
			$this->index+= 2;
			$this->rule['redirhost']= $this->words[$this->index];

			if ($this->words[$this->index + 1] === 'to') {
				$this->index+= 2;
				$this->rule['toredirhost']= $this->words[$this->index];
			}
		}
	}

	function generate()
	{
		$this->str= $this->rule['action'];

		$this->genFilterHead();
		$this->genFilterOpts();

		$this->genAfto();
		// @todo Can we have pooltype with af-to? pfctl does not complain about it

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genAfto()
	{
		$this->str.= ' af-to';
		$this->genValue('rediraf');
		$this->genValue('redirhost', 'from ');
		$this->genValue('toredirhost', 'to ');
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
			<td title="Action" class="<?php echo $this->rule['action']; ?>">
				<?php echo $this->rule['action']; ?>
			</td>
			<td title="Interface">
				<?php $this->PrintValue($this->rule['interface']); ?>
			</td>
			<td title="Log">
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
			<td title="Quick">
				<?php echo $this->rule['quick'] ? 'quick' : ''; ?>
			</td>
			<td title="Proto">
				<?php $this->PrintValue($this->rule['proto']); ?>
			</td>
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
			<td title="Redirect Address Family">
				<?php echo $this->rule['rediraf']; ?>
			</td>
			<td title="From Redirect Host">
				<?php $this->PrintFromTo($this->rule['redirhost']); ?>
			</td>
			<td title="To Redirect Host">
				<?php $this->PrintFromTo($this->rule['toredirhost']); ?>
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
			if (filter_input(INPUT_POST, 'addinterface') != '') {
				$this->addEntity("interface", filter_input(INPUT_POST, 'addinterface'));
			}

			if (filter_input(INPUT_POST, 'addproto') != '') {
				$this->addEntity("proto", filter_input(INPUT_POST, 'addproto'));
			}

			if ($this->rule['type'] != filter_input(INPUT_POST, 'type')) {
				$this->rule['type']= filter_input(INPUT_POST, 'type');
				if ($this->rule['type'] == 'af-to') {
					unset($this->rule['quick']);
					unset($this->rule['proto']);
					unset($this->rule['redirhost']);
					unset($this->rule['redirport']);
				}
			}

			$this->rule['action']= filter_input(INPUT_POST, 'action');
			$this->rule['direction']= filter_input(INPUT_POST, 'direction');

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

			$this->rule['quick']= (filter_has_var(INPUT_POST, 'quick') ? TRUE : "");

			if (!filter_has_var(INPUT_POST, 'addproto')) {
				if (filter_input(INPUT_POST, 'proto') == 'tcpudp') {
					$this->addEntity("proto", 'tcp');
					$this->addEntity("proto", 'udp');
				} else {
					$this->rule['proto']= filter_input(INPUT_POST, 'proto');
				}
			}

			if (filter_input(INPUT_POST, 'addfrom') != '') {
				$this->addEntity("from", filter_input(INPUT_POST, 'addfrom'));
			}

			if (filter_input(INPUT_POST, 'addfromport') != '') {
				$this->addEntity("fromport", filter_input(INPUT_POST, 'addfromport'));
			}

			if (filter_input(INPUT_POST, 'addto') != '') {
				$this->addEntity("to", filter_input(INPUT_POST, 'addto'));
			}

			if (filter_input(INPUT_POST, 'addport') != '') {
				$this->addEntity("port", filter_input(INPUT_POST, 'addport'));
			}

			$this->rule['redirhost']= filter_input(INPUT_POST, 'redirhost');
			$this->rule['redirport']= filter_input(INPUT_POST, 'redirport');

			$this->rule['family']= filter_input(INPUT_POST, 'family');
			$this->rule['to-family']= filter_input(INPUT_POST, 'to-family');

			$this->rule['bitmask']= (filter_has_var(INPUT_POST, 'bitmask') ? TRUE : "");
			$this->rule['least-states']= (filter_has_var(INPUT_POST, 'least-states') ? TRUE : "");
			$this->rule['random']= (filter_has_var(INPUT_POST, 'random') ? TRUE : "");
			$this->rule['round-robin']= (filter_has_var(INPUT_POST, 'round-robin') ? TRUE : "");
			$this->rule['source-hash']= (filter_has_var(INPUT_POST, 'source-hash') ? TRUE : "");
			$this->rule['source-hash-key']= filter_input(INPUT_POST, 'source-hash-key');

			$this->rule['sticky-address']= (filter_has_var(INPUT_POST, 'sticky-address') ? TRUE : "");

			$this->rule['static-port']= (filter_has_var(INPUT_POST, 'static-port') ? TRUE : "");

			$this->rule['flags']= filter_input(INPUT_POST, 'flags');

			$this->rule['comment']= filter_input(INPUT_POST, 'comment');
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		?>
		<h2>Edit Af-to Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('af-to') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" name="theform" action="<?php echo $this->href . $rulenumber; ?>" method="post">
			<table id="nvp">
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Action').':' ?>
					</td>
					<td>
						<select id="action" name="action">
							<option label="pass" <?php echo $this->rule['action'] == 'pass' ? 'selected' : ''; ?>>pass</option>
							<option label="match" <?php echo $this->rule['action'] == 'match' ? 'selected' : ''; ?>>match</option>
							<option label="block" <?php echo $this->rule['action'] == 'block' ? 'selected' : ''; ?>>block</option>
						</select>
						<?php
						$this->PrintHelp($this->rule['action']);
						
						if ($this->rule['type'] == "divert-to") {
							?>
							<input type="checkbox" id="quick" name="quick" value="quick" <?php echo ($this->rule['quick'] ? 'checked' : ''); ?> />
							<label for="quick">quick</label>
							<?php
							$this->PrintHelp('quick');
						}
						?>
					</td>
				</tr>
				<?php
				if ($this->rule['type'] != "binat-to") {
					?>
					<tr class="oddline">
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
					<?php
				}
				?>
				<tr class="evenline">
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
				<?php
				if ($this->rule['type'] != "af-to") {
					?>
					<tr class="oddline">
						<td class="title">
							<?php echo _TITLE('Protocol').':' ?>
						</td>
						<td>
							<?php
							if ($this->rule['type'] == "divert-to") {
								?>
								<select id="proto" name="proto">
									<option value="" label=""></option>
									<option value="tcp" label="tcp" <?php echo $this->rule['proto'] == 'tcp' ? 'selected' : ''; ?>>tcp</option>
									<option value="udp" label="udp" <?php echo $this->rule['proto'] == 'udp' ? 'selected' : ''; ?>>udp</option>
									<option value="tcpudp" label="tcp / udp" <?php echo is_array($this->rule['proto']) ? 'selected' : ''; ?>>tcp/udp</option>
								</select>
								<?php
							} else {
								$this->PrintDeleteLinks($this->rule['proto'], $rulenumber, 'dropproto');
								$this->PrintAddControls('addproto', NULL, 'protocol', NULL, 10);
							}
							$this->PrintHelp('proto');
							?>
						</td>
					</tr>
					<?php
				}
				?>
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
						<?php echo _TITLE('Source').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['from'], $rulenumber, 'dropfrom');
						$this->PrintAddControls('addfrom', NULL, 'ip, host or macro', NULL, NULL, $this->rule['all']);
						$this->PrintHelp('src-dst');
						?>
						<select id="family" name="family">
							<option value="" label=""></option>
							<option value="inet" label="inet" <?php echo ($this->rule['family'] == 'inet' ? 'selected' : ''); ?>>inet</option>
							<option value="inet6" label="inet6" <?php echo ($this->rule['family'] == 'inet6' ? 'selected' : ''); ?>>inet6</option>
						</select>
						<label for="family">address family</label>
						<?php $this->PrintHelp('address-family') ?>
					</td>
				</tr>
				<?php
				if ($this->rule['type'] != "af-to") {
					?>
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
					<?php
				}
				?>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Destination').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['to'], $rulenumber, 'dropto');
						$this->PrintAddControls('addto', NULL, 'ip, host, table or macro', NULL, NULL, $this->rule['all']);

						if ($this->rule['type'] == "af-to") {
							?>
							<select id="to-family" name="to-family">
								<option value="" label=""></option>
								<option value="inet" label="inet" <?php echo ($this->rule['to-family'] == 'inet' ? 'selected' : ''); ?>>inet</option>
								<option value="inet6" label="inet6" <?php echo ($this->rule['to-family'] == 'inet6' ? 'selected' : ''); ?>>inet6</option>
							</select>			
							<label for="to-family">address family</label>
							<?php
							$this->PrintHelp('address-family');
						}
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
				<?php
				if ($this->rule['type'] != "af-to") {
					?>
					<tr class="oddline">
						<td class="title">
							<?php echo _TITLE('NAT Destination').':' ?>
						</td>
						<td>
							<input type="text" id="natdest" name="natdest" size="20" value="<?php echo $this->rule['redirhost']; ?>" />
							<?php $this->PrintHelp('divert-to') ?>
						</td>
					</tr>
					<tr class="evenline">
						<td class="title">
							<?php echo _TITLE('NAT Destination Port').':' ?>
						</td>
						<td>
							<input type="text" id="natdestport" name="natdestport" size="20" value="<?php echo $this->rule['redirport']; ?>" />
							<?php $this->PrintHelp('divert-to') ?>
						</td>
					</tr>
					<?php
				}
				if ($this->rule['type'] != "divert-to") {
					?>
					<tr class="oddline">
						<td class="title">
							<?php echo _TITLE('Options').':' ?>
						</td>
						<td>
							<input type="checkbox" id="bitmask" name="bitmask" <?php echo ($this->rule['least-states'] || $this->rule['random'] || $this->rule['round-robin'] || $this->rule['source-hash'] ? 'disabled' : ''); ?> value="bitmask" <?php echo ($this->rule['bitmask'] ? 'checked' : ''); ?> />
							<label for="bitmask">bitmask</label>
							<br>
							<input type="checkbox" id="least-states" name="least-states" <?php echo ($this->rule['bitmask'] || $this->rule['random'] || $this->rule['round-robin'] || $this->rule['source-hash'] ? 'disabled' : ''); ?> value="least-states" <?php echo ($this->rule['least-states'] ? 'checked' : ''); ?> />
							<label for="least-states">least-states</label>
							<br>
							<input type="checkbox" id="random" name="random" <?php echo ($this->rule['bitmask'] || $this->rule['least-states'] || $this->rule['round-robin'] || $this->rule['source-hash'] ? 'disabled' : ''); ?> value="random" <?php echo ($this->rule['random'] ? 'checked' : ''); ?> />
							<label for="random">random</label>
							<br>
							<input type="checkbox" id="round-robin" name="round-robin" <?php echo ($this->rule['bitmask'] || $this->rule['least-states'] || $this->rule['random'] || $this->rule['source-hash'] ? 'disabled' : ''); ?> value="round-robin" <?php echo ($this->rule['round-robin'] ? 'checked' : ''); ?> />
							<label for="round-robin">round-robin</label>
							<br>
							<input type="checkbox" id="source-hash" name="source-hash" <?php echo ($this->rule['bitmask'] || $this->rule['least-states'] || $this->rule['random'] || $this->rule['round-robin'] ? 'disabled' : ''); ?> value="source-hash" <?php echo ($this->rule['source-hash'] ? 'checked' : ''); ?> />
							<label for="source-hash">source-hash</label>
							<input type="text" id="source-hash-key" name="source-hash-key" <?php echo ($this->rule['source-hash'] ? '' : 'disabled'); ?> value="<?php echo $this->rule['source-hash-key']; ?>" />
							<label for="source-hash-key">key</label>
							<br>
							<input type="checkbox" id="sticky-address" name="sticky-address" <?php echo ($this->rule['bitmask'] || $this->rule['least-states'] || $this->rule['random'] || $this->rule['round-robin'] || $this->rule['source-hash'] ? '' : 'disabled'); ?> value="sticky-address" <?php echo ($this->rule['sticky-address'] ? 'checked' : ''); ?> />
							<label for="sticky-address">sticky-address</label>
							<?php $this->PrintHelp('rdr-method') ?>
						</td>
					</tr>
					<?php
					if ($this->rule['type'] != "rdr-to") {
						?>
						<tr class="evenline">
							<td class="title">
								<?php echo _TITLE('Static Port').':' ?>
							</td>
							<td>
								<input type="checkbox" id="static-port" name="static-port" value="static-port" <?php echo ($this->rule['static-port'] ? 'checked' : ''); ?> />
								<?php $this->PrintHelp('static-port') ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('TCP Flags').':' ?>
					</td>
					<td>
						<?php
						$this->PrintAddControls('flags', NULL, 'flags or macro', $this->rule['flags'], 12);
						$this->PrintHelp('flags');
						?>
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
