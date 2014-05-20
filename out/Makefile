all: statistika.pdf analyza.pdf

statistika.pdf: $(wildcard ../data/*) ../functions.php ../generator.php ../procesor.php statistika.tex uvod.tex
	php ../procesor.php
	pdfcsplain statistika.tex
	pdfcsplain statistika.tex
	cp statistika.pdf ../

.PHONY:	tex

tex:
	pdfcsplain statistika.tex

analyza.pdf: analyza.tex $(wildcard analyza/*)
	pdfcsplain analyza.tex
	pdfcsplain analyza.tex
	cp analyza.pdf ../
