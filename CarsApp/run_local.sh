#!/usr/bin/env bash
# run_local.sh
# Quick local runner for the CarsApp web lab
# - Installs php + sqlite3 on Debian/Ubuntu (uses sudo)
# - Prepares uploads/ and data/
# - Runs init_db.php if present
# - Starts PHP built-in server at 0.0.0.0:8000 (foreground)

set -euo pipefail
shopt -s nullglob

PORT=8000
HOST=0.0.0.0

echo "[*] run_local.sh starting..."

# Detect package manager (basic)
if command -v apt-get >/dev/null 2>&1; then
  PKG_MGR="apt"
else
  PKG_MGR=""
fi

install_php_deps() {
  if [[ "$PKG_MGR" == "apt" ]]; then
    echo "[*] Installing PHP and SQLite (apt)... (requires sudo)"
    sudo apt-get update -y
    sudo apt-get install -y php php-sqlite3 sqlite3 curl unzip
  else
    echo "[!] Automatic package installation is supported only for Debian/Ubuntu (apt)."
    echo "    Please install PHP and SQLite manually. Example (Debian/Ubuntu):"
    echo "      sudo apt-get install php php-sqlite3 sqlite3"
    echo "    After installing, re-run this script."
    return 1
  fi
}

# Check PHP availability
if ! command -v php >/dev/null 2>&1; then
  echo "[*] PHP not found on PATH. Attempting to install..."
  install_php_deps || exit 1
else
  echo "[*] PHP found: $(php -v | head -n1)"
  # ensure sqlite extension exists
  php -r 'if(!extension_loaded("pdo_sqlite") && !extension_loaded("sqlite3")) { exit(2);} exit(0);' || {
    echo "[*] PHP SQLite extension not found. Attempting to install via package manager..."
    install_php_deps || exit 1
  }
fi

# Create required directories
for d in uploads data; do
  if [[ ! -d "$d" ]]; then
    echo "[*] Creating directory: $d"
    mkdir -p "$d"
  fi
  echo "[*] Ensuring permissive permissions on $d (0777) for lab use"
  chmod 0777 "$d"
done

# Initialize DB if init_db.php exists
if [[ -f "init_db.php" ]]; then
  echo "[*] Running init_db.php to initialize database (if needed)..."
  php init_db.php || echo "[!] init_db.php exited with non-zero status (may be fine if DB already exists)."
else
  echo "[*] No init_db.php found — skip DB initialization. If you need DB created, add init_db.php and re-run."
fi

echo
echo "======================================================================"
echo " READY: Starting PHP built-in server"
echo " - App root: $(pwd)"
echo " - Serve at: http://localhost:${PORT}  (or from other machines: http://${HOST}:${PORT})"
echo " - To stop: press Ctrl+C in this terminal"
echo "======================================================================"
echo

exec php -S "${HOST}:${PORT}"
