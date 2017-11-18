<?php 
/*
 * Copyright (C) 2004-2017 Soner Tari
 *
 * This file is part of PFRE.
 *
 * PFRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PFRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PFRE.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once ('Rule.php');

class IncludeCest extends Rule
{
	protected $type= 'Include';
	protected $ruleNumber= 19;
	protected $lineNumber= 25;
	protected $sender= 'include';

	protected $origRule= 'include "/etc/pfre/include.conf" # Test';
	protected $expectedDispOrigRule= 'include /etc/pfre/include.conf Test e u d x';

	protected $modifiedRule= 'ERROR: Cannot generate rule';
	protected $expectedDispModifiedRule= 'include Test1 e u d x';

	function __construct()
	{
		parent::__construct();

		$this->revertedRule= $this->modifiedRule;
		$this->generatedRule= 'include "" # Test1';
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->selectOption('#file', '');
		$I->click('Apply');

		// Force generate, otherwise we get 'ERROR: Cannot generate rule' instead
		// Check #forcegenerate after clicking Apply, otherwise it is disabled (rule is not modified yet)
		$I->checkOption('#forcegenerate');

		$this->clickApplySeeResult($I, 'include "" # Test');

		$I->fillField('comment', 'Test1');
		$this->clickApplySeeResult($I, 'include "" # Test1');

		// Uncheck #forcegenerate, so the base methods get 'ERROR: Cannot generate rule'
		$I->uncheckOption('#forcegenerate');
		$I->click('Apply');
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		// Cannot select the orig file, so pick none
		$I->selectOption('#file', '');
		$I->click('Apply');

		$I->checkOption('#forcegenerate');

		$I->fillField('comment', 'Test');
		$this->clickApplySeeResult($I, 'include "" # Test');

		$I->uncheckOption('#forcegenerate');
		$I->click('Apply');
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->selectOption('#file', '');
		$I->fillField('comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->selectOption('#file', '');
		$I->fillField('comment', 'Test');
		$I->click('Apply');
	}
}
?>