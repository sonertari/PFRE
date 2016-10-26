<?php
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

require_once ('pf.php');

$printNumbers= TRUE;
if (count($_POST) && !filter_has_var(INPUT_POST, 'numbers')) {
	$printNumbers= FALSE;
}

$testResult= $View->Controller($Output, 'TestPfRules', json_encode($View->RuleSet->rules));
if ($testResult) {
	if (filter_has_var(INPUT_POST, 'install') && filter_input(INPUT_POST, 'install') == "Install") {
		if ($View->Controller($Output, 'InstallPfRules', json_encode($View->RuleSet->rules))) {
			PrintHelpWindow(_NOTICE('Installed successfully'));
		} else {
			PrintHelpWindow('<br>' . _NOTICE('There was an error while installing'), NULL, 'ERROR');
		}
	}
} else {
	PrintHelpWindow('<br>' . _NOTICE('Failed testing ruleset'), NULL, 'ERROR');
}

$force= 0;
if (filter_has_var(INPUT_POST, 'forcedisplay')) {
	$force= 1;
}

if ($testResult || $force) {
	/// @todo Check why we cannot pass FALSE as numbers param
	$generated= $View->Controller($Output, 'GeneratePfRules', json_encode($View->RuleSet->rules), $printNumbers ? 1 : 0, $force);
}

require_once($VIEW_PATH.'/header.php');
?>
<fieldset>
	<form id="installForm" name="installForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>" method="post">
		<input type="checkbox" id="numbers" name="numbers" <?php echo $printNumbers ? 'checked' : '' ?> onclick="document.installForm.apply.click()" />
		<label for="numbers"><?php echo _CONTROL('Display line numbers') ?></label>
		<input type="checkbox" id="forcedisplay" name="forcedisplay" <?php echo filter_has_var(INPUT_POST, 'forcedisplay') ? 'checked' : ''; ?> <?php echo $testResult ? 'disabled' : ''; ?> onclick="document.installForm.apply.click()" />
		<label for="forcedisplay"><?php echo _CONTROL('Display with errors') ?></label>
		<input type="submit" id="apply" name="apply" value="<?php echo _CONTROL('Apply') ?>" />
		<input type="submit" id="install" name="install" value="<?php echo _CONTROL('Install') ?>" <?php echo $testResult ? '' : 'disabled' ?> />
		<label for="install"><?php echo _CONTROL('Install as main ruleset') ?>: /etc/pf.conf</label>
	</form>
</fieldset>
<?php
echo _TITLE('Rule file') . ': ' . $View->RuleSet->filename;
?>
<hr style="border: 0; border-bottom: 1px solid gray;" />

<pre id="rules">
<?php
if ($generated || $force) {
	echo htmlentities(implode("\n", $Output));
}
?>
</pre>
<?php
require_once($VIEW_PATH.'/footer.php');
?>
