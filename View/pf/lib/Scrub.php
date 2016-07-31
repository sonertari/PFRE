<?php
/* $pfre: Scrub.php,v 1.6 2016/07/31 10:33:34 soner Exp $ */

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

class Scrub extends Filter
{
	function __construct($str)
	{
		$this->keywords = array(
			'no-df' => array(
				'method' => 'parseBool',
				'params' => array(),
				),
			'min-ttl' => array(
				'method' => 'parseNextValue',
				'params' => array(),
				),
			'max-mss' => array(
				'method' => 'parseNextValue',
				'params' => array(),
				),
			'random-id' => array(
				'method' => 'parseBool',
				'params' => array(),
				),
			'reassemble' => array(
				'method' => 'parseNextValue',
				'params' => array(),
				),
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace('/{/', ' { ', $this->str);
		$this->str= preg_replace('/}/', ' } ', $this->str);
		$this->str= preg_replace('/\(/', ' ( ', $this->str);
		$this->str= preg_replace('/\)/', ' ) ', $this->str);
		$this->str= preg_replace('/,/', ' , ', $this->str);
	}

	function generate()
	{
		$this->str= 'match';

		$this->genFilterHead();
		$this->genFilterOpts();
		$this->genScrub();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genScrub()
	{
		$this->str.= ' scrub';
		$opt= '';
		if (isset($this->rule['no-df'])) {
			$opt.= 'no-df';
		}
		if (isset($this->rule['min-ttl'])) {
			$opt.= ', min-ttl ' . $this->rule['min-ttl'];
		}
		if (isset($this->rule['max-mss'])) {
			$opt.= ', max-mss ' . $this->rule['max-mss'];
		}
		if (isset($this->rule['random-id'])) {
			$opt.= ', random-id';
		}
		if (isset($this->rule['reassemble'])) {
			$opt.= ', reassemble ' . $this->rule['reassemble'];
		}
		if ($opt !== '') {
			$this->str.= ' (' . trim($opt, ' ,') . ')';
		}
	}

	function display($rulenumber, $count)
	{
		$this->dispHead($rulenumber);
		$this->dispValue('direction', 'Direction');
		$this->dispValue('interface', 'Interface');
		$this->dispLog();
		$this->dispKey('quick', 'Quick');
		$this->dispValue('proto', 'Proto');
		$this->dispSrcDest();
		$this->dispValue('min-ttl', 'Min-ttl');
		$this->dispValue('max-mss', 'Max-mss');
		$this->dispScrubOpts();
		$this->dispTail($rulenumber, $count);
	}

	function dispScrubOpts()
	{
		?>
		<td title="Options">
			<?php echo ($this->rule['no-df'] ? 'no-df<br>' : '') . ($this->rule['random-id'] ? 'random-id<br>' : '') . ($this->rule['reassemble'] ? 'reassemble ' . $this->rule['reassemble'] . '<br>' : ''); ?>
		</td>
		<?php
	}

	function processInput()
	{
		if (filter_has_var(INPUT_GET, 'dropinterface')) {
			$this->delEntity("interface", filter_input(INPUT_GET, 'dropinterface'));
		}

		if (filter_has_var(INPUT_GET, 'dropto')) {
			$this->delEntity("to", filter_input(INPUT_GET, 'dropto'));
		}

		if (filter_has_var(INPUT_GET, 'dropfrom')) {
			$this->delEntity("from", filter_input(INPUT_GET, 'dropfrom'));
		}

		if (count($_POST)) {
			if (filter_input(INPUT_POST, 'addfrom') != '') {
				$this->addEntity("from", filter_input(INPUT_POST, 'addfrom'));
			}

			if (filter_input(INPUT_POST, 'addto') != '') {
				$this->addEntity("to", filter_input(INPUT_POST, 'addto'));
			}

			if (filter_input(INPUT_POST, 'addinterface') != '') {
				$this->addEntity("interface", filter_input(INPUT_POST, 'addinterface'));
			}

			$this->rule['direction']= filter_input(INPUT_POST, 'direction');
			$this->rule['no-df']= (filter_has_var(INPUT_POST, 'no-df') ? TRUE : '');
			$this->rule['random-id']= (filter_has_var(INPUT_POST, 'random-id') ? TRUE : '');
			$this->rule['min-ttl']= filter_input(INPUT_POST, 'min-ttl');
			$this->rule['max-mss']= filter_input(INPUT_POST, 'max-mss');
			$this->rule['reassemble']= filter_input(INPUT_POST, 'reassemble');
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');

			if (filter_has_var(INPUT_POST, 'all')) {
				$this->rule['all']= TRUE;
				unset($this->rule['from']);
				unset($this->rule['to']);
			} else {
				unset($this->rule['all']);
			}
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		?>
		<h2>Edit Scrub Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Scrub') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" name="theform" action="<?php echo $this->href . $rulenumber; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Direction').':' ?>
					</td>
					<td>
						<select id="direction" name="direction">
							<option value="" label=""></option>
							<option value="in" label="in" <?php echo ($this->rule['direction'] == 'in' ? 'selected' : '')?>>in</option>
							<option value="out" label="out" <?php echo ($this->rule['direction'] == 'out' ? 'selected' : '')?>>out</option>					
						</select>
						<?php $this->PrintHelp('direction') ?>
					</td>
				</tr>
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
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Match All').':' ?>
					</td>
					<td>
						<input type="checkbox" id="all" name="all" value="all" <?php echo ($this->rule['all'] ? 'checked' : ''); ?> onclick="document.theform.submit()" />
						<?php $this->PrintHelp('match-all') ?>
					</td>
				</tr>
				<tr class="evenline">
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
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Destination').':' ?>
					</td>
					<td>
						<?php
						$this->PrintDeleteLinks($this->rule['to'], $rulenumber, 'dropto');
						$this->PrintAddControls('addto', NULL, 'ip, host or macro', NULL, NULL, $this->rule['all']);
						?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Options').':' ?>
					</td>
					<td>
						<input type="checkbox" id="no-df" name="no-df" value="no-df" <?php echo ($this->rule['no-df'] ? 'checked' : '')?> />
						<label for="no-df">no-df</label>
						<?php $this->PrintHelp('no-df') ?>
						<br>
						<input type="checkbox" id="random-id" name="random-id" value="random-id" <?php echo ($this->rule['random-id'] ? 'checked' : '')?> />
						<label for="random-id">random-id</label>
						<?php $this->PrintHelp('random-id') ?>
						<br>
						<input type="checkbox" id="reassemble" name="reassemble" value="tcp" <?php echo ($this->rule['reassemble'] == 'tcp' ? 'checked' : '')?> />
						<label for="reassemble">reassemble tcp</label>
						<?php $this->PrintHelp('reassemble-tcp') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Min TTL').':' ?>
					</td>
					<td>
						<input type="text" id="min-ttl" name="min-ttl" size="4" value="<?php echo $this->rule['min-ttl']; ?>" />
						<?php $this->PrintHelp('min-ttl') ?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Max MSS').':' ?>
					</td>
					<td>
						<input type="text" id="max-mss" name="max-mss" size="4" value="<?php echo $this->rule['max-mss']; ?>" />
						<?php $this->PrintHelp('max-mss') ?>
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