<?php
/* $pfre: Nat.php,v 1.2 2016/07/29 02:27:09 soner Exp $ */

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
class Nat extends Rule
{
	function parse($str)
	{
		$this->rule= array();
		if (strpos($str, "#")) {
			$this->rule['comment']= substr($str, strpos($str, "#") + '1');
			$str= substr($str, '0', strpos($str, "#"));
		}
		
		/*
		 * Sanitize the rule string so that we can deal with '{foo' as '{ foo' in
		 * the code further down without any special treatment
		 */
		$str= preg_replace("/! +/", "!", $str);
		$str= preg_replace("/{/", " { ", $str);
		$str= preg_replace("/}/", " } ", $str);
		$str= preg_replace("/\"/", " \" ", $str);
		
		$words= preg_split("/[\s,\t]+/", $str, '-1', PREG_SPLIT_NO_EMPTY);
		
		for ($i= '0'; $i < count($words); $i++) {
			switch ($words[$i]) {
				case 'pass':
				case 'match':
				case 'block':
					$this->rule['action']= $words[$i];
					break;
				case 'quick':
					$this->rule['quick']= true;
					break;
				case 'inet':
				case 'inet6':
					if (!isset($this->rule['family'])) {
						$this->rule['family']= $words[$i];
					} else {
						$this->rule['to-family']= $words[$i];
					}
					break;
				case 'in':
				case 'out':
					$this->rule['direction']= $words[$i];
					break;
				case 'log':
					if ($words[$i + 1] == '\(') {
						list($lo, $i)= $this->parseItem($words, $i, '\(', '\)');
						$this->rule['log']= array();
						for ($j= 0; $j < count($lo); $j++) {
							if ($lo[$j] == 'to') {
								$this->rule['log']['to']= $lo[++$j];
							} else {
								$this->rule['log'][$lo[$j]]= TRUE;
							}
						}
					} else {
						$this->rule['log']= TRUE;
					}
					break;
				case 'bitmask':
				case 'least-states':
				case 'round-robin':
				case 'random':
					$this->rule[$words[$i]]= true;
					break;
				case 'source-hash':
					$this->rule[$words[$i]]= true;
					// XXX: What is a possible pattern for key?
					if (preg_match('/\d+/', $words[$i + 1])) {
						$this->rule['source-hash-key']= $words[++$i];
					}
					break;
				case 'sticky-address':
					$this->rule['sticky-address']= true;
					break;
				case 'static-port':
					$this->rule['static-port']= true;
					break;
				case 'on':
					list($this->rule['interface'], $i)= $this->parseItem($words, $i);
					break;
				case 'proto':
					list($this->rule['proto'], $i)= $this->parseItem($words, $i);
					break;
				case 'from':
					if ($words[$i + 1] != "port") {
						list($this->rule['from'], $i)= $this->parseItem($words, $i);
					}
					if ($words[$i + 1] == "port") {
						list($this->rule['fromport'], $i)= $this->parsePortItem($words, ++$i);
					}
					break;
				case 'to':
					if ($words[$i + 1] != "port") {
						list($this->rule['to'], $i)= $this->parseItem($words, $i);
					}
					if ($words[$i + 1] == "port") {
						list($this->rule['port'], $i)= $this->parsePortItem($words, ++$i);
					}
					break;
				case 'flags':
					$i++;
					$this->rule['flags']= $words[$i];
					break;
				case 'af-to':
					$this->rule['type']= $words[$i];
					break;
				case 'nat-to':
				case 'binat-to':
				case 'divert-to':
					$this->rule['type']= $words[$i];
					/// @todo Fix these off-by-N errors
					if ($words[$i + 1] != 'port') {
						$this->rule['natdest']= $words[++$i];
					}
					// @attention Do not use else here
					if ($words[$i + 1] == 'port') {
						$i+= 2;
						$this->rule['natdestport']= $words[$i];
					}
			}
		}
	}

	function generate()
	{
		$str= "";
		if ($this->rule['action']) {
			$str.= $this->rule['action'];
		}
		if ($this->rule['direction']) {
			if ($this->rule['type'] != "binat-to") {
				$str.= " " . $this->rule['direction'];
			}
		}
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
			$str.= " quick";
		}
		if ($this->rule['interface']) {
			$str.= $this->generateItem($this->rule['interface'], "on");
		}
		if ($this->rule['family']) {
			if ($this->rule['type'] == "af-to") {
				$str.= " " . $this->rule['family'] . " af-to " . $this->rule['to-family'];
			} else {
				$str.= " " . $this->rule['family'];
			}
		}
		if ($this->rule['proto']) {
			if ($this->rule['type'] != "af-to") {
				$str.= $this->generateItem($this->rule['proto'], "proto");
			}
		}

		if ($this->rule['from'] || $this->rule['fromport']) {
			$str.= ' from';
			if ($this->rule['from']) {
				$str.= $this->generateItem($this->rule['from']);
			}

			if ($this->rule['fromport']) {
				$str.= $this->generateItem($this->rule['fromport'], 'port');
			}
		}

		if ($this->rule['to'] || $this->rule['port']) {
			$str.= ' to';
			if ($this->rule['to']) {
				$str.= $this->generateItem($this->rule['to']);
			}

			if ($this->rule['port']) {
				$str.= $this->generateItem($this->rule['port'], 'port');
			}
		}

		if ($this->rule['natdest']) {
			if ($this->rule['type'] != "af-to") {
				$str.= " " . $this->rule['type'] . " " . stripslashes($this->rule['natdest']);
				if ($this->rule['natdestport']) {
					$str.= " port " . $this->rule['natdestport'];
				}
			}
		}
		
		if ($this->rule['bitmask']) {
			$str.= " bitmask";
		}
		if ($this->rule['least-states']) {
			$str.= " least-states";
		}
		if ($this->rule['random']) {
			$str.= " random";
		}
		if ($this->rule['round-robin']) {
			$str.= " round-robin";
		}
		if ($this->rule['source-hash']) {
			$str.= " source-hash";
			if ($this->rule['source-hash-key']) {
				$str.= ' ' . $this->rule['source-hash-key'];
			}
		}
		if ($this->rule['sticky-address']) {
			$str.= " sticky-address";
		}
		if ($this->rule['static-port']) {
			if ($this->rule['type'] != "rdr-to") {
				$str.= " static-port";
			}
		}
		
		if ($this->rule['flags']) {
			$str.= " flags " . $this->rule['flags'];
		}

        if ($this->rule['comment']) {
			$str.= " # " . trim(stripslashes($this->rule['comment']));
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
			<td title="Action" class="<?php echo $this->rule['action']; ?>">
				<?php echo $this->rule['action']; ?>
			</td>
			<td title="Type">
				<?php echo $this->rule['type']; ?>
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
			<td title="Nat Destination">
				<?php $this->PrintFromTo($this->rule['natdest']); ?>
			</td>
			<td title="Nat Destination Port">
				<?php $this->PrintFromTo($this->rule['natdestport']); ?>
			</td>
			<td class="comment">
				<?php echo stripslashes($this->rule['comment']); ?>
			</td>
			<td class="edit">
				<?php
				$this->PrintEditLinks($rulenumber, "conf.php?sender=nat&rulenumber=$rulenumber", $count);
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
					unset($this->rule['natdest']);
					unset($this->rule['natdestport']);
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

			$this->rule['natdest']= filter_input(INPUT_POST, 'natdest');
			$this->rule['natdestport']= filter_input(INPUT_POST, 'natdestport');

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
		$href= "conf.php?sender=nat&rulenumber=$rulenumber";
		?>
		<h2>Edit NAT Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Nat') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" name="theform" action="<?php echo $href; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Type').':' ?>
					</td>
					<td>
						<select id="type" name="type" onchange="javascript: document.theform.submit()">
							<option label="af-to" <?php echo $this->rule['type'] == 'af-to' ? 'selected' : ''; ?>>af-to</option>
							<option label="nat-to" <?php echo $this->rule['type'] == 'nat-to' ? 'selected' : ''; ?>>nat-to</option>
							<option label="binat-to" <?php echo $this->rule['type'] == 'binat-to' ? 'selected' : ''; ?>>binat-to</option>
							<option label="divert-to" <?php echo $this->rule['type'] == 'divert-to' ? 'selected' : ''; ?>>divert-to</option>
							<option label="rdr-to" <?php echo $this->rule['type'] == 'rdr-to' ? 'selected' : ''; ?>>rdr-to</option>
						</select>
						<?php $this->PrintHelp($this->rule['type']) ?>
					</td>
				</tr>
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
						$this->PrintDeleteLinks($this->rule['interface'], $href, 'dropinterface');
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
								$this->PrintDeleteLinks($this->rule['proto'], $href, 'dropproto');
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
						$this->PrintDeleteLinks($this->rule['from'], $href, 'dropfrom');
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
							$this->PrintDeleteLinks($this->rule['fromport'], $href, 'dropfromport');
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
						$this->PrintDeleteLinks($this->rule['to'], $href, 'dropto');
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
						$this->PrintDeleteLinks($this->rule['port'], $href, 'dropport');
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
							<input type="text" id="natdest" name="natdest" size="20" value="<?php echo $this->rule['natdest']; ?>" />
							<?php $this->PrintHelp('divert-to') ?>
						</td>
					</tr>
					<tr class="evenline">
						<td class="title">
							<?php echo _TITLE('NAT Destination Port').':' ?>
						</td>
						<td>
							<input type="text" id="natdestport" name="natdestport" size="20" value="<?php echo $this->rule['natdestport']; ?>" />
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
