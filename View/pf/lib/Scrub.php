<?php
/* $pfre: Scrub.php,v 1.10 2016/08/03 01:12:23 soner Exp $ */

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
			'min-ttl' => array(
				'method' => 'parseNextValue',
				'params' => array(),
				),
			'max-mss' => array(
				'method' => 'parseNextValue',
				'params' => array(),
				),
			'no-df' => array(
				'method' => 'parseBool',
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

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genScrub();
		$this->genFilterOpts();

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
		$this->dispAction();
		$this->dispValue('direction', 'Direction');
		$this->dispInterface();
		$this->dispLog();
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

	function input()
	{
		$this->inputAction();

		$this->inputFilterHead();

		$this->inputLog();
		$this->inputBool('quick');

		$this->inputBool('no-df');
		$this->inputBool('random-id');
		/// @todo This is bool actually, fix parser first
		$this->inputKey('reassemble');
		$this->inputKey('min-ttl');
		$this->inputKey('max-mss');

		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function edit($rulenumber, $modified, $testResult, $action)
	{
		$this->index= 0;
		$this->rulenumber= $rulenumber;

		$this->editHead($modified);

		$this->editAction();

		$this->editFilterHead();

		$this->editLog();
		$this->editCheckbox('quick', 'Quick');

		$this->editScrubOptions();
		$this->editText('min-ttl', 'Min TTL', NULL, 10, 'number');
		$this->editText('max-mss', 'Max MSS', NULL, 10, 'number');

		$this->editFilterOpts();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
	}

	function editScrubOptions()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Scrub Options').':' ?>
			</td>
			<td>
				<input type="checkbox" id="no-df" name="no-df" value="no-df" <?php echo ($this->rule['no-df'] ? 'checked' : '')?> />
				<label for="no-df">no-df</label>
				<?php $this->editHelp('no-df') ?>
				<br>
				<input type="checkbox" id="random-id" name="random-id" value="random-id" <?php echo ($this->rule['random-id'] ? 'checked' : '')?> />
				<label for="random-id">random-id</label>
				<?php $this->editHelp('random-id') ?>
				<br>
				<input type="checkbox" id="reassemble" name="reassemble" value="tcp" <?php echo ($this->rule['reassemble'] == 'tcp' ? 'checked' : '')?> />
				<label for="reassemble">reassemble tcp</label>
				<?php $this->editHelp('reassemble-tcp') ?>
			</td>
		</tr>
		<?php
	}
}
?>