<?php
/* $pfre: Filter.php,v 1.9 2016/07/27 09:15:30 soner Exp $ */

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

class Filter extends Rule
{
	function parse($str)
	{
		$this->rule= array();
		
		if (strpos($str, "#")) {
			$this->rule['comment']= substr($str, strpos($str, "#") + '1');
			$str= substr($str, 0, strpos($str, "#"));
		}
		
		/*
		 * Sanitize the rule string so that we can deal with '{foo' as '{ foo' in
		 * the code further down without any special treatment
		 */
		$str= preg_replace("/! +/", "!", $str);
		$str= preg_replace("/{/", " { ", $str);
		$str= preg_replace("/}/", " } ", $str);
		$str= preg_replace("/\(/", " \( ", $str);
		$str= preg_replace("/\)/", " \) ", $str);
		$str= preg_replace("/,/", " , ", $str);
		$str= preg_replace("/\"/", " \" ", $str);
		
		$words= preg_split("/[\s,\t]+/", $str, '-1', PREG_SPLIT_NO_EMPTY);

		for ($i= '0'; $i < count($words); $i++) {
			switch ($words[$i]) {
				case "anchor":
					$this->rule['type']= $words[$i++];
					if ($words[$i] == "\"") {
						$this->rule['identifier']= $words[++$i];
						if ($words[$i + 1] == "\"") {
							$i++;
						}
					} else {
						$this->rule['identifier']= $words[$i];
					}
					break;
				case "antispoof":
				case "pass":
				case "block":
				case "match":
					$this->rule['type']= $words[$i];
					break;
				case "quick":
					$this->rule['quick']= TRUE;
					break;
				case "inet":
				case "inet6":
					$this->rule['family']= $words[$i];
					break;
				case "in":
					$this->rule['direction']= "in";
					break;
				case "out":
					$this->rule['direction']= "out";
					break;
				case "log":
				case "log-all":
					$this->rule['log']= $words[$i];
					break;
				case "all":
					$this->rule['all']= TRUE;
					break;
				case "allow-opts":
					$this->rule['allow-opts']= TRUE;
					break;
				case "drop":
					$this->rule['blockoption']= "drop";
					break;
				case "return":
					$this->rule['blockoption']= "return";
					break;
				case "return-rst":
					$this->rule['blockoption']= "return-rst";
					break;
				case "return-icmp":
					$this->rule['blockoption']= "return-icmp";
					break;
				case "return-icmp6":
					$this->rule['blockoption']= "return-icmp6";
					break;
				case "route-to":
					list($this->rule['route-to'], $i)= $this->parseItem($words, $i);
					break;
				case "reply-to":
					list($this->rule['reply-to'], $i)= $this->parseItem($words, $i);
					break;
				case "dup-to":
					list($this->rule['dup-to'], $i)= $this->parseItem($words, $i);
					break;
				case "divert-reply":
					$this->rule['divert-reply']= TRUE;
					break;
				case "icmp-type":
					list($this->rule['icmp-type'], $i)= $this->parseItem($words, $i);
					if ($words[$i + '1'] == "code") {
						list($this->rule['icmp-code'], $i)= $this->parseItem($words, ++$i);
					}
					break;
				case "icmp6-type":
					list($this->rule['icmp6-type'], $i)= $this->parseItem($words, $i);
					if ($words[$i + '1'] == "code") {
						list($this->rule['icmp6-code'], $i)= $this->parseItem($words, ++$i);
					}
					break;
				case "for":
				case "on":
					list($this->rule['interface'], $i)= $this->parseItem($words, $i);
					break;
				case "proto":
					list($this->rule['proto'], $i)= $this->parseItem($words, $i);
					break;
				case "any":
					if (!isset($this->rule['from'])) {
						$this->rule['from']= "any";
					} else {
						$this->rule['to']= "any";
					}
					break;
				case "from":
					if ($words[$i + 1] != "port") {
						list($this->rule['from'], $i)= $this->parseItem($words, $i);
					}
					if ($words[$i + 1] == "port") {
						list($this->rule['fromport'], $i)= $this->parsePortItem($words, ++$i);
					}
					break;
				case "to":
					if ($words[$i + 1] != "port") {
						list($this->rule['to'], $i)= $this->parseItem($words, $i);
					}
					if ($words[$i + 1] == "port") {
						list($this->rule['port'], $i)= $this->parsePortItem($words, ++$i);
					}
					break;
				case "flags":
					$i++;
					$this->rule['flags']= $words[$i];
					break;
				case "keep":
					$i++;
					$this->rule['state']= "keep";
					break;
				case "modulate":
					$i++;
					$this->rule['state']= "modulate";
					break;
				case "synproxy":
					$i++;
					$this->rule['state']= "synproxy";
					break;
				case "user":
					list($this->rule['user'], $i)= $this->parseItem($words, $i);
					break;
				case "group":
					list($this->rule['group'], $i)= $this->parseItem($words, $i);
					break;
				case "label":
					list($this->rule['label'], $i)= $this->parseString($words, $i);
					break;
				case "queue":
					list($this->rule['queue'], $i)= $this->parseItem($words, $i, "\(", "\)");
					break;
				case "tag":
					list($this->rule['tag'], $i)= $this->parseString($words, $i);
					break;
				case "tagged":
					list($this->rule['tagged'], $i)= $this->parseString($words, $i);
					break;
				case "os":
					$i++;
					unset($_data);
					if ($words[$i] != "{") {
						if ($words[$i] != "\"") {
							$_data.= $words[$i++];
						} else {
							while ($words[++$i] != "\"") {
								$_data.= " " . $words[$i];
							}
						}
						$this->rule['os']= trim($_data);
					} else {
						while (preg_replace("/[\s,]+/", "", $words[++$i]) != "}") {
							$_data= "";
							while ($words[++$i] != "\"") {
								$_data.= " " . $words[$i];
							}
							$this->rule['os'][]= trim($_data);
						}
					}
					break;
				case "probability":
					$this->rule['probability']= preg_replace("/\"/", "", $words[++$i]);
					break;
				default:
					$this->rule[]= $words[$i];
			}
		}
	}

	function generate()
	{
		$str= $this->rule['type'];

		if ($this->rule['type'] == "anchor") {
			$str.= " \"" . $this->rule['identifier'] . "\"";
		}
		if ($this->rule['blockoption']) {
			$str.= " " . $this->rule['blockoption'];
		}
		if ($this->rule['direction']) {
			$str.= " " . $this->rule['direction'];
		}
		if ($this->rule['log']) {
			$str.= " " . $this->rule['log'];
		}
		if ($this->rule['quick']) {
			$str.= " quick";
		}
		if ($this->rule['interface']) {
			if ($this->rule['type'] == "antispoof") {
				$str.= $this->generateItem($this->rule['interface'], "for");
			} else {
				$str.= $this->generateItem($this->rule['interface'], "on");
			}
		}
		if ($this->rule['route-to']) {
			$str.= $this->generateItem($this->rule['route-to'], "route-to");
		}
		if ($this->rule['reply-to']) {
			$str.= $this->generateItem($this->rule['reply-to'], "reply-to");
		}
		if ($this->rule['dup-to']) {
			$str.= $this->generateItem($this->rule['dup-to'], "dup-to");
		}
		if ($this->rule['family']) {
			$str.= " " . $this->rule['family'];
		}
		if ($this->rule['proto']) {
			$str.= $this->generateItem($this->rule['proto'], "proto");
		}
		if ($this->rule['all']) {
			$str.= " all";
		} else {
			if ($this->rule['from'] || $this->rule['fromport']) {
				$str.= ' from';
				if ($this->rule['from']) {
					$str.= $this->generateItem($this->rule['from']);
				}

				if ($this->rule['fromport']) {
					$str.= $this->generateItem($this->rule['fromport'], 'port');
				}
			}

			if ($this->rule['os']) {
				if (!is_array($this->rule['os'])) {
					$str.= ' os "' . $this->rule['os'] . '"';
				} else {
					$str.= ' os { "' . implode('" "', $this->rule['os']) . '" }';
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
		}
		if (($this->rule['family'] == "inet") &&
			((isset($this->rule['proto']) && $this->rule['proto'] == "icmp") ||
			 (is_array($this->rule['proto']) && in_array("icmp", $this->rule['proto'])))) {
			if ($this->rule['icmp-type']) {
				$str.= $this->generateItem($this->rule['icmp-type'], "icmp-type");
				if (isset($this->rule['icmp-code'])) {
					$str.= $this->generateItem($this->rule['icmp-code'], "code");
				}
			}
		}
		if (($this->rule['family'] == "inet6") &&
			((isset($this->rule['proto']) && $this->rule['proto'] == "icmp6") ||
			 (is_array($this->rule['proto']) && in_array("icmp6", $this->rule['proto'])))) {
			if ($this->rule['icmp6-type']) {
				$str.= $this->generateItem($this->rule['icmp6-type'], "icmp6-type");
				if (isset($this->rule['icmp6-code'])) {
					$str.= $this->generateItem($this->rule['icmp6-code'], "code");
				}
			}
		}
		if ($this->rule['allow-opts']) {
			$str.= " allow-opts";
		}
		if ($this->rule['flags']) {
			$str.= " flags " . $this->rule['flags'];
		}
		if ($this->rule['state']) {
			$str.= " " . $this->rule['state'] . " state";
		}
		
		if ($this->rule['divert-reply']) {
			$str.= " divert-reply";
		}

        if ($this->rule['user']) {
			$str.= $this->generateItem($this->rule['user'], "user");
		}
		
		if ($this->rule['group']) {
			$str.= $this->generateItem($this->rule['group'], "group");
		}
		
		if ($this->rule['label']) {
			$str.= " label \"" . $this->rule['label'] . "\"";
		}
		
		if ($this->rule['tag']) {
			$str.= " tag \"" . $this->rule['tag'] . "\"";
		}
		
		if ($this->rule['tagged']) {
			$str.= " tagged \"" . $this->rule['tagged'] . "\"";
		}
		
		if ($this->rule['queue']) {
			if (!is_array($this->rule['queue'])) {
				$str.= " set queue " . $this->rule['queue'];
			} else {
				$str.= " set queue (" . $this->rule['queue']['0'] . ", " . $this->rule['queue']['1'] . ")";
			}
		}
		
		if ($this->rule['probability']) {
			$str.= " probability " . $this->rule['probability'];
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
			<td title="Action" class="<?php echo $this->rule['type']; ?>" nowrap="nowrap">
				<?php echo $this->rule['type']; ?>
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
				<?php echo isset($this->rule['queue']) ? (!is_array($this->rule['queue']) ? $this->rule['queue'] : $this->rule['queue']['0'] . '<br>' . $this->rule['queue']['1']) : ''; ?>
			</td>
			<td class="comment">
				<?php echo stripslashes($this->rule['comment']); ?>
			</td>
			<td class="edit">
				<?php
				$this->PrintEditLinks($rulenumber, "conf.php?sender=filter&rulenumber=$rulenumber", $count);
				?>
			</td>
		</tr>
		<?php
	}
	
	function processInput()
	{
		if (isset($_GET['dropfrom'])) {
			$this->delEntity("from", $_GET['dropfrom']);
		}

		if (isset($_GET['dropfromport'])) {
			$this->delEntity("fromport", $_GET['dropfromport']);
		}

		if (isset($_GET['dropto'])) {
			$this->delEntity("to", $_GET['dropto']);
		}

		if (isset($_GET['dropport'])) {
			$this->delEntity("port", $_GET['dropport']);
		}

		if (isset($_GET['dropinterface'])) {
			$this->delEntity("interface", $_GET['dropinterface']);
		}

		if (isset($_GET['dropproto'])) {
			$this->delEntity("proto", $_GET['dropproto']);
		}

		if (isset($_GET['dropuser'])) {
			$this->delEntity("user", $_GET['dropuser']);
		}

		if (isset($_GET['dropgroup'])) {
			$this->delEntity("group", $_GET['dropgroup']);
		}

		if (isset($_GET['dropicmptype'])) {
			$this->delEntity("icmp-type", $_GET['dropicmptype']);
		}

		if (isset($_GET['dropicmp6type'])) {
			$this->delEntity("icmp6-type", $_GET['dropicmp6type']);
		}

		if (isset($_GET['dropos'])) {
			$this->delEntity("os", $_GET['dropos']);
		}

		if (isset($_GET['droprouteto'])) {
			$this->delEntity("route-to", $_GET['droprouteto']);
		}

		if (isset($_GET['dropreplyto'])) {
			$this->delEntity("reply-to", $_GET['dropreplyto']);
		}

		if (isset($_GET['dropdupto'])) {
			$this->delEntity("dup-to", $_GET['dropdupto']);
		}

		if (count($_POST)) {
			if (filter_input(INPUT_POST, 'addfrom') != '') {
				$this->addEntity("from", $_POST['addfrom']);
			}

			if (filter_input(INPUT_POST, 'addfromport') != '') {
				$this->addEntity("fromport", $_POST['addfromport']);
			}

			if (filter_input(INPUT_POST, 'addto') != '') {
				$this->addEntity("to", $_POST['addto']);
			}

			if (filter_input(INPUT_POST, 'addport') != '') {
				$this->addEntity("port", $_POST['addport']);
			}

			if (filter_input(INPUT_POST, 'addinterface') != '') {
				$this->addEntity("interface", $_POST['addinterface']);
			}

			if (filter_input(INPUT_POST, 'addproto') != '') {
				$this->addEntity("proto", $_POST['addproto']);
			}

			if (filter_input(INPUT_POST, 'adduser') != '') {
				$this->addEntity("user", $_POST['adduser']);
			}

			if (filter_input(INPUT_POST, 'addgroup') != '') {
				$this->addEntity("group", $_POST['addgroup']);
			}

			if (filter_input(INPUT_POST, 'addicmptype') != '') {
				$this->addEntity("icmp-type", $_POST['addicmptype']);
			}

			if (filter_input(INPUT_POST, 'addicmp6type') != '') {
				$this->addEntity("icmp6-type", $_POST['addicmp6type']);
			}

			if (filter_input(INPUT_POST, 'addos') != '') {
				$this->addEntity("os", preg_replace('/"/', '', $_POST['addos']));
			}

			if (filter_input(INPUT_POST, 'addrouteto') != '') {
				$this->addEntity("route-to", preg_replace('/"/', '', $_POST['addrouteto']));
			}

			if (filter_input(INPUT_POST, 'addreplyto') != '') {
				$this->addEntity("reply-to", preg_replace('/"/', '', $_POST['addreplyto']));
			}

			if (filter_input(INPUT_POST, 'adddupto') != '') {
				$this->addEntity("dup-to", preg_replace('/"/', '', $_POST['adddupto']));
			}

			$this->rule['type']= $_POST['type'];
			$this->rule['direction']= $_POST['direction'];
			$this->rule['log']= $_POST['log'];
			$this->rule['quick']= ($_POST['quick'] ? TRUE : '');
			$this->rule['comment']= $_POST['comment'];
			$this->rule['label']= preg_replace('/"/', '', $_POST['label']);
			$this->rule['tag']= preg_replace('/"/', '', $_POST['tag']);
			$this->rule['tagged']= preg_replace('/"/', '', $_POST['tagged']);

			if (isset($_POST['icmp-code'])) {
				$this->rule['icmp-code']= $_POST['icmp-code'];
			}

			if (isset($_POST['icmp6-code'])) {
				$this->rule['icmp6-code']= $_POST['icmp6-code'];
			}

			$this->rule['family']= $_POST['family'];
			$this->rule['divert-reply']= ($_POST['divert-reply'] ? TRUE : '');
			$this->rule['allow-opts']= ($_POST['allow-opts'] ? TRUE : '');

			if (isset($_POST['stateful'])) {
				$this->rule['state']= $_POST['stateful'];
			}

			if ($_POST['type'] == "block") {
				$this->rule['blockoption']= $_POST['blockoption'];
			} else {
				unset($this->rule['blockoption']);
			}

			$this->rule['flags']= filter_input(INPUT_POST, 'flags');

			$this->rule['probability']= filter_input(INPUT_POST, 'probability');

			if (($_POST['queue-pri'] != '') && ($_POST['queue-sec'] != '')) {
				$this->rule['queue']= array();
				$this->rule['queue']['0']= $_POST['queue-pri'];
				$this->rule['queue']['1']= $_POST['queue-sec'];
			} elseif ($_POST['queue-pri'] != '') {
				$this->rule['queue']= $_POST['queue-pri'];
			} else {
				unset($this->rule['queue']);
			}

			if (isset($_POST['all'])) {
				$this->rule['all']= TRUE;
				unset($this->rule['from']);
				unset($this->rule['fromport']);
				unset($this->rule['to']);
				unset($this->rule['port']);
			} else {
				unset($this->rule['all']);
			}
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		// XXX: Fix this
		global $View;
		
		$queueNames= $View->RuleSet->getQueueNames();
		
		$href= "conf.php?sender=filter&rulenumber=$rulenumber";
		?>
		<h2>Edit Filter Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form method="post" id="theform" name="theform" action="<?php echo $href; ?>">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Type').':' ?>
					</td>
					<td>
						<select id="type" name="type" onchange="document.theform.submit()">
							<option value="pass" label="pass" <?php echo ($this->rule['type'] == 'pass' ? 'selected' : ''); ?>>pass</option>
							<option value="block" label="block" <?php echo ($this->rule['type'] == 'block' ? 'selected' : ''); ?>>block</option>
							<option value="match" label="match" <?php echo ($this->rule['type'] == 'match' ? 'selected' : ''); ?>>match</option>
							<option value="antispoof" label="antispoof" <?php echo ($this->rule['type'] == 'antispoof' ? 'selected' : ''); ?>>antispoof</option>
						</select>
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
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Interface').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['interface'], $href, 'dropinterface');
						$this->PrintAddControls('addinterface', NULL, 'if or macro', NULL, 10, NULL, isset($this->rule['interface']));
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
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Protocol').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['proto'], $href, 'dropproto');
						$this->PrintAddControls('addproto', NULL, 'protocol', NULL, 10, NULL, isset($this->rule['proto']));
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Logging').':' ?>
					</td>
					<td>
						<select id="log" name="log">
							<option value="" label=""></option>
							<option value="log" label="log" <?php echo ($this->rule['log'] == 'log' ? 'selected' : ''); ?>>log</option>
							<option value="log-all" label="log-all" <?php echo ($this->rule['log'] == 'log-all' ? 'selected' : ''); ?>>log-all</option>
						</select>			
					</td>
				</tr>
				<tr class="oddline">
					<?php
					if ($this->rule['type'] == "block") {
						?>
						<td class="title">
							<?php echo _TITLE('Block Option').':' ?>
						</td>
						<td>
							<select id="blockoption" name="blockoption">
								<option value=""></option>
								<option value="drop" <?php echo ($this->rule['blockoption'] == 'drop' ? 'selected' : ''); ?>>drop</option>
								<option value="return" <?php echo ($this->rule['blockoption'] == 'return' ? 'selected' : ''); ?>>return</option>
								<option value="return-rst" <?php echo ($this->rule['blockoption'] == 'return-rst' ? 'selected' : ''); ?>>return-rst</option>
								<option value="return-icmp" <?php echo ($this->rule['blockoption'] == 'return-icmp' ? 'selected' : ''); ?>>return-icmp</option>
								<option value="return-icmp6" <?php echo ($this->rule['blockoption'] == 'return-icmp6' ? 'selected' : ''); ?>>return-icmp6</option>
							</select>
						</td>
						<?php
					} else {
						?>
						<td class="title">
							<?php echo _TITLE('State').':' ?>
						</td>
						<td>
							<select id="stateful" name="stateful">
								<option value=""></option>
								<option value="keep" <?php echo ($this->rule['state'] == 'keep' ? 'selected' : ''); ?>>Keep State</option>
								<option value="modulate" <?php echo ($this->rule['state'] == 'modulate' ? 'selected' : ''); ?>>Modulate State</option>
								<option value="synproxy" <?php echo ($this->rule['state'] == 'synproxy' ? 'selected' : ''); ?>>Synproxy</option>
							</select>
						</td>
						<?php
					}
					?>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Quick').':' ?>
					</td>
					<td>
						<input type="checkbox" id="quick" name="quick" value="quick" <?php echo ($this->rule['quick'] ? 'checked' : ''); ?> />
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('TCP Flags').':' ?>
					</td>
					<td>
						<?php
						$this->PrintAddControls('flags', NULL, 'flags or macro', $this->rule['flags'], 12);
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Match All').':' ?>
					</td>
					<td>
						<input type="checkbox" id="all" name="all" value="all" <?php echo ($this->rule['all'] ? 'checked' : ''); ?> onclick="document.theform.submit()" />
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Source').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['from'], $href, 'dropfrom');
						$this->PrintAddControls('addfrom', NULL, 'ip, host or macro', NULL, NULL, $this->rule['all'], isset($this->rule['from']));
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Source Port').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['fromport'], $href, 'dropfromport');
						$this->PrintAddControls('addfromport', NULL, 'number, name, table or macro', NULL, NULL, $this->rule['all'], isset($this->rule['fromport']));
						?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Destination').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['to'], $href, 'dropto');
						$this->PrintAddControls('addto', NULL, 'ip, host, table or macro', NULL, NULL, $this->rule['all'], isset($this->rule['to']));
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
						$this->PrintAddControls('addport', NULL, 'number, name or macro', NULL, NULL, $this->rule['all'], isset($this->rule['port']));
						?>
					</td>
				</tr>
				<?php
				if (isset($this->rule['proto']) && ($this->rule['proto'] == "icmp" || is_array($this->rule['proto']) && in_array("icmp", $this->rule['proto']))) {
					?>
					<tr class="oddline">
						<td class="title">
							<?php echo _TITLE('ICMP Type').':' ?>
						</td>
						<td>
							<?php
							$this->PrintDeleteLinks($this->rule['icmp-type'], $href, 'dropicmptype');
							$this->PrintAddControls('addicmptype', NULL, 'number, name or macro', NULL, NULL, NULL, isset($this->rule['icmp-type']));
							?>
						</td>
					</tr>
					<tr class="evenline">
						<td class="title">
							<?php echo _TITLE('ICMP Code').':' ?>
						</td>
						<td>
							<input type="text" name="icmp-code" id="icmp-code" value="<?php echo $this->rule['icmp-code']; ?>" <?php echo (isset($this->rule['icmp-type']) && !is_array($this->rule['icmp-type']) ? "" : "disabled=\"disabled\"")?> />
						</td>
					</tr>
					<?php
				}
				if (isset($this->rule['proto']) && ($this->rule['proto'] == "icmp6" || is_array($this->rule['proto']) && in_array("icmp6", $this->rule['proto']))) {
					?>
					<tr class="oddline">
						<td class="title">
							<?php echo _TITLE('ICMP6 Type').':' ?>
						</td>
						<td>
							<?php
							$this->PrintDeleteLinks($this->rule['icmp6-type'], $href, 'dropicmp6type');
							$this->PrintAddControls('addicmp6type', NULL, 'number, name or macro', NULL, NULL, NULL, isset($this->rule['icmp6-type']));
							?>
						</td>
					</tr>
					<tr class="evenline">
						<td class="title">
							<?php echo _TITLE('ICMP6 Code').':' ?>
						</td>
						<td>
							<input type="text" name="icmp6-code" id="icmp6-code" value="<?php echo $this->rule['icmp6-code']; ?>" <?php echo (isset($this->rule['icmp6-type']) && !is_array($this->rule['icmp6-type']) ? "" : "disabled=\"disabled\"")?> />
						</td>
					</tr>
					<?php
				}
				?>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Label').':' ?>
					</td>
					<td>
						<input type="text" id="label" name="label" value="<?php echo $this->rule['label']; ?>" />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Tag').':' ?>
					</td>
					<td>
						<input type="text" id="tag" name="tag" value="<?php echo $this->rule['tag']; ?>" />
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Match Tagged').':' ?>
					</td>
					<td>
						<input type="text" id="tagged" name="tagged" value="<?php echo $this->rule['tagged']; ?>" />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('OS').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['os'], $href, 'dropos');
						$this->PrintAddControls('addos', NULL, 'os name or macro', NULL, NULL, NULL, isset($this->rule['os']));
						?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Primary Queue').':' ?>
					</td>
					<td>
						<select id="queue-pri" name="queue-pri">
						<?php
						if (count($queueNames) == 0) {
							?>
							<option value="" disabled>No Queues defined</option>
							<?php
						} else {
							?>
							<option value="">none</option>
							<?php
							if (!is_array($this->rule['queue'])) {
								$queuepri= $this->rule['queue'];
							} else {
								$queuepri= $this->rule['queue']['0'];
							}
							foreach ($queueNames as $queue) {
								?>
								<option value="<?php echo $queue; ?>" <?php echo $queuepri == $queue ? 'selected' : ''; ?>><?php echo $queue; ?></option>
								<?php
							}
						}
						?>
						</select>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Secondary Queue').':' ?>
					</td>
					<td>
						<select id="queue-sec" name="queue-sec">
						<?php
						if (count($queueNames) == 0) {
							?>
							<option value="" disabled>No Queues defined</option>
							<?php
						} else {
							?>
							<option value="">none</option>
							<?php
							if (isset($this->rule['queue'])) {
								foreach ($queueNames as $queue) {
									?>
									<option value="<?php echo $queue; ?>" <?php echo $this->rule['queue']['1'] == $queue ? 'selected' : ''; ?>><?php echo $queue; ?></option>
									<?php
								}
							}
						}
						?>
						</select>	
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Route to').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['route-to'], $href, 'droprouteto');
						$this->PrintAddControls('addrouteto', NULL, 'ip, host, table or macro', NULL, NULL, NULL, isset($this->rule['route-to']));
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Reply to').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['reply-to'], $href, 'dropreplyto');
						$this->PrintAddControls('addreplyto', NULL, 'ip, host, table or macro', NULL, NULL, NULL, isset($this->rule['reply-to']));
						?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Dup to').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['dup-to'], $href, 'dropdupto');
						$this->PrintAddControls('adddupto', NULL, 'ip, host, table or macro', NULL, NULL, NULL, isset($this->rule['dup-to']));
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Divert Reply').':' ?>
					</td>
					<td>
						<input type="checkbox" id="divert-reply" name="divert-reply" value="divert-reply" <?php echo ($this->rule['divert-reply'] ? 'checked' : ''); ?> />
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Allow Opts').':' ?>
					</td>
					<td>
						<input type="checkbox" id="allow-opts" name="allow-opts" value="allow-opts" <?php echo ($this->rule['allow-opts'] ? 'checked' : ''); ?> />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Probability').':' ?>
					</td>
					<td>
						<?php
						$this->PrintAddControls('probability', NULL, 'probability in percent', $this->rule['probability'], 20);
						?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('User').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['user'], $href, 'dropuser');
						$this->PrintAddControls('adduser', NULL, 'username or userid', NULL, NULL, NULL, isset($this->rule['user']));
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Group').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['group'], $href, 'dropgroup');
						$this->PrintAddControls('addgroup', NULL, 'groupname or groupid', NULL, NULL, NULL, isset($this->rule['group']));
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
