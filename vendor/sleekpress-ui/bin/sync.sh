#!/usr/bin/env bash
#
# Copy this @sleekpress/ui package into another plugin's vendor/ directory.
#
# Usage:
#   ./bin/sync.sh /path/to/target-plugin
#
# Copies <this-package>/  ->  <target-plugin>/vendor/sleekpress-ui/
# (excludes node_modules / .git). After syncing, run `npm run build` in the
# target plugin.

set -euo pipefail

SRC_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TARGET="${1:-}"

if [[ -z "$TARGET" ]]; then
	echo "Usage: $0 /path/to/target-plugin" >&2
	exit 1
fi
if [[ ! -d "$TARGET" ]]; then
	echo "Target plugin directory not found: $TARGET" >&2
	exit 1
fi

DEST="$TARGET/vendor/sleekpress-ui"
mkdir -p "$DEST"

rsync -a --delete \
	--exclude '.git' \
	--exclude 'node_modules' \
	"$SRC_DIR/" "$DEST/"

echo "Synced @sleekpress/ui -> $DEST"
echo "Next: cd \"$TARGET\" && npm install && npm run build"
