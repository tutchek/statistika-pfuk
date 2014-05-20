<?php


function loadCsvData($fileName) {
	$f = fopen(dirname(__FILE__).'/'.$fileName, 'rb');

	$data = array();
	$head = null;
	while (($row = fgetcsv($f, 0, ';', '"')) !== false) {
		if (is_null($head)) {
			$head = $row;
		} else {
			$data[] = array_combine($head, $row);
		}
	}
	fclose($f); 
	return $data;
}

function vytvorKlice($zdroj, $sloupec) {
	$out = array();

	foreach ($zdroj as $row) {
		$out[$row[$sloupec]] = $row;
	}

	return $out;
}

function tabule($tableHead, $tableLeft, $tableData) {
	$out = array();

	$colCount = count($tableHead);
	
	$signatura = '|l';
	for ($i = 1; $i < $colCount; $i++) {
		$signatura .= '|c';
	}
	$signatura .= '|';

	$out[] = sprintf('\\table{%s}{\crl', $signatura);
	$out[] = sprintf('%s \crli', implode(' & ', $tableHead));

	foreach ($tableData as $k=>$row) {
		$out[] = sprintf('%s & %s \crli', $tableLeft[$k], implode(' & ', $row));
	}
	$out[] = '}';

	return implode("\n", $out);
}

function avg($arr) {
	$cnt = count($arr);
	if ($cnt > 0) { 
		return array_sum($arr)/count($arr);
	} else {
		return 0;
	}
}

function mod($arr) {
	if (count($arr) > 0) {
		$cnt = array_count_values($arr);
		arsort($cnt);
	
		$out = array();
		$max = null;
		foreach ($cnt as $k=>$v) {
			if (is_null($max) || ($max == $v)) {
				$max = $v;
				$out[] = $k;
			}
		}		
		sort($out);
		return implode('; ', $out);
	} else {
		return '---';
	}
}

function med($arr) {
	$cnt = count($arr);
	if ($cnt > 0) { 
		sort($arr);
		if ($cnt % 2 == 0) {
			return ($arr[$cnt/2-1] + $arr[$cnt/2])/2;
		} else {
			return $arr[round($cnt/2)-1];
		}
	} else {
		return '---';
	}
}

function sdev($arr) {
	$cnt = count($arr);
	if ($cnt < 2) {
		return '---';
	}

	$sum = 0;
	$sumSq = 0;

	foreach ($arr as $x) {
		$sum += $x;
		$sumSq += $x * $x;
	}

	return sqrt(($sumSq - $cnt * ($sum / $cnt) * ($sum / $cnt)) / ($cnt - 1));
}

function g1($arr) {
	$avg = avg($arr);
	$n = count($arr);

	$sumA = 0;
	$sumB = 0;

	foreach ($arr as $x) {
		$sumA += pow(($x - $avg), 3);
		$sumB += pow(($x - $avg), 2);
	}

	if ($sumB == 0) {
		return '---';
	}

	return sqrt($n) * $sumA / pow($sumB, 1.5);
}

function f($_) {
	if (is_float($_)) {
		return strtr(sprintf('%1.2f', $_), '.', ',');
	} else {
		return $_;
	}
}


function ft($_) {
	list($y, $w) = explode('-', $_);

	$m = sprintf('%04dW%02d1', $y, $w);
	$f = sprintf('%04dW%02d5', $y, $w);

	return sprintf( '%s -- %s', date_create($m)->format('d.m.y'), date_create($f)->format('d.m.y') );
}

function ftx($_) {
	list($y, $w) = explode('-', $_);

	$m = sprintf('%04dW%02d1', $y, $w);
	$f = sprintf('%04dW%02d5', $y, $w);

	return sprintf( '%s-%s', date_create($m)->format('d.m.y'), date_create($f)->format('d.m.y') );
}

function chi2test($p) {
	$r = count($p);
	$s = count($p[0]);

	$tabu = array();
	$tabuS = array();
	
	do {
		$stop = false;

		$sumRadky = array();
		$sumSloupce = array();
		$sum = 0;

		for ($j = 0; $j < $s; $j++) {
			$sumSloupce[$j] = 0;
		}

		for ($i = 0; $i < $r; $i++) {
			if (in_array($i, $tabu)) continue;

			$sumRadky[$i] = 0;
			for ($j = 0; $j < $s; $j++) {
				if (in_array($j, $tabuS)) continue;

				$sumRadky[$i] += $p[$i][$j];
				$sumSloupce[$j] += $p[$i][$j];
				$sum += $p[$i][$j];
			}
		}
		if ($sum == 0) {
			return false;
		}

		foreach ($sumSloupce as $k => $v) {
			if (($v == 0) && !in_array($k, $tabuS)) {
				$tabuS[] = $k;
				$stop = true;
			}
		} 

		for ($i = 0; $i < $r; $i++) {
			if (in_array($i, $tabu)) continue;
			for ($j = 0; $j < $s; $j++) {
				if (in_array($j, $tabuS)) continue;
				$oij = $sumRadky[$i] * $sumSloupce[$j] / $sum;
				if (($oij < 5) && (!in_array($i, $tabu))) {
					$tabu[] = $i;
					$stop = true;
				}
			}
		}

		if ((($r - count($tabu)) < 3) || (($s - count($tabuS)) < 3)) {
			return false;
		}
	} while ($stop);


	$chi = 0;

	for ($i = 0; $i < $r; $i++) {
		if (in_array($i, $tabu)) continue;
		for ($j = 0; $j < $s; $j++) {
			if (in_array($j, $tabuS)) continue;

			$oij = $sumRadky[$i] * $sumSloupce[$j] / $sum;
			$chi += ( $p[$i][$j] - $oij ) * ( $p[$i][$j] - $oij ) / $oij;		
		}
	}

	$n = ($r - count($tabu) - 1) * ($s - count($tabuS) - 1);

	$result = array(
		'chi' => $chi,
		'n'=>$n,
		90 => ($chi <= getChi2(90, $n)),
		95 => ($chi <= getChi2(95, $n)),
		975 => ($chi <= getChi2(975, $n)),
		99 => ($chi <= getChi2(99, $n)),
		999 => ($chi <= getChi2(999, $n)),
		'tabu' => $tabu,
	);
	return $result;
}

function getChi2($p, $n) {
	static $chi2table = null;

	if (is_null($chi2table)) {
		$chi2table = array();
		$data = file(dirname(__FILE__).'/data/chi2.dat');
		$head = null;
		foreach ($data as $line) {
			$line = trim($line);
			if ($line == '') continue;
			
			$r = explode("\t", $line);
			$n = array_shift($r);
			if (is_null($head)) {
				$head = array();
				foreach ($r as $k => $v) {
					$x = (int)(1000 - ($v * 1000));
					$x = (int) (substr($x, 2, 1) == 0 ? substr($x, 0, 2) : $x);
					$head[$k] = (int) $x;
				}
			} else {
				$chi2table[$n] = array();
				foreach ($r as $k=>$v) {
					$chi2table[$n][$head[$k]] = $v;
				}
			}
		}
	}

	return $chi2table[$n][$p];
}

function anova($data) {
	$dataCelkem = array();
	$k = count($data);
	$N = 0;
	$rozdilyA = 0;
	$rozdilyB = 0;

	if ($k < 2) return false;

	foreach ($data as $i => $row) {
		if (count($row) == 0) return false;
		foreach ($row as $x) {
			$dataCelkem[] = $x;
		}
	}
	$N = count($dataCelkem);
	if (($N-$k) < 1) return false;
	$prumerCelkem = avg($dataCelkem);
	

	foreach ($data as $i => $row) {
		$prumer = avg($row);
		$rozdilyA += ($prumer - $prumerCelkem) * ($prumer - $prumerCelkem) * count($row);
		$rozdilB = 0;

		foreach ($row as $x) {
			$rozdilB += ($x - $prumer) * ($x - $prumer);
		}
		$rozdilyB += $rozdilB;
	}

	$F = (($N - $k) * $rozdilyA) / (($k - 1) * $rozdilyB);

	$n = $k-1;
	$d = $N-$k;
	
	return array(
		'F' => $F,
		'n' => $n,
		'd' => $d,
		90 => ($F <= getF(90, $n, $d)),
		95 => ($F <= getF(95, $n, $d)),
		975 =>($F <= getF(975, $n, $d)),
		99 => ($F <= getF(99, $n, $d)),
		999 =>($F <= getF(999, $n, $d)),
	);
}

function getF($p, $n, $d) {
	static $Ftable = null;

	if (is_null($Ftable)) {
		$PP = array(90, 95, 975, 99, 999);

		$Ftable = array();
		foreach ($PP as $P) {
			$Ftable[$P] = array();

			$head = null;
			$data = file(dirname(__FILE__).'/data/F'.$P.'.dat');
			foreach ($data as $line) {
				$line = trim($line);
				if ($line == '') continue;
				
				$r = explode("\t", $line);
				$n = array_shift($r);

				if (is_null($head)) {
					$head = $r;
				} else {
					$Ftable[$P][$n] = array();
					foreach ($r as $k=>$v) {
						$Ftable[$P][$n][$head[$k]] = $v;
					}
				}
			}
		}
	}

	return $Ftable[$p][$n][$d];
}

function dvt($_) {
	$dvt = array(0=>'Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota');
	return isset($dvt[$_]) ? $dvt[$_] : '??';
}

function rozdelZnamky($data, $kriterium) {
	$out = array();

	foreach ($data['pokusy'] as $termin=>$znamky) {
		foreach ($znamky as $znamka) {
			$k = $znamka[$kriterium];
			if (!isset($out[$k])) {
				$out[$k] = array('pokusy'=>array()); 
			}

			if (!isset($out[$k]['pokusy'][$termin])) {
				$out[$k]['pokusy'][$termin] = array();
			}

			$out[$k]['pokusy'][$termin][] = $znamka;
		}
	}

	return $out;
}

function agregujZnamky($data, $kriterium) {
	$out = array();

	foreach ($data as $d) {
		$k = $d[$kriterium];
		if (!isset($out[$k])) {
			$out[$k] = array(1=>0, 2=>0, 3=>0, 4=>0);
		}

		$out[$k][(int)$d['ZNAMKA']]++;
	}

	return $out;
}

function agregujZnamkyEx($data, $kriterium, $kriterium2) {
	$out = array();

	foreach ($data as $d) {
		$k = $d[$kriterium];
		$k2 = $d[$kriterium2];

		if (!isset($out[$k])) {
			$out[$k] = array();
		}

		if (!isset($out[$k][$k2])) {
			$out[$k][$k2] = array(1=>0, 2=>0, 3=>0, 4=>0);
		}

		$out[$k][$k2][(int)$d['ZNAMKA']]++;
	}

	return $out;
}

function diskriminujZnamky($data, $kriterium) {
	$out = array();

	foreach ($data as $d) {
		$k = $d[$kriterium];
		if (!isset($out[$k])) {
			$out[$k] = array();
		}

		$out[$k][] = (int)$d['ZNAMKA'];
	}

	return $out;
}

function stripdiacritics($_) {
	return strtolower(preg_replace('~[^a-z,0-9/]~i', '', iconv('utf-8', 'ascii//translit', $_)));
}
function compareDiacritics($a, $b) {
	$a = stripdiacritics((string) $a['klic']);
	$b = stripdiacritics((string) $b['klic']);

	return strcmp($a, $b);
}

function normalizuj($_) {
	$_ = preg_replace('~[^a-z,0-9/.-]~i', '', iconv('utf-8', 'ascii//translit', $_));
	$_ = preg_replace('~\s*~ui', '', $_);
	return $_;
}

function sres($klic, $termin, $d) {
	$prumery = array();
	$vsechnyZnamky = array();
	foreach ($d as $k => $terminy) {
		$znamky = $terminy[$termin];
		if (count($znamky) > 0) {
			$prumer = avg($znamky);	
			$prumery[$k] = $prumer;
			$vsechnyZnamky = array_merge($vsechnyZnamky, $znamky);
		}
	}

	$prumerCelkem = avg($vsechnyZnamky);
	$rozdily = array();
	foreach ($prumery as $k => $prumer) {
		$rozdily[$k] = $prumer - $prumerCelkem;
	}
	
	$sd = sdev($rozdily);
	if (($sd == 0) || (!isset($rozdily[$klic]))) {
		return '---';
	}
	return $rozdily[$klic]/$sd;
}

function cesky($n, $jeden, $dva, $vic) {
	$N = $n % 10;
	
	if ($N == 1) {
		return $jeden;
	} else if (($N > 1) && ($N < 5)) {
		return $dva;
	} else {
		return $vic;
	}
}
