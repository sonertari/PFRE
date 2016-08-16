<?php 
/* $pfre: AfToCest.php,v 1.3 2016/08/16 05:22:24 soner Exp $ */

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

class AfToCest extends Rule
{
	protected $type= 'AfTo';
	protected $ruleNumber= 5;
	protected $ruleNumberGenerated= 11;
	protected $sender= 'afto';

	protected $origRule= 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test';
	protected $expectedDispOrigRule= 'pass in
em0
log all, matches, user, to=pflog0 tcp
192.168.0.1 ssh
2222
192.168.0.2 ssh inet
192.168.0.1 192.168.0.2 Test e u d x';

	protected $modifiedRule= 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1';
	protected $expectedDispModifiedRule= 'match out
192.168.0.1
1.1.1.1
ssh
1111
192.168.0.2
1.1.1.1
Test1 e u d x';

	function __construct()
	{
		parent::__construct();

		$this->revertedRule= 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test';
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->selectOption('#action', 'match');
		$this->clickApplySeeResult($I, 'match in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->selectOption('#direction', 'out');
		$this->clickApplySeeResult($I, 'match out log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#log-to', '');
		$this->clickApplySeeResult($I, 'match out log ( all, matches, user ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#log-all');
		$this->clickApplySeeResult($I, 'match out log ( matches, user ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#log-matches');
		$this->clickApplySeeResult($I, 'match out log ( user ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#log-user');
		$this->clickApplySeeResult($I, 'match out log quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#log');
		$this->clickApplySeeResult($I, 'match out quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#quick');
		$this->clickApplySeeResult($I, 'match out on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delInterface', 'em0');
		$this->seeResult($I, 'match out inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->selectOption('#af', '');
		$this->clickApplySeeResult($I, 'match out proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delProto', 'tcp');
		$this->seeResult($I, 'match out from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#addFrom', '1.1.1.1');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delFromPort', 'ssh');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } port 2222 os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delFromPort', '2222');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delOs', 'openbsd');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delTo', '192.168.0.2');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#addToPort', '1111');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delUser', 'root');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delGroup', 'wheel');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#flags', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#tos', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#allow-opts');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#once');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#label', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#tag', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#not-tagged');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#tagged', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delPrio', '2');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

//		$I->selectOption('#queueSec', '');
//		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } set queue std rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');
//
//		$I->selectOption('#queuePri', '');
//		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#rtable', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#probability', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#prio', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#set-tos', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#not-received-on');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#received-on', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->selectOption('#state-filter', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->selectOption('#rediraf', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$this->clickDeleteLink($I, 'delRedirHost', '192.168.0.1');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->fillField('#addToRedirHost', '1.1.1.1');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test');

		$I->uncheckOption('#sticky-address');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } source-hash 09f1cbe02e2f4801b433ba9fab728903 # Test');

		$I->fillField('#source-hash-key', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } source-hash # Test');

		$I->uncheckOption('#source-hash');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->selectOption('#action', 'pass');
		$this->clickApplySeeResult($I, 'pass out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->selectOption('#direction', 'in');
		$this->clickApplySeeResult($I, 'pass in from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#log');
		$this->clickApplySeeResult($I, 'pass in log from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#log-to', 'pflog0');
		$this->clickApplySeeResult($I, 'pass in log ( to pflog0 ) from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#log-all');
		$this->clickApplySeeResult($I, 'pass in log ( all, to pflog0 ) from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#log-matches');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, to pflog0 ) from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#log-user');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#quick');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addInterface', 'em0');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->selectOption('#af', 'inet');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addProto', 'tcp');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$this->clickDeleteLink($I, 'delFrom', '1.1.1.1');
		$this->seeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addFromPort', 'ssh');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port ssh to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addFromPort', '2222');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addOs', 'openbsd');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addTo', '192.168.0.2');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port { ssh, 1111 } af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$this->clickDeleteLink($I, 'delToPort', '1111');
		$this->seeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addUser', 'root');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addGroup', 'wheel');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#flags', 'S/SA');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#tos', '1');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#allow-opts');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#once');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#label', 'test');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#tag', 'test');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#tagged', 'test');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" tagged "test" af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#not-tagged');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addPrio', '2');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

//		$I->selectOption('#queuePri', 'std');
//		$I->click('Apply');
//
//		$I->selectOption('#queueSec', 'service');

		$I->fillField('#rtable', '3');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#probability', '10%');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#prio', '4');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#set-tos', '5');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#received-on', 'em0');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 received-on em0 af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#not-received-on');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		/// @todo Fix frag and interval? How?
		$I->selectOption('#state-filter', 'Keep State');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#max', '1');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#max-src-states', '2');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#max-src-nodes', '3');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#max-src-conn', '4');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#max-src-conn-rate', '5/5');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#sloppy');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#no-sync');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#pflow');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#if-bound');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#overload', 'over');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over>, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#flush');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#global');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->checkOption('#source-track');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->selectOption('#source-track-option', 'rule');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#src_track', '3');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#tcp_first', '4');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#tcp_opening', '5');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#tcp_established', '6');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#tcp_closing', '7');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#tcp_finwait', '8');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#tcp_closed', '9');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#udp_first', '10');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#udp_single', '11');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#udp_multiple', '12');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#icmp_first', '13');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#icmp_error', '14');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#other_first', '15');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#other_single', '16');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#other_multiple', '17');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#adaptive_start', '18');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#adaptive_end', '19');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->selectOption('#rediraf', 'inet');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet to { 192.168.0.2, 1.1.1.1 } # Test1');

		$I->fillField('#addRedirHost', '192.168.0.1');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to { 192.168.0.2, 1.1.1.1 } # Test1');

		$this->clickDeleteLink($I, 'delToRedirHost', '1.1.1.1');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 # Test1');

		$I->checkOption('#source-hash');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash # Test1');

		$I->fillField('#source-hash-key', '09f1cbe02e2f4801b433ba9fab728903');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 # Test1');

		$I->checkOption('#sticky-address');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) af-to inet from 192.168.0.1 to 192.168.0.2 source-hash 09f1cbe02e2f4801b433ba9fab728903 sticky-address # Test1');

		$I->fillField('comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->selectOption('#action', 'match');
		$I->selectOption('#direction', 'out');
		$I->fillField('#log-to', '');
		$I->uncheckOption('#log-all');
		$I->uncheckOption('#log-matches');
		$I->uncheckOption('#log-user');
		$I->uncheckOption('#log');
		$I->uncheckOption('#quick');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delInterface', 'em0');

		$I->selectOption('#af', '');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delProto', 'tcp');

		$I->fillField('#addFrom', '1.1.1.1');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delFromPort', 'ssh');
		$this->clickDeleteLink($I, 'delFromPort', '2222');
		$this->clickDeleteLink($I, 'delOs', 'openbsd');
		$this->clickDeleteLink($I, 'delTo', '192.168.0.2');

		$I->fillField('#addToPort', '1111');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delUser', 'root');
		$this->clickDeleteLink($I, 'delGroup', 'wheel');

		$I->fillField('#flags', '');
		$I->fillField('#tos', '');
		$I->uncheckOption('#allow-opts');
		$I->uncheckOption('#once');
		$I->fillField('#label', '');
		$I->fillField('#tag', '');
		$I->uncheckOption('#not-tagged');
		$I->fillField('#tagged', '');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delPrio', '2');

		$I->selectOption('#queuePri', '');
		$I->selectOption('#queueSec', '');
		$I->fillField('#rtable', '');
		$I->fillField('#probability', '');
		$I->fillField('#prio', '');
		$I->fillField('#set-tos', '');
		$I->uncheckOption('#not-received-on');
		$I->fillField('#received-on', '');
		$I->selectOption('#state-filter', '');
		$I->selectOption('#rediraf', '');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delRedirHost', '192.168.0.1');

		$I->fillField('#addToRedirHost', '1.1.1.1');
		$I->uncheckOption('#sticky-address');
		$I->fillField('#source-hash-key', '');
		// Apply to erase the source-hash-key, otherwise the rule stays modified after the last Apply
		$I->click('Apply');

		$I->uncheckOption('#source-hash');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->selectOption('#action', 'pass');
		$I->selectOption('#direction', 'in');
		$I->checkOption('#log');
		$I->click('Apply');

		$I->fillField('#log-to', 'pflog0');
		$I->checkOption('#log-all');
		$I->checkOption('#log-matches');
		$I->checkOption('#log-user');
		$I->checkOption('#quick');
		$I->fillField('#addInterface', 'em0');
		$I->selectOption('#af', 'inet');
		$I->fillField('#addProto', 'tcp');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delFrom', '1.1.1.1');

		$I->fillField('#addFromPort', 'ssh');
		$I->click('Apply');

		$I->fillField('#addFromPort', '2222');
		$I->fillField('#addOs', 'openbsd');
		$I->fillField('#addTo', '192.168.0.2');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delToPort', '1111');

		$I->fillField('#addUser', 'root');
		$I->fillField('#addGroup', 'wheel');
		$I->fillField('#flags', 'S/SA');
		$I->fillField('#tos', '1');
		$I->checkOption('#allow-opts');
		$I->checkOption('#once');
		$I->fillField('#label', 'test');
		$I->fillField('#tag', 'test');
		$I->fillField('#tagged', 'test');
		$I->click('Apply');

		$I->checkOption('#not-tagged');
		$I->fillField('#addPrio', '2');
		$I->fillField('#rtable', '3');
		$I->fillField('#probability', '10%');
		$I->fillField('#prio', '4');
		$I->fillField('#set-tos', '5');
		$I->fillField('#received-on', 'em0');
		$I->click('Apply');

		$I->checkOption('#not-received-on');
		$I->selectOption('#state-filter', 'Keep State');
		$I->click('Apply');

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
		$I->selectOption('#rediraf', 'inet');
		$I->fillField('#addRedirHost', '192.168.0.1');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delToRedirHost', '1.1.1.1');

		$I->checkOption('#source-hash');
		$I->click('Apply');

		$I->fillField('#source-hash-key', '09f1cbe02e2f4801b433ba9fab728903');
		$I->checkOption('#sticky-address');
		$I->fillField('comment', 'Test');
		$I->click('Apply');
	}
}
?>