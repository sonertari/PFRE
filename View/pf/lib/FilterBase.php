<?php
/* $pfre: FilterBase.php,v 1.2 2016/07/31 14:19:13 soner Exp $ */

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

class FilterBase extends Rule
{
	function __construct($str)
	{
		$this->keywords= array_merge(
			$this->keywords,
			array(
				'pass' => array(
					'method' => 'parseNVP',
					'params' => array('action'),
					),
				'block' => array(
					'method' => 'parseNVP',
					'params' => array('action'),
					),
				'match' => array(
					'method' => 'parseNVP',
					'params' => array('action'),
					),
				'in' => array(
					'method' => 'parseNVP',
					'params' => array('direction'),
					),
				'out' => array(
					'method' => 'parseNVP',
					'params' => array('direction'),
					),
				'on' => array(
					'method' => 'parseItems',
					'params' => array('interface'),
					),
				'inet' => array(
					'method' => 'parseNVP',
					'params' => array('af'),
					),
				'inet6' => array(
					'method' => 'parseNVP',
					'params' => array('af'),
					),
				'proto' => array(
					'method' => 'parseItems',
					'params' => array('proto'),
					),
				'any' => array(
					'method' => 'parseAny',
					'params' => array(),
					),
				'all' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				'from' => array(
					'method' => 'parseSrcDest',
					'params' => array('fromport'),
					),
				'to' => array(
					'method' => 'parseSrcDest',
					'params' => array('port'),
					),
				'user' => array(
					'method' => 'parseItems',
					'params' => array('user'),
					),
				'group' => array(
					'method' => 'parseItems',
					'params' => array('group'),
					),
				'flags' => array(
					'method' => 'parseNextValue',
					'params' => array(),
					),
				'icmp-type' => array(
					'method' => 'parseICMPType',
					'params' => array('icmp-code'),
					),
				'icmp6-type' => array(
					'method' => 'parseICMPType',
					'params' => array('icmp6-code'),
					),
				'tos' => array(
					'method' => 'parseNextValue',
					'params' => array(),
					),
				// @todo Support "(" state-opts ")" 
				'no' => array(
					'method' => 'parseNVPInc',
					'params' => array('tcp-state'),
					),
				'keep' => array(
					'method' => 'parseNVPInc',
					'params' => array('tcp-state'),
					),
				'modulate' => array(
					'method' => 'parseNVPInc',
					'params' => array('tcp-state'),
					),
				'synproxy' => array(
					'method' => 'parseNVPInc',
					'params' => array('tcp-state'),
					),
				'fragment' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				'allow-opts' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				'once' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				'divert-reply' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				'label' => array(
					'method' => 'parseDelimitedStr',
					'params' => array('label'),
					),
				'tag' => array(
					'method' => 'parseDelimitedStr',
					'params' => array('tag'),
					),
				'tagged' => array(
					'method' => 'parseDelimitedStr',
					'params' => array('tagged'),
					),
				// @todo Support !tagged
//				'!tagged' => array(
//					'method' => 'parseDelimitedStr',
//					'params' => array('!tagged'),
//					),
				// "set prio" and "set tos"
				'set' => array(
					'method' => 'parseSet',
					'params' => array(),
					),
				'queue' => array(
					'method' => 'parseItems',
					'params' => array('queue', '\(', '\)'),
					),
				'rtable' => array(
					'method' => 'parseNextValue',
					'params' => array(),
					),
				'probability' => array(
					'method' => 'parseNextValue',
					'params' => array(),
					),
				'prio' => array(
					'method' => 'parseNextValue',
					'params' => array(),
					),
				// @todo Support [ [ "!" ] "received-on" ( interface-name | interface-group ) ]
				'received-on' => array(
					'method' => 'parseItems',
					'params' => array('received-on', '\(', '\)'),
					),
				'drop' => array(
					'method' => 'parseNVP',
					'params' => array('blockoption'),
					),
				'return' => array(
					'method' => 'parseNVP',
					'params' => array('blockoption'),
					),
				'return-rst' => array(
					'method' => 'parseNVP',
					'params' => array('blockoption'),
					),
				'return-icmp' => array(
					'method' => 'parseNVP',
					'params' => array('blockoption'),
					),
				'return-icmp6' => array(
					'method' => 'parseNVP',
					'params' => array('blockoption'),
					),
				'for' => array(
					'method' => 'parseItems',
					'params' => array('interface'),
					),
				'os' => array(
					'method' => 'parseOS',
					'params' => array(),
					),
				)
			);

		parent::__construct($str);
	}

	function parseSet()
	{
		if ($this->words[$this->index + 1] === 'prio') {
			$this->index++;
			$this->parseItems('set-prio', '\(', '\)');
		} elseif ($this->words[$this->index + 1] === 'tos') {
			$this->index++;
			$this->parseNextNVP('set-tos');
		}
	}

	function genSrcDest()
	{
		if (isset($this->rule['all'])) {
			$this->str.= ' all';
		} else {
			if (isset($this->rule['from']) || isset($this->rule['fromport'])) {
				$this->str.= ' from';
				$this->genItems('from');
				$this->genItems('fromport', 'port');
			}

			/// @todo Create a function for this
			if (isset($this->rule['os'])) {
				if (!is_array($this->rule['os'])) {
					$this->str.= ' os "' . $this->rule['os'] . '"';
				} else {
					$this->str.= ' os { "' . implode('" "', $this->rule['os']) . '" }';
				}
			}
			
			if (isset($this->rule['to']) || isset($this->rule['port'])) {
				$this->str.= ' to';
				$this->genItems('to');
				$this->genItems('port', 'port');
			}
		}
	}

	function genIcmpType()
	{
		if (($this->rule['af'] === 'inet') &&
			((isset($this->rule['proto']) && $this->rule['proto'] === 'icmp') ||
			 (is_array($this->rule['proto']) && in_array('icmp', $this->rule['proto'])))) {
			if (isset($this->rule['icmp-type'])) {
				$this->str.= $this->generateItem($this->rule['icmp-type'], 'icmp-type');
				if (isset($this->rule['icmp-code'])) {
					$this->str.= $this->generateItem($this->rule['icmp-code'], 'code');
				}
			}
		}
	}

	function genIcmp6Type()
	{
		if (($this->rule['af'] === 'inet6') &&
			((isset($this->rule['proto']) && $this->rule['proto'] === 'icmp6') ||
			 (is_array($this->rule['proto']) && in_array('icmp6', $this->rule['proto'])))) {
			if (isset($this->rule['icmp6-type'])) {
				$this->str.= $this->generateItem($this->rule['icmp6-type'], 'icmp6-type');
				if (isset($this->rule['icmp6-code'])) {
					$this->str.= $this->generateItem($this->rule['icmp6-code'], 'code');
				}
			}
		}
	}

	function genQueue()
	{
		if (isset($this->rule['queue'])) {
			if (!is_array($this->rule['queue'])) {
				$this->str.= ' set queue ' . $this->rule['queue'];
			} else {
				$this->str.= ' set queue (' . $this->rule['queue'][0] . ', ' . $this->rule['queue'][1] . ')';
			}
		}
	}

	function genFilterHead()
	{
		$this->genValue('direction');
		$this->genLog();
		$this->genKey('quick');
		/// @todo Support rdomain
		$this->genItems('interface', 'on');
		$this->genValue('af');
		$this->genItems('proto', 'proto');
		$this->genSrcDest();
	}
	
	function genFilterOpts()
	{
		$this->genItems('user', 'user');
		$this->genItems('group', 'group');
		$this->genValue('flags', 'flags ');
		$this->genIcmpType();
		$this->genIcmp6Type();
		$this->genValue('tos', 'tos ');
		$this->genValue('tcp-state', NULL, ' state');
		$this->genKey('fragment');
		$this->genKey('allow-opts');
		$this->genKey('once');
		$this->genKey('divert-reply');
		$this->genValue('label', 'label "', '"');
		$this->genValue('tag', 'tag "', '"');
		$this->genValue('tagged', 'tagged "', '"');
		/// @todo !tagged
		//$this->genValue('!tagged', '!tagged "', '"');
		$this->genItems('set-prio', 'set prio', '(', ')');
		$this->genQueue();
		$this->genValue('rtable', 'rtable ');
		$this->genValue('probability', 'probability ');
		$this->genValue('prio', 'prio ');
		$this->genValue('set-tos', 'set tos ');
		$this->genValue('received-on', 'received-on ');
	}
	
	function display($rulenumber, $count)
	{
		$this->dispHead($rulenumber);
		$this->dispAction();
		$this->dispValue('direction', 'Direction');
		$this->dispValue('interface', 'Interface');
		$this->dispLog();
		$this->dispKey('quick', 'Quick');
		$this->dispValue('proto', 'Proto');
		$this->dispSrcDest();
		$this->dispValue('tcp-state', 'State');
		$this->dispQueue();
		$this->dispTail($rulenumber, $count);
	}
	
	function displayNat($rulenumber, $count)
	{
		$this->dispHead($rulenumber);
		$this->dispAction();
		$this->dispValue('direction', 'Direction');
		$this->dispValue('interface', 'Interface');
		$this->dispLog();
		$this->dispKey('quick', 'Quick');
		$this->dispValue('proto', 'Proto');
		$this->dispSrcDest();
		$this->dispValue('redirhost', 'Redirect Host');
		$this->dispValue('redirport', 'Redirect Port');
		$this->dispTail($rulenumber, $count);
	}
	
	function dispAction()
	{
		?>
		<td title="Action" class="<?php echo $this->rule['action']; ?>" nowrap="nowrap">
			<?php echo $this->rule['action']; ?>
		</td>
		<?php
	}

	function dispSrcDest()
	{
		if ($this->rule['all']) {
			?>
			<td title="Source->Destination" colspan="4" class="all">
				all
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
	}

	function dispQueue()
	{
		?>
		<td title="Queue">
			<?php echo isset($this->rule['queue']) ? (!is_array($this->rule['queue']) ? $this->rule['queue'] : $this->rule['queue'][0] . '<br>' . $this->rule['queue'][1]) : ''; ?>
		</td>
		<?php
	}

	function input()
	{
		$this->inputFilterHead();
		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function inputFilterHead()
	{
		$this->inputKey('direction');

		$this->inputDel('interface', 'dropinterface');
		$this->inputAdd('interface', 'addinterface');

		$this->inputKey('af');

		$this->inputDel('proto', 'dropproto');
		$this->inputAdd('proto', 'addproto');

		$this->inputDel('from', 'dropfrom');
		$this->inputAdd('from', 'addfrom');

		$this->inputDel('fromport', 'dropfromport');
		$this->inputAdd('fromport', 'addfromport');

		$this->inputDel('to', 'dropto');
		$this->inputAdd('to', 'addto');

		$this->inputDel('port', 'dropport');
		$this->inputAdd('port', 'addport');

		/// @attention process all after src and dest
		$this->inputAll();

		$this->inputDel('os', 'dropos');
		$this->inputAdd('os', 'addos');
	}

	function inputFilterOpts()
	{
		$this->inputKey('tcp-state');
		$this->inputKey('flags');
		$this->inputQueue();

		$this->inputDel('icmp-type', 'dropicmptype');
		$this->inputAdd('icmp-type', 'addicmptype');
		$this->inputKeyIfHasVar('icmp-code', 'icmp-type');

		$this->inputDel('icmp6-type', 'dropicmp6type');
		$this->inputAdd('icmp6-type', 'addicmp6type');
		$this->inputKeyIfHasVar('icmp6-code', 'icmp6-type');
		
		$this->inputBool('fragment');
		$this->inputBool('allow-opts');
		$this->inputBool('once');
		$this->inputBool('divert-reply');
		
		$this->inputDel('user', 'dropuser');
		$this->inputAdd('user', 'adduser');

		$this->inputDel('group', 'dropgroup');
		$this->inputAdd('group', 'addgroup');

		$this->inputKey('label');
		$this->inputKey('tag');
		/// @todo !tagged?
		$this->inputKey('tagged');

		$this->inputKey('tos');
		$this->inputKey('set-tos');
		$this->inputKey('prio');

		$this->inputDel('set-prio', 'dropprio');
		$this->inputAdd('set-prio', 'addprio');

		$this->inputKey('probability');

		$this->inputKey('rtable');
		$this->inputKey('received-on');
	}

	function inputQueue()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if ((filter_input(INPUT_POST, 'queue-pri') != '') && (filter_input(INPUT_POST, 'queue-sec') != '')) {
				$this->rule['queue']= array();
				$this->rule['queue'][0]= filter_input(INPUT_POST, 'queue-pri');
				$this->rule['queue'][1]= filter_input(INPUT_POST, 'queue-sec');
			} elseif (filter_input(INPUT_POST, 'queue-pri') != '') {
				$this->rule['queue']= filter_input(INPUT_POST, 'queue-pri');
			} else {
				unset($this->rule['queue']);
			}
		}
	}

	function inputAll()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if (filter_has_var(INPUT_POST, 'all')) {
				$this->rule['all']= TRUE;
				unset($this->rule['from']);
				unset($this->rule['fromport']);
				unset($this->rule['to']);
				unset($this->rule['port']);
			} else {
				unset($this->rule['all']);
			}
		}
	}

	function edit($rulenumber, $modified, $testResult, $action)
	{
		$this->index= 0;
		$this->rulenumber= $rulenumber;

		$this->editHead($modified);

		$this->editFilterHead();
		$this->editFilterOpts();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
	}

	function editFilterHead()
	{
		$this->editDirection();
		$this->editValues('interface', 'Interface', 'dropinterface', 'addinterface', 'if or macro', NULL, 10);
		$this->editAf();
		$this->editValues('proto', 'Protocol', 'dropproto', 'addproto', 'protocol', NULL, 10);
		$this->editCheckbox('all', 'Match All');
		$this->editValues('from', 'Source', 'dropfrom', 'addfrom', 'ip, host, table or macro', 'src-dst', NULL, isset($this->rule['all']));
		$this->editValues('fromport', 'Source Port', 'dropfromport', 'addfromport', 'number, name, table or macro', FALSE, NULL, isset($this->rule['all']));
		$this->editValues('to', 'Destination', 'dropto', 'addto', 'ip, host, table or macro', FALSE, NULL, isset($this->rule['all']));
		$this->editValues('port', 'Destination Port', 'dropport', 'addport', 'number, name, table or macro', FALSE, NULL, isset($this->rule['all']));
		$this->editValues('os', 'OS', 'dropos', 'addos', 'os name or macro');
	}

	function editFilterOpts()
	{
		$this->editState();
		$this->editText('flags', 'TCP Flags', NULL, 20, 'defaults to S/SA');
		$this->editQueue();
		$this->editIcmpType();
		$this->editIcmp6Type();
		
		$this->editCheckbox('fragment', 'Fragment');
		$this->editCheckbox('allow-opts', 'Allow Opts');
		$this->editCheckbox('once', 'Once');
		$this->editCheckbox('divert-reply', 'Divert Reply');
		
		$this->editValues('user', 'User', 'dropuser', 'adduser', 'username or userid');
		$this->editValues('group', 'Group', 'dropgroup', 'addgroup', 'groupname or groupid');
		$this->editText('label', 'Label', NULL, NULL, 'string');
		$this->editText('tagged', 'Match Tagged', NULL, NULL, 'string');
		/// @todo !tagged?
		$this->editText('tag', 'Assign Tag', NULL, NULL, 'string');
		$this->editText('tos', 'Match TOS', NULL, NULL, 'string or number');
		$this->editText('set-tos', 'Enforce TOS', NULL, NULL, 'string or number');
		$this->editText('prio', 'Match Priority', NULL, 10, 'number 0-7');
		$this->editValues('set-prio', 'Assign Priority', 'dropprio', 'addprio', 'number 0-7', NULL, 10);
		$this->editText('probability', 'Probability', NULL, 10, '0-100% or 0-1');
		$this->editText('rtable', 'Routing Table', NULL, 10, 'number');
		$this->editText('received-on', 'Received on interface', NULL, 10, 'if or macro');
	}

	function editDirection()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
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

	function editState()
	{
		/// @todo "[ "(" state-opts ")" ]
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('TCP State').':' ?>
			</td>
			<td>
				<select id="tcp-state" name="tcp-state">
					<option value=""></option>
					<option value="no" <?php echo ($this->rule['tcp-state'] == 'no' ? 'selected' : ''); ?>>No State</option>
					<option value="keep" <?php echo ($this->rule['tcp-state'] == 'keep' ? 'selected' : ''); ?>>Keep State</option>
					<option value="modulate" <?php echo ($this->rule['tcp-state'] == 'modulate' ? 'selected' : ''); ?>>Modulate State</option>
					<option value="synproxy" <?php echo ($this->rule['tcp-state'] == 'synproxy' ? 'selected' : ''); ?>>Synproxy</option>
				</select>
				<?php $this->PrintHelp('stateful') ?>
			</td>
		</tr>
		<?php
	}

	function editIcmpType()
	{
		if (isset($this->rule['proto']) && ($this->rule['proto'] == "icmp" || is_array($this->rule['proto']) && in_array("icmp", $this->rule['proto']))) {
			$this->editValues('icmp-type', 'ICMP Type', 'dropicmptype', 'addicmptype', 'number, name or macro');
			?>
			<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('ICMP Code').':' ?>
				</td>
				<td>
					<input type="text" name="icmp-code" id="icmp-code" value="<?php echo $this->rule['icmp-code']; ?>" <?php echo (isset($this->rule['icmp-type']) && !is_array($this->rule['icmp-type']) ? "" : "disabled=\"disabled\"")?> />
				</td>
			</tr>
			<?php
		}
	}

	function editIcmp6Type()
	{
		if (isset($this->rule['proto']) && ($this->rule['proto'] == "icmp6" || is_array($this->rule['proto']) && in_array("icmp6", $this->rule['proto']))) {
			$this->editValues('icmp6-type', 'ICMP6 Type', 'dropicmp6type', 'addicmp6type', 'number, name or macro');
			?>
			<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('ICMP6 Code').':' ?>
				</td>
				<td>
					<input type="text" name="icmp6-code" id="icmp6-code" value="<?php echo $this->rule['icmp6-code']; ?>" <?php echo (isset($this->rule['icmp6-type']) && !is_array($this->rule['icmp6-type']) ? "" : "disabled=\"disabled\"")?> />
				</td>
			</tr>
			<?php
		}
	}

	function editQueue()
	{
		global $View;
		
		$queueNames= $View->RuleSet->getQueueNames();
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Queue').':' ?>
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
						$queuepri= $this->rule['queue'][0];
					}
					foreach ($queueNames as $queue) {
						?>
						<option value="<?php echo $queue; ?>" <?php echo $queuepri == $queue ? 'selected' : ''; ?>><?php echo $queue; ?></option>
						<?php
					}
				}
				?>
				</select>
				<?php echo _TITLE('primary') ?>

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
							<option value="<?php echo $queue; ?>" <?php echo $this->rule['queue'][1] == $queue ? 'selected' : ''; ?>><?php echo $queue; ?></option>
							<?php
						}
					}
				}
				?>
				</select>	
				<?php echo _TITLE('secondary') ?>
				<?php $this->PrintHelp('filter-queue') ?>
			</td>
		</tr>
		<?php
	}
}
?>
