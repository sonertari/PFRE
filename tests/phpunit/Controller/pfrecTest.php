<?php
/* $pfre: pfrecTest.php,v 1.1 2016/08/12 18:28:28 soner Exp $ */

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

namespace ControllerTest;

use Model\RuleSet;
use ModelTest\FilterTest;

/// @todo Check why $MODEL_PATH is not defined in global space, and including bootstrap.php here does not define $MODEL_PATH either
$SRC_ROOT= dirname(dirname(dirname(dirname(__FILE__)))) . '/src';
$MODEL_PATH= $SRC_ROOT . '/Model';
require_once($MODEL_PATH . '/model.php');

class pfrecTest extends \PHPUnit_Framework_TestCase
{
	private $pfrec= '';

	function __construct()
	{
		global $SRC_ROOT;

		/// @attention Need a leading php, because php locations differ on different OSs, hence shebang in pfrec.php does not help
		$this->pfrec= "php $SRC_ROOT/Controller/pfrec.php";

		parent::__construct();
	}

	function testGetFileCvsTag()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/test.php';

		$cmdline= $this->pfrec . " -t GetFileCvsTag '$file'";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		$contents= file_get_contents($file);

		$tag= '';
		$re= '/:\s+(.*\.php,v\s+[\d.:\/\s]+)\s+/';
		if (preg_match($re, $contents, $match)) {
			$tag= $match[1];
		}

		$expected= $tag;

		$this->assertEquals($expected, $actual);
	}

	function testSetLogLevel()
	{
		global $TEST_DIR_PATH;

		$expected= 'LOG_DEBUG';
		$cmdline= $this->pfrec . " -t SetLogLevel $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$LOG_LEVEL', ';');

		$this->assertEquals($expected, $actual);

		$expected= 'LOG_INFO';
		$cmdline= $this->pfrec . " -t SetLogLevel $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$LOG_LEVEL', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetHelpBox()
	{
		global $TEST_DIR_PATH;

		$expected= 'FALSE';
		$cmdline= $this->pfrec . " -t SetHelpBox $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$ShowHelpBox', ';');

		$this->assertEquals($expected, $actual);

		$expected= 'TRUE';
		$cmdline= $this->pfrec . " -t SetHelpBox $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$ShowHelpBox', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetSessionTimeout()
	{
		global $TEST_DIR_PATH;

		$expected= '123';
		$cmdline= $this->pfrec . " -t SetSessionTimeout $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$SessionTimeout', ';');

		$this->assertEquals($expected, $actual);

		$expected= '300';
		$cmdline= $this->pfrec . " -t SetSessionTimeout $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$SessionTimeout', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetForceHTTPs()
	{
		global $TEST_DIR_PATH;

		$expected= 'TRUE';
		$cmdline= $this->pfrec . " -t SetForceHTTPs $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$ForceHTTPs', ';');

		$this->assertEquals($expected, $actual);

		$expected= 'FALSE';
		$cmdline= $this->pfrec . " -t SetForceHTTPs $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$ForceHTTPs', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetMaxAnchorNesting()
	{
		global $TEST_DIR_PATH;

		$expected= '10';
		$cmdline= $this->pfrec . " -t SetMaxAnchorNesting $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$MaxAnchorNesting', ';');

		$this->assertEquals($expected, $actual);

		$expected= '2';
		$cmdline= $this->pfrec . " -t SetMaxAnchorNesting $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$MaxAnchorNesting', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetPfctlTimeout()
	{
		global $TEST_DIR_PATH;

		$expected= '10';
		$cmdline= $this->pfrec . " -t SetPfctlTimeout $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$PfctlTimeout', ';');

		$this->assertEquals($expected, $actual);

		$expected= '5';
		$cmdline= $this->pfrec . " -t SetPfctlTimeout $expected";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$PfctlTimeout', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testGetPfRules()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/etc/pfre/pf.conf';

		$cmdline= $this->pfrec . " -t GetPfRules '$file' 0 0";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		$ruleStr= file_get_contents($file);
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$expected= json_encode($ruleSet);

		$this->assertJsonStringEqualsJsonString($expected, $actual);
	}

	function testGetPfRuleFiles()
	{
		global $TEST_DIR_PATH;

		$cmdline= $this->pfrec . " -t GetPfRuleFiles";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		exec("ls -1 $TEST_DIR_PATH/etc/pfre/", $output);

		$expected= implode("\n", $output);

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
		
		$cmdline= $this->pfrec . " -t DeletePfRuleFile $file";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);
		$this->assertFileNotExists($file);
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

		$json= json_encode($ruleSet->rules);
		$cmdline= $this->pfrec . " -t InstallPfRules '$json' $destFile 0";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$this->assertFileExists($destFile);
		$this->assertFileEquals($srcFile, $destFile);

		unlink($destFile);
	}
	
	function testGeneratePfRule()
	{
		global $TEST_PATH;

		require_once ($TEST_PATH . '/Model/lib/FilterTest.php');

		$test= new FilterTest();

		$ruleDef= array(
			'cat' => 'Filter',
			'rule' => $test->rule,
			);

		$json= json_encode($ruleDef);
		$cmdline= $this->pfrec . " -t GeneratePfRule '$json' 0";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		$this->assertEquals($test->out, $actual);
	}
	
	function testGeneratePfRules()
	{
		global $TEST_PATH;

		require_once ($TEST_PATH . '/Model/lib/FilterTest.php');

		$test= new FilterTest();

		$rulesArray[]= array(
			'cat' => 'Filter',
			'rule' => $test->rule,
			);

		$json= json_encode($rulesArray);
		$cmdline= $this->pfrec . " -t GeneratePfRules '$json'";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		$this->assertEquals($test->out, $actual);
	}
	
	function testGeneratePfRulesLines()
	{
		global $TEST_PATH;

		require_once ($TEST_PATH . '/Model/lib/FilterTest.php');

		$test= new FilterTest();

		$rulesArray[]= array(
			'cat' => 'Filter',
			'rule' => $test->rule,
			);

		$json= json_encode($rulesArray);
		$cmdline= $this->pfrec . " -t GeneratePfRules '$json' 1";

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		$this->assertEquals('   0: ' . $test->out . "   1: \n", $actual);
	}
}
?>