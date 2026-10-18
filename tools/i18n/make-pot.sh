#!/usr/bin/env bash
# Regenerate the .pot files, then (with --compile) the JS .json and .l10n.php files from the .po files.
# Needs WP-CLI (`wp`). theme-i18n.json is fetched from GitHub because make-pot's own download
# from svn.wordpress.org is not always reachable.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
WP="${WP:-wp}"
cd "$ROOT"
$WP i18n make-pot . languages/hi-wpessential.pot --slug=hi-wpessential --domain=hi-wpessential --skip-theme-json \
	--exclude=server,tools,demos,dist,build,docs,claude-plugins,vendor,node_modules \
	--headers='{"Report-Msgid-Bugs-To":"https://wpessential.org/support/","Language-Team":"WPEssential Team <https://wpessential.org/>"}'
curl -sSfL -o /tmp/theme-i18n.json https://raw.githubusercontent.com/WordPress/wordpress-develop/trunk/src/wp-includes/theme-i18n.json
python3 tools/i18n/theme-json-strings.py /tmp/theme-i18n.json languages/hi-wpessential.pot
$WP i18n make-pot server/wpessential-server server/wpessential-server/languages/wpessential-server.pot --domain=wpessential-server --exclude=vendor
if [ "${1:-}" = "--compile" ]; then
	# New strings are added to every .po (untranslated: they show in English until translated).
	if ls languages/*.po >/dev/null 2>&1; then
		$WP i18n update-po languages/hi-wpessential.pot languages/
		$WP i18n make-mo languages/
	fi
	rm -f languages/*.json languages/*.l10n.php
	$WP i18n make-json languages --no-purge
	$WP i18n make-php languages
fi
