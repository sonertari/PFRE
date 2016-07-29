<?php
/* $pfre: write.php,v 1.9 2016/07/27 15:08:56 soner Exp $ */

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

require_once ('include.php');

$lines= TRUE;
if (count($_POST) && !isset($_POST['lines'])) {
	$lines= FALSE;
}

$rulesStr= $View->RuleSet->generate();
$serialRulesArray= serialize(explode('\n', $rulesStr));
$testResult= $View->Controller($Output, 'TestPfRules', $serialRulesArray);

if ($testResult) {
	if (isset($_POST['install']) && $_POST['install'] == "Install") {
		if ($View->Controller($Output, 'InstallPfRules', $serialRulesArray)) {
			PrintHelpWindow("Installed successfully");
		} else {
			PrintHelpWindow("<br>There was an error while installing", NULL, 'ERROR');
		}
	}
} else {
	PrintHelpWindow("<br>Ruleset has errors", NULL, 'ERROR');
}

require_once($VIEW_PATH.'/header.php');
?>
<fieldset>
	<form id="installform" name="installform" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<label for="lines">Display line numbers</label>
		<input type="checkbox" id="lines" name="lines" <?php echo $lines ? 'checked' : '' ?> onclick="document.installform.apply.click()" />
		<input type="submit" id="apply" name="apply" value="Apply" />
		<input type="submit" id="install" name="install" value="Install" <?php echo $testResult ? '' : 'disabled' ?> />
		<label for="install">Install as main rulebase: /etc/pf.conf</label>
	</form>
</fieldset>
<?php
$printFileName= $View->RuleSet->filename == '/etc/pf.conf' || dirname($View->RuleSet->filename) == '/etc/pfre';
echo _('Rule file') . ': ' . ($printFileName ? $View->RuleSet->filename : '');
?>
<hr style="border: 0; border-bottom: 1px solid gray;" />

<pre>
<?php
echo htmlentities($View->RuleSet->generate($lines ? TRUE : NULL));
?>
</pre>
<?php
require_once($VIEW_PATH.'/footer.php');
?>
