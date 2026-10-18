"""Build languages/<locale>.po/.mo from translation maps.

Usage: build-translations.py <strings.json> <dir with <locale>.json>
strings.json is the .pot as a list (see make-pot.sh); each <locale>.json maps the list index to
the translation (a list of forms for plural strings). Then run make-pot.sh's JSON/PHP step.
"""
import json
import os
import sys

import polib

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
PLURALS = {
    'ar': 'nplurals=6; plural=(n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 && n%100<=99 ? 4 : 5);',
    'ru_RU': 'nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);',
    'pl_PL': 'nplurals=3; plural=(n==1 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);',
    'ja': 'nplurals=1; plural=0;', 'zh_CN': 'nplurals=1; plural=0;', 'ko_KR': 'nplurals=1; plural=0;', 'vi': 'nplurals=1; plural=0;',
    'fr_FR': 'nplurals=2; plural=n > 1;', 'pt_BR': 'nplurals=2; plural=n > 1;', 'tr_TR': 'nplurals=2; plural=n > 1;',
    'fa_IR': 'nplurals=2; plural=n > 1;', 'hi_IN': 'nplurals=2; plural=n != 1;', 'id_ID': 'nplurals=2; plural=n > 1;',
}
LANG = {'ar': 'ar', 'ja': 'ja', 'vi': 'vi', 'ur': 'ur'}

strings = json.load(open(sys.argv[1], encoding='utf-8'))
pot = polib.pofile(os.path.join(ROOT, 'languages', 'hi-wpessential.pot'))
refs = {(e.msgctxt, e.msgid): e for e in pot}
for name in sorted(os.listdir(sys.argv[2])):
    if not name.endswith('.json'):
        continue
    locale = name[:-5]
    tr = json.load(open(os.path.join(sys.argv[2], name), encoding='utf-8'))
    po = polib.POFile()
    po.metadata = dict(pot.metadata)
    po.metadata.update({
        'Language': LANG.get(locale, locale),
        'Plural-Forms': PLURALS.get(locale, 'nplurals=2; plural=n != 1;'),
        'PO-Revision-Date': pot.metadata.get('POT-Creation-Date', ''),
        'Last-Translator': 'WPEssential Team <https://wpessential.org/>',
    })
    for i, s in enumerate(strings):
        src = refs[(s.get('ctx'), s['id'])]
        value = tr[str(i)]
        entry = polib.POEntry(msgid=s['id'], msgctxt=s.get('ctx'), occurrences=src.occurrences, comment=src.comment)
        if 'plural' in s:
            entry.msgid_plural = s['plural']
            forms = value if isinstance(value, list) else [value]
            entry.msgstr_plural = {n: f for n, f in enumerate(forms)}
        else:
            entry.msgstr = value
        po.append(entry)
    base = os.path.join(ROOT, 'languages', locale)
    po.save(base + '.po')
    po.save_as_mofile(base + '.mo')
    print(locale, len(po))
