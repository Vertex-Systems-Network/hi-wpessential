#!/usr/bin/env python3
"""Fails when a theme stylesheet defines a class without the wpe- prefix.

Classes owned by WordPress, WooCommerce, Elementor or plugins are allowed: the theme styles them but
does not invent them. Common prefixes are matched below; other core/plugin class names that existed when
this guard was added live in tools/prefix-allowlist.txt (one per line). Add to it only classes the theme
does not output itself. Run with --write-allowlist to regenerate it.
"""
import pathlib, re, sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
ALLOWED = re.compile(r'^(wpe-|wpek-|wp-|is-|has-|alignwide|alignfull|aligncenter|alignleft|alignright|screen-reader-text|'
                     r'woocommerce|wc-|wc_|product|cart|checkout|shop_|single_add|button|added_to_cart|onsale|price|amount|'
                     r'star-rating|quantity|qty|variations|reset_variations|elementor|e-|e_|menu-|sub-menu|current-|page_item|'
                     r'page-|post-|single|archive|home|blog|search|error404|logged-in|admin-bar|custom-logo|sticky|'
                     r'bypostauthor|comment|children|reply|says|avatar|gallery|wp_|dark|no-js|js|select2|dashicons|'
                     r'components-|block-editor|editor-|edit-post|interface-|customize-|attachment|hentry|type-|status-|format-|tag-|category-)')
LIST = ROOT / 'tools/prefix-allowlist.txt'
listed = set(LIST.read_text().split()) if LIST.exists() else set()
bad = {}
for css in sorted(ROOT.glob('style.css')) + sorted((ROOT / 'assets/css').glob('*.css')):
    text = re.sub(r'/\*.*?\*/', '', css.read_text(encoding='utf-8'), flags=re.S)
    text = re.sub(r'url\([^)]*\)', '', text)
    for sel in re.findall(r'([^{}]+)\{', text):
        if sel.strip().startswith('@'):
            continue
        for cls in re.findall(r'\.(-?[A-Za-z_][\w-]*)', sel):
            if not ALLOWED.match(cls) and cls not in listed:
                bad.setdefault(cls, css.relative_to(ROOT).as_posix())
if bad and '--write-allowlist' in sys.argv:
    LIST.write_text('\n'.join(sorted(listed | set(bad))) + '\n')
    print(f'Wrote {len(bad)} new names to {LIST.name}')
    sys.exit(0)
if bad:
    for cls, f in sorted(bad.items()):
        print(f'{f}: .{cls} needs the wpe- prefix')
    sys.exit(1)
print('Prefix guard: all theme classes use wpe- (or a WordPress/plugin-owned class).')
