#!/usr/bin/env bash
set -euo pipefail

# Run latest iOS build of the Flutter app after ensuring Flutter SDK compatibility
# - Switch to stable channel
# - Upgrade Flutter
# - Fetch packages
# - Install CocoaPods (if available)
# - Open iOS Simulator
# - Run the app on iOS

echo "[INFO] Starting iOS run script for connect_jobs_mobile"

# Resolve repo root as the directory containing this script/.. (repo root)
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
APP_DIR="$REPO_ROOT/connect_jobs_mobile"

# Helper: prefer fvm if available
fl() {
  if command -v fvm >/dev/null 2>&1; then
    fvm flutter "$@"
  else
    flutter "$@"
  fi
}

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "[ERROR] Required command not found: $1" >&2
    exit 1
  fi
}

require_cmd bash
if ! command -v flutter >/dev/null 2>&1 && ! command -v fvm >/dev/null 2>&1; then
  echo "[ERROR] Flutter (or fvm) is not installed or not on PATH. Please install Flutter and re-run." >&2
  exit 1
fi

# Show current Flutter version
set +e
fl --version
set -e

# Switch to stable and upgrade
echo "[INFO] Switching to Flutter stable channel..."
fl channel stable

echo "[INFO] Upgrading Flutter SDK (this may take a few minutes)..."
# Answer yes automatically if prompted
yes | fl upgrade || fl upgrade

echo "[INFO] Flutter SDK after upgrade:"
fl --version

# Move to Flutter app directory
cd "$APP_DIR"

echo "[INFO] Cleaning previous builds..."
fl clean

echo "[INFO] Resolving Dart/Flutter packages..."
fl pub get

# iOS CocoaPods (best-effort)
if command -v pod >/dev/null 2>&1; then
  echo "[INFO] Installing iOS pods..."
  (cd ios && pod install) || echo "[WARN] pod install failed (continuing)."
else
  echo "[WARN] CocoaPods not found; if iOS build fails, install Xcode + CocoaPods."
fi

# Open iOS Simulator (macOS)
if [[ "${OSTYPE:-}" == darwin* ]]; then
  echo "[INFO] Opening iOS Simulator..."
  open -a Simulator || echo "[WARN] Could not open Simulator automatically."
fi

# Show devices
echo "[INFO] Available Flutter devices:"
fl devices || true

# Run on iOS
echo "[INFO] Running app on iOS..."
fl run -d iOS

