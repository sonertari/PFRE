<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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

namespace ControllerTest;

use Model\RuleSet;
use ModelTest\FilterTest;

/// @todo Check why $MODEL_PATH is not defined in global space, and including bootstrap.php here does not define $MODEL_PATH either
$SRC_ROOT= dirname(dirname(dirname(dirname(__FILE__)))) . '/src';
$MODEL_PATH= $SRC_ROOT . '/Model';
require_once($MODEL_PATH . '/model.php');

class ctlrTest extends \PHPUnit_Framework_TestCase
{
	private $ctlr= '';

	function __construct()
	{
		global $SRC_ROOT;

		/// @attention Need a leading php, because php locations differ on different OSs, hence shebang in ctlr.php does not help
		$this->ctlr= "php $SRC_ROOT/Controller/ctlr.php";

		parent::__construct();
	}

	function testSetLogLevel()
	{
		global $TEST_DIR_PATH;

		$expected= 'LOG_DEBUG';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetLogLevel', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$LOG_LEVEL', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= 'LOG_INFO';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetLogLevel', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$LOG_LEVEL', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetHelpBox()
	{
		global $TEST_DIR_PATH;

		$expected= 'FALSE';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetHelpBox', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$ShowHelpBox', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= 'TRUE';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetHelpBox', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$ShowHelpBox', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetSessionTimeout()
	{
		global $TEST_DIR_PATH;

		$expected= '123';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetSessionTimeout', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$SessionTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= '300';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetSessionTimeout', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$SessionTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetForceHTTPs()
	{
		global $TEST_DIR_PATH;

		$expected= 'TRUE';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetForceHTTPs', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$ForceHTTPs', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= 'FALSE';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetForceHTTPs', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$ForceHTTPs', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetMaxAnchorNesting()
	{
		global $TEST_DIR_PATH;

		$expected= '10';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetMaxAnchorNesting', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$MaxAnchorNesting', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= '2';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetMaxAnchorNesting', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$MaxAnchorNesting', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetPfctlTimeout()
	{
		global $TEST_DIR_PATH;

		$expected= '10';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetPfctlTimeout', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();
		$actual= $model->GetNVP($file, '\$PfctlTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= '5';
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'SetPfctlTimeout', $expected], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= $model->GetNVP($file, '\$PfctlTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testGetRules()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/etc/pfre/pf.conf';

		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'GetRules', $file, 0, 0], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		$ruleStr= file_get_contents($file);
		$ruleSet= new RuleSet();
		$ruleSet->parse($ruleStr);

		$expected= json_encode($ruleSet);

		$this->assertJsonStringEqualsJsonString($expected, $actual);
	}

	function testGetRuleFiles()
	{
		global $TEST_DIR_PATH;

		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'GetRuleFiles'], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= explode("\n", json_decode($outputArray[0])[0]);
		sort($actual);
		$actual= implode("\n", $actual);

		exec("ls -1 $TEST_DIR_PATH/etc/pfre/", $output);

		$expected= $output;
		sort($expected);
		$expected= implode("\n", $expected);

		$this->assertEquals($expected, $actual);
	}

	function testDeleteRuleFile()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/etc/pfre/delete.conf';

		if (file_exists($file)) {
			unlink($file);
		}

		file_put_contents($file, '', LOCK_EX);

		$this->assertFileExists($file);
		
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'DeleteRuleFile', $file], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);
		$this->assertFileNotExists($file);
	}

	function testInstallRulesInstallConf()
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
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'InstallRules', $json, $destFile, 0], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$this->assertFileExists($destFile);
		$this->assertFileEquals($srcFile, $destFile);

		unlink($destFile);
	}
	
	function testGenerateRule()
	{
		global $TEST_PATH;

		require_once ($TEST_PATH . '/Model/lib/FilterTest.php');

		$test= new FilterTest();

		$ruleDef= array(
			'cat' => 'Filter',
			'rule' => $test->rule,
			);

		$json= json_encode($ruleDef);
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'GenerateRule', $json, 0], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		$this->assertEquals($test->out, $actual);
	}
	
	function testGenerateRules()
	{
		global $TEST_PATH;

		require_once ($TEST_PATH . '/Model/lib/FilterTest.php');

		$test= new FilterTest();

		$rulesArray[]= array(
			'cat' => 'Filter',
			'rule' => $test->rule,
			);

		$json= json_encode($rulesArray);
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'GenerateRules', $json], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		$this->assertEquals($test->out, $actual);
	}
	
	function testGenerateRulesLines()
	{
		global $TEST_PATH;

		require_once ($TEST_PATH . '/Model/lib/FilterTest.php');

		$test= new FilterTest();

		$rulesArray[]= array(
			'cat' => 'Filter',
			'rule' => $test->rule,
			);

		$json= json_encode($rulesArray);
		$cmdline= "$this->ctlr -t ".escapeshellarg(json_encode(['en_EN', 'GenerateRules', $json, 1], JSON_UNESCAPED_SLASHES));

		exec($cmdline, $outputArray, $retval);

		$this->assertEquals(0, $retval);

		$actual= json_decode($outputArray[0])[0];

		$this->assertEquals('   0: ' . $test->out . "   1: \n", $actual);
	}
}
?>
