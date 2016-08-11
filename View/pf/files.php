<?php
/* $pfre: files.php,v 1.13 2016/08/08 08:37:55 soner Exp $ */

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

use View\RuleSet;

require_once ('pf.php');

if (filter_has_var(INPUT_POST, 'reload')) {
	$force= 0;
	/// @attention Need separate force* vars for each form, otherwise they all are checked
	if (filter_has_var(INPUT_POST, 'forceload')) {
		$force= 1;
	}

	$ruleSet= new RuleSet();
	if ($ruleSet->load('/etc/pf.conf', NULL, $force)) {
		$View->RuleSet= $ruleSet;
		PrintHelpWindow('Main pf rules reloaded: ' . $View->RuleSet->filename);
	} else {
		PrintHelpWindow('<br>Failed loading main pf rules', NULL, 'ERROR');
	}
	/// @attention Remove the session, otherwise the existing session confuses the user if s/he edits the same rule number in the session
	unset($_SESSION['edit']);
}

$loadfile= '';
if (filter_has_var(INPUT_POST, 'load')) {
	$force= 0;
	if (filter_has_var(INPUT_POST, 'forceload')) {
		$force= 1;
	}

	// Accept only file names, no paths
	$loadfile= basename(filter_input(INPUT_POST, 'filename'));
	$filepath= "$PF_CONFIG_PATH/$loadfile";
	
	$ruleSet= new RuleSet();
	if ($ruleSet->load($filepath, 0, $force)) {
		$View->RuleSet= $ruleSet;
		PrintHelpWindow('Rules loaded: ' . $View->RuleSet->filename);
	} else {
		PrintHelpWindow("<br>Failed loading: $filepath", NULL, 'ERROR');
	}
	unset($_SESSION['edit']);
}

$savefile= '';
if (filter_has_var(INPUT_POST, 'save')) {
	$force= 0;
	if (filter_has_var(INPUT_POST, 'forcesave')) {
		$force= 1;
	}

	// Accept only file names, no paths
	$savefile= basename(filter_input(INPUT_POST, 'filename'));
	$filepath= "$PF_CONFIG_PATH/$savefile";

	if ($force || $View->Controller($Output, 'TestPfRules', json_encode($View->RuleSet->rules))) {
		/// @attention Use 0, not FALSE for boolean here, otherwise arg type check fails
		if ($View->Controller($Output, 'InstallPfRules', json_encode($View->RuleSet->rules), $filepath, 0, $force)) {
			$View->RuleSet->filename= $filepath;
			PrintHelpWindow("Saved: $filepath");
		} else {
			PrintHelpWindow("<br>Failed saving: $filepath", NULL, 'ERROR');
		}
	} else {
		PrintHelpWindow("<br>Failed saving: $filepath, ruleset has errors", NULL, 'ERROR');
	}
}

$deleteFile= '';
if (filter_has_var(INPUT_POST, 'remove')) {
	// Accept only file names, no paths
	$deleteFile= basename(filter_input(INPUT_POST, 'deleteFilename'));
	$filepath= "$PF_CONFIG_PATH/$deleteFile";
	
	if ($View->Controller($Output, 'DeletePfRuleFile', $filepath)) {
		PrintHelpWindow("Rules file deleted: $filepath");
	} else {
		PrintHelpWindow("<br>Failed deleting: $filepath", NULL, 'ERROR');
	}
}

if (filter_has_var(INPUT_POST, 'upload')) {
	$force= 0;
	if (filter_has_var(INPUT_POST, 'forceupload')) {
		$force= 1;
	}

	if ($_FILES['file']['error'] == 0) {
		$ruleSet= new RuleSet();
		if ($ruleSet->load($_FILES['file']['tmp_name'], TRUE, $force, $_FILES['file']['name'])) {
			$View->RuleSet= $ruleSet;
			/// @todo Unlink the tmp file?
			PrintHelpWindow('File uploaded: ' . $_FILES['file']['name']);
		} else {
			PrintHelpWindow('<br>Failed uploading: ' . $_FILES['file']['name'], NULL, 'ERROR');
		}
	} else {
		PrintHelpWindow('Failed uploading: ' . $_FILES['file']['tmp_name'], NULL, 'ERROR');
	}
	unset($_SESSION['edit']);
}

if (filter_has_var(INPUT_POST, 'download')) {
	$force= 0;
	if (filter_has_var(INPUT_POST, 'forcedownload')) {
		$force= 1;
	}

	if ($View->Controller($Output, 'GeneratePfRules', json_encode($View->RuleSet->rules), 0, $force) || $force) {
		if (filter_has_var(INPUT_SERVER, 'HTTP_USER_AGENT') && preg_match("/MSIE/", filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'))) {
			// For IE
			ini_set('zlib.output_compression', 'Off');
		}

		header('Content-Type: application/octet-stream');

		if ($View->RuleSet->filename != '') {
			if (preg_match('/^(.*)\s+\(uploaded\)/', $View->RuleSet->filename, $match)) {
				$filename= $match[1];
			} else {
				$filename= basename($View->RuleSet->filename);
			}
		} else {
			$filename= 'pf.conf';
		}

		header('Content-Disposition: attachment; filename="' . $filename . '"');

		echo implode("\n", $Output);
		exit;
	} else {
		PrintHelpWindow('<br>Failed downloading, cannot generate pf rules', NULL, 'ERROR');
	}
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
	<br />
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
	<input type="checkbox" id="forceload" name="forceload" <?php echo filter_has_var(INPUT_POST, 'forceload') ? 'checked' : ''; ?> />
	<label for="forceload">Load with errors</label>
</form>

<p>&nbsp;</p>

<h2>Save rulebase</h2>
<br />
<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
	<input type="text" name="filename" id="filename" value="<?php echo $savefile; ?>" />
	<input type="submit" id="save" name="save" value="Save" />
	<label for="save">Save current working rules to file</label>
	<input type="checkbox" id="forcesave" name="forcesave" <?php echo filter_has_var(INPUT_POST, 'forcesave') ? 'checked' : ''; ?> />
	<label for="forcesave">Save with errors</label>
</form>

<p>&nbsp;</p>

<h2>Delete rulebase</h2>
<br />
<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
	<select id="deleteFilename" name="deleteFilename">
		<option value="" label=""></option>
		<?php
		foreach ($ruleFiles as $file) {
			?>
			<option value="<?php echo $file; ?>" label="<?php echo $file; ?>" <?php echo ($deleteFile == $file ? 'selected' : ''); ?>><?php echo $file; ?></option>
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
    <input type="hidden" name="max_file_size" value="300000" />
    Upload file: <input name="file" type="file" />
	<input type="checkbox" id="forceupload" name="forceupload" <?php echo filter_has_var(INPUT_POST, 'forceupload') ? 'checked' : ''; ?> />
	<label for="forceupload">Upload with errors</label>
</form>

<p>&nbsp;</p>

<h2>Download rulebase</h2>
<br />
<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
	<input type="submit" id="download" name="download" value="Download" />
	<label for="download">Download current working rulebase</label>
	<input type="checkbox" id="forcedownload" name="forcedownload" <?php echo filter_has_var(INPUT_POST, 'forcedownload') ? 'checked' : ''; ?> />
	<label for="forcedownload">Download with errors</label>
</form>
<?php
require_once($VIEW_PATH.'/footer.php');
?>
