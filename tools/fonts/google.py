"""Build assets/data/google-fonts.json from Google Fonts' public metadata.

Usage: python3 tools/fonts/google.py <metadata.json>   (download https://fonts.google.com/metadata/fonts first)
Each entry: [family, category, weights, italic, variable_weight_range or null, popularity rank].
"""
import json
import pathlib
import sys

meta = json.loads(pathlib.Path(sys.argv[1]).read_text())
out = []
for f in meta['familyMetadataList']:
    if f.get('isNoto') and f.get('popularity', 9999) > 400:
        continue  # keep the popular Noto families only; the rest are script-specific
    weights = sorted({int(k.rstrip('i')) for k in f.get('fonts', {}) if k.rstrip('i').isdigit()})
    italic = any(k.endswith('i') for k in f.get('fonts', {}))
    axis = next((a for a in f.get('axes', []) if a.get('tag') == 'wght'), None)
    out.append([
        f['family'],
        {'Sans Serif': 'sans', 'Serif': 'serif', 'Display': 'display', 'Handwriting': 'hand', 'Monospace': 'mono'}.get(f.get('category'), 'sans'),
        weights,
        1 if italic else 0,
        [int(axis['min']), int(axis['max'])] if axis else None,
        f.get('popularity', 9999),
    ])
out.sort(key=lambda r: r[5])
dest = pathlib.Path(__file__).resolve().parents[2] / 'assets' / 'data' / 'google-fonts.json'
dest.write_text(json.dumps(out, separators=(',', ':')))
print(len(out), 'families,', dest.stat().st_size // 1024, 'KB')
