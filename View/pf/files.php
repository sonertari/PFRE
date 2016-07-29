<?php
/* $pfre: files.php,v 1.11 2016/07/27 15:08:56 soner Exp $ */

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
require_once ('include.php');

if (filter_has_var(INPUT_POST, 'reload')) {
	$ruleSet= new RuleSet($View);
	if ($ruleSet) {
		$View->RuleSet= $ruleSet;
		PrintHelpWindow('Main pf rules reloaded successfully: ' . $View->RuleSet->filename);
	}
}

$loadfile= '';
if (filter_has_var(INPUT_POST, 'load')) {
	// Accept only file names, no paths
	$loadfile= basename(filter_input(INPUT_POST, 'filename'));
	$filepath= "$PF_CONFIG_PATH/$loadfile";
	
	$ruleSet= new RuleSet($View, $filepath);
	if ($ruleSet) {
		$View->RuleSet= $ruleSet;
		PrintHelpWindow('Rules loaded successfully: ' . $View->RuleSet->filename);
	}
}

$deletefile= '';
if (filter_has_var(INPUT_POST, 'remove')) {
	// Accept only file names, no paths
	$deletefile= basename(filter_input(INPUT_POST, 'deletefilename'));
	$filepath= "$PF_CONFIG_PATH/$deletefile";
	
	if ($View->Controller($Output, 'DeletePfRuleFile', $filepath)) {
		PrintHelpWindow("Rules file deleted: $filepath");
	}
}

$savefile= '';
if (filter_has_var(INPUT_POST, 'save')) {
	$rulesStr= $View->RuleSet->generate();
	$serialRulesArray= serialize(explode('\n', $rulesStr));
	$testResult= $View->Controller($Output, 'TestPfRules', $serialRulesArray);
	
	if ($testResult) {
		// Accept only file names, no paths
		$savefile= basename(filter_input(INPUT_POST, 'filename'));
		$filepath= "$PF_CONFIG_PATH/$savefile";

		/// @attention Use 1, not FALSE for boolean here, otherwise arg type check fails
		if ($View->Controller($Output, 'InstallPfRules', $serialRulesArray, $filepath, 1)) {
			PrintHelpWindow("Saved successfully: $filepath");
		} else {
			PrintHelpWindow("<br>There was an error while saving: $filepath", NULL, 'ERROR');
		}
	} else {
		PrintHelpWindow('<br>Ruleset has errors', NULL, 'ERROR');
	}
}

if (filter_has_var(INPUT_POST, 'upload')) {
	if ($_FILES['file']['error'] == 0) {
		$ruleSet= new RuleSet($View, $_FILES['file']['tmp_name'], file_get_contents($_FILES['file']['tmp_name']));
		if ($ruleSet) {
			$View->RuleSet= $ruleSet;
			PrintHelpWindow('File uploaded successfully');
		}
		unlink($_FILES['file']['tmp_name']);
	} else {
		PrintHelpWindow('File upload failed: ' . $_FILES['file']['tmp_name'], NULL, 'ERROR');
	}
}

if (filter_has_var(INPUT_POST, 'download')) {
	if (filter_has_var(INPUT_SERVER, 'HTTP_USER_AGENT') && preg_match("/MSIE/", filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'))) {
		// IE Bug in download name workaround
		ini_set('zlib.output_compression', 'Off');
	}
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="pf.conf"');
	echo $View->RuleSet->generate();
	exit();
}

$View->Controller($Output, 'GetPfRuleFiles');
$ruleFiles= $Output;

require_once($VIEW_PATH.'/header.php');
?>
<h2>Load rulebase</h2>
<br />
<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
	<input type="submit" id="reload" name="reload" value="Reload" />
	<label for="reload">Reload main rulebase</label>
</form>
<br />
<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
	<select id="filename" name="filename">
		<option value="" label=""></option>
		<?php
		foreach ($ruleFiles as $file) {
			?>
			<option value="<?php echo $file; ?>" label="<?php echo $file; ?>" <?php echo ($loadfile == $file ? 'selected' : ''); ?>><?php echo $file; ?></option>
			<?php
		}
		?>
	</select>
	<input type="submit" id="load" name="load" value="Load" />
	<label for="load">Load rules from file</label>
</form>

<p>&nbsp;</p>

<h2>Save rulebase</h2>
<br />
<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
	<input type="text" name="filename" id="filename" value="<?php echo $savefile; ?>" />
	<input type="submit" id="save" name="save" value="Save" />
	<label for="save">Save current working rules to file</label>
</form>

<p>&nbsp;</p>

<h2>Delete rulebase</h2>
<br />
<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
	<select id="deletefilename" name="deletefilename">
		<option value="" label=""></option>
		<?php
		foreach ($ruleFiles as $file) {
			?>
			<option value="<?php echo $file; ?>" label="<?php echo $file; ?>" <?php echo ($deletefile == $file ? 'selected' : ''); ?>><?php echo $file; ?></option>
			<?php
		}
		?>
	</select>
	<input type="submit" id="remove" name="remove" value="Delete" title="Delete" onclick="return confirm('Are you sure you want to delete the rules file?')"/>
	<label for="load">Delete rules file</label>
</form>

<p>&nbsp;</p>

<h2>Upload rulebase</h2>
<br />
<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" enctype="multipart/form-data" method="post">
    <input type="submit" id="upload" name="upload" value="Upload" />
    <input type="hidden" name="max_file_size" value="30000" />
    Upload file: <input name="file" type="file" />
</form>

<p>&nbsp;</p>

<h2>Download rulebase</h2>
<br />
<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
	<input type="submit" id="download" name="download" value="Download" />
	<label for="download">Download current working rulebase</label>
</form>
<?php
require_once($VIEW_PATH.'/footer.php');
?>
