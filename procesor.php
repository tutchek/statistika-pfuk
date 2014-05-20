<?php
date_default_timezone_set('Europe/Prague');

require_once dirname(__FILE__).'/functions.php';
require_once dirname(__FILE__).'/generator.php';

// load data

echo "Load: katedry\t";
$Xkatedry = loadCsvData('data/katedry.csv');
$katedry = vytvorKlice($Xkatedry, 'KOD');
echo "ok\n";

echo "Load: kody\t";
$Xpredmety = loadCsvData('data/kody.csv');
$predmety = vytvorKlice($Xpredmety, 'POVINN');
echo "ok\n";

$Xznamky = array();

for ($i = 1; $i <=3; $i++) {
	echo "Load: termin {$i}\t";
	$Xznamky[$i] = loadCsvData("data/pokus{$i}.csv");
	echo "ok\n";
}

$povinne = array();
foreach ($predmety as $predmet) {
	if ($predmet['POVINNY']) {
		$semestr = $predmet['SEMESTR'];
		$povinne[$predmet['POVINN']] = $semestr;
	}
}


$p = array();


for ($i = 1; $i <= 3; $i++) {
	foreach ($Xznamky[$i] as $znamka) {
		$uznano = $znamka['UZNANO'];
		if ($uznano == 'U') { continue; }	


		$kod = $znamka['ZPOVINN'];
		$ucitel = $znamka['ZKOUSEJICI'];
			if ($ucitel == 'Adamová, K.' && !in_array($kod, array('HP0891', 'HP0892'))) { continue; }

		$znamka['DEN'] = date_create($znamka['DATUM'])->format('w');
		$znamka['TYDEN'] = date_create($znamka['DATUM'])->format('Y-W');

		$datum = date_create($znamka['DATUM'])->format('Y-m');
		if (($datum < '2010-10') || ($datum > '2013-09')) continue;

		$y = date_create($znamka['DATUM'])->format('Y');
		$m = date_create($znamka['DATUM'])->format('m');
		if ($m < 10) {
			$ar = $y-1;
		} else {
			$ar = $y;
		}
		$znamka['AR'] = sprintf('%d/%d', $ar, $ar + 1 - 2000);

		$predmet = $predmety[$kod];
		$katedra = $katedry[$predmet['PGARANT']];
		

		if (!isset($p[$katedra['KOD']])) {
			$p[$katedra['KOD']] = array();
		}

		if (!isset($p[$katedra['KOD']][$predmet['POVINN']])) {
			$p[$katedra['KOD']][$predmet['POVINN']] = array(
				'ucitele' => array(),
				'pokusy' => array(1=>array(), 2=>array(), 3=>array()),
			);
		}	

		if (!isset($p[$katedra['KOD']][$predmet['POVINN']]['ucitele'][$znamka['ZKOUSEJICI']])) {
			$p[$katedra['KOD']][$predmet['POVINN']]['ucitele'][$znamka['ZKOUSEJICI']] = array(
				1=>array(), 2=>array(), 3=>array()
			);
		}

		$p[$katedra['KOD']][$predmet['POVINN']]['pokusy'][$i][] = $znamka;
		$p[$katedra['KOD']][$predmet['POVINN']]['ucitele'][$znamka['ZKOUSEJICI']][$i][] = $znamka;
	}
}

uksort($p, function ($a, $b) use($katedry) {
	return strcmp($katedry[$a]['NAZEV'], $katedry[$b]['NAZEV']);
});

foreach ($p as $k => $v) {
	uksort($p[$k], function ($a, $b) use($predmety) {
		return strcmp($predmety[$a]['PNAZEV'], $predmety[$b]['PNAZEV']);
	});

	foreach ($v as $kk => $v) {
		uksort($p[$k][$kk]['ucitele'], function ($a, $b) {
			return strcmp($a, $b);
		});
	}
}

$statistikaPovinne = statistikaPovinnych($p, $povinne, $predmety);
analyzaPovinnych($statistikaPovinne, $predmety);

$allOut = array();
$allFn = sprintf('%s/out/all.tex', dirname(__FILE__));

foreach ($p as $k => $v) {
	echo sprintf("%s\n", $katedry[$k]['NAZEV']);
	$katFn = sprintf('%s/out/%s.tex', dirname(__FILE__), $k);

	$katOut = array();
	$predmOut[] = sprintf('\\headname={%s}', $katedry[$k]['NAZEV']);
	$katOut[] = sprintf("\\chap %s", $katedry[$k]['NAZEV']);
	$katOut[] = '';

	foreach ($v as $kk => $vv) {
		$predmFn = sprintf('%s/out/%s/%s.tex', dirname(__FILE__), $k, $kk);
		$predmDir = dirname($predmFn);
		if (!is_dir($predmDir)) {
			mkdir($predmDir, 0700, true);
		}

		$predmOut = array();
		$predmOut[] = sprintf('\\headname={%s: %s -- %s}', $katedry[$k]['NAZEV'], $kk, $predmety[$kk]['PNAZEV']);
		$predmOut[] = sprintf("\\sec %s -- %s", $kk, $predmety[$kk]['PNAZEV']);
		$predmOut[] = '';
		
		statistikaPredmetu($kk, $vv, $predmety, $predmOut);
		$predmOut[] = '';
		$predmOut[] = '\\vfill';
		$predmOut[] = '\\eject';

		/*foreach ($vv['ucitele'] as $uk => $uv) {
			$predmOut[] = sprintf("\\secc %s", $uk);
			$predmOut[] = '';
			$predmOut[] = '';
		}*/
	
		file_put_contents($predmFn, implode("\n", $predmOut));

		echo sprintf("\t%s\n", $predmety[$kk]['PNAZEV']);
		$katOut[] = sprintf("\\input %s/%s", $k, $kk);;
		$katOut[] = '';
	}
	$katOut[] = '';
	$katOut[] = '';

	file_put_contents($katFn, implode("\n", $katOut));
	$allOut[] = sprintf('\\input %s', $k);
}

file_put_contents($allFn, implode("\n", $allOut));

