<?php
/* $pfre: Queue.php,v 1.6 2016/07/31 10:33:34 soner Exp $ */

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

class Queue extends Rule
{
	function __construct($str)
	{
		$this->keywords = array(
			'queue' => array(
				'method' => 'parseNextNVP',
				'params' => array('name'),
				),
			'on' => array(
				'method' => 'parseItems',
				'params' => array('interface'),
				),
			'parent' => array(
				'method' => 'parseNextValue',
				'params' => array(),
				),
			'bandwidth' => array(
				'method' => 'parseBandwidth',
				'params' => array('bw-burst', 'bw-time'),
				),
			'min' => array(
				'method' => 'parseBandwidth',
				'params' => array('min-burst', 'min-time'),
				),
			'max' => array(
				'method' => 'parseBandwidth',
				'params' => array('max-burst', 'max-time'),
				),
			'qlimit' => array(
				'method' => 'parseNextValue',
				'params' => array(),
				),
			'default' => array(
				'method' => 'parseBool',
				'params' => array(),
				),
			);

		// Base should not merge keywords
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

	function parseBandwidth($burst, $time)
	{
		$this->parseNextValue();

		/// @todo Fix this possible off-by-N errors
		if ($this->words[$this->index + 1] == 'burst') {
			$this->index+= 2;
			$this->rule[$burst]= $this->words[$this->index];
		}
		if ($this->words[$this->index + 1] == 'for') {
			$this->index+= 2;
			$this->rule[$time]= $this->words[$this->index];
		}
	}

	function generate()
	{
		$this->str= 'queue ' . $this->rule['name'];
		$this->genItems('interface', 'on');
		$this->genValue('parent', 'parent ');
		$this->genBandwidth('bandwidth', 'bw');
		$this->genBandwidth('min', 'min');
		$this->genBandwidth('max', 'max');
		$this->genValue('qlimit', 'qlimit ');
		$this->genKey('default');

		$this->genComment();
		$this->str.= "\n";
		return $this->str;
	}
	
	function genBandwidth($key, $pre)
	{
		if (isset($this->rule[$key])) {
			$this->str.= " $key " . $this->rule[$key] . ($this->rule["$pre-burst"] ? ' burst ' . $this->rule["$pre-burst"] : '') . ($this->rule["$pre-time"] ? ' for ' . $this->rule["$pre-time"] : '');
		}
	}

	function display($rulenumber, $count)
	{
		$this->dispHead($rulenumber);
		$this->dispValue('name', 'Name');
		$this->dispValue('interface', 'Interface');
		$this->dispValue('parent', 'Parent');
		$this->dispBandwidth('bandwidth', 'bw', 'Bandwidth', 3);
		$this->dispBandwidth('min', 'min', 'Min', 2);
		$this->dispBandwidth('max', 'max', 'Max', 2);
		$this->dispValue('qlimit', 'Qlimit');
		$this->dispKey('default', 'Default');
		$this->dispTail($rulenumber, $count);
	}
	
	function dispBandwidth($key, $pre, $title, $colspan)
	{
		?>
		<td title="<?php echo $title; ?>" colspan="<?php echo $colspan; ?>">
			<?php echo $this->rule[$key] . ($this->rule["$pre-burst"] ? '<br>burst: ' . $this->rule["$pre-burst"] : '') . ($this->rule["$pre-time"] ? '<br>time: ' . $this->rule["$pre-time"] : ''); ?>
		</td>
		<?php
	}

	function processInput()
	{
		if (count($_POST)) {
			$this->rule['name']= filter_input(INPUT_POST, 'name');
			$this->rule['interface']= filter_input(INPUT_POST, 'interface');
			$this->rule['parent']= filter_input(INPUT_POST, 'parent');
			$this->rule['bandwidth']= filter_input(INPUT_POST, 'bandwidth');
			$this->rule['bw-burst']= filter_input(INPUT_POST, 'bw-burst');
			$this->rule['bw-time']= filter_input(INPUT_POST, 'bw-time');
			$this->rule['min']= filter_input(INPUT_POST, 'min');
			$this->rule['min-burst']= filter_input(INPUT_POST, 'min-burst');
			$this->rule['min-time']= filter_input(INPUT_POST, 'min-time');
			$this->rule['max']= filter_input(INPUT_POST, 'max');
			$this->rule['max-burst']= filter_input(INPUT_POST, 'max-burst');
			$this->rule['max-time']= filter_input(INPUT_POST, 'max-time');
			$this->rule['qlimit']= filter_input(INPUT_POST, 'qlimit');
			$this->rule['default']= (filter_has_var(INPUT_POST, 'default') ? TRUE : '');
			$this->rule['comment']= filter_input(INPUT_POST, 'comment');
		}

		$this->deleteEmptyEntries();
	}
	
	function edit($rulenumber, $modified, $testResult, $action)
	{
		?>
		<h2>Edit Queue Rule <?php echo $rulenumber . ($modified ? ' (modified)' : ''); ?><?php $this->PrintHelp('Queue') ?></h2>
		<h4><?php echo htmlentities($this->generate()); ?></h4>
		<form id="theform" action="<?php echo "conf.php?sender=queue&rulenumber=$rulenumber"; ?>" method="post">
			<table id="nvp">
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Name').':' ?>
					</td>
					<td>
						<input type="text" id="name" name="name" size="10" value="<?php echo $this->rule['name']; ?>" />
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Interface').':' ?>
					</td>
					<td>
						<input type="text" id="interface" name="interface" size="10" value="<?php echo $this->rule['interface']; ?>" />
						<?php $this->PrintHelp('queue-interface') ?>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Parent').':' ?>
					</td>
					<td>
						<input type="text" id="parent" name="parent" size="10" value="<?php echo $this->rule['parent']; ?>" />
						<?php $this->PrintHelp('parent') ?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Bandwidth').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" id="bandwidth" name="bandwidth" size="10" value="<?php echo $this->rule['bandwidth']; ?>" />
											</td>
											<td class="optitle">bandwidth<?php $this->PrintHelp('bandwidth') ?></td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="bw-burst" name="bw-burst" size="10" value="<?php echo $this->rule['bw-burst']; ?>" />
											</td>
											<td class="optitle">burst</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="bw-time" name="bw-time" size="10" value="<?php echo $this->rule['bw-time']; ?>" />
											</td>
											<td class="optitle">time</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Min').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" id="min" name="min" size="10" value="<?php echo $this->rule['min']; ?>" />
											</td>
											<td class="optitle">bandwidth</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="min-burst" name="min-burst" size="10" value="<?php echo $this->rule['min-burst']; ?>" />
											</td>
											<td class="optitle">burst</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="min-time" name="min-time" size="10" value="<?php echo $this->rule['min-time']; ?>" />
											</td>
											<td class="optitle">time</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Max').':' ?>
					</td>
					<td>
						<table style="width: auto;">
							<tr>
								<td class="ifs">
									<table>
										<tr>
											<td class="ifs">
												<input type="text" id="max" name="max" size="10" value="<?php echo $this->rule['max']; ?>" />
											</td>
											<td class="optitle">bandwidth</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="max-burst" name="max-burst" size="10" value="<?php echo $this->rule['max-burst']; ?>" />
											</td>
											<td class="optitle">burst</td>
										</tr>
										<tr>
											<td class="ifs">
												<input type="text" id="max-time" name="max-time" size="10" value="<?php echo $this->rule['max-time']; ?>" />
											</td>
											<td class="optitle">time</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="oddline">
					<td class="title">
						<?php echo _TITLE('Qlimit').':' ?>
					</td>
					<td>
						<input type="text" id="qlimit" name="qlimit" size="10" value="<?php echo $this->rule['qlimit']; ?>" />
						<?php $this->PrintHelp('qlimit') ?>
					</td>
				</tr>
				<tr class="evenline">
					<td class="title">
						<?php echo _TITLE('Default').':' ?>
					</td>
					<td>
						<input type="checkbox" id="default" name="default" <?php echo ($this->rule['default']) ? 'checked' : '' ; ?> />
						<?php $this->PrintHelp('default') ?>
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