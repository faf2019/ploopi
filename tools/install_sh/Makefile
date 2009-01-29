all: distribution


distribution: console_install.tar.gz

console_install.tar.gz: install.sh fr.po en.po
	mkdir -p ./tmp/config
	mkdir -p ./tmp/config/en/LC_MESSAGES
	mkdir -p ./tmp/config/fr/LC_MESSAGES
	cp install.sh ./tmp/config/
	msgfmt -o ./tmp/config/en/LC_MESSAGES/ploopi_console.mo en.po
	msgfmt -o ./tmp/config/fr/LC_MESSAGES/ploopi_console.mo fr.po
	tar -C ./tmp -czf ./tmp/console_install.tar.gz config
    

clean:
	rm -rf ./tmp
