statistika-pfuk
===============

Nástroje na vygenerování statistiky pro PF UK

Skript je spustitelný pomocí příkazu make

Pro přegenerování grafů je třeba v out/Makefile přidat k příkazu php ../procesor.php parametr g

Skript očekává v adresáři data soubory pokus1.csv, pokus2.csv a pokus3.csv, v kódování utf-8 se sloupci oddělenými středníky.

Vzor dat:

ZPOVINN;ZNAMKA;DATUM;ZKOUSEJICI;UZNANO;ROCNIK;KOLIKATE_ZAPSANI;POHLAVI;
HP0011;3;19.5.2011 0:00;Novák, J.;S;3;;1;
