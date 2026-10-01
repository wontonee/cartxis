#!/usr/bin/env bash
# package-release.sh — Build a Shared Hosting zip for Cartxis (includes public/build).
#
# Usage:
#   ./scripts/package-release.sh [--with-vendor|--no-vendor] [--skip-npm]
#
# Defaults:
#   --with-vendor   Include vendor/ via composer install --no-dev (shared hosting)
#   npm build runs unless --skip-npm and public/build/manifest.json already exists
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

WITH_VENDOR=1
SKIP_NPM=0

for arg in "$@"; do
  case "$arg" in
    --with-vendor) WITH_VENDOR=1 ;;
    --no-vendor) WITH_VENDOR=0 ;;
    --skip-npm) SKIP_NPM=1 ;;
    -h|--help)
      sed -n '2,12p' "$0"
      exit 0
      ;;
    *)
      echo "Unknown option: $arg" >&2
      exit 1
      ;;
  esac
done

VERSION="$(php -r 'echo json_decode(file_get_contents("composer.json"))->version ?? "0.0.0";')"
DIST_DIR="$ROOT/dist"
STAGE_NAME="cartxis-${VERSION}-shared-hosting"
STAGE_DIR="$DIST_DIR/$STAGE_NAME"
ZIP_PATH="$DIST_DIR/${STAGE_NAME}.zip"

echo "==> Cartxis shared-hosting package v${VERSION}"

# ── Frontend assets ──────────────────────────────────────────────────────────
if [[ -f public/build/manifest.json ]]; then
  echo "  Found existing public/build/manifest.json"
  if [[ "$SKIP_NPM" -eq 0 ]]; then
    echo "  Rebuilding assets (omit with --skip-npm to reuse)..."
    if [[ -f package-lock.json ]]; then
      npm ci
    else
      npm install
    fi
    npm run build
  fi
else
  if [[ "$SKIP_NPM" -eq 1 ]]; then
    echo "ERROR: --skip-npm set but public/build/manifest.json is missing." >&2
    exit 1
  fi
  echo "  Building frontend assets..."
  if [[ -f package-lock.json ]]; then
    npm ci
  else
    npm install
  fi
  npm run build
fi

if [[ ! -f public/build/manifest.json ]]; then
  echo "ERROR: public/build/manifest.json missing after build. Aborting." >&2
  exit 1
fi
echo "  ✔ Frontend assets ready"

# ── Composer (optional vendor) ───────────────────────────────────────────────
if [[ "$WITH_VENDOR" -eq 1 ]]; then
  echo "  Running composer install --no-dev --optimize-autoloader..."
  composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
  echo "  ✔ vendor/ ready"
else
  echo "  Skipping vendor/ (--no-vendor)"
fi

# ── Stage copy ───────────────────────────────────────────────────────────────
echo "  Staging files into ${STAGE_DIR}..."
rm -rf "$STAGE_DIR"
mkdir -p "$STAGE_DIR"

# Copy project tree with exclusions (rsync preferred; fall back to tar)
EXCLUDE_FILE="$(mktemp)"
cat > "$EXCLUDE_FILE" <<'EOF'
.git/
.github/
.cursor/
.agent/
.vscode/
.idea/
node_modules/
tests/
dist/
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
.env
.env.*
!.env.example
*.log
.phpunit.result.cache
.phpunit.cache/
Homestead.json
Homestead.yaml
auth.json
npm-debug.log
yarn-error.log
EOF

if command -v rsync >/dev/null 2>&1; then
  rsync -a \
    --exclude-from="$EXCLUDE_FILE" \
    --exclude '.git' \
    --exclude 'node_modules' \
    --exclude 'tests' \
    --exclude 'dist' \
    --exclude '.github' \
    --exclude '.cursor' \
    --exclude '.agent' \
    "$ROOT/" "$STAGE_DIR/"
else
  tar -C "$ROOT" \
    --exclude='.git' \
    --exclude='.github' \
    --exclude='.cursor' \
    --exclude='.agent' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='dist' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    -cf - . | tar -C "$STAGE_DIR" -xf -
fi

rm -f "$EXCLUDE_FILE"

# Ensure empty storage dirs exist for shared hosts
mkdir -p \
  "$STAGE_DIR/storage/app/public" \
  "$STAGE_DIR/storage/framework/cache" \
  "$STAGE_DIR/storage/framework/sessions" \
  "$STAGE_DIR/storage/framework/views" \
  "$STAGE_DIR/storage/logs"
touch \
  "$STAGE_DIR/storage/app/.gitignore" \
  "$STAGE_DIR/storage/framework/cache/.gitignore" \
  "$STAGE_DIR/storage/framework/sessions/.gitignore" \
  "$STAGE_DIR/storage/framework/views/.gitignore" \
  "$STAGE_DIR/storage/logs/.gitignore" 2>/dev/null || true

# Drop vendor when --no-vendor (rsync may have copied an existing one)
if [[ "$WITH_VENDOR" -eq 0 ]]; then
  rm -rf "$STAGE_DIR/vendor"
fi

# Always ensure build assets are present in the stage
if [[ ! -f "$STAGE_DIR/public/build/manifest.json" ]]; then
  echo "ERROR: staged package missing public/build/manifest.json" >&2
  exit 1
fi

# ── Install instructions inside zip ──────────────────────────────────────────
cat > "$STAGE_DIR/INSTALL-SHARED-HOSTING.txt" <<EOF
Cartxis ${VERSION} — Shared Hosting Install
==========================================

Requirements
------------
- PHP 8.3+ with extensions: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath
- MySQL 8.0+ or MariaDB 10.6+
- Document root MUST point to the public/ directory (not the project root)
- Node.js is NOT required for this package (public/build is included)

Upload steps
------------
1. Upload and extract this zip on your host (e.g. AlwaysData, cPanel, Plesk).
2. Set the web document root to:  .../cartxis/public
3. Copy .env.example to .env and edit DB_* / APP_URL.
4. Generate an app key (SSH or host terminal):
     php artisan key:generate
5. Install / migrate (preferred if SSH is available):
     php artisan cartxis:install
   Or open https://YOUR-DOMAIN/setup in the browser after configuring .env
   and running migrations manually if your panel only allows limited CLI.

Permissions
-----------
Ensure these are writable by the PHP user:
  storage/
  bootstrap/cache/

Docs
----
See docs/SHARED_HOSTING.md in this package or on GitHub:
https://github.com/cartxis/cartxis/blob/main/docs/SHARED_HOSTING.md

EOF

cp "$STAGE_DIR/INSTALL-SHARED-HOSTING.txt" "$STAGE_DIR/DIST_README.txt"

# ── Zip ──────────────────────────────────────────────────────────────────────
mkdir -p "$DIST_DIR"
rm -f "$ZIP_PATH"
(
  cd "$DIST_DIR"
  if command -v zip >/dev/null 2>&1; then
    zip -qr "$ZIP_PATH" "$STAGE_NAME"
  else
    tar -czf "${ZIP_PATH%.zip}.tar.gz" "$STAGE_NAME"
    echo "WARNING: zip not found; wrote ${ZIP_PATH%.zip}.tar.gz instead" >&2
    ZIP_PATH="${ZIP_PATH%.zip}.tar.gz"
  fi
)

echo ""
echo "==> Package ready: ${ZIP_PATH}"
ls -lh "$ZIP_PATH"
echo "Done."
