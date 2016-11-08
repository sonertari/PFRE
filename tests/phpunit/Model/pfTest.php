<?php
/*
 * Copyright (C) 2004-2016 Soner Tari
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

namespace ModelTest;

use Model\RuleSet;

/// @todo Check why $MODEL_PATH is not defined in global space, and including bootstrap.php here does not define $MODEL_PATH either
$SRC_ROOT= dirname(dirname(dirname(dirname(__FILE__)))) . '/src';
$MODEL_PATH= $SRC_ROOT . '/Model';
require_once($MODEL_PATH.'/pf.php');

class pfTest extends \PHPUnit_Framework_TestCase
{
	function testGetPfRules()
	{
		global $TEST_DIR_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/etc/pfre/pf.conf');

		$ruleStr= file_get_contents($TEST_DIR_PATH . '/etc/pfre/pf.conf');
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$expected= json_encode($ruleSet);
		$actual= $Output;

		$this->assertTrue($result);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
	}

	function testGetPfRulesTmpFile()
	{
		global $TEST_DIR_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/tmp/tmp.conf', TRUE);

		$ruleStr= file_get_contents($TEST_DIR_PATH . '/tmp/tmp.conf');
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$expected= json_encode($ruleSet);
		$actual= $Output;

		$this->assertTrue($result);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
	}

	function testGetPfRulesTmpFileNoTmpArg()
	{
		global $TEST_DIR_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/tmp/tmp.conf');

		$expected= '';
		$actual= $Output;

		$this->assertFalse($result);
		$this->assertEquals($expected, $actual);
	}

	function testGetPfRulesNonExistentFile()
	{
		global $TEST_DIR_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/etc/pfre/none.conf');

		$expected= '';
		$actual= $Output;

		$this->assertFalse($result);
		$this->assertEquals($expected, $actual);
	}

	function testGetPfRulesInvalidFilename()
	{
		global $TEST_DIR_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/etc/pfre/pf$.conf');

		$expected= '';
		$actual= $Output;

		$this->assertFalse($result);
		$this->assertEquals($expected, $actual);
	}

	function testGetPfRulesInvalidLocation()
	{
		global $TEST_DIR_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/etc/invalid.conf');

		$expected= '';
		$actual= $Output;

		$this->assertFalse($result);
		$this->assertEquals($expected, $actual);
	}

	function testGetPfRuleFiles()
	{
		global $TEST_DIR_PATH, $Output;

		$pf= new \Pf();
		$pf->GetPfRuleFiles();

		exec("ls -1 $TEST_DIR_PATH/etc/pfre/", $output);

		$expected= implode("\n", $output);
		$actual= $Output;

		$this->assertEquals($expected, $actual);
	}

	function testDeletePfRuleFile()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/etc/pfre/delete.conf';

		if (file_exists($file)) {
			unlink($file);
		}

		file_put_contents($file, '', LOCK_EX);

		$this->assertFileExists($file);
		
		$pf= new \Pf();
		$result= $pf->DeletePfRuleFile($file);

		$this->assertTrue($result);
		$this->assertFileNotExists($file);
	}

	function testDeletePfRuleFileNonExistentFile()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/etc/pfre/delete.conf';

		if (file_exists($file)) {
			unlink($file);
		}

		$this->assertFileNotExists($file);
		
		$pf= new \Pf();
		$result= $pf->DeletePfRuleFile($file);

		$this->assertFalse($result);
		$this->assertFileNotExists($file);
	}

	function testDeletePfRuleFileInvalidFilename()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/etc/pfre/pf$.conf';

		$this->assertFileExists($file);
		
		$pf= new \Pf();
		$result= $pf->DeletePfRuleFile('pf$.conf');

		$this->assertFalse($result);
		$this->assertFileExists($file);
	}

	function testInstallPfRules()
	{
		global $TEST_DIR_PATH;

		$srcFile= $TEST_DIR_PATH . '/etc/pfre/pf.conf';

		$ruleStr= file_get_contents($srcFile);
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$destFile= $TEST_DIR_PATH . '/etc/pf.conf';

		if (file_exists($destFile)) {
			unlink($destFile);
		}

		$this->assertFileNotExists($destFile);

		$pf= new \Pf();
		$result= $pf->InstallPfRules(json_encode($ruleSet->rules), NULL, FALSE);

		$this->assertTrue($result);
		$this->assertFileExists($destFile);
		$this->assertFileEquals($srcFile, $destFile);

		unlink($destFile);
	}

	function testInstallPfRulesInstallConf()
	{
		global $TEST_DIR_PATH;

		$srcFile= $TEST_DIR_PATH . '/etc/pfre/pf.conf';

		$ruleStr= file_get_contents($srcFile);
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$destFile= $TEST_DIR_PATH . '/etc/pfre/install.conf';

		if (file_exists($destFile)) {
			unlink($destFile);
		}

		$this->assertFileNotExists($destFile);

		$pf= new \Pf();
		$result= $pf->InstallPfRules(json_encode($ruleSet->rules), $destFile, FALSE);

		$this->assertTrue($result);
		$this->assertFileExists($destFile);
		$this->assertFileEquals($srcFile, $destFile);

		unlink($destFile);
	}

	function testValidateFilename()
	{
		$filename= '/etc/pfre/pf.conf';

		$pf= new \Pf();
		$result= $pf->ValidateFilename($filename);

		$this->assertTrue($result);
		$this->assertEquals('pf.conf', $filename);
	}

	function testValidateFilenameInvalid()
	{
		$filename= '/etc/pfre/pf$.conf';

		$pf= new \Pf();
		$result= $pf->ValidateFilename($filename);

		$this->assertFalse($result);
		$this->assertEquals('pf$.conf', $filename);
	}
	
	function testGeneratePfRule()
	{
		global $TEST_PATH, $Output;

		require_once ($TEST_PATH . '/Model/lib/FilterTest.php');

		$test= new FilterTest();

		$ruleDef= array(
			'cat' => 'Filter',
			'rule' => $test->rule,
			);

		$pf= new \Pf();
		$result= $pf->GeneratePfRule(json_encode($ruleDef), 0);

		$this->assertTrue($result);
		$this->assertEquals($test->out, $Output);
	}
	
	function testGeneratePfRules()
	{
		global $TEST_PATH, $Output;

		require_once ($TEST_PATH . '/Model/lib/FilterTest.php');

		$test= new FilterTest();

		$rulesArray[]= array(
			'cat' => 'Filter',
			'rule' => $test->rule,
			);

		$pf= new \Pf();
		$result= $pf->GeneratePfRules(json_encode($rulesArray));

		$this->assertTrue($result);
		$this->assertEquals($test->out, $Output);
	}
	
	function testGeneratePfRulesLines()
	{
		global $TEST_PATH, $Output;

		require_once ($TEST_PATH . '/Model/lib/FilterTest.php');

		$test= new FilterTest();

		$rulesArray[]= array(
			'cat' => 'Filter',
			'rule' => $test->rule,
			);

		$pf= new \Pf();
		$result= $pf->GeneratePfRules(json_encode($rulesArray), TRUE);

		$this->assertTrue($result);
		$this->assertEquals('   0: ' . $test->out . "   1: \n", $Output);
	}
}
?>