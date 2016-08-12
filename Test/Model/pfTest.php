<?php
/* $pfre: RuleSetTest.php,v 1.2 2016/08/11 19:36:31 soner Exp $ */

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

namespace ModelTest;

use Model\RuleSet;

/// @todo Check why $MODEL_PATH is not defined in global space, and including bootstrap.php here does not define $MODEL_PATH either
$ROOT= dirname(dirname(dirname(__FILE__)));
$MODEL_PATH= $ROOT.'/Model';
require_once($MODEL_PATH.'/pf.php');

class pfTest extends \PHPUnit_Framework_TestCase
{
	function testGetPfRules()
	{
		global $TEST_ROOT_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/etc/pfre/pf.conf');

		$ruleStr= file_get_contents($TEST_ROOT_PATH . '/etc/pfre/pf.conf');
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$expected= json_encode($ruleSet);
		$actual= $Output;

		$this->assertTrue($result);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
	}

	function testGetPfRulesTmpFile()
	{
		global $TEST_ROOT_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/tmp/tmp.conf', TRUE);

		$ruleStr= file_get_contents($TEST_ROOT_PATH . '/tmp/tmp.conf');
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$expected= json_encode($ruleSet);
		$actual= $Output;

		$this->assertTrue($result);
		$this->assertJsonStringEqualsJsonString($expected, $actual);
	}

	function testGetPfRulesTmpFileNoTmpArg()
	{
		global $TEST_ROOT_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/tmp/tmp.conf');

		$expected= '';
		$actual= $Output;

		$this->assertFalse($result);
		$this->assertEquals($expected, $actual);
	}

	function testGetPfRulesNonExistentFile()
	{
		global $TEST_ROOT_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/etc/pfre/none.conf');

		$expected= '';
		$actual= $Output;

		$this->assertFalse($result);
		$this->assertEquals($expected, $actual);
	}

	function testGetPfRulesInvalidFilename()
	{
		global $TEST_ROOT_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/etc/pfre/pf$.conf');

		$expected= '';
		$actual= $Output;

		$this->assertFalse($result);
		$this->assertEquals($expected, $actual);
	}

	function testGetPfRulesInvalidLocation()
	{
		global $TEST_ROOT_PATH, $Output;

		$pf= new \Pf();
		$result= $pf->GetPfRules('/etc/invalid.conf');

		$expected= '';
		$actual= $Output;

		$this->assertFalse($result);
		$this->assertEquals($expected, $actual);
	}

	function testGetPfRuleFiles()
	{
		global $TEST_ROOT_PATH, $Output;

		$pf= new \Pf();
		$pf->GetPfRuleFiles();

		exec("ls -1 $TEST_ROOT_PATH/etc/pfre/", $output);

		$expected= implode("\n", $output);
		$actual= $Output;

		$this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($actual));
	}

	function testDeletePfRuleFile()
	{
		global $TEST_ROOT_PATH;

		$file= $TEST_ROOT_PATH . '/etc/pfre/delete.conf';

		$this->assertFileNotExists($file);

		file_put_contents($file, '', LOCK_EX);

		$this->assertFileExists($file);
		
		$pf= new \Pf();
		$result= $pf->DeletePfRuleFile($file);

		$this->assertTrue($result);
		$this->assertFileNotExists($file);
	}

	function testDeletePfRuleFileNonExistentFile()
	{
		global $TEST_ROOT_PATH;

		$file= $TEST_ROOT_PATH . '/etc/pfre/delete.conf';

		$this->assertFileNotExists($file);
		
		$pf= new \Pf();
		$result= $pf->DeletePfRuleFile($file);

		$this->assertFalse($result);
		$this->assertFileNotExists($file);
	}

	function testDeletePfRuleFileInvalidFilename()
	{
		global $TEST_ROOT_PATH;

		$file= $TEST_ROOT_PATH . '/etc/pfre/pf$.conf';

		$this->assertFileExists($file);
		
		$pf= new \Pf();
		$result= $pf->DeletePfRuleFile('pf$.conf');

		$this->assertFalse($result);
		$this->assertFileExists($file);
	}

	function testInstallPfRules()
	{
		global $TEST_ROOT_PATH;

		$srcFile= $TEST_ROOT_PATH . '/etc/pfre/pf.conf';

		$ruleStr= file_get_contents($srcFile);
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$destFile= $TEST_ROOT_PATH . '/etc/pf.conf';

		unlink($destFile);

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
		global $TEST_ROOT_PATH;

		$srcFile= $TEST_ROOT_PATH . '/etc/pfre/pf.conf';

		$ruleStr= file_get_contents($srcFile);
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$destFile= $TEST_ROOT_PATH . '/etc/pfre/install.conf';

		unlink($destFile);

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