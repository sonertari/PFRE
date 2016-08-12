<?php
/* $pfre: modelTest.php,v 1.2 2016/08/12 14:18:43 soner Exp $ */

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

/// @todo Check why $MODEL_PATH is not defined in global space, and including bootstrap.php here does not define $MODEL_PATH either
$SRC_ROOT= dirname(dirname(dirname(__FILE__))) . '/src';
$MODEL_PATH= $SRC_ROOT . '/Model';
require_once($MODEL_PATH.'/model.php');

class modelTest extends \PHPUnit_Framework_TestCase
{
	function testGetFileCvsTag()
	{
		global $TEST_DIR_PATH, $Output;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/test.php';

		$model= new \Model();
		$result= $model->GetFileCvsTag($file);

		$contents= file_get_contents($file);

		$tag= '';
		$re= '/:\s+(.*\.php,v\s+[\d.:\/\s]+)\s+/';
		if (preg_match($re, $contents, $match)) {
			$tag= $match[1];
		}

		$expected= $tag;
		$actual= $Output;

		$this->assertTrue($result);
		$this->assertEquals($expected, $actual);
	}

	function testSetLogLevel()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();

		$expected= 'LOG_DEBUG';
		$result= $model->SetLogLevel($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$LOG_LEVEL', ';');

		$this->assertEquals($expected, $actual);

		$expected= 'LOG_INFO';
		$result= $model->SetLogLevel($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$LOG_LEVEL', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetHelpBox()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();

		$expected= 'FALSE';
		$result= $model->SetHelpBox($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$ShowHelpBox', ';');

		$this->assertEquals($expected, $actual);

		$expected= 'TRUE';
		$result= $model->SetHelpBox($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$ShowHelpBox', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetSessionTimeout()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();

		$expected= '123';
		$result= $model->SetSessionTimeout($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$SessionTimeout', ';');

		$this->assertEquals($expected, $actual);

		$expected= '300';
		$result= $model->SetSessionTimeout($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$SessionTimeout', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetSessionTimeoutInvalidFixed()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();

		$result= $model->SetSessionTimeout('0');
		
		$this->assertTrue($result);

		$expected= '10';
		$actual= $model->GetNVP($file, '\$SessionTimeout', ';');

		$this->assertEquals($expected, $actual);

		$expected= '300';
		$result= $model->SetSessionTimeout($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$SessionTimeout', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetForceHTTPs()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();

		$expected= 'TRUE';
		$result= $model->SetForceHTTPs($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$ForceHTTPs', ';');

		$this->assertEquals($expected, $actual);

		$expected= 'FALSE';
		$result= $model->SetForceHTTPs($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$ForceHTTPs', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetMaxAnchorNesting()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();

		$expected= '10';
		$result= $model->SetMaxAnchorNesting($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$MaxAnchorNesting', ';');

		$this->assertEquals($expected, $actual);

		$expected= '2';
		$result= $model->SetMaxAnchorNesting($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$MaxAnchorNesting', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetPfctlTimeout()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();

		$expected= '10';
		$result= $model->SetPfctlTimeout($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$PfctlTimeout', ';');

		$this->assertEquals($expected, $actual);

		$expected= '5';
		$result= $model->SetPfctlTimeout($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$PfctlTimeout', ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}
}
?>