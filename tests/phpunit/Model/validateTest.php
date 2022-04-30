<?php
/*
 * Copyright (C) 2004-2022 Soner Tari
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

class validateTest extends \PHPUnit_Framework_TestCase
{
	function runTests($re, $pass, $fail, $dollar= TRUE, $backtick= TRUE) {
		$result= TRUE;

		foreach ($pass as $test) {
			$result&= preg_match("/$re/", $test) == 1;
		}

		foreach ($fail as $test) {
			$result&= preg_match("/$re/", $test) == 0;
		}
		
		if ($dollar) {
			$result&= preg_match("/$re/", '$') == 0;
		}

		if ($backtick) {
			$result&= preg_match("/$re/", '`') == 0;
		}

		/// @attention Make sure the return value is bool, not 0/1, & op may not produce bool result
		return (bool)$result;
	}

	function testBOOL() {
		$this->assertTrue(
			$this->runTests(
				RE_BOOL,
				array(
					'0',
					'1',
					),
				array(
					'',
					'01',
					)
				)
			);
	}

	function testNAME() {
		$this->assertTrue(
			$this->runTests(
				RE_NAME,
				array(
					'a0_.-',
					'',
					),
				array(
					str_repeat('0', 51),
					)
				)
			);
	}

	function testNUM() {
		$this->assertTrue(
			$this->runTests(
				RE_NUM,
				array(
					'0123',
					),
				array(
					'',
					'012345678901234567890',
					)
				)
			);
	}

	function testSHA1() {
		$this->assertTrue(
			$this->runTests(
				RE_SHA1,
				array(
					'6df73cc169278dd6daab5fe7d6cacb1fed537131',
					),
				array(
					'',
					'01234567890123456789012345678901234567890',
					)
				)
			);
	}

	function testID() {
		$this->assertTrue(
			$this->runTests(
				RE_ID,
				array(
					'a0_-',
					str_repeat('0', 50),
					),
				array(
					'',
					str_repeat('0', 51),
					)
				)
			);
	}

	function testMACRO_VAR() {
		global $RE_MACRO_VAR;

		$this->assertTrue(
			$this->runTests(
				"^$RE_MACRO_VAR$",
				array(
					'$a0',
					'$01234567890123456789012345678901234567890123456789',
					),
				array(
					'',
					'a0',
					'a$0', // Allow $ only at the start
					'$012345678901234567890123456789012345678901234567890',
					)
				)
			);
	}

	function testMACRO_VALUE() {
		$this->assertTrue(
			$this->runTests(
				RE_MACRO_VALUE,
				array(
					'a0_-./*',
					'$a0_-./*',
					'',
					),
				array(
					str_repeat('0', 51),
					'a$0', // Allow $ only at the start
					),
				FALSE
				)
			);
	}

	function testIF() {
		$this->assertTrue(
			$this->runTests(
				RE_IF,
				array(
					'a0',
					'$a0',
					'$a0:0',
					'$a0:broadcast',
					'$a0:network',
					'$a0:peer',
					'01234567890123456789', // $RE_IF_NAME
					'$01234567890123456789012345678901234567890123456789', // $RE_MACRO_VAR
					),
				array(
					'',
					'$a0:test',
					'$a0 : peer',
					'012345678901234567890', // $RE_IF_NAME
					'$012345678901234567890123456789012345678901234567890', // $RE_MACRO_VAR
					'a$0', // Allow $ only at the start
					)
				)
			);
	}

	function testIFSPEC() {
		$this->assertTrue(
			$this->runTests(
				RE_IFSPEC,
				array(
					'(a0)',
					'( $a0 )',
					'(  $a0:0  )',
					'( $a0:broadcast)',
					'($a0:network )',
					'( $a0:peer )',
					'(01234567890123456789)', // $RE_IF_NAME
					'($01234567890123456789012345678901234567890123456789)', // $RE_MACRO_VAR
					),
				array(
					'()',
					'($)',
					'($a0:test)',
					'($a0 : peer)',
					'(012345678901234567890)', // $RE_IF_NAME
					'($012345678901234567890123456789012345678901234567890)', // $RE_MACRO_VAR
					'(a$0)', // Allow $ only at the start
					)
				)
			);
	}

	function testPROTOSPEC() {
		$this->assertTrue(
			$this->runTests(
				RE_PROTOSPEC,
				array(
					'a0-',
					'$a0',
					str_repeat('0', 50), // $RE_PROTO
					'$01234567890123456789012345678901234567890123456789', // $RE_MACRO_VAR
					),
				array(
					'',
					str_repeat('0', 51), // $RE_PROTO
					'$012345678901234567890123456789012345678901234567890', // $RE_MACRO_VAR
					'a$0', // Allow $ only at the start
					)
				)
			);
	}

	function testAF() {
		$this->assertTrue(
			$this->runTests(
				RE_AF,
				array(
					'inet',
					'inet6',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testDIRECTION() {
		$this->assertTrue(
			$this->runTests(
				RE_DIRECTION,
				array(
					'in',
					'out',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testIP() {
		global $RE_IP;

		$this->assertTrue(
			$this->runTests(
				"^$RE_IP$",
				array(
					'0.0.0.0',
					'111.222.333.444',
					),
				array(
					'',
					'a0',
					'1111.222.333.444',
					'111.2222.333.444',
					'111.222.3333.444',
					'111.222.333.4444',
					'111.222.333.444.5',
					)
				)
			);
	}

	function testIP_RANGE() {
		global $RE_IP_RANGE;

		$this->assertTrue(
			$this->runTests(
				"^$RE_IP_RANGE$",
				array(
					'1.2.3.4 - 111.222.333.444',
					),
				array(
					'',
					'a0',
					// pfctl gets stuck if there are no spaces around the dash -
					'1.2.3.4-111.222.333.444',
					)
				)
			);
	}

	function testIP6() {
		global $RE_IP6;

		$this->assertTrue(
			$this->runTests(
				"^$RE_IP6$",
				array(
					'64:ff9b::/96',
					),
				array(
					'',
					str_repeat('0', 51),
					)
				)
			);
	}

	function testHOSTNAME() {
		global $RE_HOSTNAME;

		$this->assertTrue(
			$this->runTests(
				"^$RE_HOSTNAME$",
				array(
					'a0_./',
					str_repeat('0', 100),
					),
				array(
					'',
					str_repeat('0', 101),
					)
				)
			);
	}

	function testADDRESS_KEYWORDS() {
		global $RE_ADDRESS_KEYWORDS;

		$this->assertTrue(
			$this->runTests(
				"^$RE_ADDRESS_KEYWORDS$",
				array(
					'any',
					'no-route',
					'self',
					'urpf-failed',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testWEIGHT() {
		global $RE_WEIGHT;

		$this->assertTrue(
			$this->runTests(
				"^$RE_WEIGHT$",
				array(
					'',
					' weight 1',
					'  weight  1',
					' weight 12345',
					),
				array(
					'weight',
					'weight 1',
					' weight1',
					' weight 1 ',
					' weight 123456',
					)
				)
			);
	}

	function testADDRESS_BASE() {
		global $RE_ADDRESS_BASE;

		$this->assertTrue(
			$this->runTests(
				"^$RE_ADDRESS_BASE$",
				array(
					'a0',
					'(a0)',
					'comixwall.org',
					'self',
					'1.2.3.4',
					'1.2.3.4 - 111.222.333.444',
					'2001:db8::c633:6464',
					'$a0',
					'($a0)',
					),
				array(
					'',
					)
				)
			);
	}

	function testADDRESS() {
		global $RE_ADDRESS;

		$this->assertTrue(
			$this->runTests(
				"^$RE_ADDRESS$",
				array(
					'a0 weight 1',
					'(a0) weight 1',
					'comixwall.org weight 1',
					'self weight 1',
					'1.2.3.4 weight 1',
					'1.2.3.4 - 111.222.333.444 weight 1',
					'2001:db8::c633:6464 weight 1',
					'$a0 weight 1',
					'($a0) weight 1',
					),
				array(
					'',
					'weight 1',
					)
				)
			);
	}

	function testADDRESS_NET() {
		global $RE_ADDRESS_NET;

		$this->assertTrue(
			$this->runTests(
				"^$RE_ADDRESS_NET$",
				array(
					'a0/12 weight 1',
					'(a0)/12 weight 1',
					'comixwall.org/12 weight 1',
					'self/12 weight 1',
					'1.2.3.4/12 weight 1',
					'1.2.3.4 - 111.222.333.444/12 weight 1', // Well, it's hard to do everything right
					'2001:db8::c633:6464/12 weight 1',
					'$a0/12 weight 1',
					'($a0)/12 weight 1',
					),
				array(
					'',
					'1.2.3.4/123 weight 1',
					)
				)
			);
	}

	function testTABLE_VAR() {
		global $RE_TABLE_VAR;

		$this->assertTrue(
			$this->runTests(
				"^$RE_TABLE_VAR$",
				array(
					'<a0_->',
					'<01234567890123456789012345678901234567890123456789>',
					),
				array(
					'<>',
					'<012345678901234567890123456789012345678901234567890>',
					)
				)
			);
	}

	function testTABLE_ADDRESS() {
		global $RE_TABLE_ADDRESS;

		$this->assertTrue(
			$this->runTests(
				"^$RE_TABLE_ADDRESS$",
				array(
					'comixwall.org',
					'a0',
					'self',
					'1.2.3.4',
					'2001:db8::c633:6464',
					'$a0',
					),
				array(
					'',
					'(a0)',
					'($a0)',
					)
				)
			);
	}

	function testTABLE_ADDRESS_NET() {
		global $RE_TABLE_ADDRESS_NET;

		$this->assertTrue(
			$this->runTests(
				"^$RE_TABLE_ADDRESS_NET$",
				array(
					'comixwall.org/12',
					'a0/12',
					'self/12',
					'1.2.3.4/12',
					'1.2.3.4 / 12',
					'2001:db8::c633:6464/12',
					'$a0/12',
					),
				array(
					'/12',
					'(a0)/12',
					'($a0)/12',
					)
				)
			);
	}

	function testTABLE_ADDRESS2() {
		$this->assertTrue(
			$this->runTests(
				RE_TABLE_ADDRESS,
				array(
					'!comixwall.org/12',
					'!a0/12',
					'!self/12',
					'!1.2.3.4/12',
					'!1.2.3.4 / 12',
					'!2001:db8::c633:6464/12',
					'!$a0/12',
					),
				array(
					'!',
					'!(a0)/12',
					'!($a0)/12',
					)
				)
			);
	}

	function testHOST() {
		$this->assertTrue(
			$this->runTests(
				RE_HOST,
				array(
					'!a0/12 weight 1',
					'!(a0)/12 weight 1',
					'!comixwall.org/12 weight 1',
					'!self/12 weight 1',
					'!1.2.3.4/12 weight 1',
					'!2001:db8::c633:6464/12 weight 1',
					'!$a0/12 weight 1',
					'!($a0)/12 weight 1',
					'!<a0_->',
					'!<a0_-> weight 1', // pfctl allows, but BNF does not
					),
				array(
					'!',
					'! weight 1',
					'!<> weight 1',
					)
				)
			);
	}
	
	function testREDIRHOST() {
		$this->assertTrue(
			$this->runTests(
				RE_REDIRHOST,
				array(
					'a0/12 weight 1',
					'(a0)/12 weight 1',
					'comixwall.org/12 weight 1',
					'self/12 weight 1',
					'1.2.3.4/12 weight 1',
					'2001:db8::c633:6464/12 weight 1',
					'$a0/12 weight 1',
					'($a0)/12 weight 1',
					),
				array(
					'',
					)
				)
			);
	}

	function testHOST_AT_IF() {
		global $RE_HOST_AT_IF;

		$this->assertTrue(
			$this->runTests(
				"^$RE_HOST_AT_IF$",
				array(
					'comixwall.org@a0',
					'comixwall.org @ a0',
					'a0 @ a0',
					'1.2.3.4@a0',
					'1.2.3.4@$a0',
					'1.2.3.4 @ a0',
					'1.2.3.4 / 12 @ a0',
					'2001:db8::c633:6464 @ a0',
					'$a0 @ a0',
					),
				array(
					'',
					'@',
					'comixwall.org',
					'comixwall.org/12',
					'a0/12',
					'self/12',
					'1.2.3.4',
					'1.2.3.4/12',
					'2001:db8::c633:6464/12',
					'$a0/12',
					)
				)
			);
	}

	function testIF_ADDRESS_NET() {
		global $RE_IF_ADDRESS_NET;

		$this->assertTrue(
			$this->runTests(
				"^$RE_IF_ADDRESS_NET$",
				array(
					'(a0 comixwall.org/12)',
					'(a0 1.2.3.4/12)',
					'($a0 comixwall.org/12)',
					'($a0 1.2.3.4/12)',
					'($a0:0 1.2.3.4/12)',
					'(a0  1.2.3.4/12)',
					'( a0 1.2.3.4/12 )',
					),
				array(
					'',
					'a0 comixwall.org/12',
					'a0/12',
					'1.2.3.4/12',
					'a$0', // Allow $ only at the start
					)
				)
			);
	}

	function testROUTEHOST() {
		$this->assertTrue(
			$this->runTests(
				RE_ROUTEHOST,
				array(
					'1.2.3.4/12 weight 1',
					'1.2.3.4@$a0',
					'(a0 1.2.3.4/12)',
					),
				array(
					'',
					'@',
					'()',
					'1.2.3.4@$a0 weight 1',
					'(a0 1.2.3.4/12) weight 1',
					)
				)
			);
	}

	function testPORT() {
		$this->assertTrue(
			$this->runTests(
				RE_PORT,
				array(
					'a0<>=!: -',
					str_repeat('0', 50),
					'$a0',
					),
				array(
					'',
					str_repeat('0', 51),
					'a$0', // Allow $ only at the start
					)
				)
			);
	}

	function testPORTSPEC() {
		$this->assertTrue(
			$this->runTests(
				RE_PORTSPEC,
				array(
					'a0*: -',
					str_repeat('0', 50),
					'$a0',
					),
				array(
					'',
					str_repeat('0', 51),
					'a$0', // Allow $ only at the start
					)
				)
			);
	}

	function testFLAGS() {
		$this->assertTrue(
			$this->runTests(
				RE_FLAGS,
				array(
					'S',
					'R',
					'P',
					'A',
					'U',
					'E',
					'W',
					'S/SA',
					'any',
					'SSSSSSSSSS',
					'$a0',
					),
				array(
					'',
					'a0',
					'SSSSSSSSSSS',
					'a$0', // Allow $ only at the start
					)
				)
			);
	}

	function testW_1_10() {
		$this->assertTrue(
			$this->runTests(
				RE_W_1_10,
				array(
					'a',
					'0123456789',
					'$a0',
					),
				array(
					'',
					'01234567890',
					'a$0', // Allow $ only at the start
					)
				)
			);
	}

	function testSTATE() {
		$this->assertTrue(
			$this->runTests(
				RE_STATE,
				array(
					'no',
					'keep',
					'modulate',
					'synproxy',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testMAXPKTRATE() {
		$this->assertTrue(
			$this->runTests(
				RE_MAXPKTRATE,
				array(
					'1/2',
					'0123456789/0123456789',
					),
				array(
					'',
					'1',
					'a0',
					'01234567890/0123456789',
					'0123456789/01234567890',
					// '5000000000/10', // pfctl: "only positive values permitted"
					// '10/5000000000', // pfctl: "only positive values permitted"
					)
				)
			);
	}

	function testPROBABILITY() {
		$this->assertTrue(
			$this->runTests(
				RE_PROBABILITY,
				array(
					'.1',
					'10%',
					'0123456789',
					),
				array(
					'',
					'%10',
					'01234567890',
					)
				)
			);
	}

	function testOS() {
		$this->assertTrue(
			$this->runTests(
				RE_OS,
				array(
					'a0.*:/_ -',
					str_repeat('0', 50),
					'$a0',
					),
				array(
					'',
					str_repeat('0', 51),
					'a$0', // Allow $ only at the start
					)
				)
			);
	}

	function testANCHOR_ID() {
		$this->assertTrue(
			$this->runTests(
				RE_ANCHOR_ID,
				array(
					'a0_/*-',
					str_repeat('0', 100),
					),
				array(
					'',
					str_repeat('0', 101),
					)
				)
			);
	}

	function testBLANK() {
		$this->assertTrue(
			$this->runTests(
				RE_BLANK,
				array(
					'',
					"\n",
					"\n\n\n\n\n\n\n\n\n\n\n", // 11 \n's not 10, due to $?
					),
				array(
					'a0',
					"\n\n\n\n\n\n\n\n\n\n\n\n",
					)
				)
			);
	}
	
	function testCOMMENT_INLINE() {
		$this->assertTrue(
			$this->runTests(
				RE_COMMENT_INLINE,
				array(
					'',
					str_repeat('0', 100),
					),
				array(
					str_repeat('0', 101),
					),
				FALSE,
				FALSE
				)
			);
	}
	
	function testCOMMENT() {
		$this->assertTrue(
			$this->runTests(
				RE_COMMENT,
				array(
					'',
					str_repeat('0', 2000),
					),
				array(
					str_repeat('0', 2001),
					),
				FALSE,
				FALSE
				)
			);
	}

	function testACTION() {
		$this->assertTrue(
			$this->runTests(
				RE_ACTION,
				array(
					'pass',
					'match',
					'block',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testBLOCKOPTION() {
		$this->assertTrue(
			$this->runTests(
				RE_BLOCKOPTION,
				array(
					'drop',
					'return',
					'return-rst',
					'return-icmp',
					'return-icmp6',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testTYPE() {
		$this->assertTrue(
			$this->runTests(
				RE_TYPE,
				array(
					'a-z',
					str_repeat('a', 30),
					),
				array(
					'',
					'A',
					'0',
					str_repeat('a', 31),
					)
				)
			);
	}

	function testSOURCE_HASH_KEY() {
		$this->assertTrue(
			$this->runTests(
				RE_SOURCE_HASH_KEY,
				array(
					str_repeat('0', 16),
					),
				array(
					'',
					str_repeat('0', 15),
					)
				)
			);
	}

	function testBLOCKPOLICY() {
		$this->assertTrue(
			$this->runTests(
				RE_BLOCKPOLICY,
				array(
					'drop',
					'return',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testSTATEPOLICY() {
		$this->assertTrue(
			$this->runTests(
				RE_STATEPOLICY,
				array(
					'if-bound',
					'floating',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testOPTIMIZATION() {
		$this->assertTrue(
			$this->runTests(
				RE_OPTIMIZATION,
				array(
					'normal',
					'high-latency',
					'satellite',
					'aggressive',
					'conservative',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testRULESETOPTIMIZATION() {
		$this->assertTrue(
			$this->runTests(
				RE_RULESETOPTIMIZATION,
				array(
					'none',
					'basic',
					'profile',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testDEBUG() {
		$this->assertTrue(
			$this->runTests(
				RE_DEBUG,
				array(
					'emerg',
					'alert',
					'crit',
					'err',
					'warning',
					'notice',
					'info',
					'debug',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testREASSEMBLE() {
		$this->assertTrue(
			$this->runTests(
				RE_REASSEMBLE,
				array(
					'yes',
					'no',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testSYNCOOKIES() {
		$this->assertTrue(
			$this->runTests(
				RE_SYNCOOKIES,
				array(
					'never',
					'always',
					'adaptive',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testPERCENT() {
		$this->assertTrue(
			$this->runTests(
				RE_PERCENT,
				array(
					'0.1%',
					'10%',
					),
				array(
					'',
					// '.1%', pfctl: "syntax error"
					'%10',
					'01234567890',
					)
				)
			);
	}

	function testBANDWIDTH() {
		$this->assertTrue(
			$this->runTests(
				RE_BANDWIDTH,
				array(
					'1',
					'1K',
					'1M',
					'1G',
					'0123456789012345',
					'0123456789012345K',
					),
				array(
					'',
					'a0',
					'01234567890123456',
					)
				)
			);
	}

	function testBWTIME() {
		$this->assertTrue(
			$this->runTests(
				RE_BWTIME,
				array(
					'1ms',
					'0123456789012345ms',
					),
				array(
					'',
					'1',
					'a0ms',
					'01234567890123456ms',
					)
				)
			);
	}

	function testREASSEMBLE_TCP() {
		$this->assertTrue(
			$this->runTests(
				RE_REASSEMBLE_TCP,
				array(
					'tcp',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testCONNRATE() {
		$this->assertTrue(
			$this->runTests(
				RE_CONNRATE,
				array(
					'1/2',
					'01234567890123456789/01234567890123456789',
					),
				array(
					'',
					'1',
					'a0',
					'012345678901234567890/01234567890123456789',
					'01234567890123456789/012345678901234567890',
					)
				)
			);
	}

	function testSOURCETRACKOPTION() {
		$this->assertTrue(
			$this->runTests(
				RE_SOURCETRACKOPTION,
				array(
					'rule',
					'global',
					),
				array(
					'',
					'a0',
					)
				)
			);
	}

	function testICMPTYPE() {
		$this->assertTrue(
			$this->runTests(
				RE_ICMPTYPE,
				array(
					'a0-',
					'a0- code a0-',
					'01234567890123456789',
					'01234567890123456789 code 01234567890123456789',
					),
				array(
					'',
					'012345678901234567890',
					'01234567890123456789 code 012345678901234567890',
					)
				)
			);
	}
}
?>