"""Append theme.json / styles/*.json strings to the .pot (what `wp i18n make-pot` does
when it can reach theme-i18n.json). Usage: theme-json-strings.py <theme-i18n.json> <pot>"""
import glob
import json
import os
import sys

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
schema = json.load(open(sys.argv[1]))
pot = sys.argv[2]
found = {}


def walk(data, spec, ref):
    if isinstance(spec, str):
        if isinstance(data, str) and data:
            found.setdefault((spec, data), set()).add(ref)
    elif isinstance(spec, list):
        for item in data if isinstance(data, list) else []:
            walk(item, spec[0], ref)
    elif isinstance(spec, dict) and isinstance(data, dict):
        for key, sub in spec.items():
            if key == '*':
                for k, v in data.items():
                    walk(v, sub, ref)
            elif key in data:
                walk(data[key], sub, ref)


for path in ['theme.json'] + sorted(glob.glob(os.path.join(ROOT, 'styles', '*.json'))):
    full = os.path.join(ROOT, path)
    walk(json.load(open(full)), schema, os.path.relpath(full, ROOT))

existing = open(pot, encoding='utf-8').read()


def q(s):
    return '"' + s.replace('\\', '\\\\').replace('"', '\\"') + '"'


out = []
for (ctx, text), refs in sorted(found.items()):
    if 'msgctxt %s\nmsgid %s\n' % (q(ctx), q(text)) in existing:
        continue
    out.append('#: %s\nmsgctxt %s\nmsgid %s\nmsgstr ""\n' % (' '.join(sorted(refs)), q(ctx), q(text)))
with open(pot, 'a', encoding='utf-8') as f:
    f.write('\n' + '\n'.join(out))
print('added', len(out), 'theme.json strings')
