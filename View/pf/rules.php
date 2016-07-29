<?php
/* $pfre: rules.php,v 1.11 2016/07/27 15:08:56 soner Exp $ */

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

$ruleCategoryNames = array(
    'filter' => 'Filter',
    'anchor' => 'Anchor',
    'macro' => 'Macro',
    'table' => 'Table',
    'nat' => 'Nat',
    'queue' => 'Queue',
    'scrub' => 'Scrub',
    'options' => 'Options',
    'timeoutoptions' => 'Timeout Options',
    'loadanchor' => 'Load Anchor',
    'include' => 'Include',
    'comment' => 'Comment',
    'blank' => 'Blank Line',
);

if (isset($_GET['sender']) && array_key_exists($_GET['sender'], $ruleCategoryNames)) {
    $edit= $_GET['sender'];
	$rulenumber= $_GET['rulenumber'];
	
	if (isset($_GET['action']) && $_GET['action'] == 'add') {
		// Get action has precedence
		// Accept only add action here
		$action= 'add';
	} elseif (isset($_POST['state']) && $_POST['state'] == 'create') {
		// Post action is used while saving new rules, create is the next state after add
		// Accept only create action here
		$action= 'create';
	} else {
		// Default action is edit, which takes care of unacceptable actions too
		$action= 'edit';
	}
}

if (isset($_POST['add']) && $_POST['rulenumber'] != "") {
    $edit= $_POST['category'];
	$rulenumber= $_POST['rulenumber'];
	$action= 'add';
}

if (isset($edit)) {
	// Assume a new rule requested, if the page is submitted on the address line with a non-existing rule number
	if ($action == 'edit' && !array_key_exists($rulenumber, $View->RuleSet->rules)) {
		$action= 'add';
	}

    require ('edit.php');
    exit();
}

if (isset($_GET['up'])) {
    $View->RuleSet->up($_GET['up']);
}

if (isset($_GET['down'])) {
    $View->RuleSet->down($_GET['down']);
}

if (isset($_GET['del'])) {
    $View->RuleSet->del($_GET['del']);
}

if ($_POST['delete']) {
	$View->RuleSet->deleteRules();
	PrintHelpWindow('Rulebase deleted');
}

/// @attention Reduce multiline comments to single line, so that reported and actual rule numbers match
$rulesStr.= $View->RuleSet->generate(FALSE, NULL, TRUE, TRUE);
$View->Controller($Output, 'TestPfRules', serialize(explode('\n', $rulesStr)));

require_once($VIEW_PATH.'/header.php');
?>
<div id="main">
    <fieldset>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="category">Add new</label>
            <select id="category" name="category">
                <?php
                foreach ($ruleCategoryNames as $category => $name) {
                    ?>
                    <option value="<?php echo $category; ?>" label="<?php echo $category; ?>" <?php echo ($_POST['category'] == $category ? 'selected' : ''); ?>><?php echo $name; ?></option>
                    <?php
                }
                ?>
            </select>
            <label for="rulenumber">rule as rule number:</label>
            <input type="text" name="rulenumber" id="rulenumber" size="5" value="<?php echo $View->RuleSet->nextRuleNumber(); ?>" />
            <input type="submit" name="add" value="Add" />
			<input type="submit" id="delete" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete the rulebase?')"/>
			<label for="delete">Delete current working rulebase</label><br />
        </form>
    </fieldset>
	<?php
	$printFileName= $View->RuleSet->filename == '/etc/pf.conf' || dirname($View->RuleSet->filename) == '/etc/pfre';
	echo _('Rule file') . ': ' . ($printFileName ? $View->RuleSet->filename : '');
	?>
    <table>
        <tr>
            <th>No</th>
            <th>Type</th>
            <th colspan="12">Rule</th>
            <th>Comment</th>
            <th>Edit</th>
        </tr>
        <?php
        $rulenumber = 0;
        $count = count($View->RuleSet->rules) - 1;
        $colorcount = 0;
        foreach ($View->RuleSet->rules as $rule) {
            $rule->display($rulenumber++, $count, $colorcount++ % 2 ? ' class="oddline"' : '');
        }
        ?>
    </table>
</div>
<?php
require_once($VIEW_PATH . '/footer.php');
?>
