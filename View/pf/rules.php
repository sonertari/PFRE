<?php
/* $pfre: rules.php,v 1.13 2016/08/03 19:02:48 soner Exp $ */

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
    'antispoof' => 'Antispoof',
    'anchor' => 'Anchor',
    'macro' => 'Macro',
    'table' => 'Table',
    'afto' => 'Af Translate',
    'natto' => 'Nat',
    'binatto' => 'Binat',
    'divertto' => 'Divert',
    'divertpacket' => 'Divert Packet',
    'rdrto' => 'Redirect',
    'route' => 'Route',
    'queue' => 'Queue',
    'scrub' => 'Scrub',
    'option' => 'Option',
    'timeout' => 'Timeout',
    'limit' => 'Limit',
    'state' => 'State Defaults',
    'loadanchor' => 'Load Anchor',
    'include' => 'Include',
    'comment' => 'Comment',
    'blank' => 'Blank Line',
);

$ruleType2Class= array(
    'filter' => 'Filter',
    'antispoof' => 'Antispoof',
    'anchor' => 'Anchor',
    'macro' => 'Macro',
    'table' => 'Table',
    'afto' => 'AfTo',
    'natto' => 'NatTo',
    'binatto' => 'BinatTo',
    'divertto' => 'DivertTo',
    'divertpacket' => 'DivertPacket',
    'rdrto' => 'RdrTo',
    'route' => 'Route',
    'queue' => 'Queue',
    'scrub' => 'Scrub',
    'option' => 'Option',
    'timeout' => 'Timeout',
    'limit' => 'Limit',
    'state' => 'State',
    'loadanchor' => 'LoadAnchor',
    'include' => '_Include',
    'comment' => 'Comment',
    'blank' => 'Blank',
);

if (filter_has_var(INPUT_GET, 'sender') && array_key_exists(filter_input(INPUT_GET, 'sender'), $ruleCategoryNames)) {
    $edit= filter_input(INPUT_GET, 'sender');
	$rulenumber= filter_input(INPUT_GET, 'rulenumber');
	
	if (filter_has_var(INPUT_GET, 'action') && filter_input(INPUT_GET, 'action') == 'add') {
		// Get action has precedence
		// Accept only add action here
		$action= 'add';
	} elseif (filter_has_var(INPUT_POST, 'state') && filter_input(INPUT_POST, 'state') == 'create') {
		// Post action is used while saving new rules, create is the next state after add
		// Accept only create action here
		$action= 'create';
	} else {
		// Default action is edit, which takes care of unacceptable actions too
		$action= 'edit';
	}
}

if (filter_has_var(INPUT_POST, 'rulenumber') && filter_input(INPUT_POST, 'rulenumber') !== '') {
	if (filter_has_var(INPUT_POST, 'add')) {
		$edit= filter_input(INPUT_POST, 'category');
		$rulenumber= filter_input(INPUT_POST, 'rulenumber');
		$action= 'add';
	} elseif (filter_has_var(INPUT_POST, 'edit')) {
		$rulenumber= filter_input(INPUT_POST, 'rulenumber');
		if (array_key_exists($rulenumber, $View->RuleSet->rules)) {
			$edit= array_search($View->RuleSet->rules[$rulenumber]->cat, $ruleType2Class);
		} else {
			// Will add a new rule of category $edit otherwise
			$edit= filter_input(INPUT_POST, 'category');
		}
		$action= 'edit';
	}
}

if (isset($edit)) {
	// Assume a new rule requested, if the page is submitted on the address line with a non-existing rule number
	if ($action == 'edit' && !array_key_exists($rulenumber, $View->RuleSet->rules)) {
		$action= 'add';
	}

    require ('edit.php');
    exit();
}

if (filter_has_var(INPUT_GET, 'up')) {
    $View->RuleSet->up(filter_input(INPUT_GET, 'up'));
}

if (filter_has_var(INPUT_GET, 'down')) {
    $View->RuleSet->down(filter_input(INPUT_GET, 'down'));
}

if (filter_has_var(INPUT_GET, 'del')) {
    $View->RuleSet->del(filter_input(INPUT_GET, 'del'));
}

if (filter_has_var(INPUT_POST, 'move')) {
	if (filter_has_var(INPUT_POST, 'rulenumber') && filter_input(INPUT_POST, 'rulenumber') !== '' &&
		filter_has_var(INPUT_POST, 'moveto') && filter_input(INPUT_POST, 'moveto') !== '') {
		$View->RuleSet->move(filter_input(INPUT_POST, 'rulenumber'), filter_input(INPUT_POST, 'moveto'));
	}
}

if (filter_has_var(INPUT_POST, 'delete')) {
    $View->RuleSet->del(filter_input(INPUT_POST, 'rulenumber'));
}

if (filter_has_var(INPUT_POST, 'delete-all')) {
	$View->RuleSet->deleteRules();
	PrintHelpWindow('Rulebase deleted');
}

/// @attention Reduce multiline comments to single line, so that reported and actual rule numbers match
$View->Controller($Output, 'TestPfRules', json_encode($View->RuleSet->rules));

require_once($VIEW_PATH.'/header.php');
?>
<div id="main">
    <fieldset>
        <form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
            <label for="category">Add new</label>
            <select id="category" name="category">
                <?php
                foreach ($ruleCategoryNames as $category => $name) {
                    ?>
                    <option value="<?php echo $category; ?>" label="<?php echo $category; ?>" <?php echo (filter_input(INPUT_POST, 'category') == $category ? 'selected' : ''); ?>><?php echo $name; ?></option>
                    <?php
                }
                ?>
            </select>
            <label for="rulenumber">rule as rule number:</label>
            <input type="text" name="rulenumber" id="rulenumber" size="5" value="<?php echo $View->RuleSet->nextRuleNumber(); ?>" placeholder="number" />
            <input type="submit" name="add" value="Add" />
            <input type="submit" name="edit" value="Edit" />
            <input type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete the rule?')"/>
            <input type="text" name="moveto" id="moveto" size="5" value="<?php echo filter_input(INPUT_POST, 'moveto') ?>" placeholder="move to" />
            <input type="submit" name="move" value="Move" />
			<input type="submit" id="delete-all" name="delete-all" value="Delete All" onclick="return confirm('Are you sure you want to delete the entire rulebase?')"/>
			<label for="delete-all">Delete current working rulebase</label><br />
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
        foreach ($View->RuleSet->rules as $rule) {
            $rule->display($rulenumber++, $count);
        }
        ?>
    </table>
</div>
<?php
require_once($VIEW_PATH . '/footer.php');
?>
