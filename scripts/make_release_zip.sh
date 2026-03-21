#!/usr/bin/env bash
set -euo pipefail

VERSION="$(cat VERSION | tr -d ' \n\r\t')"
OUT_DIR="dist"
OUT_FILE="${OUT_DIR}/rbac-starter-kit-v${VERSION}.zip"

mkdir -p "${OUT_DIR}"

# Create a clean source zip from tracked files only.
git archive --format=zip --output "${OUT_FILE}" HEAD

echo "Created: ${OUT_FILE}"

