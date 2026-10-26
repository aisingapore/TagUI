# Modern Chrome engine

TagUI and RPA-Python can run browser and logic flows on current Chrome or Edge without CasperJS or PhantomJS. The engine is opt-in. A run that does not pass `modern` still uses CasperJS, PhantomJS, and the existing Chrome file bridge.

This note is the architecture record, the comparison of replacement options, the migration plan, and the status of the engine on `marcelocecin/TagUI` branch `rpa_python`. The modern engine runs the common browser and logic steps, `js` / `py` / `r`, JavaScript outside the small expression subset, and non-English keywords through the existing translator. Visual steps can drive OculiX through the existing Sikuli file bridge when a jar and a display are available. Phase 3, making `modern` the default for Chrome, is not ready.

## How TagUI runs a flow today

`src/tagui` (and `src/tagui.cmd` on Windows) is a launcher. It strips options, then:

1. `php -q tagui_parse.php` reads the flow and writes a CasperJS script (`flow.js`).
2. Optional helpers start for R, Python, Sikuli, or Chrome.
3. `casperjs flow.js` executes that script. CasperJS runs on PhantomJS by default. `firefox` uses SlimerJS, which is also unmaintained.

`package.json` depends on `casperjs`, `phantomjs-prebuilt`, and `slimerjs`. PhantomJS has had no release since 2018 and does not run on Apple Silicon.

### Parser

`src/tagui_parse.php` turns each step into JavaScript wrapped in `casper.then(...)`. Control flow (`if` / `else` / `for` / `{` `}`) becomes real JavaScript around those steps. The natural-language conditions in `src/samples/4_conditions` (`contains`, `equals to`, `more than`, `and` / `or`, `for n from X to Y`) are rewritten before that JavaScript is emitted.

Chrome and headless Chrome do not remove CasperJS. After the script is generated, a post-pass rewrites `casper.*` calls to `chrome.*`. Those helpers live in `src/tagui_header.js`. `src/tagui_footer.js` closes the run with `casper.then` plus `casper.run()`.

### Chrome is already DevTools, behind a file bridge

For `chrome` and `headless`, the launcher starts Chrome with `--remote-debugging-port=9222` and a fixed profile `chrome/tagui_user_profile`, then starts `src/tagui_chrome.php`.

`tagui_header.js` does not open a websocket itself. Each browser action writes a numbered JSON line to `tagui_chrome.in`. The PHP process polls that file (default 100000 microseconds), sends the command over the vendored Textalk websocket client in `src/ws/`, and writes the reply to `tagui_chrome.out`. The JavaScript side polls the out file until the matching id appears. Clicks are mouse events in the Puppeteer style (`Input.dispatchMouseEvent`), not DOM `.click()`.

So the dependency split is:

| Piece | Needs CasperJS / PhantomJS | Needs Chrome |
| --- | --- | --- |
| Step sequencing, `casper.then`, `casper.wait`, `casper.run` | yes | no |
| Live-mode read loop | yes, today | no |
| `url`, `click`, `type`, `read`, `snap` in headless/chrome mode | the sequencer only | yes, via CDP |
| Default browser when no browser flag is set | PhantomJS, and Casper's own page API | no |
| `firefox` | SlimerJS | no |
| Sikuli / visual steps | the sequencer | no, they use a separate Sikuli process |

The Chrome websocket code and the locator order already exist. What is stuck on CasperJS is the sequencer and the default headless browser.

### Locators

`tx()` / `check_tx()` in `tagui_header.js` resolve a target in this order: CSS selector, then an XPath search over id, name, class, title, aria-label, visible text, and href. XPath is chosen when the locator contains `/` or `(`. The modern engine repeats that order inside the page with `Runtime.evaluate`.

### Live mode, and why RPA-Python cares

RPA-Python does not call TagUI as a library. `tagui.py` in [tebelorg/RPA-Python](https://github.com/tebelorg/RPA-Python) starts:

```text
tagui rpa_python chrome|headless
```

with stdin and stdout pipes. The flow file contains only `live`. The parent waits until stdout contains:

```text
LIVE MODE - type done to quit
```

The banner is followed by a space and a backspace (`\n \b`), which paints a prompt on a terminal and must stay byte-for-byte so existing clients keep matching.

`send()` then, for each instruction:

1. writes `echo "[RPA][id] - <instruction>"`
2. writes the instruction itself
3. writes `echo "[RPA][id] - listening for inputs"`
4. reads until a line starts with `[RPA][` and ends with `] - listening for inputs`

Results come back through `dump EXPR to rpa_python.txt` (`url()`, `title()`, `exist('sel').toString()`, `read X to read_result`). `close()` echoes a finished marker and then `done`, and waits for the process to exit. `check`, `frame`, and `popup` are rejected in live mode because they open a block the single-line reader cannot see.

Classic live mode is the tebelorg loop: one `casper.then` that blocks on `while (true)` and `sys.stdin.readLine()` until a line starts with `done`. RPA-Python's published client depends on that loop. `wait` and `run` typed inside it are queued and do not run until `done`, which is the tebelorg behavior. Modern live mode is separate: the PHP engine reads one line at a time, so `wait` and `run` finish before the next line.

`src/tagui_mcp` speaks the same banner. `LiveSession` writes a flow that contains `live`, starts `tagui`, reads until the banner, then sends a step plus an `echo TAGUI_MCP_END_*` marker. `dump` writes an expression to a file. `reap_tagui_chrome` kills the helper on port 9222, `tagui_user_profile`, and `tagui_chrome.php`.

## Tump TagUI-Python versus this branch

RPA-Python installs TagUI by downloading a base zip from tebelorg/Tump releases, then overwriting seven files from [Tump `TagUI-Python`](https://github.com/tebelorg/Tump/tree/master/TagUI-Python):

- `tagui`
- `tagui.cmd`
- `end_processes`
- `end_processes.cmd`
- `tagui_header.js`
- `tagui_parse.php`
- `tagui.sikuli/tagui.py`

Compared on 16 Oct 2026, those files against `rpa_python` at `35cfc42` (before this proof of concept), with CRLF normalized:

| File | SequenceMatcher | What differs |
| --- | --- | --- |
| `end_processes` | 1.000 | identical |
| `end_processes.cmd` | 1.000 | identical |
| `tagui.sikuli/tagui.py` | 1.000 | identical |
| `tagui.cmd` | 0.9986 | local adds a comment that Windows still forwards only arg2–arg9 and that the MCP server uses `TAGUI_PARAM_n` |
| `tagui` | 0.9655 | Tump joins `$2`–`$9` into one string and only scans those slots for a `.csv`. Local forwards `"${@:2}"` separately (spaces, and arguments past `$9`) and treats any remaining `.csv` as the datatable |
| `tagui_header.js` | matches tebelorg `7ecc6ab` | classic mode emits the tebelorg header. `p1`–`p16` and the numeric live `wait` stay in the modern engine |
| `tagui_parse.php` | `csv_row_count()` only | PHP 8 used to throw `TypeError` on `count(false)` at CSV EOF. `end_tx()` and the live stdin loop match tebelorg again |

Local `rpa_python` is ahead of the Tump overlay. Nothing in those seven files is a feature this branch lacks, except the runtime string patches RPA-Python applies itself after install (turbo mode, macOS PhantomJS and `python3` shebangs). This proof of concept then adds a `tagui_modern_profile_` kill loop to `end_processes` and `end_processes.cmd`, so those two files now differ from Tump for that reason only.

## Replacement options

The constraints that matter for an upstream-acceptable change:

- Keep the TagUI step language and the existing live stdin/stdout contract, so RPA-Python and the MCP server do not need a new protocol.
- Run on Windows, macOS (including Apple Silicon), and Linux.
- Keep the install small. TagUI's own Chrome bridge is PHP on purpose, to avoid requiring Node or Python for that path.
- Leave the CasperJS engine in place until coverage is close enough that maintainers can flip the default.

| Option | What it keeps | Cost |
| --- | --- | --- |
| Casper-compatible JavaScript shim on a current JS runtime | the parser and `tagui_header.js` almost unchanged | still a JS runtime, and a large fake of `casper.*`, `casper.page`, and Phantom's module system. Phantom-only flows stay phantom-shaped |
| Playwright (Python or Node) | a maintained browser driver, auto-waiting, traces | new runtime and a large browser download. The live contract can be preserved, but every step is reimplemented and the install story changes. RPA-Python would grow a second dependency stack |
| Node + `chrome-remote-interface` or Puppeteer | close to what `chrome.*` already does | Node becomes mandatory. TagUI comments rejected that for the Chrome bridge |
| Python CDP (the RPA-Python process drives Chrome itself) | no Casper inside the Python API | breaks `tagui flow.tag` for everyone who does not use RPA-Python, and splits one language across two engines |
| PHP CDP, opt-in flag (this engine) | PHP, which is already required; the vendored websocket client; the live banner; RPA-Python's `send` / `dump` / `done` unchanged | Firefox and `test` / xUnit stay on the CasperJS engine. `js` runs in the page, so Casper objects are absent. The modern engine reads the step table from `src/tagui_steps.php`. Classic mode keeps tebelorg's keyword checks |

## Recommendation

Drive Chrome and Edge directly from PHP with the DevTools Protocol, behind the `modern` flag. CasperJS stays the default.

Reasons:

- PHP and `src/ws/` are already the Chrome bridge. The new engine removes the file polling and the Casper sequencer, and talks to the page websocket itself.
- No Node, Playwright, or Python runtime is added for the engine. RPA-Python and the MCP server keep working because they only depend on the live banner, echo markers, `dump`, and `done`.
- Chrome and Edge ship native builds for Apple Silicon. The engine does not download PhantomJS.
- A flag lets maintainers try the engine on real flows without changing the default that existing users and the Tump zip expect.

Keyword aliases, the code-step predicate, and locator attribute order live in `src/tagui_steps.php`. The modern engine loads that file. Classic mode keeps tebelorg's keyword checks inside `tagui_parse.php` and `tagui_header.js`, so the Casper script for the official suite matches the pinned parser.

### Phased migration

1. **This change.** `tagui flow modern` runs the PHP engine. Omitted, the run is CasperJS as before. RPA-Python can pass `modern` once it knows the binary supports it; until then it keeps working against Casper live mode.
2. **Grow coverage and share the step table.** `src/tagui_steps.php` is the shared table. The modern engine runs `select`, `upload`, `download`, `receive`, `table`, `frame`, `popup`, `api`, `run`, `while`, `break`, `continue`, `check`, `ask`, `save`, `write`, `load`, datatables, and `show page` as HTML. `js`, `py`, `r`, translated keywords, and the OculiX file bridge are in place as described below. `test` / xUnit stays on the CasperJS engine.
3. **Flip the default for Chrome.** `headless` and `chrome` start the modern engine. A `casper` flag selects the old sequencer. PhantomJS remains available only through that flag.
4. **Drop PhantomJS.** Remove `phantomjs-prebuilt`, `casperjs`, and `slimerjs` from `package.json` when the remaining visual and Firefox paths have a replacement. RPA-Python can then stop patching the PhantomJS binary and the macOS shebang. The Tump overlay shrinks to whatever live-mode delta is still required, and eventually to nothing if upstream live mode is the one-step-per-line version.

## Keeping PHP

Keep PHP as the engine runtime. `js`, `py`, and `r` are helpers beside it. Moving the engine to Python, Node, Deno, or a Go binary is a later product decision, and it is not required to run those steps.

- The parser, the translator, the datatable tools, and the Casper sequencer are PHP. The modern engine reuses them. A second implementation of that sequencer would exist for as long as CasperJS remains the default.
- TagUI already requires PHP on Windows, macOS, and Linux. The Chrome bridge was written in PHP so the browser path would not add Node. Chrome is the JavaScript runtime for `js` steps, through CDP `Runtime.evaluate`.
- RPA-Python installs TagUI by downloading a zip and overlaying files. That zip can keep shipping PHP sources. Users who run `tagui` directly keep the dependency they already have.
- Upstream review stays smaller while the default engine and the opt-in engine share `src/tagui_steps.php` and the live-mode contract.

| Runtime | Install on Win / macOS / Linux | RPA-Python zip | Upstream fit | Effort and risk |
| --- | --- | --- | --- | --- |
| PHP (keep) | PHP, which TagUI already asks for. `js` uses the Chrome that the run already started. `py` needs Python only when a flow uses `py`. `r` needs Rscript only when a flow uses `r` | Same overlay of PHP files | One step table with the Casper engine | The work in this branch |
| Python | Python 3 becomes mandatory for every flow, including browser-only ones. RPA-Python already has Python. A standalone `tagui` install that today is PHP plus Chrome would add Python | The zip stops being a file overlay and becomes a module layout, shebangs, and a Windows launcher | Two engines until Casper is removed, then a Python engine and a retired PHP parser | Rewrite of the parser, translator, live loop, and CDP client. High chance of drifting from the Casper step language |
| Node or Deno | Node becomes mandatory. TagUI avoided that for the Chrome bridge. Deno is a single binary and is still a new dependency to ship and update | A binary or `node_modules` inside the zip | The generated Casper file is JavaScript, and the modern engine still would not execute that file. The step language would be reimplemented either way | Same rewrite as Python, plus a Windows, macOS, and Linux binary pipeline if the distribution is a bundled runtime |
| Go single binary | One static binary. No PHP on the machine. Smallest long-term footprint | Replaces the zip's PHP entry point. RPA-Python would launch the binary | A clean install after Casper is gone. Difficult to accept while the parser and the default engine are PHP | Full rewrite. Highest effort. The install story pays off after the step language has stopped moving |

## SikuliX and OculiX

Visual steps talk to `src/tagui.sikuli/tagui.py` through `tagui_sikuli.in` and `tagui_sikuli.out`. The script is Jython 2 (`print` statements, `except IOError, OSError`). It calls `click`, `exists`, `find`, `type`, `Region`, `Screen`, `Settings.OcrTextRead`, and `findText`. Classic TagUI starts a SikuliX 1.1.x jar on OpenJDK 8.

[oculix-org/SikuliX1](https://github.com/oculix-org/SikuliX1) is a read-only historical mirror. The maintained project is [oculix-org/Oculix](https://github.com/oculix-org/Oculix) (MIT). Upstream SikuliX was archived in March 2026. The probe below used OculiX 4.0.0.

| Question | SikuliX 1.1.x in TagUI today | oculix-org/SikuliX1 | OculiX 4.0.0 |
| --- | --- | --- | --- |
| Activity | Unmaintained. TagUI points at the 1.1.x jar and OpenJDK 8 | Read-only mirror. Fixes do not land there | Active. Tag `v4.0.0` publishes per-OS IDE and API jars |
| License | Historical SikuliX GPL | Same historical tree | MIT |
| Java | OpenJDK 8 | Java 8 era | Java 17 or newer. OculiX 4.0.0 is Java 17 bytecode (class file 61). Java 11 fails with UnsupportedClassVersionError. This VM used OpenJDK 21.0.12. Temurin 17.0.20.1 also starts the jar |
| Apple Silicon, Windows, Linux | Java 8 builds. Apple Silicon needs an x64 JDK under Rosetta | No new platform builds | Native Apple Silicon jars from the 3.x line. Linux, macOS, and Windows fat jars are on the release. The Linux IDE jar probed here is 197708979 bytes |
| API versus `tagui.py` | The bridge was written for this API | Same historical API | Jython 2.7.4 loaded `tagui.py` unchanged (`setAutoWaitTimeout`, `Settings`, `Screen`, `type`) |
| OCR and the JVM crash | Bundled Tesseract from the 1.1.x era. aisingapore/TagUI#1299 is the report of a JVM crash in that pairing. The issue page was not available to re-read; the failure mode is the known SikuliX 1.1.x native crash | No new OCR stack | Legerix Tesseract 5.5.0 is in the jar. On Ubuntu 26.04 with `libjpeg.so.8` it read `Hello OCR Test 123`. On Debian 13 the same call fails: bundled `libleptonica.so.6` needs `libjpeg.so.8`. See Linux and OculiX |
| How a flow starts it | `java -jar sikulix.jar -r src/tagui.sikuli`, working directory `src/` | Use OculiX instead | `java -jar oculixide-<os>.jar -r src/tagui.sikuli`, working directory `src/`. `TAGUI_SIKULI_JAR` points at that jar. Linux needs the full JRE (not `openjdk-*-jre-headless`) and an X11 `DISPLAY`. `Xvfb :99 -screen 0 1366x768x24` is enough for the file bridge. Wayland is untested |

The modern engine sends `keyboard`, `mouse`, `vision`, `rclick` of an image, `click (x,y)`, and `exist()` / `present()` / `visible()` of a `.png` or `.bmp` through that bridge (`TaguiSikuli` in `src/tagui_runtime.php`). `TaguiSikuli::jar()` uses `TAGUI_SIKULI_JAR` when that path is a file, then `src/sikulix/oculixide.jar`, then `src/sikulix/sikulix.jar`. The process is `java -jar <that jar> -r src/tagui.sikuli` with working directory `src/`. `TAGUI_JAVA` selects the binary. When it is unset and `JAVA_HOME/bin/java` exists, that file is used; otherwise the `java` on `PATH` is used. OculiX 4.0.0 needs Java 17 or newer. A SikuliX 1.1/2 jar can still run on Java 8. The JVM must not be a headless-only build. Image paths are made absolute from the flow directory before that call, because Sikuli's working directory is `src/`. `snap page` is a fullscreen Chrome capture. `snap page.png` and `snap page.bmp` are a Sikuli full-desktop capture, the same branch official `is_sikuli()` takes before the plain `page` check. An image `exist()` keeps asking until the step timeout. `present()` and `visible()` ask once. The same `tagui.py` is what SikuliX 2.0.x loads under Java 8: `setAutoWaitTimeout`, `Screen`, `exists`, `Pattern`, `type`, and `click` are the 1.1/2 API, not OculiX-only calls. A production flow on Ubuntu 22.04 used that pairing (Java 8, a SikuliX 2 jar, PHP 8.1.2, Chrome 155). On RPA OCI (Ubuntu 22.04, Chrome 155, SikuliX 2, Corretto 8) that flow finished in 237s on the modern engine and 243s on the official engine. This VM did not re-run Java 8 or that production flow; it ran the bridge on OpenJDK 21 and OculiX 4.0.0.

PHP does not need the curl or dom extensions. Downloads and CDP use the bundled websocket client and `file_get_contents`. The logic suite ran here on PHP 8.3.6 with both extensions absent. PHP 8.1.2 itself was not installed on this VM.

The jar is not vendored. Headless on this VM, Xvfb plus OculiX 4.0.0:

- `[0] START`, then `vision print(...)` returned SUCCESS and the log contained the print
- `Screen().text()` on the Ubuntu 24.04 probe returned SUCCESS on a blank Xvfb screen. That call did not read a glyph. Ubuntu 26.04 with `libjpeg.so.8` did read `Hello OCR Test 123`. Debian 13, which has no `libjpeg.so.8`, still fails
- `keyboard a` returned SUCCESS and the log showed `TYPE "a"`
- A `click` of a PNG cropped from the on-screen fixture matched at device scale 1. OculiX looks up `button.bmp` as `button.bmp.png`, so the harness stores that PNG beside the real bitmap. The Apple Silicon run below clicked an image once the PNG was the physical-pixel size

```text
export TAGUI_SIKULI_JAR=/path/to/oculixide-4.0.0-linux.jar
export DISPLAY=:99
tagui flow.tag modern
```

The jar for the probe was `oculixide-4.0.0-linux.jar` from the [v4.0.0 release](https://github.com/oculix-org/Oculix/releases/tag/v4.0.0). `-r` wants the IDE jar. `oculixapi` is the library jar. If OculiX exits before `[0] START`, or the Java process dies during a step, the TagUI error quotes the first `Exception` / `Caused by` line and any `[error]` lines from `tagui_oculix.log` in the temp directory. `UnsupportedClassVersionError` adds `OculiX requires Java 17+; set PATH/JAVA_HOME or TAGUI_JAVA`. A trailing `... 5 more` stack line is not the message. `Mouse not useable (blocked)` becomes a macOS permissions hint. `java.awt.HeadlessException` (`running in headless environment`, including a machine whose only Java is `openjdk-*-jre-headless`) becomes a hint to install the full JRE and set `DISPLAY`. The log is truncated when that Java process starts, so the lines belong to the current run.

A missed image is `visual step failed` plus `Image not on screen` and, when OculiX can score a candidate, `best similarity 0.41`. The bridge searches once at the normal similarity, then once more at a very low similarity only to read that score. It does not click the low-scoring match. `Settings.AlwaysResize` stays unset: a global resize would also scale images that were already captured at screen resolution.

### macOS and OculiX

Confirmed on an Apple Silicon Mac with a built-in Retina display (3024×1964), macOS, Java 21 Corretto, and `oculixide-4.0.0-macos.jar`, using `tagui flow chrome modern`:

1. Install Java 17 or newer. Corretto 21 is enough. OculiX 4.0.0 starts natively on ARM64 (OpenCV, Legerix Tesseract 5.5.0, Jython 2.7.4).
2. Download `oculixide-4.0.0-macos.jar` from the OculiX release. Point `TAGUI_SIKULI_JAR` at that file. The engine runs `java -jar` on `src/tagui.sikuli` with the working directory `src/`.
3. Grant **Accessibility** and **Screen Recording** to the app that launches TagUI (Terminal, iTerm, or the IDE). Without them OculiX exits with `FATAL: Mouse.init: Mouse not useable (blocked)`. TagUI reports that line and the permission hint instead of only `OculiX exited before it was ready`.
4. Retina. Sikuli compares PNG pixels with the physical screen. A button that is 260×90 CSS pixels is 520×180 physical pixels at devicePixelRatio 2. `snap` uses `Page.captureScreenshot` with `fromSurface: true` and clip scale 1, which is the device-pixel image. The official profile launch does not pass `--force-device-scale-factor`, so Chrome keeps the display scale and a new snap matches the screen. The isolated container launch forces scale factor 1 when headless (override with `TAGUI_DEVICE_SCALE`). An image snapped earlier, at CSS size, will miss until it is snapped again. On this Linux VM, `TAGUI_DEVICE_SCALE=2` saved a 100×40 CSS box as a 200×80 PNG.
5. `file://` URLs are opened at `realpath`. On macOS `/tmp` is a symlink to `/private/tmp`, and `file:///tmp/...` otherwise lands on `chrome-error://chromewebdata/`. The same step with the real path, or with `http://`, opens the page.

### Linux and OculiX

Confirmed on Debian 13 (trixie), an X11 session at 1920×1200, PHP 8.4.26, Chrome 154, OpenJDK 21, and `oculixide-4.0.0-linux.jar`:

1. Install the full JRE, Java 17 or newer. The package name `openjdk-*-jre-headless` is not enough: AWT throws `java.awt.HeadlessException: running in headless environment` even when a display exists. TagUI quotes that exception and says to install the full JRE and set `DISPLAY`.
2. An X11 session. Export `DISPLAY` (a real X server, or `Xvfb :99 -screen 0 1366x768x24`). Wayland is untested.
3. Image click works at 1× scale. `btn.png` was 260×90 and the page title became `clicado-pelo-oculix`.
4. OCR follows `libjpeg.so.8`, which Ubuntu ships as `libjpeg-turbo8` and Debian 13 does not (`libjpeg62-turbo` is `libjpeg.so.62`). OculiX's Legerix `libleptonica.so.6` is linked to `.8`. Without it, OculiX falls back to a `tesseract` binary, and `tesseract-ocr` / `libtesseract5` 5.5.0 still throws `UnsatisfiedLinkError` for `leptonica`. On Ubuntu 26.04 (X11 display `:1`, PHP 8.5.4, Chromium 155, full OpenJDK 21, OculiX 4.0.0) Legerix Tesseract 5.5.0 recognized `Hello OCR Test 123`, and `read page.png to var` returned that text. Ubuntu's `libtesseract5` 5.5.0 was installed and OculiX issues 211 and 401 (the system library loading first and missing `TessBaseAPIGetPAGEText`) did not appear. The Ubuntu 24.04 probe only showed a blank-screen `Screen().text()` staying up.
5. `vision print(Screen().text())` used to kill OculiX. Jython 2 `print` encodes stdout as ASCII, and OCR of window chrome includes `—` and `®`, which raises `UnicodeEncodeError`. `src/tagui.sikuli/tagui.py` wraps stdout and stderr with `codecs.getwriter('utf-8')` and flushes each write. `-Dfile.encoding` does not change that stream. `tagui_sikuli.out` and `tagui_sikuli.txt` are written as UTF-8. This was confirmed on the Ubuntu 26.04 webtop.
6. If Java dies in the middle of a step, TagUI checks the process on each poll and reports the `[error]` lines from `tagui_oculix.log` immediately. It does not wait out the 30 second visual timeout.

On that same Ubuntu 26.04 session an image click exited 0. The snap was 520×180 and matched at device scale 2. A missing image failed immediately with `best similarity 0.35` and exit 1. `START` / `FINISH` used local time. `/usr/bin/chromium` on Ubuntu is a script; the browser that accepts DevTools flags is `/usr/lib/chromium/chromium`. The engine prefers that ELF when the `chromium` it found is a shell wrapper, including when `TAGUI_CHROME_BIN` points at the wrapper. The webtop run itself set `TAGUI_CHROME_BIN` and was not repeated after this lookup change.

A possible workaround is to put `libjpeg.so.8` on the loader path (Ubuntu's `libjpeg-turbo8` package, or a copy of that library). That workaround was not run on a Debian container here, so it stays unverified.

Draft for an upstream OculiX issue. It is not filed.

```text
Title: Linux OCR fails on Debian: bundled libleptonica.so.6 needs libjpeg.so.8

OculiX 4.0.0 (oculixide-4.0.0-linux.jar) on Debian 13, full OpenJDK 21, DISPLAY set to an X11 session.

Image click works. Screen().text() does not.

The Legerix build inside the jar ships libleptonica.so.6 linked against libjpeg.so.8. Debian's libjpeg62-turbo provides libjpeg.so.62 and does not provide libjpeg.so.8 (that soname is Ubuntu's libjpeg-turbo8). OculiX falls back to an external tesseract binary. With tesseract-ocr and libtesseract5 5.5.0 installed, the call still throws UnsatisfiedLinkError for leptonica.

Please build the bundled leptonica against libjpeg.so.62, or ship libjpeg.so.8 next to it, so the in-jar Tesseract loads on both Debian and Ubuntu.
```

## Proof of concept

`src/tagui` and `src/tagui.cmd` accept `modern` in any option slot from 2 to 9, the same way they accept `headless` or `quiet`. When it is set they do not call the parser or CasperJS. They run:

```text
php -q tagui_modern.php <flow> <remaining args>
```

The modern path does not pipe through `tee`. `tagui_write_output()` writes each line to stdout, flushes it, and appends the same bytes to `TAGUI_LOG` (`flow.log`). GNU `tee` and Windows `tee` block-buffer when stdout is a pipe, which hid the short live-mode banner from RPA-Python. The CasperJS path still uses `tee`. The Windows launcher (`src/tagui.cmd`) sets `TAGUI_LOG` and calls `php` the same way. That branch was read and not executed: this VM is Linux only.

`phantomjs` is treated as `headless` under `modern`. `firefox` is rejected. `chrome` is a visible window. Any other browser value, including the default, is headless Chrome.

### Browser process

`TaguiChrome` picks a binary in this order: `TAGUI_CHROME_BIN`, then `google-chrome-stable`, `/opt/google/chrome/chrome`, `/usr/lib/chromium/chromium`, `/usr/lib/chromium-browser/chromium-browser`, then `chromium` and `chromium-browser` on `PATH`, then Edge, and `google-chrome` last. The last position is deliberate. On this VM `/usr/local/bin/google-chrome` is a wrapper that forces port 9222 and a shared profile, which fights a private debugging port. On Ubuntu, `/usr/bin/chromium` is also a script. If the chosen file is named `chromium` or `chromium-browser` and is not an ELF binary, the engine uses `/usr/lib/chromium/chromium` (or the matching path next to the wrapper) when that file is an ELF. After the Linux names it looks for app bundles under `/Applications`: Google Chrome, Chrome Canary, Edge, Edge Canary, Chromium, and Brave. Those paths are the same on Apple Silicon and Intel macOS. On Windows it looks for Chrome and Edge under `Program Files`, `Program Files (x86)`, and `%LOCALAPPDATA%`. The macOS and Windows lists were not executed here.

The process gets a free debugging port. The profile defaults to `src/chrome/tagui_user_profile`, the same directory official TagUI uses. `TAGUI_CHROME_PROFILE` overrides it. That launch matches the official visible command: `--remote-debugging-port`, `--user-data-dir=<profile>`, `--window-size=1366,842`, and `about:blank`. Extensions stay enabled, so a profile extension such as Buster loads. There is no `--disable-extensions` and no `--no-sandbox`. Headless adds only the official `--headless --disable-gpu`. The profile directory is created if it is missing and is not deleted on shutdown.

`TAGUI_CHROME_ISOLATED=1` is the container path, and the browser tests on this VM set it. It uses a temporary `tagui_modern_profile_<pid>_<hex>` directory, `--remote-allow-origins=*`, `--disable-extensions`, and `--no-sandbox` unless `TAGUI_CHROME_SANDBOX=1`. Headless isolated runs use `--headless=new` and `--force-device-scale-factor=1`, unless `TAGUI_DEVICE_SCALE` sets another factor. A visible isolated window keeps the display scale unless that variable is set. `--allow-file-access-from-files` is set on this path, and a `file://` step is rewritten with `realpath` before `Page.navigate`.

Startup polls `/json/list` with a one-second HTTP timeout and prefers the `page` target whose URL is `about:blank`. The websocket timeout is 30 seconds. `Page.enable`, `Runtime.enable`, and `Page.bringToFront` run before the first step. Shutdown closes the socket and signals the process. An isolated profile is then `pkill`ed and deleted. The official profile is left on disk. `end_processes` kills leftover `tagui_modern_profile_` processes. The MCP reaper does the same.

### Steps that work

| Step | Behavior |
| --- | --- |
| `http://`, `https://`, `file://` | `Page.navigate`, then poll `document.readyState === "complete"` and a non-blank URL. `file://` is opened at `realpath`, so macOS `/tmp` becomes `/private/tmp`. A `chrome-error://` page is a failed navigation. Minimum wait budget 20 seconds |
| `click` / `tap`, `rclick` / `rtap`, `dclick` / `dtap`, `hover` / `move` | resolve the locator, then `Input.dispatchMouseEvent` at the element center |
| `type` / `enter` | click and focus the field, then `Input.dispatchKeyEvent` for each character (`keyDown` with `text`, then `keyUp`). `[enter]`, `[tab]`, `[esc]`, `[backspace]`, `[space]`, and the other named keys are real key events. `[enter]` keyDown carries `text` and `unmodifiedText` `\r`, matching Puppeteer's US keyboard layout, so a password field submits the form. Text is set only where that layout sets it (`[space]` is a space; Tab, Escape, arrows, and the rest have none). `[clear]` is Ctrl+A (Command+A on macOS, with the modifier bit set) and backspace. The step trace redacts the text after `as`. A visual `type` rewrites only the image path, not text that happens to end in `.png`. There is no `Input.insertText` and no synthetic `InputEvent` |
| `select` / `choose` | set the matching `<option>` by value or text and dispatch `change`. `[clear]` selects the empty value |
| `upload` | `DOM.setFileInputFiles` on the file input |
| `read` / `fetch` | `read page to var` stores `outerHTML`. An element stores its text or input value |
| `show` / `print` | `show page` prints `show page -` plus `documentElement.outerHTML`, the same string `chrome.getHTML()` returns |
| `save` / `write` / `load` | write page or element text to a file, append it, or read a file into a variable |
| `table` | write a table, or an XPath or CSS list of rows, to csv |
| `download` / `receive` | `download to folder` sets Chrome's download directory with `Browser.setDownloadBehavior` (falling back to `Page.setDownloadBehavior`), the same idea as official `chrome.download(path)`. `download url to file` still saves that URL with `file_get_contents`, so the PHP curl extension is not required, and that URL is recorded for a later `receive`. `receive` waits for a network response whose URL contains the keyword and prefers a `Document` response |
| `api` | HTTP call from `api_config` when that variable is set. The body is `api_result` and parsed JSON is `api_json` |
| `run` | a shell command, or a `run begin` / `run finish` block. Output is `run_result` |
| `echo` | A quoted expression still concatenates, so `echo "n is " n` prints the value. Unquoted prose is a literal, matching TagUI v6: `echo Retorno anexado no ClickUp` prints those words. A single unquoted name still prints the variable when it exists. Mixed `'` and `"` is an error |
| `check` | `check condition \| true-text \| false-text` prints one of the two texts |
| `ask` | reads stdin into `ask_result`. A visible `chrome` window uses `prompt()` |
| `snap` | `snap page` is `Page.captureScreenshot` (`fromSurface`, clip scale 1) of the current page viewport. If that call stops answering after a frame click, the same shot is taken once without `fromSurface` and later shots in the run skip the compositor path. `snap page.png` and `snap page.bmp` (including a path such as `visao/page.png`, and including `to path`) go to Sikuli, which captures the whole desktop with `Screen().capture(Screen().getBounds())`. Any other `.png` or `.bmp` is a Sikuli snap of that image, with the path absolute from the flow directory. On a 2x Retina display a 260×90 CSS button is a 520×180 PNG, which is the size OculiX searches |
| `wait` | `floatval` of the argument, as in `wait_intent`. Empty is 5 seconds. `wait //*[@id="x"]` is 0 seconds, the same as official `floatval` of a non-numeric string. Capped at 120. `wait for <selector>` and `wait element <selector>` wait until that DOM target or image appears, within the step timeout, and fail the step if it does not. `TAGUI_MODERN_WAIT_ELEMENT=1` makes a non-numeric `wait <selector>` do the same. A numeric `wait 5` stays a sleep |
| `dump expr` and `dump expr to file` | writes the value with no extra newline. With no `to`, the file is `textN.txt` in the flow directory, the same counter `save` and `write` use. Booleans are `true` and `false`. `write expr` with no `to` appends that value plus a carriage return and newline to the current `textN.txt` |
| `timeout N` | element-find budget in seconds, default 10. A trailing `//` comment is ignored, then `floatval`, so `timeout 60  // comment` is 60 |
| `dom <js>` | `Runtime.evaluate` of `(function(dom_json){ ... })(dom_json)`, the same signature as official `chrome.evaluate`. `dom_json` is the flow variable when it is an object, otherwise `{}`. The result is `dom_result`. `dom begin` / `dom finish` is one script with that same wrapper. A `prompt()`, `alert()`, or `confirm()` is accepted so the script continues; `prompt()` receives an empty string. Calling a method on a null selector stops with `dom target is null` instead of a page TypeError |
| `js` | The statement runs in the page via `Runtime.evaluate`. Scalar flow variables are placed in a `with` scope and copied back. `js begin` / `js finish` is one script. Inside it, `title()`, `url()`, `text()`, `exist()`, `present()`, `visible()`, and `count()` read the page. `casper`, `this`, `require`, and `fs` are absent |
| `py` | One persistent Python 3 process (`TAGUI_PYTHON`, otherwise `python3`). Assignments survive later `py` steps. Printed text is `py_result`. When that text is JSON it is also `py_json`. `py begin` / `py finish` is one block. A Python exception is stored in `py_result` and the flow continues |
| `r` | One persistent `Rscript` process (`TAGUI_RSCRIPT`). Base R only. An `r begin` / `r finish` block is joined with `; ` the way `parse_closure` joins `[END_OF_LINE]`, so a `#` comment consumes the rest of that joined line. The helper runs `eval(parse(text = code), envir = .GlobalEnv)`. Printed lines (`cat`, `print`) are space-joined into `r_result`. A bare expression such as `1+2` does not print. `message()` stays off `r_result`. JSON text is also `r_json`. An R error clears `r_result`, stops the flow, and says `cannot execute R command(s)`. A missing `Rscript` stops the step and names Rscript |
| other languages | `TAGUI_LANGUAGE`, or `tagui_language` in `tagui_config.txt`, runs each line through `src/translate.php` and `src/languages/<language>.csv` before the step runs. English skips that |
| `keyboard`, `mouse`, `vision`, image and coordinate targets | Sent to OculiX or SikuliX when `TAGUI_SIKULI_JAR` or `src/sikulix/oculixide.jar` is present. That includes `rclick`, `click (x,y)`, and `exist()` / `present()` / `visible()` when the argument is a `.png`, `.bmp`, or `(x,y)`. `.png` and `.bmp` targets are absolute paths from the flow directory. An image `exist()` polls until the step timeout, then returns false. An image `present()` or `visible()` is one Sikuli call. Official `visible_intent` sets the auto-wait to 1 second for that call, not the flow timeout. A missing jar still fails on the first call. OCR text from a `read` / `show` / `save` comes back from `tagui_sikuli.txt` as UTF-8. That works on Ubuntu when `libjpeg.so.8` is present, and fails on Debian 13 without it (see Linux and OculiX) |
| `frame name` | the next step or `{ }` block searches inside that frame. `name \| child` nests. Mouse coordinates add the frame's offset. A frame that is still loading is polled until the step timeout |
| `popup keyword` | the next step or block attaches to another page target whose URL contains the keyword, then returns to the parent page. `about:blank` and `chrome://` targets are not popups, so a keyword such as `about` does not attach to the blank start page. By default a click that opens another tab leaves DOM steps, navigation, and `snap page` on the first page, the same as official Chrome, which moves only inside `popup`. `TAGUI_MODERN_FOLLOW_TABS=1` adopts the newest page whose `openerId` chain starts at the current page before those steps. A target already left by `popup` is not adopted again |
| `var = expr` and `var x = ...` | assignments. `exist()`, `present()`, `visible()`, `count()`, `title()`, `url()`, `text()` are calls. An image argument to `exist()`, `present()`, or `visible()` asks Sikuli whether it is on screen. `.indexOf`, `.toString`, `.trim`, and `.toLowerCase` stay in PHP. A method or token that parser rejects, including `.replace(/ /g, "_")`, is evaluated in the page scope. `echo`, `dump`, `check`, and conditions use that fallback |
| `` `expression` `` and `'+name+'` | a backtick pair is an expression, as in the classic parser. `echo `title()`` prints the title. On a click or type step the value is inserted into the line. `'+name+'` and `"+name+"` are replaced with that variable the same way, so `click '+locator+'` clicks the saved locator. Repository columns such as `[iteration]` are replaced first |
| datatables | a csv argument is transposed the way `transpose.php` does. A sidecar `flow.csv` is read as columns. `tagui_local.csv` and `tagui_global.csv` fill object names and are also flow variables, the way official emits `var name = 'value'`. Spaces become underscores and other characters are dropped, so `js user = atob(usuario_erp)` sees `usuario_erp`. `[iteration]` is the dataset index. The next dataset waits 3 seconds unless `speed` is set |
| `expect <target>` | required check. Waits for a DOM selector until the step timeout, or polls an image the way `exist()` does. Missing target fails the step (`required check failed`) and the run exits 1. This step is modern-only |
| `if` / `else if` / `else` | natural-language conditions and a parenthesized JavaScript-shaped condition. A false `if` or `else if` logs `SKIP -` plus the source line. That is not a failure, and the flow continues. Quiet mode hides that line with the other step traces. `TAGUI_MODERN_STRICT=1` turns a false `if exist(...)`, `if present(...)`, or `if visible(...)` (optionally `== true`) into that same required-check failure. `if !exist(...)` stays a skip |
| `for n from A to B` and `for (n=1; n<=N; n++)` | bounds are integers or variable names, so `for i from 0 to counti` is `for (i=0; i<=counti; i++)`. `infinity` is 1024. Ranges larger than 10000 are rejected |
| `while` | re-checked each pass, stopped after 10000 iterations |
| `break` / `continue` | leave or restart the enclosing `for` or `while` |
| `{` `}` | blocks, including a block as an if/for/while/frame/popup body. Indented lines under `if`, `else`, `else if`, `for`, `while`, `frame`, and `popup` are the body too, including nested blocks. A single next line at the same indent remains the body, so older flows that do not indent still run |
| `tagui other.tag` | inlines that flow, relative to the file that contains the step, and runs it in the same variable scope. A missing file stops the step. Official does this in `expand_intent` before the rest of the parse |
| `live` | prints the classic banner and reads stdin until a line whose first characters are `done` |

Flow mode polls for an element until `timeout`. Live mode looks once, matching `check_tx()` (no `waitFor`). A missing element in a flow stops the run, which is the classic `this.exit()` behavior. In live mode the error is printed and the next line is read. Errors in flow mode use `ERROR - [LINE n] ...` so the MCP server's line parser still matches. Quiet mode hides `START`, the step trace, and `FINISH`. `echo` and errors still print. Every run, including quiet mode, ends with `SUMMARY - ran N, skipped M, blocks skipped B` on stdout and in `TAGUI_LOG`. `ran` is steps that executed. `skipped` is steps inside a branch that was not taken. `blocks skipped` is false `if` / `else if` branches. The default exit code is unchanged: skips alone still exit 0. Set `TAGUI_MODERN_STRICT=1`, or add `expect <target>`, when a missed login check must exit 1.

`p1`–`p16` start as empty strings, then take `TAGUI_PARAM_n`, then take non-empty CLI arguments, so the CLI wins. An empty parameter is falsy in a `js` step, which is how `js if (p2)` behaves when the flow was started without that argument.

The modern engine exits 1 when a flow step sets `stopped`, and when Chrome fails to start or the flow file is missing. A false `if exist(...)` does not set `stopped` unless `TAGUI_MODERN_STRICT=1`. `expect` and `wait for` do. `src/tagui` and `src/tagui.cmd` pass that status through. Live mode does not set `stopped` (the error is printed and the next line is read), so a live session that reaches `done` still exits 0. A datatable run exits 1 if any row stopped; later rows still run.

The Casper launcher is unchanged. It initializes `tagui_error_code` to 0 and a step error does not raise it, so existing flows and callers that treat a non-zero status as "TagUI itself failed to launch" keep that contract on the default engine. The modern engine is already opt-in (`modern`). A second flag would leave `tagui flow chrome modern` exiting 0, which hides a failed step from `set -e` and from CI. The MCP server still prefers the `ERROR -` line when both are present, so `failing_line` stays the step text.

### Steps that stay on the CasperJS engine

`test` / xUnit. JavaScript that needs Casper or Phantom: `casper`, `this.getCurrentUrl()`, `require('fs')`, `fs.read`. `this.echo(...)` prints through the engine. Any other `this.*` stops with `Casper-only` before the page runs. `8_hastebin` reaches `var fs = require('fs')` and stops with `unknown function require`. A line with more than one statement, such as `var a='red'; var b='green';`, is sent to the JavaScript runner whole. `timer()` is seconds since the run started. `mouse_xy()`, `mouse_x()`, and `mouse_y()` ask Sikuli for the pointer. An undefined name in `check` or `if` compares as undefined, so the condition is false and the run continues. `run begin` / `run finish` runs the lines between them as one shell command.

Visual steps with no jar configured stop and name OculiX, Java 17+, and `TAGUI_SIKULI_JAR`. The engine does not download the jar. Linux needs the full JRE and an X11 display. A Debian 13 desktop click of a 260×90 PNG passed. OCR works on Ubuntu 26.04 with `libjpeg.so.8` and fails on Debian 13 without it; see Linux and OculiX.

`for n from A to B` accepts an integer or a variable that holds a number. `frame` and `popup` are flow-file blocks. In live mode they print that they are not supported there, which matches the CasperJS live-mode restriction.

### One step table

`src/tagui_steps.php` defines keyword order, code prefixes, and the locator attributes `id`, `name`, `class`, `title`, `aria-label`, `text`, `href`. `tagui_steps_javascript()` emits `tagui_keyword_intent()`, `tagui_is_code()`, `tagui_locator_attributes`, and `tagui_locator_xpath()` into the CasperJS script ahead of `tagui_header.js`. A PHP test checks the aliases. `node --check` accepts the generated functions, and the same inputs classify the same way in PHP and Node.

## Wiring that stayed compatible

RPA-Python is not modified. `src/test/test_modern_live.py` speaks its init, send, and close sequence to `tagui <flow> headless modern quiet`: banner, echo markers, `exist().toString()`, `type`, `click`, `read`, `dump`, `url()`, `done`.

The MCP server grew an optional engine selector and nothing else in the protocol:

- `browser_start(..., engine="")` accepts `casper` (default) or `modern`. An empty value follows `TAGUI_ENGINE`, then `casper`.
- `live_command` appends `modern` only for that engine. `modern` is an allowed runner option and is not a browser name, so `headless modern` is valid.
- The reaper also kills processes whose command line contains `tagui_modern_profile_`.
- `run_flow` and `run_workspace_flow` accept `modern` in `options`. A missing element still returns `failing_line`, `failing_step`, `error_message`, and `screenshots`. `src/test/test_modern_repair.py` checks that against the modern engine.

## What was verified on this VM

Linux, PHP 8.3.6 with `pcntl`, `google-chrome-stable`, and R 4.3.3. CasperJS and PhantomJS from a local npm install ran classic parse parity, the live-mode probe, and a CasperJS flow of the suite's `r` steps. macOS and Windows were not executed.

- `php -l` reports no syntax errors in `src/tagui_modern.php`, `src/tagui_steps.php`, `src/tagui_runtime.php`, `src/tagui_parse.php`, and `src/translate.php`. `bash -n src/tagui` passes.
- `php src/test/test_modern_logic.php` covers condition rewrite, both `for` forms including `for i from 0 to counti`, indented `if` / `else` / `for` blocks, `tagui other.tag` includes, unquoted `echo` text, `timeout 60  // comment`, `wait` of an XPath (0 seconds), `download to folder`, repository names as `js` variables (`atob(usuario_erp)`), `while` / `break`, datatable rows and `[iteration]`, `load` / `write`, `check`, the shared keyword table, `.replace(/ /g,"_")` through Node, a `js begin` block, persistent `py` (`42` then `240`), a Python error left in `py_result`, a missing `Rscript`, and with Rscript installed the suite R steps (`3`, `12`, `TRUE1TRUE2`, an empty bare `1+2`, `[1] 1 [1] 2`, a `#` comment that consumes the rest of the semicolon-joined block, `message()` left out of `r_result`, and an R error that stops the flow and clears `r_result`), Chinese `输入` / `如果` / `回声`, a missing OculiX jar, `UnsupportedClassVersionError` reporting `OculiX requires Java 17+`, and `TAGUI_JAVA` / `JAVA_HOME` selecting the Java binary. `click (x,y)`, `keyboard`, `rclick`, and `exist("file.png")` route to that jar instead of a DOM lookup. `snap page.png` is routed to Sikuli and `snap page` stays a Chrome capture. Image `present()` / `visible()` are one call. The same run checks trusted `type` key events (including the Ctrl/Command bit on `[clear]`), redacted type traces, `SKIP -` on a false `if` / `else if`, absolute visual paths, image `exist()` polling, the official Chrome profile arguments, `expect`, `wait for` / `TAGUI_MODERN_WAIT_ELEMENT`, `TAGUI_MODERN_STRICT`, and the end-of-run `SUMMARY` line. The three engine PHP files do not call `curl_` or `DOMDocument`. This run was PHP 8.3.6 with the curl and dom extensions absent.
- `php src/test/test_modern_browser.php` runs a local fixture in headless Chrome: the same `.replace` assignment in the page (`Tan_Kay_Chen`), `py print(40+2)`, `select`, `upload`, `table`, `show page` (HTML, including `<title>Modern Steps</title>`), `dom` with `dom_json`, `frame`, `popup`, `run`, `api`, `download`, `download to` a folder, and `receive`. All checks passed.
- `php src/test/test_modern_suite.php` runs `src/test/positive_test` after `this.exit()` on the modern engine. The original flow is not edited. A copy rewrites the dead hosts to `src/test/fixtures/suite_router.php`. On this VM, with R 4.3.3, OpenJDK 21, Xvfb, and `oculixide-4.0.0-linux.jar`, that run passed 453 of 453 steps it attempted (99.6% of 455 step lines once explicit skips are counted). It skipped 2: the suite's while loop whose body never changes `number`. Classic parses that loop as `while (((number <= 5)))` around `this.click(tx('test'))` and would hang too. The official `live` step runs because the harness feeds stdin (`echo`, a click on the fixture, an assignment, `done`). The 30 image and coordinate steps run against `src/test/fixtures/visual.html`. The harness opens that page in headed Chrome at device scale 1 on a private Xvfb (`1366x768x24`) and crops each mark into `button.png`, `textbox.png`, `option_list.png`, `option_item.png`, `sometext.png`, `1.png`, and `test.png` (plus the `.bmp` names the suite uses). Those crops are also stored under `src/test/fixtures/images/`. `read page` stores the page HTML in `variable`, so the executed copy assigns `variable = 5` before the two xpath index clicks that still name `variable`. Without the jar, or without a display, those visual steps are skipped instead of failed. Without `Rscript` only the `test r` section is skipped. A selector that matches nothing stops with `dom target is null`. `.github/workflows/modern-suite.yml` installs `r-base-core`, Chrome, `openjdk-21-jre`, and Xvfb, downloads OculiX 4.0.0, and runs this test.
- `php src/test/test_modern_enter.php` opens a local form with `#username` and `#password` and runs `type #password as x[enter]`. The page title becomes `trusted:x`: the Enter keyDown submitted the form as a trusted event, and the password value stayed `x`.
- `php src/test/test_modern_tab.php` clicks a `target="_blank"` link. With the default (follow off) the title stays `First`. With `TAGUI_MODERN_FOLLOW_TABS=1` the same flow reads the new page (`Second` / `second-tab`) without a `popup` step.
- `TAGUI_LANGUAGE=chinese tagui <flow> headless modern quiet` on a one-line flow `回声 "from-cli"` printed `from-cli`.
- `php src/test/test_modern_sikuli.php`, with `/tmp/oculixide-4.0.0-linux.jar`, OpenJDK 21, and Xvfb `:99` on Ubuntu 24.04, reached `[0] START`, ran `vision print`, a blank-screen `Screen().text()` (SUCCESS, empty text; this VM has `libjpeg.so.8`), `keyboard a`, a missing PNG (`not on screen`), and a Java process killed during a click (reported as exited during the step, without waiting out the timeout). The test skips when the jar, Java, or Xvfb is absent. The missing-`Rscript` error is the logic test with `TAGUI_RSCRIPT` pointed at a file that is not there.
- `php src/test/test_modern_screen.php` with `TAGUI_DEVICE_SCALE=2` opened a `file://` URL through a symlink (the landed URL was the real path), reported devicePixelRatio 2, and saved a 100×40 CSS box as a 200×80 PNG. The page snap was about twice the CSS viewport, not four times.
- `php src/test/test_modern_exit.php` exits 0 for `echo "ready"` and 1 for a step error. With `TZ=Pacific/Auckland`, the START line matches that zone rather than PHP's UTC default.
- `python3 src/test/test_modern_repair.py` runs `run_flow(..., options=["headless", "modern", "quiet"])` against a missing element and a visual step whose OculiX jar is missing. Each result has `failing_line`, `failing_step`, and `screenshots` entries for `failure_tab.png` and `failure_desktop.png` written beside the flow before Chrome closes.
- `python3 src/test/test_modern_live.py` passed the RPA-style exchange (`exist`, `type`, `click`, `read`, `url()`) and `LiveSession(engine="modern")`. The banner arrived without a `tee` pipe. The same run feeds `echo`, a click on the suite fixture, `seen = 1`, and `done` into modern live mode, then repeats that exchange through the MCP tools `browser_start` (engine `modern`), `browser_step`, `browser_go`, `browser_click`, `browser_read`, and `browser_stop`.
- `PYTHONPATH=src/tagui_mcp python3 -m pytest src/tagui_mcp/tests -q` passed 24 tests.
- `node --check` accepts the JavaScript emitted by `tagui_steps_javascript()`. The same nine sample lines classify identically in PHP and Node.

### Sample parity

`tagui <sample> modern quiet` on the flows in `src/samples`. "Locator missed" means Chrome opened the URL and a later selector was not on the live page. The classic engine could not be compared because CasperJS is not installed.

| Sample | Modern engine |
| --- | --- |
| `4_conditions` | Passed. Condition echoes, `for`, `break` (1 through 5), and `continue` (1, 2, 4, 5) matched the flow |
| `b_visualoutlook` | Reached `live` and printed the ready banner. The sample waits for stdin |
| `1_yahoo` | Opened the URL, then `[LINE 27] cannot find search-box` |
| `2_twitter` | `[LINE 12] cannot find ProfileAvatar` |
| `3_github` | `[LINE 11] cannot find (//*[@class="numbers-summary"]/li)[5]` |
| `5_repositories` | `[LINE 34] cannot find quick_email` |
| `6A_datatables` | Ran both csv rows, then `[LINE 31] cannot find .public` on each |
| `7_testing` | `[LINE 11] cannot find uh-tb-home` |
| `8_hastebin` | Opened the URL. The `js if (p2)` lines ran. `[LINE 15] unknown function require` on `var fs = require('fs')`, which is Phantom code |
| `a_facedetect` | The `.replace(/ /g,"_")` assignment ran. The following `for n from 1 to number_of_frgnd_images` now uses the variable as the upper bound. That sample was not re-run after the change |
| `c_chineseflow` | With `TAGUI_LANGUAGE=chinese`, line 19 becomes `type search-box as github` and Chrome reports `[LINE 19] cannot find search-box`. The default `tagui_config.txt` is english, and that run leaves the Chinese line untranslated |
| `9_misc` | Produced no step error before a 25-second timeout during the first page load |

## Phase 3

Phase 3 would make `modern` the default for `headless` and `chrome`, with a `casper` flag for the old sequencer. It is not ready.

The browser, logic, datatable, live-mode, and MCP repair paths work, and the step table is shared. `js` / `py` / `r`, `.replace` expressions, and translated keywords run. Flipping the default would still change every flow that calls `casper`, `require`, or `fs` inside `js`, every `test` step, and every visual step on a machine without a SikuliX or OculiX jar and a display. The live samples still fail on selectors the engine cannot tell apart from a missing step, and there is no side-by-side CasperJS run on this VM.

Until then, callers opt in with `tagui flow.tag modern` or `modern` in the MCP `options` / `engine` field. The CasperJS engine remains the default. RPA-Python's installer still overlays the Tump files and still patches PhantomJS on macOS. Shipping `modern` as the default inside a Tump zip waits on that same flip.

What is still untested: Windows `tagui.cmd` including the direct log write, OculiX on Windows, Wayland, the `libjpeg.so.8` workaround on Debian, and samples other than the R probe and live-mode probe against the CasperJS engine. OCR of visible text worked on Ubuntu 26.04 (`Hello OCR Test 123`, `read page.png to var`) and failed on Debian 13 (see Linux and OculiX). On Ubuntu 26.04 at device scale 2, an image click exited 0 with a 520×180 snap, a missing image exited 1 with `best similarity 0.35`, and the timestamps were local. On a Debian 13 X11 desktop at 1920×1200, an image click of a 260×90 `btn.png` passed. On Apple Silicon, a user confirmed Chrome launch, native OculiX 4.0.0, and an image click after a 2× upscale of a CSS-sized snap. The engine now saves snaps at device pixels so that upscale is the capture itself. That Mac was not re-run after the capture change. The Ubuntu webtop set `TAGUI_CHROME_BIN` to `/usr/lib/chromium/chromium` and was not re-run after the wrapper lookup.
