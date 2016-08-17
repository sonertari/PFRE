<?php 
/* $pfre: StateCest.php,v 1.2 2016/08/16 05:22:24 soner Exp $ */

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

class StateCest extends Rule
{
	protected $type= 'State';
	protected $ruleNumber= 17;
	protected $lineNumber= 23;
	protected $sender= 'state';

	protected $origRule= 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test';
	protected $expectedDispOrigRule= 'max: 1, max-src-states: 2, max-src-nodes: 3, max-src-conn: 4, max-src-conn-rate: 5/5, sloppy, no-sync, pflow, if-bound, overload: <over> flush global, source-track rule, frag: 1, interval: 2, src.track: 3, tcp.first: 4, tcp.opening: 5, tcp.established: 6, tcp.closing: 7, tcp.finwait: 8, tcp.closed: 9, udp.first: 10, udp.single: 11, udp.multiple: 12, icmp.first: 13, icmp.error: 14, other.first: 15, other.single: 16, other.multiple: 17, adaptive.start: 18, adaptive.end: 19 Test e u d x';

	protected $modifiedRule= 'set state-defaults frag 1, interval 2 # Test1';
	protected $expectedDispModifiedRule= 'frag: 1, interval: 2 Test1 e u d x';

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#max', '');
		$this->clickApplySeeResult($I, 'set state-defaults max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#max-src-states', '');
		$this->clickApplySeeResult($I, 'set state-defaults max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#max-src-nodes', '');
		$this->clickApplySeeResult($I, 'set state-defaults max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#max-src-conn', '');
		$this->clickApplySeeResult($I, 'set state-defaults max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#max-src-conn-rate', '');
		$this->clickApplySeeResult($I, 'set state-defaults sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->uncheckOption('#sloppy');
		$this->clickApplySeeResult($I, 'set state-defaults no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->uncheckOption('#no-sync');
		$this->clickApplySeeResult($I, 'set state-defaults pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->uncheckOption('#pflow');
		$this->clickApplySeeResult($I, 'set state-defaults if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->uncheckOption('#if-bound');
		$this->clickApplySeeResult($I, 'set state-defaults overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->uncheckOption('#global');
		$this->clickApplySeeResult($I, 'set state-defaults overload <over> flush, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->uncheckOption('#flush');
		$this->clickApplySeeResult($I, 'set state-defaults overload <over>, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#overload', '');
		$this->clickApplySeeResult($I, 'set state-defaults source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->selectOption('#source-track-option', '');
		$this->clickApplySeeResult($I, 'set state-defaults source-track, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->uncheckOption('#source-track');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		/// @todo Fix frag and interval?
//		$I->fillField('#frag', '');
//		$this->clickApplySeeResult($I, 'set state-defaults interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');
//
//		$I->fillField('#interval', '');
//		$this->clickApplySeeResult($I, 'set state-defaults src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#src_track', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#tcp_first', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#tcp_opening', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#tcp_established', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#tcp_closing', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#tcp_finwait', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#tcp_closed', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#udp_first', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#udp_single', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#udp_multiple', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#icmp_first', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#icmp_error', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#other_first', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#other_single', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#other_multiple', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, adaptive.start 18, adaptive.end 19 # Test');

		$I->fillField('#adaptive_start', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2, adaptive.end 19 # Test');

		$I->fillField('#adaptive_end', '');
		$this->clickApplySeeResult($I, 'set state-defaults frag 1, interval 2 # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#max', '1');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, frag 1, interval 2 # Test1');

		$I->fillField('#max-src-states', '2');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, frag 1, interval 2 # Test1');

		$I->fillField('#max-src-nodes', '3');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, frag 1, interval 2 # Test1');

		$I->fillField('#max-src-conn', '4');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, frag 1, interval 2 # Test1');

		$I->fillField('#max-src-conn-rate', '5/5');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, frag 1, interval 2 # Test1');

		$I->checkOption('#sloppy');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, frag 1, interval 2 # Test1');

		$I->checkOption('#no-sync');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, frag 1, interval 2 # Test1');

		$I->checkOption('#pflow');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, frag 1, interval 2 # Test1');

		$I->checkOption('#if-bound');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, frag 1, interval 2 # Test1');

		$I->fillField('#overload', 'over');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over>, frag 1, interval 2 # Test1');

		$I->checkOption('#flush');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush, frag 1, interval 2 # Test1');

		$I->checkOption('#global');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, frag 1, interval 2 # Test1');

		$I->checkOption('#source-track');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track, frag 1, interval 2 # Test1');

		$I->selectOption('#source-track-option', 'rule');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2 # Test1');

//		$I->fillField('#frag', '1');
//		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1 # Test1');
//
//		$I->fillField('#interval', '2');
//		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2 # Test1');

		$I->fillField('#src_track', '3');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3 # Test1');

		$I->fillField('#tcp_first', '4');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4 # Test1');

		$I->fillField('#tcp_opening', '5');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5 # Test1');

		$I->fillField('#tcp_established', '6');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6 # Test1');

		$I->fillField('#tcp_closing', '7');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7 # Test1');

		$I->fillField('#tcp_finwait', '8');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8 # Test1');

		$I->fillField('#tcp_closed', '9');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9 # Test1');

		$I->fillField('#udp_first', '10');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10 # Test1');

		$I->fillField('#udp_single', '11');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11 # Test1');

		$I->fillField('#udp_multiple', '12');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12 # Test1');

		$I->fillField('#icmp_first', '13');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13 # Test1');

		$I->fillField('#icmp_error', '14');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14 # Test1');

		$I->fillField('#other_first', '15');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15 # Test1');

		$I->fillField('#other_single', '16');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16 # Test1');

		$I->fillField('#other_multiple', '17');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17 # Test1');

		$I->fillField('#adaptive_start', '18');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18 # Test1');

		$I->fillField('#adaptive_end', '19');
		$this->clickApplySeeResult($I, 'set state-defaults max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#max', '');
		$I->fillField('#max-src-states', '');
		$I->fillField('#max-src-nodes', '');
		$I->fillField('#max-src-conn', '');
		$I->fillField('#max-src-conn-rate', '');
		$I->uncheckOption('#sloppy');
		$I->uncheckOption('#no-sync');
		$I->uncheckOption('#pflow');
		$I->uncheckOption('#if-bound');
		$I->uncheckOption('#global');
		$I->uncheckOption('#flush');
		$I->fillField('#overload', '');
		$I->selectOption('#source-track-option', '');
		$I->uncheckOption('#source-track');
//		$I->fillField('#frag', '');
//		$I->fillField('#interval', '');
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
		$I->fillField('#max', '1');
		$I->fillField('#max-src-states', '2');
		$I->fillField('#max-src-nodes', '3');
		$I->fillField('#max-src-conn', '4');
		$I->fillField('#max-src-conn-rate', '5/5');
		$I->checkOption('#sloppy');
		$I->checkOption('#no-sync');
		$I->checkOption('#pflow');
		$I->checkOption('#if-bound');
		$I->fillField('#overload', 'over');
		$I->click('Apply');

		$I->checkOption('#flush');
		$I->click('Apply');

		$I->checkOption('#global');
		$I->checkOption('#source-track');
		$I->click('Apply');

		$I->selectOption('#source-track-option', 'rule');
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