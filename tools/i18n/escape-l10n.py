"""Escape control characters in languages/*.l10n.php (run after `wp i18n make-php`).

WP-CLI writes the gettext context separator (byte 0x04) raw inside single-quoted strings.
PHP reads it fine, but the WordPress.org theme upload scan flags "non-printable characters".
This rewrites each raw 0x04 as ' . "\\4" . ' so the files hold printable text only and the
loaded arrays stay identical.
"""
import glob
import os

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
for path in sorted(glob.glob(os.path.join(ROOT, 'languages', '*.l10n.php'))):
    data = open(path, 'rb').read()
    if b'\x04' not in data:
        continue
    open(path, 'wb').write(data.replace(b'\x04', b"' . \"\\4\" . '"))
    print('escaped', os.path.relpath(path, ROOT))
