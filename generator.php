<?php
define('TEST_CHI', 'test_chi');
define('TEST_F', 'test_f');

define('GRAF_HISTOGRAM', 'graf_histogram');
define('GRAF_LINE', 'graf_line');

function statistikaPovinnych(&$p, $povinne, $predmety) {
	$statistika = array();

	foreach ($p as $k => $v) {
		foreach ($v as $kk => $vv) {
			if (!isset($povinne[$kk])) {continue;}

			$semestr = (int)$povinne[$kk];

			
			$z = array();
			$t = array();


			foreach ($vv['pokusy'] as $termin => $znamky) {
				foreach ($znamky as $znamka) {
					$rok = $znamka['AR'];

					if (!isset($statistika[$rok]['obtiznost']['semestry'][$semestr])) {
						$statistika[$rok]['obtiznost']['semestry'][$semestr] = array();
					}

					if (!isset($statistika[$rok]['prumer']['semestry'][$semestr])) {
						$statistika[$rok]['prumer']['semestry'][$semestr] = array();
					}
					
					if (!isset($statistika[$rok])) {
						$statistika[$rok] = array(
							'prumer' => array(
								'semestry' => array(),
								'vse' => array()
							),
							'obtiznost' => array(
								'semestry' => array(),
								'vse' => array()
							)
						); 
					}

					if (!isset($z[$rok])) {
						$z[$rok] = array();
					}

					if ($znamka['ZNAMKA'] != 4) {
						if (!isset($t[$rok])) {
							$t[$rok] = array();
						}
						$t[$rok][] = $termin;
					}
					$z[$rok][] = $znamka['ZNAMKA'];
				}
			}

			foreach ($z as $rok => $Z) {
				$prumer = avg($Z);
				$statistika[$rok]['prumer']['vse'][] = array($kk,$prumer, count($Z));
				$statistika[$rok]['prumer']['semestry'][$semestr][] = array($kk,$prumer,count($Z));
			}
			foreach ($t as $rok => $T) {
				$obtiznost = avg($T);

				$statistika[$rok]['obtiznost']['vse'][] = array($kk,$obtiznost, count($T));
				$statistika[$rok]['obtiznost']['semestry'][$semestr][] = array($kk,$obtiznost, count($T));
			}
		}
	}
	$sortFce = function($a, $b) {
		$A = $a[1];
		$B = $b[1];

		if ($A < $B) {
			return -1;
		} else if ($A > $B) {
			return 1;
		} else {
			return 0;
		}
	};
	ksort($statistika);
	
	foreach ($statistika as $rok => $void) {
		usort($statistika[$rok]['prumer']['vse'], $sortFce);
		usort($statistika[$rok]['obtiznost']['vse'], $sortFce);

		ksort($statistika[$rok]['prumer']['semestry']);
		ksort($statistika[$rok]['obtiznost']['semestry']);

		foreach ($statistika[$rok]['prumer']['semestry'] as $semestr => $void) {
			usort($statistika[$rok]['prumer']['semestry'][$semestr], $sortFce);
		}

		foreach ($statistika[$rok]['obtiznost']['semestry'] as $semestr => $void) {
			usort($statistika[$rok]['obtiznost']['semestry'][$semestr], $sortFce);
		}
	}

	return $statistika;
}

function analyzaPovinnych($s, $p) {
	$out = array();

	$out[] = '\\secc Náročnost předmětů podle úspěšného termínu';
	$out[] = '';

	
	$out[] = '\\begitems \\style -';
	foreach ($s as $rok => $d) {
		$out[] = sprintf('\\penalty-800 * %s', $rok);
		$out[] = '\\nobreak\\medskip';
		$out[] = '\\table{|c|l|c|c|}{\\crl';
		$out[] = '\\malebf poř.&\\malebf Předmět&\\malebf termín&\\malebf počet\\crl';
		$por = 0;
		foreach ($d['obtiznost']['vse'] as $dd) {
			$por++;

			$out[] = sprintf('\\male %d.&\\male %s (%s)&\\male %s&\\male %d\\crl', $por, $p[$dd[0]]['PNAZEV'], $p[$dd[0]]['POVINN'], f($dd[1]), $dd[2]);
		}
		$out[] = '}';
		$out[] = '\\begitems \\style x';
		foreach ($d['obtiznost']['semestry'] as $semestr => $dd) {
			$out[] = sprintf('\\penalty-800 * %d. semestr (%s)', $semestr, $rok);
			$out[] = '\\nobreak\\medskip';
			$out[] = '\\table{|c|l|c|c|}{\\crl';
			$out[] = '\\malebf poř.&\\malebf Předmět&\\malebf termín&\\malebf počet\\crl';
			$por = 0;
			foreach ($dd as $ddd) {
				$por++;

				$out[] = sprintf('\\male %d.&\\male %s (%s)&\\male %s&\\male %d\\crl', $por, $p[$ddd[0]]['PNAZEV'], $p[$ddd[0]]['POVINN'], f($ddd[1]), $ddd[2]);
			}
			$out[] = '}';
		}
		$out[] = '\\enditems';
	}
	$out[] = '\\enditems';

	$out[] = '';
	$out[] = '\\secc Náročnost předmětů podle průměru';
	$out[] = '';

	
	$out[] = '\\begitems \\style -';
	foreach ($s as $rok => $d) {
		$out[] = sprintf('\\penalty-800 * %s', $rok);
		$out[] = '\\nobreak\\medskip';
		$out[] = '\\table{|c|l|c|c|}{\\crl';
		$out[] = '\\malebf poř.&\\malebf Předmět&\\malebf průměr&\\malebf počet\\crl';
		$por = 0;
		foreach ($d['prumer']['vse'] as $dd) {
			$por++;

			$out[] = sprintf('\\male %d.&\\male %s (%s)&\\male %s&\\male %d\\crl', $por, $p[$dd[0]]['PNAZEV'], $p[$dd[0]]['POVINN'], f($dd[1]), $dd[2]);
		}
		$out[] = '}';
		$out[] = '\\begitems \\style x';
		foreach ($d['prumer']['semestry'] as $semestr => $dd) {
			$out[] = sprintf('\\penalty-800 * %d. semestr (%s)', $semestr, $rok);
			$out[] = '\\nobreak\\medskip';
			$out[] = '\\table{|c|l|c|c|}{\\crl';
			$out[] = '\\malebf poř.&\\malebf Předmět&\\malebf průměr&\\malebf počet\\crl';
			$por = 0;
			foreach ($dd as $ddd) {
				$por++;

				$out[] = sprintf('\\male %d.&\\male %s (%s)&\\male %s&\\male %s\\crl', $por, $p[$ddd[0]]['PNAZEV'], $p[$ddd[0]]['POVINN'], f($ddd[1]), $ddd[2]);
			}
			$out[] = '}';
		}
		$out[] = '\\enditems';
	}
	$out[] = '\\enditems';

	$out[] = '';

	$fn = sprintf('%s/out/analyza/fakulta.tex', dirname(__FILE__));
	file_put_contents($fn, implode("\n", $out));
}

function statistikaPredmetu($kod, &$d, &$predmety, &$out) {
	hypotezy($kod, $d, $predmety, $out);
	grafy($kod, $d, $predmety, $out);
	tabulky($kod, $d, $predmety, $out);
	statistikaFakulta($kod, $d, $predmety);
}

function statistikaFakulta($kod, &$d, &$predmety) {
	$predmet = $predmety[$kod];
	if ($predmet['POVINNY'] != 'A') return;

	$out = array();
	$out[] = sprintf('\\secc %s (%s)', $predmet['PNAZEV'], $kod);
	$out[] = '';

	$out[] = '\\begitems \\style N';

	$stats = array();
	$data = array();

        foreach ($d['pokusy'] as $termin => $znamky) {
                foreach ($znamky as $znamka) {
                        $klic = $znamka['AR'];
                        if (!isset($stats[$klic])) {
                                $stats[$klic] = array('celkem' => array(1=>0, 2=>0, 3=>0), 'u'=>array(1=>0,2=>0,3=>0), 'n'=>array(1=>0,2=>0,3=>0), 'rocnik' => array(),'ucitel'=>array());
                        }
			
			$u = $znamka['ZKOUSEJICI'];
			if (!isset($stats[$klic]['ucitel'][$u])) {
				$stats[$klic]['ucitel'][$u] = 0;
			}
			$stats[$klic]['ucitel'][$u]++;

                        $stats[$klic]['celkem'][$termin]++;
			if ($termin == 1) {
				$rocnik = $znamka['ROCNIK'];
				if (!isset($stats[$klic]['rocnik'][$rocnik])) {
					$stats[$klic]['rocnik'][$rocnik] = 0;
				}
				$stats[$klic]['rocnik'][$rocnik]++;
			}

                        if ($znamka['ZNAMKA'] != 4) {
                                if (!isset($data[$klic])) {
                                        $data[$klic] = array();
                                }
                                $data[$klic][] = $termin;
                                $stats[$klic]['u'][$termin]++;
                        } else {
                                $stats[$klic]['n'][$termin]++;
                        }
                }
        }

        ksort($stats);

	$out[] = '* O předmětu';
	$out[] = '\\begitems \\style a';
	$out[] = '* {\\bi Kolik studentů se pokusilo alespoň o jeden termín zkoušky?}';
	$out[] = '';
	$out[] = '\\noindent O alespoň jeden termín zkoušky se pokusilo:';
	$out[] = '\\begitems \\style -';
	foreach ($stats as $k => $s) {
		$out[] = sprintf('* v ak. roce %s: %d %s', $k, $s['celkem'][1], cesky($s['celkem'][1], 'student', 'studenti', 'studentů'));
	}
	$out[] = '\\enditems';
	$out[] = '\\bigskip';

	$out[] = '* {\\bi Kolik studentů předmět nezvládlo? (tzn. kolik studentů neuspělo u 2. OT)}';
	$out[] = '';
	$out[] = '\\noindent Při druhém opravném termínu neuspělo:';
	$out[] = '\\begitems \\style -';
	foreach ($stats as $k => $s) {
		$out[] = sprintf('* v ak. roce %s: %d %s', $k, $s['n'][3], cesky($s['n'][3], 'student', 'studenti', 'studentů'));
	}
	$out[] = '\\enditems';
	$out[] = '\\bigskip';

	$out[] = '* {\\bi Kolik studentů předmět vzdalo? (tzn. kolik studentů nevyčerpalo všechny pokusy a přesto neuspěli)}';
	$out[] = '';
	$out[] = '\\noindent Předmět nedokončilo, aniž by se pokusilo o druhý opravný termín:';
	$out[] = '\\begitems \\style -';
	foreach ($stats as $k => $s) {
		$n = $s['celkem'][1] - $s['u'][1] - $s['u'][2] - $s['u'][3] - $s['n'][3];
		$out[] = sprintf('* v ak. roce %s: %d %s', $k, $n, cesky($n, 'student', 'studenti', 'studentů'));
	}
	$out[] = '\\enditems';
	$out[] = '\\bigskip';

	$out[] = '* {\\bi Z jakého ročníku pochází studenti, kteří skládají tento předmět?}';
	$out[] = '\\begitems \\style -';
	foreach ($stats as $k => $s) {
		ksort($s['rocnik']);
		$rocniky = $s['rocnik'];
		
		$row = array();
		$celkem = array_sum($rocniky);
		foreach ($rocniky as $rocnik=>$pocet) {
			$row[] = sprintf('%d. ročník (%d %s $\\sim$ %s \\%%)', $rocnik, $pocet, cesky($pocet, 'student', 'studenti', 'studentů'), f($pocet/$celkem * 100));
		}
		
		$out[] = sprintf('* v ak. roce %s: %s', $k, implode(', ', $row));
	}
	$out[] = '\\enditems';
	$out[] = '* {\\bi Jaké jsou popisné statistiky výsledků z tohoto předmětu?}';
	$out[] = '\\nobreak\\bigskip\\nobreak';
	$tableData = tabulkaStatistikaZnamekTermin($d, 'AR', array(), '', 1);
	tabulka( 'Statistika známek podle akademického roku a termínů',  $tableData, $out);
	$out[] = '\\bigskip';
	$out[] = '\\enditems';

	$out[] = '* O zkoušejících';
	$out[] = '\\begitems \\style a';
	$out[] = '* {\\bi Hodnotí zkoušející srovnatelně?}';
	$out[] = '\\medskip';
	$data = agregujZnamky($d['pokusy'][1], 'ZKOUSEJICI');

	$chiData = array();
	foreach ($data as $k => $dd) {
		$row = array();

		for ($i = 1; $i <= 4; $i++) {
		 $row[] = $dd[$i];
		}
		$chiData[] = $row;
	}
	$chiRes = chi2test($chiData);

	if ($chiRes === false) {
		$out[] = '\\noindent{\\it Test nelze na poskytnutých datech provést.}';
	} else {
		if ($chiRes[95]) {
			$out[] = '\\noindent Na základě provedného $\chi^2$ testu {\\bi nelze zamítnout} hypotézu, že známka nezávisí na zkoušejícím. Známka tedy pravděpodobně nezávisí na tom, kdo studenta zkoušel.';
		} else {
			$out[] = '\\noindent Na základě provedného $\chi^2$ testu {\\bi zamítáme} hypotézu, že známka nezávisí na zkoušejícím. Známka tedy pravděpodobně závisí na tom, kdo studenta zkoušel.';
		}
	}
	//hypo($out, 0, TEST_CHI, 'Známka nezávisí na zkoušejícím', $data);
	$out[] = '\\bigskip\\penalty-300';
	$out[] = '* {\\bi Vybočuje některý zkoušející svým hodnocením?}';
	$out[] = '\\medskip';
	$out[] = 'Údaje jsou patrné z následující tabulky:';
	$tableData = tabulkaReziduaZnamekTermin($d, 'ZKOUSEJICI', array(), '', 1);
	$out[] = '\\nobreak\\bigskip\\nobreak';

	tabulka( 'Standardizovaná pearsonova rezidua známek podle učitele a termínů',  $tableData, $out);

	$out[] = '\\bigskip';
	$out[] = '* {\\bi Liší se udělená známka podle pohlaví studenta?}';
	$out[] = '\\medskip';
	
	$data = agregujZnamky($d['pokusy'][1], 'POHLAVI');

	$Fdata = array();
	foreach ($data as $k => $dd) {
		$row = array();

		for ($i = 1; $i <= 4; $i++) {
			$row[] = $dd[$i];
		}
		$Fdata[] = $row;
	}

	$Fres = anova($Fdata);

	if ($Fres === false) {
		$out[] = '\\noindent{\\it Test nelze na poskytnutých datech provést.}';
	} else {
		if ($Fres[95]) {
			$out[] = '\\noindent Na základě provedného $F$ testu {\\bi nelze zamítnout} hypotézu, že se udělená známka neliší podle pohlaví studenta. Známka tedy pravděpodobně nezávisí na pohlaví studenta.';
		} else {
			$out[] = '\\noindent Na základě provedného $F$ testu {\\bi zamítáme} hypotézu, že se udělená známka neliší podle pohlaví studenta. Známka tedy pravděpodobně závisí na pohlaví studenta.';
		}
	}
	//hypo($out, 0, TEST_CHI, 'Známka nezávisí na zkoušejícím', $data);
	$out[] = '\\bigskip';
	$out[] = '* {\\bi Jak často kdo zkouší?}';
	$out[] = '\\medskip';
	
	$out[] = '\\begitems \\style -';
	foreach ($stats as $rok => $data) {
		$out[] = sprintf('* %s:\\par\\nobreak', $rok);

		$out[] = '\\hfil\\table{|l|c|c|}{\\crl';
		$out[] = '\\malebf Jméno&\\malebf počet&\\malebf podíl\\crl';
		$sum = array_sum($data['ucitel']);
		arsort($data['ucitel']);
		foreach ($data['ucitel'] as $jmeno => $pocet) {
			$out[] = sprintf('\\male %s&\\male %d&\\male %s \\%%\\crl', $jmeno, $pocet, f($pocet/$sum * 100));
		}
		$out[] = '}\\bigskip';
	}
	$out[] = '\\enditems';
	$out[] = '\\enditems';

	$out[] = '\\enditems';
	$out[] = '\\bigskip\\vfill';


	$fn = sprintf('%s/out/analyza/%s.tex', dirname(__FILE__), $kod);	
	$dir = dirname($fn);
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}

	file_put_contents($fn, implode("\n", $out));
}

function hypotezy($kod, &$d, &$predmety, &$out) {
	$out[] = '\\notoc\\secc Hypotézy a slovní popis';
	$out[] = '';
	$out[] = '\\begitems \\style x \\parskip=0.5em';

	$data = agregujZnamky($d['pokusy'][1], 'ZKOUSEJICI');
	hypo($out, 0, TEST_CHI, 'Známka nezávisí na zkoušejícím', $data);

	$dataAR = agregujZnamkyEx($d['pokusy'][1], 'AR', 'ZKOUSEJICI');
	foreach ($dataAR as $rok => $data) {
		hypo($out, sprintf("0(%s)", $rok), TEST_CHI, sprintf('Známka nezávisí na zkoušejícím (ak. rok %s)', $rok), $data);
	}

	// $data = agregujZnamky($d['pokusy'][1], 'DEN');
	// hypo($out, 1, TEST_CHI, 'Známka nezávisí na dni v týdnu', $data);

	// $data = agregujZnamky($d['pokusy'][1], 'TYDEN');
	// hypo($out, 2, TEST_CHI, 'Známka nezávisí na týdnu', $data);
	
	//$data = agregujZnamky($d['pokusy'][1], 'POHLAVI');
	//hypo($out, 1, TEST_CHI, 'Známka nezávisí na pohlaví', $data);

	$data = agregujZnamky($d['pokusy'][1], 'ZKOUSEJICI');
	hypo($out, 1, TEST_F, 'Výběrové průměry učitelů se neliší', $data);

	$data = agregujZnamky($d['pokusy'][1], 'POHLAVI');
	hypo($out, 2, TEST_F, 'Výběrové průměry podle pohlaví se neliší', $data);

	$data = array();
	foreach ($d['ucitele'] as $jmeno => $dat) {
		$data[$jmeno] = agregujZnamky($dat[1], 'POHLAVI');
	}
	hypoTable($out, TEST_F, 'Výběrové průměry učitelů se neliší podle pohlaví', 'Vyučující', $data);

	$out[] = '\\enditems';
}

function grafy($kod, &$d, &$predmety, &$out) {
	$out[] = '\\notoc\\secc Grafy';
	$out[] = '';
	
	// 	$data = histogramData( $d['pokusy'], '' );
 	//graf('histo-terminy', 'Histogram známek podle termínu', GRAF_HISTOGRAM, $data, $out);
 
 	$data = histogramData( $d['pokusy'], 'ZKOUSEJICI' );
 	graf('histo-ucitele', 'Histogram známek podle učitele', GRAF_HISTOGRAM, $data, $kod, $out);

 	$data = grafData( $d['pokusy'], 'ZKOUSEJICI' );
 	graf('stat-ucitele', 'Statistika známek podle učitele', GRAF_LINE, $data, $kod, $out);
 
 	$data = histogramData( $d['pokusy'], 'POHLAVI', array(1=>'Muž', 'Žena') );
 	graf('histo-pohlavi', 'Histogram známek podle pohlaví', GRAF_HISTOGRAM, $data, $kod, $out);

 	$data = grafData( $d['pokusy'], 'POHLAVI', array(1=>'Muž', 'Žena') );
 	graf('stat-pohlavi', 'Statistika známek podle pohlaví', GRAF_LINE, $data, $kod, $out);

 	$data = grafData( $d['pokusy'], 'DEN', array(0=>'Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota') );
 	graf('stat-den', 'Statistika známek podle dne v týdnu', GRAF_LINE, $data, $kod, $out);

 	$data = grafData( $d['pokusy'], 'TYDEN', array(), 'ft' );
 	graf('stat-tyden', 'Statistika známek podle týdne', GRAF_LINE, $data, $kod, $out);

 	$data = histogramData( $d['pokusy'], 'ROCNIK');
 	graf('histo-rocniky', 'Histogram známek podle ročníku', GRAF_HISTOGRAM, $data, $kod, $out);

 	$data = grafData( $d['pokusy'], 'ROCNIK');
 	graf('stat-rocniky', 'Statistika známek podle ročníku', GRAF_LINE, $data, $kod, $out);
}


function tabulky($kod, &$d, &$predmety, &$out) {
	$out[] = '\\notoc\\secc Tabulky';
	$out[] = '';
	
	$tableData = tabulkaUspesnostPredmetu($d);
	tabulka( 'Statistika úspěšnosti předmětu',  $tableData, $out);

	$tableData = tabulkaCetnostZnamekTermin($d, 'AR');
	tabulka( 'Četnost známek podle akademického roku a termínů',  $tableData, $out);

	$tableData = tabulkaStatistikaZnamekTermin($d, 'AR');
	tabulka( 'Statistika známek podle akademického roku a termínů',  $tableData, $out);

	$tableData = tabulkaReziduaZnamekTermin($d, 'ZKOUSEJICI');
	tabulka( 'Rezidua známek podle učitele a termínů',  $tableData, $out);

	$data = rozdelZnamky($d, 'AR');
	foreach ($data as $rok => $dd) {
		$tableData = tabulkaReziduaZnamekTermin($dd, 'ZKOUSEJICI');
		tabulka( sprintf('Rezidua známek podle učitele a termínů (ak. rok %s)', $rok),  $tableData, $out);
	}

	$tableData = tabulkaCetnostZnamekTermin($d, 'ZKOUSEJICI');
	tabulka( 'Četnost známek podle učitele a termínů',  $tableData, $out);

	$tableData = tabulkaStatistikaZnamekTermin($d, 'ZKOUSEJICI');
	tabulka( 'Statistika známek podle učitele a termínů',  $tableData, $out);

	$tableData = tabulkaCetnostZnamekTermin($d, 'POHLAVI', array(1=>'Muž', '2' => 'Žena'));
	tabulka( 'Četnost známek podle pohlaví a termínů',  $tableData, $out);

	$tableData = tabulkaStatistikaZnamekTermin($d, 'POHLAVI', array(1=>'Muž', '2' => 'Žena'));
	tabulka( 'Statistika známek podle pohlaví a termínů',  $tableData, $out);

	$tableData = tabulkaCetnostZnamekTermin($d, array('ZKOUSEJICI', 'POHLAVI'), array(array(), array(1=>'Muž', '2' => 'Žena')));
	tabulka( 'Četnost známek podle učitele, pohlaví a termínů',  $tableData, $out);

	$tableData = tabulkaStatistikaZnamekTermin($d, array('ZKOUSEJICI', 'POHLAVI'), array(array(), array(1=>'Muž', '2' => 'Žena')));
	tabulka( 'Statistika známek podle učitele, pohlaví a termínů',  $tableData, $out);

	$tableData = tabulkaCetnostZnamekTermin($d, array('ZKOUSEJICI', 'AR'));
	tabulka( 'Četnost známek podle učitele, akademického roku a termínů',  $tableData, $out);

	$tableData = tabulkaStatistikaZnamekTermin($d, array('ZKOUSEJICI', 'AR'));
	tabulka( 'Statistika známek podle učitele, akademického roku a termínů',  $tableData, $out);

	$tableData = tabulkaCetnostZnamekTermin($d, 'DEN', array(0=>'Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'));
	tabulka( 'Četnost známek podle dne a termínů',  $tableData, $out);

	$tableData = tabulkaStatistikaZnamekTermin($d, 'DEN', array(0=>'Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'));
	tabulka( 'Statistika známek podle dne a termínů',  $tableData, $out);

	$tableData = tabulkaCetnostZnamekTermin($d, 'TYDEN', array(), 'ft');
	tabulka( 'Četnost známek podle týdne a termínů',  $tableData, $out);

	$tableData = tabulkaStatistikaZnamekTermin($d, 'TYDEN', array(), 'ft');
	tabulka( 'Statistika známek podle týdne a termínů',  $tableData, $out);

	$tableData = tabulkaCetnostZnamekTermin($d, 'ROCNIK');
	tabulka( 'Četnost známek podle ročníku a termínů',  $tableData, $out);

	$tableData = tabulkaStatistikaZnamekTermin($d, 'ROCNIK');
	tabulka( 'Statistika známek podle ročníku a termínů',  $tableData, $out);
}

function graf($fn, $nazev, $typ, $data, $dir, &$out) {

	$fnCmd  = sprintf('/tmp/%s.cmd', $fn);	
	$fnData = sprintf('/tmp/%s.dat', $fn);	
	$dir = sprintf('%s/out/grafy/%s', dirname(__FILE__), $dir);

	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}

	$grCmd = array();
	$grCmd[] = 'set terminal pdf size 24cm,8cm';
	$grCmd[] = sprintf("set output '%s/%s.pdf'", $dir, $fn);

	switch ($typ) {
		case GRAF_LINE:
			$grCmd[] = 'set style data linespoints'; 
			$grCmd[] = 'set key inside right top vertical Right noreverse noenhanced autotitles nobox'; 
			$grCmd[] = "set datafile missing '-'"; 
			$grCmd[] = 'set grid ytics lt 0 lw 1 lc rgb "#bbbbbb"';
			$grCmd[] = 'set grid xtics lt 0 lw 1 lc rgb "#bbbbbb"';
			$grCmd[] = 'set ylabel "[znamka]"';
			$grCmd[] = 'set xtics border in scale 0,0 nomirror rotate by -45  offset character 0, 0, 0 autojustify';
			//$grCmd[] = 'set xtics  norangelimit font ",8"';
			$grCmd[] = 'set xtics   ()';
			$grCmd[] = 'set yrange [0:5]';
			$grCmd[] = sprintf('set xrange [-1:%d]', count($data['data']));
			// $grCmd[] = 'set yrange [ 0.00000 : 110.0 ] noreverse nowriteback';
			$grCmd[] = sprintf("plot '%s' using 2:xticlabels(1) title columnheader(2), '' u 3 title columnheader(3), '' u 4 title columnheader(4), '' u 5 title columnheader(5)", $fnData);

			$row = array();
			$row[] = 'xx';
			foreach ($data['head'] as $h) {
				$row[] = $h;
			}
			$grData[] = implode(" ", $row);
			
			foreach($data['data'] as $k=>$v) {
				$row = array($k);
				
				foreach ($v as $d) {
					$row[] = $d;
				}
				$grData[] = implode(" ", $row);
			}
			
			break;

		case GRAF_HISTOGRAM:
			$grCmd[] = 'set style data histogram'; 
			$grCmd[] = 'set style fill   solid 1.00 border lt -1'; 
			$grCmd[] = 'set key inside right top vertical Right noreverse noenhanced autotitles nobox'; 
			$grCmd[] = "set datafile missing '-'"; 
			$grCmd[] = 'set xtics border in scale 0,0 nomirror rotate by -45 offset character 0, 0, 0 autojustify';
			//$grCmd[] = 'set xtics  norangelimit font ",8"';
			$grCmd[] = 'set xtics   ()';
			$grCmd[] = 'set ylabel "[%]"';
			$grCmd[] = 'set boxwidth 0.9 relative';
			// $grCmd[] = 'set yrange [ 0.00000 : 110.0 ] noreverse nowriteback';
			$grCmd[] = sprintf("plot '%s' using 2:xticlabels(1) ti col, '' u 3 ti col, '' u 4 ti col, '' u 5 ti col", $fnData);
			
			$row = array();
			$row[] = 'xx';
			foreach ($data['head'] as $h) {
				$row[] = $h;
			}
			$grData[] = implode(" ", $row);
			
			foreach($data['data'] as $k=>$v) {
				$row = array($k);
				
				foreach ($v as $d) {
					$row[] = $d;
				}
				$grData[] = implode(" ", $row);
			}
			break;
	}
	$grCmd[] = '';
	$grData[] = '';

	file_put_contents($fnCmd, implode("\n", $grCmd));
	file_put_contents($fnData, implode("\n", $grData));
	
	$cmd = sprintf('/opt/local/bin/gnuplot %s', $fnCmd);
	if (($_SERVER['argc'] > 1) && ($_SERVER['argv'][1] == 'g')) {
		echo sprintf("\t\tplot %s (%s, %s, %s)\n", $fn, $fnCmd, $fnData, $dir);
		system($cmd);
	}
	unlink($fnCmd);
	unlink($fnData);

	$out[] = sprintf('\\picw=0pt\\picheight=0.4\\vsize\\centerline{\\inspic %s/%s.pdf }\\nobreak', $dir, $fn);
	$out[] = sprintf('\\par\\caption/f %s\\par\\vskip 12pt plus 1fil minus 12pt\\penalty0', $nazev);
}

function histogramData($d, $diskriminator, $mapa = array(), $fn = '') {
	$data = array();

	foreach ($d[1] as $znamka) {
		if (is_array($diskriminator)) {
			$klic = array();
			$diskKlic = array();
			foreach($diskriminator as $k=>$d) {
				$x = $znamka[$d];
				$diskKlic[] = $znamka[$d];
				if (isset($mapa[$k][$x])) {
					$x = $mapa[$k][$x];
				} else if ($fn != '') {
					$x = $fn($x);
				}
				$klic[] = $x;
			}
			$x = trim($x);
			$klic = implode('/', $klic);
			$diskKlic = implode('/', $diskKlic);
		} else {
			$klic = $znamka[$diskriminator];
			$diskKlic = $znamka[$diskriminator];
			if (isset($mapa[$klic])) {
				$klic = $mapa[$klic];
			} else if ($fn != '') {
				$klic = $fn($klic);
			}
			$klic = trim($klic);
		}
		$klic = normalizuj($klic);

		if (!isset($data[$klic])) {
			$data[$klic] = array( 1=>0, 2=>0, 3=>0, 4=>0
				/*
				1 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
				2 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
				3 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
				4 => array( 1=>0, 2=>0, 3=>0, 4=>0 ),
				'klic' => $diskKlic,
				*/
				
			);
		}

		//$data[$klic][$termin][$znamka['ZNAMKA']]++;
		//$data[$klic][4][$znamka['ZNAMKA']]++;
		$data[$klic][$znamka['ZNAMKA']]++;
	}

	foreach ($data as $k=>$v) {
		$sum = array_sum($v);
		foreach ($v as $kk => $vv) {
			$data[$k][$kk] = $vv/$sum*100;
		}
	}

	if (empty($mapa)) {
		ksort($data);
	} else {
		uksort($data, function($a, $b) use ($mapa) {
			$A = array_search($a, $mapa);
			$B = array_search($a, $mapa);
			
			if ($A < $B) {
				return -1;
			} else if ($A > $B) {
				return 1;
			} else {
				return 0;
			}
		});
	}

	$out = array('head' => array('1','2','3','4'), 'data'=>$data);
	return $out;
}

function grafData($d, $diskriminator, $mapa = array(), $fn = '') {
	$data = array();
	$fnmap = array();
	$mapmap = array();

	foreach ($d[1] as $znamka) {
		if (is_array($diskriminator)) {
			$klic = array();
			$diskKlic = array();
			$mapMapKlic = array();
			$fnMapKlic = array();
			foreach($diskriminator as $k=>$d) {
				$x = $znamka[$d];
				$diskKlic[] = $znamka[$d];
				if (isset($mapa[$k][$x])) {
					$mapMapKlic[normalizuj($mapa[$k][$x])] = $x;
					$x = $mapa[$k][$x];
				} else if ($fn != '') {
					$fnMapKlic[normalizuj($fn($x))] = $x;
					$x = $fn($x);
				}
				$klic[] = $x;
			}
			$x = trim($x);
			$klic = implode('/', $klic);
			$diskKlic = implode('/', $diskKlic);
			if (!empty($mapMapKlic)) {
				$mapmap[implode('/', array_keys($mapMapKlic))] = implode('/',$mapMapKlic);
			}
			if (!empty($fnMapKlic)) {
				$fnmap[implode('/', array_keys($fnMapKlic))] = implode('/',$fnMapKlic);
			}
		} else {
			$klic = $znamka[$diskriminator];
			$diskKlic = $znamka[$diskriminator];
			if (isset($mapa[$klic])) {
				$mapamapa[normalizuj($mapa[$klic])] = $klic;
				$klic = $mapa[$klic];
			} else if ($fn != '') {
				$fnmap[normalizuj($fn($klic))] = $klic;
				$klic = $fn($klic);
			}
			$klic = trim($klic);
		}
		$klic = normalizuj($klic);

		if (!isset($data[$klic])) {
			$data[$klic] = array();
		}

		$data[$klic][] = $znamka['ZNAMKA'];
	}

	$dataOut = array();
	foreach ($data as $k=>$v) {
		$row = array(1=>'-', 2=>'-', 3=>'-', 4=>'-');
		if (count($v) > 0) {
			$avg = avg($v);
			$row[1] = $avg;
			$row[2] = med($v);
			$sdev = sdev($v);
			$row[3] = $avg-$sdev;
			$row[4] = $avg+$sdev;
		}
		$dataOut[$k] = $row;
	}

	if (empty($mapa)) {
		ksort($dataOut);
	} else if ($fn != '') {
		uksort($dataOut, function($a, $b) use ($fnmap) {
			$A = $fnmap[$a];
			$B = $fnmap[$b];
			
			if ($A < $B) {
				return -1;
			} else if ($A > $B) {
				return 1;
			} else {
				return 0;
			}
		});
	} else {
		uksort($dataOut, function($a, $b) use ($mapamapa) {
			$A = $mapamapa[$a];
			$B = $mapamapa[$b];
			
			if ($A < $B) {
				return -1;
			} else if ($A > $B) {
				return 1;
			} else {
				return 0;
			}
		});
	}

	$out = array('head' => array('prum','med','min','max'), 'data'=>$dataOut);
	return $out;
}

function hypoTable(&$out, $test, $popis, $nadpis, $data) {
	$out[] = sprintf('* %s:', $popis);
	
	switch ($test) {
		default:
			$out[] = 'Neznámý test hypotézy';
			break;

		case TEST_CHI:
			$chiData = array();
			$tabuL = array();
			foreach ($data as $k => $d) {
				$row = array();

				for ($i = 1; $i <= 4; $i++) {
				 $row[] = $d[$i];
				}
				$chiData[] = $row;
				$tabuL[] = $k;
			}
			$chiRes = chi2test($chiData);

			if ($chiRes === false) {
				$out[] = '{\\it Test nelze na poskytnutých datech provést.}';
			} else {
				$out[] = sprintf('{\\bf $H_{%d}$ %s}\\hfill\\nl', $hypoId, $chiRes[95] ? 'NEZAMÍTÁME' : 'ZAMÍTÁME');
				$out[] = sprintf('$\\chi^2 = %1.3f$, $n=%d$. ', $chiRes['chi'], $chiRes['n']);
				
				$hladiny = array();
				$hl = array(90 => 10, 95 => 5, 975 => 2.5, 99 => 1, 999 => 0.1);
				foreach ($hl as $k=>$v) {
					$hladiny[] = sprintf('$\\alpha=%s \\%% \\sim~%s$', $v, $chiRes[$k] ? '\\oplus' : '\\ominus');
				}
				$out[] = implode(', ', $hladiny);

				if (!empty($chiRes['tabu'])) {
					$t = array();
					foreach ($chiRes['tabu'] as $ta) {
						$t[] = $tabuL[$ta];
					}
					$out[] = sprintf('(z testu vyřazeni: %s).',implode(', ', $t));
				}
			}	

			break;
		case TEST_F:
			$out[] = '';
			$out[] = '\\hskip\\iindent\\table{|l|c|c|c|c|c|c|c|c|c|}{\\crx';
			$out[] = sprintf('\\malebf %s & \\malebf Výsledek & \\malebf F & \\malebf n & \\malebf d & \\male 10 \\%% & \\male 5 \\%% & \\male 2,5 \\%% & \\male 1 \\%% & \\male 0,1 \\%% \\crx', $nadpis);
			foreach ($data as $nadpisRadek => $dd) {
				$Fdata = array();
				foreach ($dd as $k => $d) {
					$row = array();

					for ($i = 1; $i <= 4; $i++) {
						$row[] = $d[$i];
					}
					$Fdata[] = $row;
				}
				
				$Fres = anova($Fdata);
				if ($Fres === false) {
					$out[] = sprintf('\\malebf %s & \\multispan9\\male\\quad Test nelze na zadaných datech provést \\hfil\\strut\\vrule\\crx', $nadpisRadek);
				} else {
					$out[] = sprintf('\\malebf %s & {\\malebf %s} &\\male %s &\\male %d &\\male %d &', $nadpisRadek, $Fres[95] ? 'NEZAMÍTÁME' : 'ZAMÍTÁME', f($Fres['F']), $Fres['n'], $Fres['d']);
					$hladiny = array();
					$hl = array(90 => 10, 95 => 5, 975 => 2.5, 99 => 1, 999 => 0.1);
					foreach ($hl as $k=>$v) {
						$hladiny[] = sprintf('\\male $%s$', $Fres[$k] ? '\\oplus' : '\\ominus');
					}
					$out[] = implode(' & ', $hladiny).'\\crx';
				}	
			}
			$out[] = '}';
			$out[] = '';
			$out[] = '\\vfil';
			break;
	}
}
function hypo(&$out, $hypoId, $test, $popis, $data) {
	$out[] = sprintf('* Hypotéza $H_{%s}$: %s.', $hypoId, $popis);
	
	switch ($test) {
		default:
			$out[] = 'Neznámý test hypotézy';
			break;

		case TEST_CHI:
			$chiData = array();
			$tabuL = array();
			foreach ($data as $k => $d) {
				$row = array();

				for ($i = 1; $i <= 4; $i++) {
				 $row[] = $d[$i];
				}
				$chiData[] = $row;
				$tabuL[] = $k;
			}
			$chiRes = chi2test($chiData);

			if ($chiRes === false) {
				$out[] = '{\\it Test nelze na poskytnutých datech provést.}';
			} else {
				$out[] = sprintf('{\\bf $H_{%s}$ %s}\\hfill\\nl', $hypoId, $chiRes[95] ? 'NEZAMÍTÁME' : 'ZAMÍTÁME');
				$out[] = sprintf('$\\chi^2 = %1.3f$, $n=%d$. ', $chiRes['chi'], $chiRes['n']);
				
				$hladiny = array();
				$hl = array(90 => 10, 95 => 5, 975 => 2.5, 99 => 1, 999 => 0.1);
				foreach ($hl as $k=>$v) {
					$hladiny[] = sprintf('$\\alpha=%s \\%% \\sim~%s$', $v, $chiRes[$k] ? '\\oplus' : '\\ominus');
				}
				$out[] = implode(', ', $hladiny);

				if (!empty($chiRes['tabu'])) {
					$t = array();
					foreach ($chiRes['tabu'] as $ta) {
						$t[] = $tabuL[$ta];
					}
					$out[] = sprintf('(z testu vyřazeni: %s).',implode(', ', $t));
				}
			}	

			break;
		case TEST_F:
			$Fdata = array();
			foreach ($data as $k => $d) {
				$row = array();

				for ($i = 1; $i <= 4; $i++) {
					$row[] = $d[$i];
				}
				$Fdata[] = $row;
			}
			
			$Fres = anova($Fdata);
			if ($Fres === false) {
				$out[] = '{\\it Test nelze na poskytnutých datech provést.}';
			} else {
				$out[] = sprintf('{\\bf $H_{%s}$ %s}\\hfill\\nl', $hypoId, $Fres[95] ? 'NEZAMÍTÁME' : 'ZAMÍTÁME');
				$out[] = sprintf('$F = %1.3f$, $n=%d$, $d=%d$. ', $Fres['F'], $Fres['n'], $Fres['d']);
				
				$hladiny = array();
				$hl = array(90 => 10, 95 => 5, 975 => 2.5, 99 => 1, 999 => 0.1);
				foreach ($hl as $k=>$v) {
					$hladiny[] = sprintf('$\\alpha=%s \\%% \\sim~%s$', $v, $Fres[$k] ? '\\oplus' : '\\ominus');
				}
				$out[] = implode(', ', $hladiny);
			}	
			break;
	}
}

function tabulkaCetnostZnamekTermin($d, $diskriminator, $mapa = array(), $fn = '') {
	$out = array('template' => '|P||Q|Q|Q|Q||Q|Q|Q|Q||Q|Q|Q|Q||Q|Q|Q|Q|', 'head' => array(), 'left' => array(), 'data' => array());

	$data = array();
	foreach ($d['pokusy'] as $termin => $znamky) {
		foreach ($znamky as $znamka) {
			if (is_array($diskriminator)) {
				$klic = array();
				$diskKlic = array();
				foreach($diskriminator as $k=>$d) {
					$x = $znamka[$d];
					$diskKlic[] = $znamka[$d];
					if (isset($mapa[$k][$x])) {
						$x = $mapa[$k][$x];
					} else if ($fn != '') {
						$x = $fn($x);
					}
					$klic[] = $x;
				}
				$x = trim($x);
				$klic = implode('@', $klic);
				$diskKlic = implode('@', $diskKlic);
			} else {
				$klic = $znamka[$diskriminator];
				$diskKlic = $znamka[$diskriminator];
				if (isset($mapa[$klic])) {
					$klic = $mapa[$klic];
				} else if ($fn != '') {
					$klic = $fn($klic);
				}
				$klic = trim($klic);
			}

			if (!isset($data[$klic])) {
				$data[$klic] = array( 
					1 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
					2 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
					3 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
					4 => array( 1=>0, 2=>0, 3=>0, 4=>0 ),
					'klic' => $diskKlic,
				);
			}

			$data[$klic][$termin][$znamka['ZNAMKA']]++;
			$data[$klic][4][$znamka['ZNAMKA']]++;
		}
	}
	
	
	$out['head'] = array(
		array('', 
			'\\multispan4\\quad\\hfil\\bf Řádný termín\\hfil\\quad\\tabvvline', 
			'\\multispan4\\quad\\hfil\\bf 1. opravný termín\\hfil\\quad\\tabvvline', 
			'\\multispan4\\quad\\hfil\\bf 2. opravný termín\\hfil\\quad\\tabvvline', 
			'\\multispan4\\quad\\hfil $\\Sigma$\\hfil\\quad\\vrule'
		),
		array('', 
			'\\malebf 1', '\\malebf 2', '\\malebf 3', '\\malebf 4', 
			'\\malebf 1', '\\malebf 2', '\\malebf 3', '\\malebf 4', 
			'\\malebf 1', '\\malebf 2', '\\malebf 3', '\\malebf 4', 
			'\\malebf 1', '\\malebf 2', '\\malebf 3', '\\malebf 4',),
	);

	uasort($data, 'compareDiacritics');
	
	foreach ($data as $klic => $terminy) {
		$row = array();

		$out['left'][] = sprintf('\\male %s', $klic);
		
		foreach ($terminy as $termin => $znamky) {
			if ($termin == 'klic') continue;

			$sum = array_sum($znamky);
		
			foreach ($znamky as $znamka => $pocet) {
				if ($sum != 0) {
					$row[] = sprintf('\\male %d\\break(%d\\%%)', $pocet, round($pocet / $sum * 100));
				} else {
					$row[] = sprintf('\\male %d\\break(---)', $pocet);
				}
			}
		}

		$out['data'][] = $row;
	}

	return $out;
}

function tabulkaReziduaZnamekTermin($d, $diskriminator, $mapa = array(), $fn = '', $Termin = 0) {
	if ($Termin == 0) {
		$out = array('template' => '|P||c|c|c|c||c|c|c|c||c|c|c|c||c|c|c|c|', 'head' => array(), 'left' => array(), 'data' => array());
	} else {
		$out = array('template' => '|P||c|c|c|c|', 'head' => array(), 'left' => array(), 'data' => array());
	}

	$pocty = array(
		1 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
		2 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
		3 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
		4 => array( 1=>0, 2=>0, 3=>0, 4=>0 ),
	);

	$data = array();
	foreach ($d['pokusy'] as $termin => $znamky) {
		foreach ($znamky as $znamka) {
			if (is_array($diskriminator)) {
				$klic = array();
				$diskKlic = array();
				foreach($diskriminator as $k=>$d) {
					$x = $znamka[$d];
					$diskKlic[] = $znamka[$d];
					if (isset($mapa[$k][$x])) {
						$x = $mapa[$k][$x];
					} else if ($fn != '') {
						$x = $fn($x);
					}
					$klic[] = $x;
				}
				$x = trim($x);
				$klic = implode('@', $klic);
				$diskKlic = implode('@', $diskKlic);
			} else {
				$klic = $znamka[$diskriminator];
				$diskKlic = $znamka[$diskriminator];
				if (isset($mapa[$klic])) {
					$klic = $mapa[$klic];
				} else if ($fn != '') {
					$klic = $fn($klic);
				}
				$klic = trim($klic);
			}

			if (!isset($data[$klic])) {
				$data[$klic] = array( 
					1 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
					2 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
					3 => array( 1=>0, 2=>0, 3=>0, 4=>0 ), 
					4 => array( 1=>0, 2=>0, 3=>0, 4=>0 ),
					'klic' => $diskKlic,
				);
			}

			$data[$klic][$termin][$znamka['ZNAMKA']]++;
			$data[$klic][4][$znamka['ZNAMKA']]++;

			$pocty[$termin][$znamka['ZNAMKA']]++;
			$pocty[4][$znamka['ZNAMKA']]++;
		}
	}

	$out['vysvetleni'] = '{\\maleit Tabulka obsahuje tzv. Pearsonova standardizovaná rezidua pro jednotlivé zkoušející a známky. Jde o normovaný rozdíl mezi skutečným a očekávaným počtem udělení příslušné známky. Hodnoty v tabulce pochází z normálního rozdělení N(0,1), a lze tedy dovodit, že hodnoty, které jsou v absolutní hodnotě větší než 2 jsou výjimečné a hodnoty, které jsou v absolutní hodnotě větší než 3 jsou extremní. Záporná hodnota znamená, že příslušný učitel udělil danou známku méně často než je z dat očekávatelné, kladná naopak znamená, že známka byla udělena častěji.}\par';	

	if ($Termin == 0) {
		$out['head'] = array(
			array('', 
				'\\multispan4\\quad\\hfil\\bf Řádný termín\\hfil\\quad\\tabvvline', 
				'\\multispan4\\quad\\hfil\\bf 1. opravný termín\\hfil\\quad\\tabvvline', 
				'\\multispan4\\quad\\hfil\\bf 2. opravný termín\\hfil\\quad\\tabvvline', 
				'\\multispan4\\quad\\hfil $\\Sigma$\\hfil\\quad\\vrule'
			),
			array('', 
				'\\malebf 1', '\\malebf 2', '\\malebf 3', '\\malebf 4', 
				'\\malebf 1', '\\malebf 2', '\\malebf 3', '\\malebf 4', 
				'\\malebf 1', '\\malebf 2', '\\malebf 3', '\\malebf 4', 
				'\\malebf 1', '\\malebf 2', '\\malebf 3', '\\malebf 4',),
		);
	} else {
		$T = array(1=>'Řádný termín', 2=>'1. opravný termín', 3 => '2. opravný termín', 4=>'$\\Sigma$');
		$out['head'] = array(
			array('', 
				sprintf('\\multispan4\\quad\\hfil\\bf %s\\hfil\\quad\\vrule', $T[$Termin]), 
			),
			array('', 
				'\\malebf 1', '\\malebf 2', '\\malebf 3', '\\malebf 4',
			), 
		);
	}

	uasort($data, 'compareDiacritics');

	foreach ($data as $klic => $terminy) {
		$row = array();

		$out['left'][] = sprintf('\\male %s', $klic);
		
		foreach ($terminy as $termin => $znamky) {
			if ($termin == 'klic') continue;
			if (($Termin != 0) && ($Termin != $termin)) {continue;}

			$sum = array_sum($znamky);
			$sumCelek = array_sum($pocty[$termin]);
	
			foreach ($znamky as $znamka => $pocet) {
				$pocetCelek = $pocty[$termin][$znamka];

				if (($sumCelek != 0) && ($sum != $sumCelek) && ($pocetCelek != $sumCelek) && ($pocetCelek != 0) && ($sum != 0)) {
					$oij = $pocet;
					$eij = ($pocetCelek/$sumCelek) * $sum;

					$rij = ($oij - $eij) / sqrt($eij * (1 -  $sum/$sumCelek) * (1 - $pocetCelek/$sumCelek));

					$row[] = sprintf('\\male%s %s%s', abs($rij) >= 2 ? 'bf':'', f($rij), abs($rij) >= 2 ? (abs($rij) >= 3 ? '!!' : '!'):'' );
				} else {
					$row[] = '---';
				}
			}
		}

		$out['data'][] = $row;
	}

	return $out;
}

function tabulkaUspesnostPredmetu($d) {
	$out = array('template' => '|l||c||c|c|c|c|c|c||c|c|', 'head' => array(), 'left' => array(), 'data' => array());

	$out['head'] = array(
                array('',
			'\\malebf celkem',
                        '\\multispan6\\quad\\hfil\\bf Úspěšný termín\\hfil\\quad\\tabvvline',
			'\\malebf nezvládlo',
			'\\malebf vzdalo',
                ),
                array('','',
                        '\\malebf počet', '\\malebf \\o', '\\malebf med', /*'\\malebf mod',*/ '\\malebf s', '\\malebf g${}_1$', '\\malebf $\\hat{e}_{Ni}$\\hbox{\\vrule height10pt depth3.5pt width0pt}',
			'\\multispan2\\hfil\\vrule'
                ),
        );

	$data = array();

	$stats = array();

	foreach ($d['pokusy'] as $termin => $znamky) {
		foreach ($znamky as $znamka) {
			$klic = $znamka['AR'];
			if (!isset($stats[$klic])) {
				$stats[$klic] = array('celkem' => array(1=>0, 2=>0, 3=>0), 'u'=>array(1=>0,2=>0,3=>0), 'n'=>array(1=>0,2=>0,3=>0));
			}
			
			$stats[$klic]['celkem'][$termin]++;
			
			if ($znamka['ZNAMKA'] != 4) {
				if (!isset($data[$klic])) {
					$data[$klic] = array();
				}
				$data[$klic][] = $termin;
				$stats[$klic]['u'][$termin]++;
			} else {
				$stats[$klic]['n'][$termin]++;
			}
		}
	}

	ksort($data);

	foreach ($data as $k => $v) {
		$out['left'][] = sprintf('\\male %s', $k);

		$row = array();
		$row[] = sprintf('\\male %s', f($stats[$k]['celkem'][1]));

		$row[] = sprintf('\\male %s', f(count($v)));
		$row[] = sprintf('\\male %s', f(avg($v)));
		$row[] = sprintf('\\male %s', f(med($v)));
		$row[] = sprintf('\\male %s', f(sdev($v)));
		$row[] = sprintf('\\male %s', f(g1($v)));
		$row[] = sprintf('\\male %s', 0); //f(sres($klic, $termin, $data)));

		$row[] = sprintf('\\male %s', f($stats[$k]['n'][3]));
		$row[] = sprintf('\\male %s', f($stats[$k]['celkem'][1] - $stats[$k]['u'][1] - $stats[$k]['u'][2] - $stats[$k]['u'][3] - $stats[$k]['n'][3]));
		$out['data'][] = $row;
	}

	return $out;	
}

function tabulkaStatistikaZnamekTermin($d, $diskriminator, $mapa = array(), $fn = '', $Termin = 0) {
	//$out = array('template' => '|P||c|c|c|c|c|c|c||c|c|c|c|c|c|c||c|c|c|c|c|c|c||c|c|c|c|c|c|c|', 'head' => array(), 'left' => array(), 'data' => array());
	if ($Termin == 0) {
		$out = array('template' => '|P||c|c|c|c|c|c||c|c|c|c|c|c||c|c|c|c|c|c||c|c|c|c|c|c|', 'head' => array(), 'left' => array(), 'data' => array());
	} else {
		$out = array('template' => '|P||c|c|c|c|c|c|', 'head' => array(), 'left' => array(), 'data' => array());
	}

	$data = array();
	foreach ($d['pokusy'] as $termin => $znamky) {
		foreach ($znamky as $znamka) {
			if (is_array($diskriminator)) {
				$klic = array();
				$diskKlic = array();
				foreach($diskriminator as $k=>$d) {
					$x = $znamka[$d];
					$diskKlic[] = $znamka[$d];
					if (isset($mapa[$k][$x])) {
						$x = $mapa[$k][$x];
					} else if ($fn != '') {
						$x = $fn($x);
					}
					$klic[] = $x;
				}
				$x = trim($x);
				$klic = implode('@', $klic);
				$diskKlic = implode('@', $diskKlic);
			} else {
				$klic = $znamka[$diskriminator];
				$diskKlic = $znamka[$diskriminator];
				if (isset($mapa[$klic])) {
					$klic = $mapa[$klic];
				} else if ($fn != '') {
					$klic = $fn($klic);
				}
				$klic = trim($klic);
			}

			if (!isset($data[$klic])) {
				$data[$klic] = array( 
					1 => array(), 
					2 => array(), 
					3 => array(), 
					4 => array(),
					'klic' => $diskKlic,  
				);
			}

			$data[$klic][$termin][] = $znamka['ZNAMKA'];
			$data[$klic][4][] = $znamka['ZNAMKA'];
		}
	}

	$out['vysvetleni'] = '{\\maleit Popis jednotlivých sloupců: {\\malebi počet} -- celkový počet známek, {\\o} -- aritmetický průměr známek, {\\malebi med} - medián, polovina hodnot je nižší a polovina vyšší než medián, {\\malebi s} -- směrodadná odchylka, většina hodnot se nachází do vzdálenosti směrodatné odchylky od průměru. Skoro všechny hodnoty se nachází do vzdálenosti dvou směrodatných odchylek od průměru. {\\malebi g${}_1$} -- šikmost, kladná hodnota znamená, že hodnoty jsou spíše lepší než průměr, záporná hodnota znamená, že hodnoty jsou spíše horší než průměr. Při vysoké absolutní hodnotě šikmosti přestane být průměr vypovídající hodnotou a data je lepší posuzovat podle mediánu. $\\hat{e}_{Ni}$ -- normovaná rezidua, jde o rozdíl mezi průměrem pro vybraný znak a průměrem celku, vydělený směrodatnou odchylkou. Hodnoty přesahující v absolutní hodnotě číslo 3 jsou zcela odlehlé, hodnoty přesahující v absolutní hodnotě číslo 2 jsou výjimečné. Kladná hodnota znamená vyšší průměr, záporná hodnota nižší průměr.}\par';	
	if ($Termin == 0) {	
		$out['head'] = array(
				array('', 
				'\\multispan6\\quad\\hfil\\bf Řádný termín\\hfil\\quad\\tabvvline', 
				'\\multispan6\\quad\\hfil\\bf 1. opravný termín\\hfil\\quad\\tabvvline', 
				'\\multispan6\\quad\\hfil\\bf 2. opravný termín\\hfil\\quad\\tabvvline', 
				'\\multispan6\\quad\\hfil $\\Sigma$\\hfil\\quad\\vrule'
			),
			array('', 
				'\\malebf počet', '\\malebf \\o', '\\malebf med', /*'\\malebf mod',*/ '\\malebf s', '\\malebf g${}_1$', '\\malebf $\\hat{e}_{Ni}$',
				'\\malebf počet', '\\malebf \\o', '\\malebf med', /*'\\malebf mod',*/ '\\malebf s', '\\malebf g${}_1$', '\\malebf $\\hat{e}_{Ni}$',
				'\\malebf počet', '\\malebf \\o', '\\malebf med', /*'\\malebf mod',*/ '\\malebf s', '\\malebf g${}_1$', '\\malebf $\\hat{e}_{Ni}$',
				'\\malebf počet', '\\malebf \\o', '\\malebf med', /*'\\malebf mod',*/ '\\malebf s', '\\malebf g${}_1$', '\\malebf $\\hat{e}_{Ni}$',
			),
		);
	} else {
		$T = array(1=>'Řádný termín', 2=>'1. opravný termín', 3 => '2. opravný termín', 4=>'$\\Sigma$');
		$out['head'] = array(
				array('', 
				sprintf('\\multispan6\\quad\\hfil\\bf %s\\hfil\\quad\\vrule', $T[$Termin]), 
			),
			array('', 
				'\\malebf počet', '\\malebf \\o', '\\malebf med', /*'\\malebf mod',*/ '\\malebf s', '\\malebf g${}_1$', '\\malebf $\\hat{e}_{Ni}$',
			),
		);

	}

	uasort($data, 'compareDiacritics');
	
	foreach ($data as $klic => $terminy) {
		$row = array();

		$out['left'][] = sprintf('\\male %s', $klic);
		
		foreach ($terminy as $termin => $znamky) {
			if ($termin == 'klic') continue;
			if (($Termin != 0) && ($Termin != $termin)) {continue;}
			$row[] = sprintf('\\male %s', f(count($znamky)));
			$row[] = sprintf('\\male %s', f(avg($znamky)));
			$row[] = sprintf('\\male %s', f(med($znamky)));
			//$row[] = sprintf('\\male %s', f(mod($znamky)));
			$row[] = sprintf('\\male %s', f(sdev($znamky)));
			$row[] = sprintf('\\male %s', f(g1($znamky)));
			$row[] = sprintf('\\male %s', f(sres($klic, $termin, $data)));
		}

		$out['data'][] = $row;
	}

	return $out;
}


function tabulka($nazev, $data, &$out, $centruj = true) {
	if (empty($data)) return;
	if (!is_array($data['head'][0])) {
		$data['head'] = array($data['head']);
	}

	if (count($data['data']) > 10) {
		$out[] = sprintf('\\setbox0=\\table{%s}{\\crx', $data['template']);
	} else {
		$out[] = sprintf('%s\\table{%s}{\\crx', $centruj ? '\\hfil':'', $data['template']);
	}

	foreach ($data['head'] as $row) {
		$out[] = implode('& ', $row).'\\crx';
	}
	
	$count = count($data['data']);
	$i = 0;
	foreach ($data['data'] as $k => $d) {
		$i++;
		$row = array();
		
		$row[] = strtr($data['left'][$k], array('@' => ',\penalty0 '));

		foreach ($d as $dd) {
			$row[] = $dd;
		}

		$out[] = implode('&', $row) . ($i != $count ? '\\crx' : '\\crx')."";
	}

	$out[] = '}';
	if (count($data['data']) > 10) {
		$out[] = '\\unvbox0';
	}

	$out[] = sprintf('\\nobreak\\par\\nobreak\\medskip\\caption/t %s\\par%s\\penalty0\\bigskip\\vfil', $nazev, isset($data['vysvetleni']) ? '\\nobreak\\medskip'.$data['vysvetleni'] : '');
}

