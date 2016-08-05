<?php
/* $pfre: FilterBase.php,v 1.13 2016/08/04 14:42:52 soner Exp $ */

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

class FilterBase extends State
{
	function display($rulenumber, $count)
	{
		$this->dispHead($rulenumber);
		$this->dispAction();
		$this->dispValue('direction', 'Direction');
		$this->dispInterface();
		$this->dispLog();
		$this->dispKey('quick', 'Quick');
		$this->dispValue('proto', 'Proto');
		$this->dispSrcDest();
		$this->dispValue('state-filter', 'State');
		$this->dispQueue();
		$this->dispTail($rulenumber, $count);
	}
	
	function displayNat($rulenumber, $count)
	{
		$this->dispHead($rulenumber);
		$this->dispAction();
		$this->dispValue('direction', 'Direction');
		$this->dispInterface();
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
				<?php $this->printHostPort($this->rule['from']); ?>
			</td>
			<td title="Source Port">
				<?php $this->printHostPort($this->rule['fromport']); ?>
			</td>
			<td title="Destination">
				<?php $this->printHostPort($this->rule['to']); ?>
			</td>
			<td title="Destination Port">
				<?php $this->printHostPort($this->rule['port']); ?>
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

		$this->inputInterface();

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
		$this->inputKey('state-filter');
		$this->inputState();

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
		$this->inputKey('tagged');
		$this->inputBool('not-tagged');

		$this->inputKey('tos');
		$this->inputKey('set-tos');
		$this->inputKey('prio');

		$this->inputDel('set-prio', 'dropprio');
		$this->inputAdd('set-prio', 'addprio');

		$this->inputKey('probability');

		$this->inputKey('rtable');
		$this->inputKey('received-on');
		$this->inputBool('not-received-on');
	}

	function inputQueue()
	{
		if (filter_has_var(INPUT_POST, 'state')) {
			if (filter_has_var(INPUT_POST, 'queue-pri') && filter_input(INPUT_POST, 'queue-pri') !== '' &&
				filter_has_var(INPUT_POST, 'queue-sec') && filter_input(INPUT_POST, 'queue-sec') !== '') {
				$this->rule['queue']= array();
				$this->rule['queue'][0]= filter_input(INPUT_POST, 'queue-pri');
				$this->rule['queue'][1]= filter_input(INPUT_POST, 'queue-sec');
			} elseif (filter_has_var(INPUT_POST, 'queue-pri') && filter_input(INPUT_POST, 'queue-pri') !== '') {
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
		$this->editIndex= 0;
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
		$this->editInterface();
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
		$this->editStateFilter();
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
		$this->editTagged();
		$this->editText('tag', 'Assign Tag', NULL, NULL, 'string');
		$this->editText('tos', 'Match TOS', NULL, NULL, 'string or number');
		$this->editText('set-tos', 'Enforce TOS', NULL, NULL, 'string or number');
		$this->editText('prio', 'Match Priority', NULL, 10, 'number 0-7');
		$this->editValues('set-prio', 'Assign Priority', 'dropprio', 'addprio', 'number 0-7', NULL, 10);
		$this->editText('probability', 'Probability', NULL, 10, '0-100% or 0-1');
		$this->editText('rtable', 'Routing Table', NULL, 10, 'number');
		$this->editReceivedOn();
	}

	function editDirection()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Direction').':' ?>
			</td>
			<td>
				<select id="direction" name="direction">
					<option value="" label=""></option>
					<option value="in" label="in" <?php echo ($this->rule['direction'] == 'in' ? 'selected' : ''); ?>>in</option>
					<option value="out" label="out" <?php echo ($this->rule['direction'] == 'out' ? 'selected' : ''); ?>>out</option>
				</select>
				<?php $this->editHelp('direction') ?>
			</td>
		</tr>
		<?php
	}

	function editStateFilter()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Stateful Filtering').':' ?>
			</td>
			<td>
				<select id="state-filter" name="state-filter">
					<option value=""></option>
					<option value="no" <?php echo ($this->rule['state-filter'] == 'no' ? 'selected' : ''); ?>>No State</option>
					<option value="keep" <?php echo ($this->rule['state-filter'] == 'keep' ? 'selected' : ''); ?>>Keep State</option>
					<option value="modulate" <?php echo ($this->rule['state-filter'] == 'modulate' ? 'selected' : ''); ?>>Modulate State</option>
					<option value="synproxy" <?php echo ($this->rule['state-filter'] == 'synproxy' ? 'selected' : ''); ?>>Synproxy</option>
				</select>
				<?php $this->editHelp('state-filter') ?>
			</td>
		</tr>
		<?php
		if (isset($this->rule['state-filter'])) {
			$this->editState();
		}
	}

	function editIcmpType()
	{
		if (isset($this->rule['proto']) && ($this->rule['proto'] == 'icmp' || is_array($this->rule['proto']) && in_array('icmp', $this->rule['proto']))) {
			$this->editValues('icmp-type', 'ICMP Type', 'dropicmptype', 'addicmptype', 'number, name or macro');
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('ICMP Code').':' ?>
				</td>
				<td>
					<input type="text" name="icmp-code" id="icmp-code" value="<?php echo $this->rule['icmp-code']; ?>" <?php echo (isset($this->rule['icmp-type']) && !is_array($this->rule['icmp-type']) ? '' : 'disabled')?> />
				</td>
			</tr>
			<?php
		}
	}

	function editIcmp6Type()
	{
		if (isset($this->rule['proto']) && ($this->rule['proto'] == 'icmp6' || is_array($this->rule['proto']) && in_array('icmp6', $this->rule['proto']))) {
			$this->editValues('icmp6-type', 'ICMP6 Type', 'dropicmp6type', 'addicmp6type', 'number, name or macro');
			?>
			<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
				<td class="title">
					<?php echo _TITLE('ICMP6 Code').':' ?>
				</td>
				<td>
					<input type="text" name="icmp6-code" id="icmp6-code" value="<?php echo $this->rule['icmp6-code']; ?>" <?php echo (isset($this->rule['icmp6-type']) && !is_array($this->rule['icmp6-type']) ? '' : 'disabled')?> />
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
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
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
				<?php $this->editHelp('filter-queue') ?>
			</td>
		</tr>
		<?php
	}

	function editTagged()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo 'Match Tagged:' ?>
			</td>
			<td>
				<input type="text" id="tagged" name="tagged" value="<?php echo $this->rule['tagged']; ?>" placeholder="string" />
				<?php $this->editHelp('tagged'); ?>
				<input type="checkbox" id="not-tagged" name="not-tagged" value="not-tagged" <?php echo ($this->rule['not-tagged'] ? 'checked' : ''); ?> <?php echo (!isset($this->rule['tagged']) ? 'disabled' : ''); ?> />
				<label for="not-tagged">negated</label>
			</td>
		</tr>
		<?php
	}

	function editReceivedOn()
	{
		?>
		<tr class="<?php echo ($this->editIndex++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo 'Received on Interface:' ?>
			</td>
			<td>
				<input type="text" id="received-on" name="received-on" value="<?php echo $this->rule['received-on']; ?>" size="10" placeholder="if or macro" />
				<?php $this->editHelp('received-on'); ?>
				<input type="checkbox" id="not-received-on" name="not-received-on" value="not-received-on" <?php echo ($this->rule['not-received-on'] ? 'checked' : ''); ?> <?php echo (!isset($this->rule['received-on']) ? 'disabled' : ''); ?> />
				<label for="not-received-on">negated</label>
			</td>
		</tr>
		<?php
	}
}
?>
