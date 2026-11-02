#!/usr/bin/env bash
# Classic flow inputs must reach CasperJS as separate arguments: values with spaces stay
# one input, values past $9 are kept, and a datatable csv is found in any slot.
set -euo pipefail
repo=$(cd "$(dirname "$0")/../.." && pwd)
work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT

# Run a copy of src/tagui from a directory whose other entries link to src, with a
# casperjs stub that records its arguments. The real src/ is not modified.
shadow="$work/src"
mkdir "$shadow"
for entry in "$repo"/src/* "$repo"/src/.[!.]*; do
	[ -e "$entry" ] || continue
	case "$(basename "$entry")" in tagui | casperjs | phantomjs) continue ;; esac
	ln -s "$entry" "$shadow/$(basename "$entry")"
done
cp "$repo/src/tagui" "$shadow/tagui"
mkdir -p "$shadow/casperjs/bin"
cat > "$shadow/casperjs/bin/casperjs" << 'STUB'
#!/usr/bin/env bash
printf '%s\n' "$@" >> "$TAGUI_ARGS_REPORT"
printf '%s\n' '--end--' >> "$TAGUI_ARGS_REPORT"
STUB
chmod +x "$shadow/casperjs/bin/casperjs"

flow="$work/flow.tag"
printf 'echo hi\n' > "$flow"

run_stub() {
	export TAGUI_ARGS_REPORT="$1"
	shift
	: > "$TAGUI_ARGS_REPORT"
	bash "$shadow/tagui" "$flow" -nobrowser "$@" > "$TAGUI_ARGS_REPORT.out" 2>&1
}

expect_args() {
	local report="$1"
	shift
	local expected
	expected=$(printf '%s\n' "$flow.js" "$@" '--end--')
	if [ "$(cat "$report")" != "$expected" ]; then
		echo "FAIL $report"
		echo "expected:"; printf '%s\n' "$expected"
		echo "actual:"; cat "$report"
		exit 1
	fi
}

# no inputs and options only: nothing is forwarded
run_stub "$work/none.txt"
expect_args "$work/none.txt"
run_stub "$work/opts.txt" -quiet
expect_args "$work/opts.txt"

# plain inputs are unchanged, options between them are still stripped
run_stub "$work/plain.txt" alpha -quiet beta
expect_args "$work/plain.txt" alpha beta

# an input with spaces arrives as one argument, and a glob is not expanded
run_stub "$work/spaces.txt" "hello world" '*' "  padded  "
expect_args "$work/spaces.txt" "hello world" '*' "  padded  "

# more than eight inputs are all forwarded
run_stub "$work/many.txt" a b c d e f g h i "j k"
expect_args "$work/many.txt" a b c d e f g h i "j k"

# -debug still adds --verbose
run_stub "$work/debug.txt" -debug one
expect_args "$work/debug.txt" --verbose one

# a datatable csv after eight inputs is used: one CasperJS run per data row
printf 'name\nx\ny\n' > "$work/data.csv"
run_stub "$work/csv.txt" a b c d e f g h data.csv
[ "$(grep -c -- '--end--' "$work/csv.txt")" -eq 2 ] || { echo "FAIL csv rows"; cat "$work/csv.txt" "$work/csv.txt.out"; exit 1; }
grep -qx 'data.csv' "$work/csv.txt"

# a missing csv in a later slot is reported instead of ignored
if run_stub "$work/missing.txt" a b c d e f g h missing.csv; then echo "FAIL missing csv accepted"; exit 1; fi
grep -q "ERROR - cannot find .*missing.csv" "$work/missing.txt.out"

# an api step still adds --web-security=false as its own argument
printf 'api http://127.0.0.1:9/x\n' > "$flow"
run_stub "$work/api.txt" "hello world"
expect_args "$work/api.txt" "hello world" --web-security=false

echo "ok classic inputs (stub)"

# with CasperJS and PhantomJS installed in src/, check p1 and p8 end to end
if [ -x "$repo/src/casperjs/bin/casperjs" ] && [ -e "$repo/src/phantomjs/bin/phantomjs" ]; then
	printf 'echo [`p1`]\necho [`p8`]\n' > "$work/real.tag"
	(cd "$work" && bash "$repo/src/tagui" real.tag -nobrowser "hello world" b c d e f g "last one" > real.out 2>&1) || { cat "$work/real.out"; exit 1; }
	grep -qx '\[hello world\]' "$work/real.out" || { echo "FAIL p1"; cat "$work/real.out"; exit 1; }
	grep -qx '\[last one\]' "$work/real.out" || { echo "FAIL p8"; cat "$work/real.out"; exit 1; }
	echo "ok classic inputs (casperjs)"
else
	echo "skip classic inputs (casperjs): src/casperjs or src/phantomjs not installed"
fi
