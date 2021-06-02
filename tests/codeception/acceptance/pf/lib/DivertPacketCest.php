<?php 
/*
 * Copyright (C) 2004-2021 Soner Tari
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

class DivertPacketCest extends Rule
{
	protected $type= 'DivertPacket';
	protected $ruleNumber= 9;
	protected $lineNumber= 15;
	protected $sender= 'divertpacket';

	protected $origRule= 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test';
	protected $expectedDispOrigRule= 'pass in
em0
log all, matches, user, to=pflog0 quick tcp
192.168.0.1 ssh
2222
192.168.0.2 ssh ssh
Test e u d x';

	protected $modifiedRule= 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1';
	protected $expectedDispModifiedRule= 'match out
192.168.0.1
1.1.1.1
ssh
1111
1111
Test1 e u d x';

	function __construct()
	{
		parent::__construct();

		$this->revertedRule= 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test';
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->selectOption('#action', 'match');
		$this->clickApplySeeResult($I, 'match in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->selectOption('#direction', 'out');
		$this->clickApplySeeResult($I, 'match out log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#log-to', '');
		$this->clickApplySeeResult($I, 'match out log ( all, matches, user ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->uncheckOption('#log-all');
		$this->clickApplySeeResult($I, 'match out log ( matches, user ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->uncheckOption('#log-matches');
		$this->clickApplySeeResult($I, 'match out log ( user ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->uncheckOption('#log-user');
		$this->clickApplySeeResult($I, 'match out log quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->uncheckOption('#log');
		$this->clickApplySeeResult($I, 'match out quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->uncheckOption('#quick');
		$this->clickApplySeeResult($I, 'match out on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$this->clickDeleteLink($I, 'delInterface', 'em0');
		$this->seeResult($I, 'match out inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->selectOption('#af', '');
		$this->clickApplySeeResult($I, 'match out proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$this->clickDeleteLink($I, 'delProto', 'tcp');
		$this->seeResult($I, 'match out from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#addFrom', '1.1.1.1');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$this->clickDeleteLink($I, 'delFromPort', 'ssh');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } port 2222 os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$this->clickDeleteLink($I, 'delFromPort', '2222');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$this->clickDeleteLink($I, 'delOs', 'openbsd');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$this->clickDeleteLink($I, 'delTo', '192.168.0.2');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#addToPort', '1111');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$this->clickDeleteLink($I, 'delUser', 'root');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$this->clickDeleteLink($I, 'delGroup', 'wheel');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#flags', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#tos', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->uncheckOption('#allow-opts');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->uncheckOption('#once');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#label', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#tag', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->uncheckOption('#not-tagged');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#tagged', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$this->clickDeleteLink($I, 'delPrio', '2');
		$this->seeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

//		$I->selectOption('#queueSec', '');
//		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } set queue std rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');
//
//		$I->selectOption('#queuePri', '');
//		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#rtable', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#max-pkt-rate', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#probability', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#prio', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#set-tos', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->uncheckOption('#not-received-on');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->fillField('#received-on', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test');

		$I->selectOption('#state-filter', '');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port ssh # Test');

		$I->fillField('#divertport', '1111');
		$this->clickApplySeeResult($I, 'match out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->selectOption('#action', 'pass');
		$this->clickApplySeeResult($I, 'pass out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->selectOption('#direction', 'in');
		$this->clickApplySeeResult($I, 'pass in from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->checkOption('#log');
		$this->clickApplySeeResult($I, 'pass in log from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->fillField('#log-to', 'pflog0');
		$this->clickApplySeeResult($I, 'pass in log ( to pflog0 ) from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->checkOption('#log-all');
		$this->clickApplySeeResult($I, 'pass in log ( all, to pflog0 ) from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->checkOption('#log-matches');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, to pflog0 ) from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->checkOption('#log-user');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->checkOption('#quick');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->fillField('#addInterface', 'em0');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->selectOption('#af', 'inet');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->fillField('#addProto', 'tcp');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$this->clickDeleteLink($I, 'delFrom', '1.1.1.1');
		$this->seeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->fillField('#addFromPort', 'ssh');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port ssh to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->fillField('#addFromPort', '2222');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->fillField('#addOs', 'openbsd');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to port { ssh, 1111 } divert-packet port 1111 # Test1');

		$I->fillField('#addTo', '192.168.0.2');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port { ssh, 1111 } divert-packet port 1111 # Test1');

		$this->clickDeleteLink($I, 'delToPort', '1111');
		$this->seeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh divert-packet port 1111 # Test1');

		$I->fillField('#addUser', 'root');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root divert-packet port 1111 # Test1');

		$I->fillField('#addGroup', 'wheel');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel divert-packet port 1111 # Test1');

		$I->fillField('#flags', 'S/SA');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA divert-packet port 1111 # Test1');

		$I->fillField('#tos', '1');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 divert-packet port 1111 # Test1');

		$I->checkOption('#allow-opts');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts divert-packet port 1111 # Test1');

		$I->checkOption('#once');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once divert-packet port 1111 # Test1');

		$I->fillField('#label', 'test');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" divert-packet port 1111 # Test1');

		$I->fillField('#tag', 'test');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" divert-packet port 1111 # Test1');

		$I->fillField('#tagged', 'test');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" tagged "test" divert-packet port 1111 # Test1');

		$I->checkOption('#not-tagged');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" divert-packet port 1111 # Test1');

		$I->fillField('#addPrio', '2');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 divert-packet port 1111 # Test1');

//		$I->selectOption('#queuePri', 'std');
//		$I->click('Apply');
//
//		$I->selectOption('#queueSec', 'service');

		$I->fillField('#rtable', '3');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 divert-packet port 1111 # Test1');

		$I->fillField('#max-pkt-rate', '100/10');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 divert-packet port 1111 # Test1');

		$I->fillField('#probability', '10%');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% divert-packet port 1111 # Test1');

		$I->fillField('#prio', '4');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 divert-packet port 1111 # Test1');

		$I->fillField('#set-tos', '5');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 divert-packet port 1111 # Test1');

		$I->fillField('#received-on', 'em0');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 received-on em0 divert-packet port 1111 # Test1');

		$I->checkOption('#not-received-on');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 divert-packet port 1111 # Test1');

		/// @todo Fix frag and interval? How?
		$I->selectOption('#state-filter', 'Keep State');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->fillField('#max', '1');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->fillField('#max-src-states', '2');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->fillField('#max-src-nodes', '3');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->fillField('#max-src-conn', '4');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->fillField('#max-src-conn-rate', '5/5');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->checkOption('#sloppy');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->checkOption('#no-sync');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->checkOption('#pflow');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->checkOption('#if-bound');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->fillField('#overload', 'over');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over>, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->checkOption('#flush');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->checkOption('#global');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->checkOption('#source-track');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->selectOption('#source-track-option', 'rule');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2 ) divert-packet port 1111 # Test1');

		$I->fillField('#src_track', '3');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3 ) divert-packet port 1111 # Test1');

		$I->fillField('#tcp_first', '4');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4 ) divert-packet port 1111 # Test1');

		$I->fillField('#tcp_opening', '5');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5 ) divert-packet port 1111 # Test1');

		$I->fillField('#tcp_established', '6');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6 ) divert-packet port 1111 # Test1');

		$I->fillField('#tcp_closing', '7');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7 ) divert-packet port 1111 # Test1');

		$I->fillField('#tcp_finwait', '8');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8 ) divert-packet port 1111 # Test1');

		$I->fillField('#tcp_closed', '9');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9 ) divert-packet port 1111 # Test1');

		$I->fillField('#udp_first', '10');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10 ) divert-packet port 1111 # Test1');

		$I->fillField('#udp_single', '11');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11 ) divert-packet port 1111 # Test1');

		$I->fillField('#udp_multiple', '12');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12 ) divert-packet port 1111 # Test1');

		$I->fillField('#icmp_first', '13');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13 ) divert-packet port 1111 # Test1');

		$I->fillField('#icmp_error', '14');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14 ) divert-packet port 1111 # Test1');

		$I->fillField('#other_first', '15');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15 ) divert-packet port 1111 # Test1');

		$I->fillField('#other_single', '16');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16 ) divert-packet port 1111 # Test1');

		$I->fillField('#other_multiple', '17');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17 ) divert-packet port 1111 # Test1');

		$I->fillField('#adaptive_start', '18');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18 ) divert-packet port 1111 # Test1');

		$I->fillField('#adaptive_end', '19');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port 1111 # Test1');

		$I->fillField('#divertport', 'ssh');
		$this->clickApplySeeResult($I, 'pass in log ( all, matches, user, to pflog0 ) quick on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) divert-packet port ssh # Test1');

		$I->fillField('#comment', 'Test');
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
		$I->fillField('#max-pkt-rate', '');
		$I->fillField('#probability', '');
		$I->fillField('#prio', '');
		$I->fillField('#set-tos', '');
		$I->uncheckOption('#not-received-on');
		$I->fillField('#received-on', '');
		$I->selectOption('#state-filter', '');
		$I->fillField('#divertport', '1111');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');

		// Apply again to delete stale elements in the rule struct, specifically the state options
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
		$I->fillField('#max-pkt-rate', '100/10');
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
		$I->fillField('#divertport', 'ssh');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>