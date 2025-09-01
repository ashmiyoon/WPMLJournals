OUT = de.mo en.mo es.mo fr.mo it.mo ro.mo sh.mo sv.mo

all: $(OUT)

.PHONY: clean

%.mo: %.po
	msgfmt -o $@ $^

clean:
	rm $(OUT)
