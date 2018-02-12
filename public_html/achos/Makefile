objects := $(wildcard langs/??.po)

default: $(patsubst %.po,%.mo,$(objects))
	
langs/messages.po: *.php
	xgettext --keyword=T_ *.php -p langs --from-code=UTF-8 -i
#--omit-header
$(objects): langs/messages.po
	msgmerge -U $@ langs/messages.po
	touch $@
langs/%.mo: langs/%.po
	msgfmt $< -o $@
