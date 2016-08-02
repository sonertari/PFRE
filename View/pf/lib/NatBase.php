<?php
/* $pfre: NatBase.php,v 1.2 2016/07/31 14:19:13 soner Exp $ */

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

class NatBase extends Filter
{
	function __construct($str)
	{
		$this->keywords = array_merge(
			$this->keywords,
			array(
				'bitmask' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				'least-states' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				'round-robin' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				'random' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				'source-hash' => array(
					'method' => 'parseSourceHash',
					'params' => array(),
					),
				'sticky-address' => array(
					'method' => 'parseBool',
					'params' => array(),
					),
				)
			);

		parent::__construct($str);
	}

	function sanitize()
	{
		$this->str= preg_replace("/! +/", "!", $this->str);
		$this->str= preg_replace("/{/", " { ", $this->str);
		$this->str= preg_replace("/}/", " } ", $this->str);
		$this->str= preg_replace("/\"/", " \" ", $this->str);
	}

	function parseSourceHash()
	{
		$this->parseBool();

		/// @attention No pattern for hash key or string, so check keywords instead
		/// This is one of the benefits of using keyword lists instead of switch/case structs while parsing
		//if (preg_match('/[a-f\d]{16,}/', $this->words[$this->index + 1])) {
		if (!in_array($this->words[$this->index + 1], $this->keywords)) {
			$this->rule['source-hash-key']= $this->words[++$this->index];
		}
	}

	function generate()
	{
		$this->genAction();

		$this->genFilterHead();
		$this->genFilterOpts();

		$this->genValue('type');
		$this->genValue('redirhost');
		$this->genValue('redirport', 'port ');
		$this->genPoolType();

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}

	function genPoolType()
	{
		$this->genKey('bitmask');
		$this->genKey('least-states');
		$this->genKey('random');
		$this->genKey('round-robin');

		$this->genKey('source-hash');
		if (isset($this->rule['source-hash'])) {
			$this->genValue('source-hash-key');
		}

		$this->genKey('sticky-address');
	}

	function display($rulenumber, $count)
	{
		$this->displayNat($rulenumber, $count);
	}
	
	function input()
	{
		$this->inputAction();

		$this->inputFilterHead();

		$this->inputLog();
		$this->inputBool('quick');

		$this->inputNat();

		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function inputNat()
	{
		$this->inputKey('redirhost');
		$this->inputKey('redirport');
		$this->inputRedirOptions();
	}

	function inputRedirOptions()
	{
		$this->inputBool('bitmask');
		$this->inputBool('least-states');
		$this->inputBool('random');
		$this->inputBool('round-robin');
		$this->inputBool('source-hash');
		$this->inputKeyIfHasVar('source-hash-key', 'source-hash');
		$this->inputBool('sticky-address');
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

		$this->editNat();

		$this->editFilterOpts();

		$this->editComment();
		$this->editTail($modified, $testResult, $action);
	}

	function editNat()
	{
		$this->editText('redirhost', 'Redirect Host', 'Nat', NULL, 'ip, host, table or macro');
		$this->editText('redirport', 'Redirect Port', 'Nat', NULL, 'number, name, table or macro');
		$this->editRedirOptions();
	}

	function editRedirOptions()
	{
		?>
		<tr class="<?php echo ($this->index++ % 2 ? 'evenline' : 'oddline'); ?>">
			<td class="title">
				<?php echo _TITLE('Redirect Options').':' ?>
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
				<input type="text" id="source-hash-key" name="source-hash-key" <?php echo ($this->rule['source-hash'] ? '' : 'disabled'); ?> value="<?php echo $this->rule['source-hash-key']; ?>" size="32" />
				<label for="source-hash-key">key</label>
				<br>
				<input type="checkbox" id="sticky-address" name="sticky-address" <?php echo ($this->rule['bitmask'] || $this->rule['least-states'] || $this->rule['random'] || $this->rule['round-robin'] || $this->rule['source-hash'] ? '' : 'disabled'); ?> value="sticky-address" <?php echo ($this->rule['sticky-address'] ? 'checked' : ''); ?> />
				<label for="sticky-address">sticky-address</label>
				<?php $this->PrintHelp('rdr-method') ?>
			</td>
		</tr>
		<?php
	}
}
?>
