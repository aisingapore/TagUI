# TagUI MCP server

A [Model Context Protocol](https://modelcontextprotocol.io) server that lets an AI assistant run [TagUI](https://github.com/tebelorg/TagUI) on the machine where the server is installed.

The server is a separate Python package under `src/tagui_mcp`. It does not modify TagUI's runner, parser, or browser integration. It calls the existing `tagui` script, which is the same path the command line, the crontab API queue, and [TagUI for Python](https://github.com/tebelorg/TagUI-Python) already use.

## Why Python

TagUI's runtime is a shell script, a PHP translator, and CasperJS. The project deliberately avoided adding Node.js as a requirement for Chrome automation. Python is already the language TagUI documents for external control: `py` steps run inside a flow, and TagUI for Python drives live mode from outside the process. The official MCP Python SDK (`mcp` 2.x, `MCPServer`) speaks stdio, which is what Claude Desktop and Cursor launch. A small Python process next to the `tagui` script is easier for upstream to review than folding a protocol server into `tagui_parse.php` or the shell runner.

## What you need

The MCP package does not vendor TagUI's engines. A packaged TagUI install (or a manual install of the same tools) has to be on the machine:

- The `tagui` script, normally `src/tagui` in this repository
- PHP CLI (`php`), used to translate a flow
- CasperJS and PhantomJS, used to execute the translated script
- Google Chrome or Edge for `headless` or `chrome` runs and for every live browser tool

`run_flow` / `run_steps` with no browser flag use TagUI's default PhantomJS browser. `browser_*` tools always start live mode with Chrome, because live-mode navigation only works there. Pass `"engine": "modern"` to `browser_start`, or `modern` in `options` for `run_flow` / `run_steps`, to use the DevTools engine in `src/tagui_modern.php` instead of CasperJS and PhantomJS. `TAGUI_ENGINE=modern` does the same for `browser_start` when `engine` is omitted. See `docs/modern-engine.md`.

## Install

From this directory:

```bash
cd src/tagui_mcp
python3 -m pip install -e .
```

`pip install -e .` adds a `tagui-mcp` command. To run without installing, set `PYTHONPATH` to `src/tagui_mcp` and use `python3 -m tagui_mcp`.

The server finds TagUI in this order:

1. `TAGUI_BIN` — full path to the `tagui` script (or `tagui.cmd` on Windows)
2. `TAGUI_SRC` — directory that contains that script
3. `src/tagui` next to this package, when the package is used from a TagUI checkout
4. `tagui` on `PATH`

Check the resolved path with the `tagui_status` tool.

## Claude Desktop

Edit `claude_desktop_config.json` (macOS: `~/Library/Application Support/Claude/claude_desktop_config.json`, Windows: `%APPDATA%\Claude\claude_desktop_config.json`):

```json
{
  "mcpServers": {
    "tagui": {
      "command": "tagui-mcp",
      "env": {
        "TAGUI_SRC": "/absolute/path/to/TagUI/src"
      }
    }
  }
}
```

If `tagui-mcp` is not on `PATH`:

```json
{
  "mcpServers": {
    "tagui": {
      "command": "python3",
      "args": ["-m", "tagui_mcp"],
      "env": {
        "PYTHONPATH": "/absolute/path/to/TagUI/src/tagui_mcp",
        "TAGUI_SRC": "/absolute/path/to/TagUI/src"
      }
    }
  }
}
```

Restart Claude Desktop after saving. The server uses stdio; there is no port to open.

## Cursor

Project config (`.cursor/mcp.json`) or the global MCP settings:

```json
{
  "mcpServers": {
    "tagui": {
      "command": "tagui-mcp",
      "env": {
        "TAGUI_SRC": "/absolute/path/to/TagUI/src"
      }
    }
  }
}
```

The `python3 -m tagui_mcp` form above works the same way in Cursor. Paths must be absolute.

## Streamable HTTP with a bearer token

stdio is the default. For a machine that runs unattended (a service that starts on boot, reached through an SSH tunnel), the same server can speak Streamable HTTP:

```bash
export TAGUI_MCP_TOKEN_FILE=$HOME/.config/tagui-mcp/token   # chmod 600, at least 32 characters
tagui-mcp --transport http --host 127.0.0.1 --port 8765
```

- Every HTTP request must send `Authorization: Bearer <token>`. A missing or wrong token gets `401`, and the request never reaches the MCP app. The comparison is constant time.
- The token comes from `TAGUI_MCP_TOKEN` or from the file named by `TAGUI_MCP_TOKEN_FILE`, never from a command-line argument. The server refuses to start without one, with a token shorter than 32 characters, or when the token file is readable by group or others. It does not log the token.
- It binds to `127.0.0.1` and refuses any other address unless `--allow-remote` is given. The connection is plain HTTP. To reach it across a network, put a TLS reverse proxy in front and keep the server on loopback.
- When placed behind a reverse proxy that preserves the client's `Host` header (such as `cloud.cidadejardim.com`), configure `TAGUI_MCP_ALLOWED_HOSTS="cloud.cidadejardim.com,localhost,127.0.0.1"` (or pass `--allowed-hosts`) so the MCP SDK's DNS-rebinding protection accepts the request. Pass `*` to disable DNS rebinding protection entirely behind a trusted proxy.
- The default endpoint is `/mcp` (configurable via `TAGUI_MCP_PATH` or `--path`). Generate a token with `python3 -c "import secrets; print(secrets.token_urlsafe(48))"`.

An MCP client that supports HTTP headers connects with:

```json
{
  "mcpServers": {
    "tagui": {
      "url": "http://127.0.0.1:8765/mcp",
      "headers": { "Authorization": "Bearer <token>" }
    }
  }
}
```

Use a dedicated `TAGUI_CHROME_PROFILE` for the service, not a profile that another TagUI install or a scheduled run also uses. The server stops a Chrome that was started on exactly that profile when a session ends, when a start fails, and when the server starts or stops. Chrome moves itself out of a systemd service's cgroup, so stopping the unit alone does not close it. A new profile in visible (`chrome`) mode stops at Chrome's first-run terms dialog until someone accepts it once on the display; `headless` has no dialog.

Child TagUI processes inherit the server's environment, so `DISPLAY`, `TAGUI_CHROME_PROFILE`, `TAGUI_SIKULI_JAR`, `TAGUI_JAVA`, and `TAGUI_ENGINE=modern` set there apply to `browser_start` and `run_flow`.

## Tools

The repair loop is `create_flow` or `save_flow`, `validate_flow`, `run_workspace_flow`, then `edit_flow` on `failing_line`.

| Tool | What it does |
| --- | --- |
| `tagui_status` | Resolved `tagui` path, workspace, live-session transcript, and whether a browser session is open |
| `list_flows` | `.tagui` files in the workspace |
| `read_flow` | Full text plus 1-based line numbers |
| `create_flow` | New workspace flow. Fails if the name exists |
| `save_flow` | Create or replace a workspace flow |
| `edit_flow` | `replace`, `insert_before`, `delete`, or `append`. `line` is 1-based. `text` may contain newlines |
| `validate_flow` | Run `tagui_parse.php` only. Reports unbalanced `{ }`, bad `for` loops, and quote errors. Does not open a browser |
| `run_workspace_flow` | Run a workspace flow. Same result shape as `run_flow` |
| `run_flow` | Run any flow file. Optional `options` (`quiet`, `headless`, `chrome`, `firefox`, `report`, `speed`, `debug`, `test`, `baseline`, `upload`), `inputs` (p1–p16), `datatable` (a csv), `timeout_seconds` |
| `run_steps` | Write ad-hoc TagUI lines to a flow and run them. Returns `work_directory`, which is kept |
| `save_session` | Write the live transcript to a workspace flow. `only_successful` drops steps that returned `ERROR` |
| `browser_start` | Start live mode on headless Chrome (`mode`: `headless` or `chrome`). `engine`: `casper` (default) or `modern` |
| `browser_stop` | Send `done`, stop TagUI, and close TagUI's Chrome |
| `browser_go` | Open an `http` or `https` URL and poll until the page location changes |
| `browser_click` | `click <selector>` using TagUI's identifier rules (xpath, css, id, name, class, text, href) |
| `browser_type` | `type <selector> as <text>`. `[clear]` and `[enter]` work as in TagUI. Single quotes are escaped |
| `browser_read` | Visible text of the page (`target` = `page`) or of one element |
| `page_state` | URL, title, and visible text (text is truncated to 4000 characters) |
| `browser_step` | One or more TagUI lines. `wait N` and `run` stay in the live browser. `check`, `frame`, `popup`, `if`, `for`, `while`, and `{` `}` replay the transcript as a real flow and then close the browser |

Omitting `options` on `run_flow` / `run_steps` is quiet mode and TagUI's default PhantomJS browser. Example:

```json
{ "flow_file": "/absolute/path/to/TagUI/src/tagui_mcp/examples/hello.tagui" }
```

```json
{
  "steps": ["https://example.com", "echo title()", "snap page to page.png"],
  "options": ["headless", "quiet"]
}
```

A flow that is only `echo` lines does not need a browser flag. Chrome flags do need Chrome installed.

Every tool returns a JSON object. `ok` is false when TagUI prints `ERROR -`, the process exits non-zero, a dependency is missing, or the call times out. The Casper engine returns exit code 0 after a successful parse even when a later step fails. The modern engine returns 1 when a flow step fails. `ok` follows the `ERROR -` lines and the exit code; when both are present the step line is the `error` text.

A flow run also returns:

| Field | Meaning |
| --- | --- |
| `exit_code` | Process status. Casper can still be `0` when a later step failed. Modern is `1` when a flow step failed |
| `failing_line` | Line number from TagUI's `[LINE n]` message, when the parser emitted one |
| `failing_step` | That line of the flow file |
| `error_message` | First `ERROR -` line, preferring one that names a line |
| `log` | Contents of the `.log` file, truncated |
| `screenshots` | `.png` / `.jpg` files written beside the flow during the run |
| `hint` | A short repair note for a few known errors |
| `parameter_source` | `cli` or `environment` (`TAGUI_PARAM_n` when an input contains spaces or would pass the shell's 8-argument list) |

When a click or type times out, TagUI captures the page with `casper.capture(snap_image())` before exiting. That file shows up in `screenshots`. A failed live step also returns `url`, `title`, `text`, and `screenshot` while the browser is still open.

The workspace is `TAGUI_WORKSPACE`, or `src/tagui_mcp/workspace` when that variable is unset. Flow names are a single file name. `.tagui` is added when the name has no extension.

## Authoring notes

These come from closed TagUI issues and from how the parser actually behaves:

- A URL stored in a variable is its own line: `` `url` ``. `visit url` is not a step.
- The datatable row number is `[iteration]`.
- Variables are visible to a flow included with the `tagui` step. There is no separate global declaration.
- Nested `if`, `for`, `frame`, and `popup` need `{` and `}` on their own lines. `validate_flow` reports a mismatch.
- After `run some-command`, print the output with `echo run_result`. A line that is only `` echo `run_result` `` prints the literal `+run_result+`. Live mode also echoes `run_result` for you.
- Print an input with `echo p1`. Inputs that contain spaces are delivered as `TAGUI_PARAM_n` and still arrive in `p1`, `p2`, ...
- `api_config.header` is an array of `'Name: value'` strings. An object such as `{Authorization: '...'}` is ignored by `call_api`.
- Identifiers are tried in order: xpath, css, id, name, class, title, aria-label, visible text, href. `click h1` often fails; visible text or an xpath works. For a sibling, an xpath such as `//span[text()="350,90"]/ancestor::tr//input[contains(@class,"ico-salvar")]` is the usual shape.
- `if !exist(button.png)` is the visual-automation condition. `click button.png` uses the image. OCR of a whole region can crash Sikuli's Tesseract process; that abort is a dead process, not an `ERROR -` line.
- `snap` captures the viewport. A table taller than the window is clipped. Headless `snap page to file.pdf` has produced unreadable PDFs; use `.png` for failure shots.
- PhantomJS and Chrome do not share one JavaScript regex engine. Page scripts that must match a browser should run with `headless` or `chrome`, or in a `dom` step.
- Visual automation from cron needs a display. This server runs in the user's session, same as a terminal `tagui` run.

## Try it

```bash
cd src/tagui_mcp
python3 -m pip install -r requirements-dev.txt
python3 -m pytest
```

The tests use `tests/fake_tagui.py` as `TAGUI_BIN`, then connect with the MCP Python client over stdio and call `run_flow`, `run_steps`, and the browser tools. That checks the protocol without PHP or Chrome.

With a real TagUI install:

```bash
tagui-mcp
```

Leave that process in a terminal only when you want to see it start; desktop clients launch it themselves. To exercise the real CLI, point `TAGUI_SRC` at `src` and call `run_flow` on `examples/hello.tagui`.

An MCP inspector session is the same stdio process:

```bash
npx @modelcontextprotocol/inspector python3 -m tagui_mcp
```

Run that from a shell where `PYTHONPATH` includes `src/tagui_mcp`, or use the installed `tagui-mcp` command as the inspector's command.

## Limitations

- **Same privileges as the user.** A flow can run shell commands (`run`) and Python (`py`). This is a local automation driver, not a sandbox. The `upload` option sends the run log to hastebin.com.
- **One TagUI Chrome at a time.** Live mode and `headless` / `chrome` flows use debugging port 9222 and `src/chrome/tagui_user_profile`. Those two values are hardcoded in `src/tagui`, `tagui_chrome.php`, and `end_processes`. A second concurrent browser needs a port and a profile threaded through that whole path, so the server still refuses `run_flow` / `run_steps` while a browser session is open. If a session is stuck, run `src/end_processes`.
- **Live mode still cannot compile blocks.** `wait N` and a single-line `run` now run inside the live browser: the live loop is one CasperJS step per line, so `casper.wait` and `casper.waitForExec` finish before the next line is read. `check`, `frame`, `popup`, `if`, `for`, `while`, and `{` `}` are generated by the PHP parser as blocks around the following steps. Reimplementing that compiler inside the live interpreter would fork the language. `browser_step` detects those lines, closes the live browser, and runs the transcript through the normal flow runner instead. `browser_go` still polls the URL because navigation returns before the load event.
- **A newline is a new step.** The live prompt reads one line at a time. `browser_step` accepts several lines. A value typed into a field cannot contain a newline; use `[enter]`. Single quotes in `click` and `type` are escaped. A step that mixes `'` and `"` is still rejected by TagUI's string handling.
- **`py begin` / `run begin` and the matching `finish` must be one `browser_step`.** The sync marker is an `echo` line. If it lands inside the block, TagUI treats it as part of the block.
- **Files from the live session** are written under the `work_directory` returned by `browser_start`. `browser_stop` deletes that directory. `save_session` writes the transcript into the workspace, which is kept. `run_steps` leaves its `work_directory` in place.
- **Shell argument list.** On macOS and Linux, `src/tagui` forwards every remaining argument, including values with spaces. `tagui.cmd` still forwards only eight arguments and splits on spaces. The MCP server sends overflow inputs and inputs that contain spaces as `TAGUI_PARAM_1` … `TAGUI_PARAM_16`, which `tagui_header.js` copies into `p1` … `p16` when the CLI slot is empty. Options plus a datatable still share eight slots. Inputs cannot contain newlines.
- **Engines are not bundled.** Missing `php`, CasperJS, PhantomJS, or Chrome shows up as a failed tool call (`stderr` often says `command not found`). Packaged TagUI builds already include those binaries. CasperJS 1.1's launcher asks for `python` on `PATH` (`python3` is not enough unless you symlink it). PhantomJS 2.1.1 on a system OpenSSL 3 fails unless the client env sets `OPENSSL_CONF` to `/dev/null` or another config that does not load the OpenSSL 3 `providers` module. On PHP 8, `count(false)` from `fgetcsv` used to abort the parser; `csv_row_count()` reports an empty datatable row instead.
- **stdio by default.** Desktop clients launch the server over stdio. HTTP is opt-in (`--transport http`) and always needs a bearer token. There is no TLS and no per-user identity: one shared token grants every tool, and the tools run flows with the server user's privileges.
- **Windows.** The executable name switches to `tagui.cmd`. Browser tools were exercised on Linux. Live mode reads CasperJS stdout a line at a time; that matches TagUI for Python, which depends on CasperJS flushing each `echo`.

## Layout

```
src/tagui_mcp/
  tagui_mcp/server.py     MCP tools (stdio)
  tagui_mcp/http_transport.py  Streamable HTTP behind a bearer token
  tagui_mcp/service.py    one-at-a-time flow runs and the live session
  tagui_mcp/runner.py     tagui CLI wrapper
  tagui_mcp/session.py    live-mode stdin/stdout protocol
  examples/hello.tagui
  tests/                  unit tests plus an MCP client test
```

## Upstream

The server is meant to be proposed to tebelorg/TagUI as an optional add-on. The package lives in this directory. A few small runner changes support it and are useful without MCP:

- `src/tagui` forwards every remaining argument, including values that contain spaces. `tagui.cmd` is unchanged apart from a comment.
- `tagui_header.js` reads `TAGUI_PARAM_n` into `p1`–`p16` when that CLI slot is empty, escapes single quotes in live `click` / `type`, and implements live `wait` and `run`.
- `tagui_parse.php` runs live mode as one CasperJS step per line, snaps the page when an element is missing, and uses `csv_row_count()` so PHP 8 does not throw `count(false)` on a datatable.

`tagui_service.php` stays the crontab-fed batch queue. It is a poor fit for an interactive MCP session. Dropping `src/tagui_mcp` leaves the runner changes above in place.
