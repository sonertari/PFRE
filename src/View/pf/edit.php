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

/** @file
 * Edit page.
 */

require_once ('pf.php');

if (isset($edit) && array_key_exists($edit, $ruleType2Class)) {
	$cat= $ruleType2Class[$edit];

	$View->RuleSet->setupEditSession($cat, $action, $ruleNumber);

	$ruleObj= &$_SESSION['edit']['object'];
	$ruleObj->input();

	$testResult= $View->RuleSet->test($ruleNumber, $ruleObj);
	$View->RuleSet->cancel();
	$View->RuleSet->save($action, $ruleNumber, $ruleObj, $testResult);

	$modified= TRUE;
	if ($action != 'create') {
		$modified= $View->RuleSet->isModified($ruleNumber, $ruleObj);
	}

	$force= 0;
	if (filter_has_var(INPUT_POST, 'forcegenerate')) {
		$force= 1;
	}

	$generateResult= $View->Controller($Output, 'GeneratePfRule', json_encode($ruleObj), $ruleNumber, $force);
	if ($generateResult || $force) {
		/// @attention Inline anchor rules are multi-line, hence implode.
		$ruleStr= implode("\n", $Output);
	} else {
		$ruleStr= _NOTICE('ERROR') . ': ' . _NOTICE('Cannot generate rule');
	}

	require_once($VIEW_PATH.'/header.php');
	/// @attention $ruleStr is passed as a global var.
	$ruleObj->edit($ruleNumber, $modified, $testResult, $generateResult, $action);
	require_once($VIEW_PATH.'/footer.php');

	exit;
}
?>
