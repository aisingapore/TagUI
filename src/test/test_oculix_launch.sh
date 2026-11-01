#!/usr/bin/env bash
# The Sikuli launch stays java -jar sikulix/sikulix.jar unless TAGUI_SIKULI_JAR or TAGUI_JAVA is set.
# A fake java records each call. CasperJS is not needed: the Sikuli process starts before it.
set -euo pipefail
repo=$(cd "$(dirname "$0")/../.." && pwd)
work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT
printf 'click button.png\n' > "$work/flow.tag"
mkdir "$work/bin"
cat > "$work/bin/java" << 'JAVA'
#!/usr/bin/env bash
if [ "${1:-}" = "-version" ]; then echo 'openjdk version "17.0.12"' >&2; exit 0; fi
printf '%s %s\n' "$0" "$*" >> "$TAGUI_JAVA_REPORT"
JAVA
chmod +x "$work/bin/java"
cp "$work/bin/java" "$work/java17"
cat > "$work/bin/php" << 'PHP'
#!/usr/bin/env bash
if [ "${2:-}" = "tagui_oculix_runtime.php" ]; then echo runtime >> "$TAGUI_PHP_REPORT"; fi
exec "$TAGUI_REAL_PHP" "$@"
PHP
chmod +x "$work/bin/php"
export TAGUI_REAL_PHP="$(command -v php)"
export PATH="$work/bin:$PATH"

launch() {
	export TAGUI_JAVA_REPORT="$1" TAGUI_PHP_REPORT="$1.php"
	: > "$1"; : > "$1.php"
	shift
	env "$@" bash "$repo/src/tagui" "$work/flow.tag" -nobrowser > /dev/null 2>&1 || true
	sleep 1
}

launch "$work/default.txt" -u TAGUI_SIKULI_JAR -u TAGUI_JAVA
grep -qx "$work/bin/java -jar sikulix/sikulix.jar -r tagui.sikuli -d 3" "$work/default.txt"
test ! -s "$work/default.txt.php"

launch "$work/same.txt" -u TAGUI_JAVA TAGUI_SIKULI_JAR=sikulix/sikulix.jar
grep -qx "$work/bin/java -jar sikulix/sikulix.jar -r tagui.sikuli -d 3" "$work/same.txt"
test ! -s "$work/same.txt.php"

printf 'jar' > "$work/oculixide.jar"
launch "$work/oculix.txt" TAGUI_SIKULI_JAR="$work/oculixide.jar" TAGUI_JAVA="$work/java17"
grep -qx "$work/java17 -jar $work/oculixide.jar -r tagui.sikuli -d 3" "$work/oculix.txt"
grep -qx runtime "$work/oculix.txt.php"

launch "$work/java.txt" -u TAGUI_SIKULI_JAR TAGUI_JAVA="$work/java17"
grep -qx "$work/java17 -jar sikulix/sikulix.jar -r tagui.sikuli -d 3" "$work/java.txt"
grep -qx runtime "$work/java.txt.php"

printf '%s\n' 'ok oculix launch'
