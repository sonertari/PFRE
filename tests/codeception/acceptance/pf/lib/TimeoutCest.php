<?php 
/* $pfre: TimeoutCest.php,v 1.2 2016/08/16 05:22:24 soner Exp $ */

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

require_once ('Rule.php');

class TimeoutCest extends Rule
{
	protected $type= 'Timeout';
	protected $ruleNumber= 15;
	protected $lineNumber= 21;
	protected $sender= 'timeout';

	protected $origRule= 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test';
	protected $expectedDispOrigRule= 'frag: 1, interval: 2, src.track: 3, tcp.first: 4, tcp.opening: 5, tcp.established: 6, tcp.closing: 7, tcp.finwait: 8, tcp.closed: 9, udp.first: 10, udp.single: 11, udp.multiple: 12, icmp.first: 13, icmp.error: 14, other.first: 15, other.single: 16, other.multiple: 17, adaptive.start: 18, adaptive.end: 19 Test e u d x';

	protected $modifiedRule= ' # Test1';
	protected $expectedDispModifiedRule= 'Test1 e u d x';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#frag', '');
		$this->clickApplySeeResult($I, 'set timeout { interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#interval', '');
		$this->clickApplySeeResult($I, 'set timeout { src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#src_track', '');
		$this->clickApplySeeResult($I, 'set timeout { tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#tcp_first', '');
		$this->clickApplySeeResult($I, 'set timeout { tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#tcp_opening', '');
		$this->clickApplySeeResult($I, 'set timeout { tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#tcp_established', '');
		$this->clickApplySeeResult($I, 'set timeout { tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#tcp_closing', '');
		$this->clickApplySeeResult($I, 'set timeout { tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#tcp_finwait', '');
		$this->clickApplySeeResult($I, 'set timeout { tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#tcp_closed', '');
		$this->clickApplySeeResult($I, 'set timeout { udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#udp_first', '');
		$this->clickApplySeeResult($I, 'set timeout { udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#udp_single', '');
		$this->clickApplySeeResult($I, 'set timeout { udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#udp_multiple', '');
		$this->clickApplySeeResult($I, 'set timeout { icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#icmp_first', '');
		$this->clickApplySeeResult($I, 'set timeout { icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#icmp_error', '');
		$this->clickApplySeeResult($I, 'set timeout { other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#other_first', '');
		$this->clickApplySeeResult($I, 'set timeout { other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#other_single', '');
		$this->clickApplySeeResult($I, 'set timeout { other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#other_multiple', '');
		$this->clickApplySeeResult($I, 'set timeout { adaptive.start 18, adaptive.end 19 } # Test');

		$I->fillField('#adaptive_start', '');
		$this->clickApplySeeResult($I, 'set timeout adaptive.end 19 # Test');

		$I->fillField('#adaptive_end', '');
		$this->clickApplySeeResult($I, ' # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#frag', '1');
		$this->clickApplySeeResult($I, 'set timeout frag 1 # Test1');

		$I->fillField('#interval', '2');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2 } # Test1');

		$I->fillField('#src_track', '3');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3 } # Test1');

		$I->fillField('#tcp_first', '4');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4 } # Test1');

		$I->fillField('#tcp_opening', '5');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5 } # Test1');

		$I->fillField('#tcp_established', '6');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6 } # Test1');

		$I->fillField('#tcp_closing', '7');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7 } # Test1');

		$I->fillField('#tcp_finwait', '8');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8 } # Test1');

		$I->fillField('#tcp_closed', '9');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9 } # Test1');

		$I->fillField('#udp_first', '10');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10 } # Test1');

		$I->fillField('#udp_single', '11');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11 } # Test1');

		$I->fillField('#udp_multiple', '12');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12 } # Test1');

		$I->fillField('#icmp_first', '13');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13 } # Test1');

		$I->fillField('#icmp_error', '14');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14 } # Test1');

		$I->fillField('#other_first', '15');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15 } # Test1');

		$I->fillField('#other_single', '16');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16 } # Test1');

		$I->fillField('#other_multiple', '17');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17 } # Test1');

		$I->fillField('#adaptive_start', '18');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18 } # Test1');

		$I->fillField('#adaptive_end', '19');
		$this->clickApplySeeResult($I, 'set timeout { frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 } # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#frag', '');
		$I->fillField('#interval', '');
		$I->fillField('#src_track', '');
		$I->fillField('#tcp_first', '');
		$I->fillField('#tcp_opening', '');
		$I->fillField('#tcp_established', '');
		$I->fillField('#tcp_closing', '');
		$I->fillField('#tcp_finwait', '');
		$I->fillField('#tcp_closed', '');
		$I->fillField('#udp_first', '');
		$I->fillField('#udp_single', '');
		$I->fillField('#udp_multiple', '');
		$I->fillField('#icmp_first', '');
		$I->fillField('#icmp_error', '');
		$I->fillField('#other_first', '');
		$I->fillField('#other_single', '');
		$I->fillField('#other_multiple', '');
		$I->fillField('#adaptive_start', '');
		$I->fillField('#adaptive_end', '');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#frag', '1');
		$I->fillField('#interval', '2');
		$I->fillField('#src_track', '3');
		$I->fillField('#tcp_first', '4');
		$I->fillField('#tcp_opening', '5');
		$I->fillField('#tcp_established', '6');
		$I->fillField('#tcp_closing', '7');
		$I->fillField('#tcp_finwait', '8');
		$I->fillField('#tcp_closed', '9');
		$I->fillField('#udp_first', '10');
		$I->fillField('#udp_single', '11');
		$I->fillField('#udp_multiple', '12');
		$I->fillField('#icmp_first', '13');
		$I->fillField('#icmp_error', '14');
		$I->fillField('#other_first', '15');
		$I->fillField('#other_single', '16');
		$I->fillField('#other_multiple', '17');
		$I->fillField('#adaptive_start', '18');
		$I->fillField('#adaptive_end', '19');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>