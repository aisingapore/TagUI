#!/usr/bin/env bash
# The Chromium launcher lives in src/tagui. This runs that function against a
# fake wrapper so the check does not need a system Chromium package.
set -eu

root="$(cd "$(dirname "$0")/../.." && pwd)"
script="$root/src/tagui"
tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT

awk '/^# tagui_unwrap_chromium$/,/^# end tagui_unwrap_chromium$/' "$script" > "$tmp/fn.sh"
# shellcheck disable=SC1091
source "$tmp/fn.sh"

mkdir -p "$tmp/tree/bin" "$tmp/tree/lib/chromium"
printf '#!/bin/sh\necho wrapper\n' > "$tmp/tree/bin/chromium"
printf '#!/bin/sh\necho chrome\n' > "$tmp/tree/bin/google-chrome"
printf '\177ELF' > "$tmp/tree/lib/chromium/chromium"
chmod +x "$tmp/tree/bin/chromium" "$tmp/tree/bin/google-chrome" "$tmp/tree/lib/chromium/chromium"

export PATH="$tmp/tree/bin:$PATH"

wrapper="$(tagui_unwrap_chromium chromium)"
expected="$(readlink -f "$tmp/tree/lib/chromium/chromium")"
if [ "$wrapper" != "$expected" ]; then
echo "FAIL wrapper: expected $expected, got $wrapper"
exit 1
fi

chrome="$(tagui_unwrap_chromium google-chrome)"
if [ "$chrome" != "google-chrome" ]; then
echo "FAIL google-chrome changed to $chrome"
exit 1
fi

missing="$(tagui_unwrap_chromium not-a-browser)"
if [ "$missing" != "not-a-browser" ]; then
echo "FAIL missing command changed to $missing"
exit 1
fi

cp "$tmp/tree/lib/chromium/chromium" "$tmp/tree/bin/chromium"
chmod +x "$tmp/tree/bin/chromium"
elf="$(tagui_unwrap_chromium chromium)"
if [ "$elf" != "chromium" ]; then
echo "FAIL elf chromium changed to $elf"
exit 1
fi

echo "ok chromium wrapper"
