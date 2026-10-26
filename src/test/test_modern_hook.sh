#!/usr/bin/env bash
# The modern option must reach tagui_modern.php and leave CasperJS as the default.
set -euo pipefail
repo=$(cd "$(dirname "$0")/../.." && pwd)
work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT
flow="$work/flow.tag"
printf 'echo hi\n' > "$flow"
mkdir "$work/bin"
cat > "$work/bin/php" << 'EOF'
#!/usr/bin/env bash
if [ "${1:-}" = "-q" ] && [ "${2:-}" = "tagui_modern.php" ]; then
	printf '%s\n' "$*" > "$TAGUI_HOOK_REPORT"
	printf 'browser=%s\n' "${tagui_web_browser:-}" >> "$TAGUI_HOOK_REPORT"
	printf 'speed=%s\n' "${tagui_speed_mode:-}" >> "$TAGUI_HOOK_REPORT"
	printf 'quiet=%s\n' "${tagui_quiet_mode:-}" >> "$TAGUI_HOOK_REPORT"
	printf 'log=%s\n' "${TAGUI_LOG:-}" >> "$TAGUI_HOOK_REPORT"
	exit 0
fi
exec /usr/bin/php "$@"
EOF
chmod +x "$work/bin/php"
export PATH="$work/bin:$PATH"

run_hook() {
	export TAGUI_HOOK_REPORT="$1"
	shift
	bash "$repo/src/tagui" "$@"
}

run_hook "$work/plain.txt" "$flow" modern alpha
grep -qx '^-q tagui_modern.php .*/flow.tag alpha$' "$work/plain.txt"
grep -qx 'browser=headless' "$work/plain.txt"
grep -qx 'speed=false' "$work/plain.txt"
grep -qx "log=$flow.log" "$work/plain.txt"

run_hook "$work/chrome.txt" "$flow" -chrome modern beta
grep -qx 'browser=chrome' "$work/chrome.txt"
grep -q 'beta$' "$work/chrome.txt"

run_hook "$work/speed.txt" "$flow" modern -speed
grep -qx 'speed=true' "$work/speed.txt"

run_hook "$work/quiet.txt" "$flow" -quiet -modern
grep -qx 'quiet=true' "$work/quiet.txt"
grep -qx 'browser=headless' "$work/quiet.txt"

set +e
firefox_out=$(bash "$repo/src/tagui" "$flow" -firefox modern 2>&1)
firefox_code=$?
set -e
test "$firefox_code" -ne 0
printf '%s\n' "$firefox_out" | grep -q 'modern engine does not support firefox'

help_out=$(bash "$repo/src/tagui" || true)
printf '%s\n' "$help_out" | grep -q 'modern         run on Chrome through the DevTools Protocol'

# CasperJS stays the default: without modern, the DevTools entry point is not called.
export TAGUI_HOOK_REPORT="$work/classic.txt"
rm -f "$work/classic.txt"
set +e
timeout 20 bash "$repo/src/tagui" "$flow" alpha >"$work/classic.out" 2>&1
classic_code=$?
set -e
if [ -f "$work/classic.txt" ]; then
	echo 'classic path invoked the modern engine' >&2
	exit 1
fi
if [ "$classic_code" -eq 124 ]; then
	echo 'classic path timed out' >&2
	exit 1
fi
printf '%s\n' 'ok modern hook'
