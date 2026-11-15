#!/usr/bin/env bash
# Build the release zips into build/:
#   hi-wpessential.zip        theme (+ bundled Core), only the files WordPress needs (.distignore)
#   demos/<id>-<builder>.zip  demo packages for the server (from dist/)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/build"
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT
rm -rf "$OUT" && mkdir -p "$OUT/demos"

# Theme: copy everything except the .distignore patterns.
EXCLUDES=()
while IFS= read -r line; do
	line="${line%%#*}"; line="${line// /}"
	[ -n "$line" ] && EXCLUDES+=(--exclude="./$line")
done < "$ROOT/.distignore"
mkdir -p "$STAGE/hi-wpessential"
tar -C "$ROOT" "${EXCLUDES[@]}" --exclude='.DS_Store' --exclude='__pycache__' -cf - . | tar -C "$STAGE/hi-wpessential" -xf -
(cd "$STAGE" && zip -qr9 "$OUT/hi-wpessential.zip" hi-wpessential)
# The unzipped theme folder too, for the CI artifact (no zip inside a zip).
mkdir -p "$OUT/theme" && cp -a "$STAGE/hi-wpessential" "$OUT/theme/"

# Demo packages.
python3 "$ROOT/tools/demo-builder/build.py" --zip >/dev/null
cp "$ROOT"/dist/*.zip "$OUT/demos/"

ls -lh "$OUT" "$OUT/demos"
