#!/bin/bash
# Package a Cartxis theme into a distributable zip file
# Usage: ./scripts/package-theme.sh <theme-slug>
# Example: ./scripts/package-theme.sh dmart-electronics

set -e

THEME_SLUG="${1:-dmart-electronics}"
THEMES_DIR="$(cd "$(dirname "$0")/.." && pwd)/themes"
THEME_DIR="$THEMES_DIR/$THEME_SLUG"
OUTPUT_DIR="$(cd "$(dirname "$0")/.." && pwd)/storage/app/theme-packages"

if [ ! -d "$THEME_DIR" ]; then
    echo "Error: Theme directory not found: $THEME_DIR"
    exit 1
fi

if [ ! -f "$THEME_DIR/theme.json" ]; then
    echo "Error: theme.json not found in $THEME_DIR"
    exit 1
fi

# Read version from theme.json
VERSION=$(python3 -c "import json; print(json.load(open('$THEME_DIR/theme.json'))['version'])" 2>/dev/null || echo "1.0.0")

mkdir -p "$OUTPUT_DIR"

OUTPUT_FILE="$OUTPUT_DIR/${THEME_SLUG}-v${VERSION}.zip"

echo "Packaging theme: $THEME_SLUG v$VERSION"
echo "Source: $THEME_DIR"
echo "Output: $OUTPUT_FILE"

cd "$THEMES_DIR"

# Create zip excluding unnecessary files
zip -r "$OUTPUT_FILE" "$THEME_SLUG/" \
    -x "$THEME_SLUG/node_modules/*" \
    -x "$THEME_SLUG/.git/*" \
    -x "$THEME_SLUG/.DS_Store" \
    -x "$THEME_SLUG/**/.DS_Store" \
    -x "$THEME_SLUG/*.log"

FILE_SIZE=$(du -h "$OUTPUT_FILE" | cut -f1)
FILE_COUNT=$(unzip -l "$OUTPUT_FILE" | tail -1 | awk '{print $2}')

echo ""
echo "✅ Theme packaged successfully!"
echo "   File: $OUTPUT_FILE"
echo "   Size: $FILE_SIZE"
echo "   Files: $FILE_COUNT"
echo ""
echo "Installation: Copy the zip to storage/app/themes/ and extract,"
echo "or upload via Admin > Themes panel."
