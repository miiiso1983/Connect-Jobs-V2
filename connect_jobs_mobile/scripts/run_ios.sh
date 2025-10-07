#!/usr/bin/env bash
# Usage: scripts/run_ios.sh [device-id-or-name]
# Example: scripts/run_ios.sh "iPhone 15 Pro"
# This script cleans, fetches packages, installs CocoaPods, opens the iOS Simulator, and runs the Flutter app.

set -Eeuo pipefail

# Ensure Homebrew bin (pod) is in PATH on Apple Silicon
export PATH="/opt/homebrew/bin:$PATH"

step() { echo "\n==> $*"; }

# Resolve project root (connect_jobs_mobile)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
pushd "$PROJECT_ROOT" >/dev/null

if ! command -v flutter >/dev/null 2>&1; then
  echo "Flutter is not installed or not in PATH. Please install Flutter and try again." >&2
  exit 1
fi

if [[ "$(uname)" != "Darwin" ]]; then
  echo "This script targets iOS (macOS only)." >&2
fi

DEVICE_TARGET="${1:-}"

step "Flutter clean"
flutter clean

step "Fetching Dart/Flutter packages"
flutter pub get

if [[ -d ios ]]; then
  if ! command -v pod >/dev/null 2>&1; then
    echo "CocoaPods not found. Install it via Homebrew: brew install cocoapods" >&2
    echo "Or via RubyGems (may require sudo): sudo gem install cocoapods" >&2
    exit 1
  fi
  step "Installing CocoaPods (pod install --repo-update)"
  pushd ios >/dev/null
  pod install --repo-update
  popd >/dev/null
fi

step "Opening iOS Simulator"
open -a Simulator || true
sleep 2

step "Listing Flutter devices"
flutter devices || true

# Auto-detect iOS Simulator if no target provided
if [[ -z "$DEVICE_TARGET" ]]; then
  CANDIDATE=$(flutter devices | sed -n 's/^\s*\(.*\) (mobile) • .* ios .* (simulator)$/\1/p' | head -n1 || true)
  if [[ -n "$CANDIDATE" ]]; then
    DEVICE_TARGET="$CANDIDATE"
    echo "Auto-selected iOS Simulator: $DEVICE_TARGET"
  else
    echo "No iOS Simulator detected automatically. Please pass a device name, e.g.:" >&2
    echo "  scripts/run_ios.sh \"iPhone 16 Plus\"" >&2
    exit 1
  fi
fi

# Allow passing extra flutter run args via FLUTTER_RUN_ARGS env var
RUN_ARGS=${FLUTTER_RUN_ARGS:-}

step "Running app on device: $DEVICE_TARGET"
flutter run -d "$DEVICE_TARGET" $RUN_ARGS

popd >/dev/null

