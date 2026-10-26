"""stdio MCP server for TagUI.

stdout is reserved for MCP messages. This module does not print.
"""

from __future__ import annotations

import atexit
import json
import signal

from mcp.server.mcpserver import MCPServer

from tagui_mcp import __version__
from tagui_mcp.service import get_service, reset_service

atexit.register(reset_service)

INSTRUCTIONS = (
    "Drive TagUI, a local RPA tool, on this machine. "
    "Prefer reusable flow files: create_flow or save_flow, validate_flow, "
    "run_workspace_flow, then edit_flow on the failing_line and run again. "
    "run_flow and run_workspace_flow return failing_line, failing_step, error_message, "
    "log, screenshots, and a hint. "
    "Use browser_start for a live Chrome session. browser_step accepts several lines. "
    "wait N and run work in live mode. check, frame, popup, if, for, while, and brace "
    "blocks are replayed as a flow because the live interpreter cannot compile them. "
    "save_session writes the live transcript into the workspace. "
    "Only one TagUI Chrome can run at a time. "
    "Flows run with the user's privileges, including any run or py steps in a flow file. "
    "A URL stored in a variable is a line that is just `url`. "
    "Datatable row number is [iteration]. Variables are visible to flows included with tagui. "
    "echo run_result prints a run step. echo p1 prints an input. "
    "api headers are an array of 'Name: value' strings."
)

mcp = MCPServer(
    "tagui",
    title="TagUI",
    description="Run TagUI flows and drive a live Chrome session.",
    instructions=INSTRUCTIONS,
    version=__version__,
)


def _json(payload: dict[str, object]) -> str:
    return json.dumps(payload, indent=2, ensure_ascii=False)


@mcp.tool()
def tagui_status() -> str:
    """Report the TagUI executable in use and whether a live browser session is open."""
    return _json(get_service().status())


@mcp.tool()
def run_flow(
    flow_file: str,
    options: list[str] | None = None,
    inputs: list[str] | None = None,
    datatable: str | None = None,
    timeout_seconds: float = 120,
) -> str:
    """Run a TagUI flow file and return its output as JSON.

    flow_file is a local path. TagUI accepts no extension, or .txt, .js, .tag, or .tagui.
    options are CLI flags: chrome, headless, firefox, quiet, report, speed, debug,
    test, baseline, upload, modern. Omit options to run in quiet mode on TagUI's
    default PhantomJS browser. Pass ["headless"] or ["chrome"] to use Chrome.
    Add "modern" to drive Chrome through the DevTools engine instead of CasperJS.
    A failing run still reports failing_line, failing_step, and screenshots.
    inputs become flow variables p1, p2, ... and cannot contain whitespace.
    options, inputs, and an optional datatable csv share TagUI's limit of 8 extra arguments.
    ok is false when the process fails, times out, or the output contains TagUI ERROR lines.
    TagUI's own exit code stays 0 when parsing succeeds even if a later step fails.
    """
    return _json(
        get_service().run_flow(
            flow_file,
            options=options,
            inputs=inputs,
            datatable=datatable,
            timeout_seconds=timeout_seconds,
        )
    )


@mcp.tool()
def run_steps(
    steps: list[str],
    options: list[str] | None = None,
    inputs: list[str] | None = None,
    datatable: str | None = None,
    timeout_seconds: float = 120,
) -> str:
    """Run ad-hoc TagUI steps as a one-shot flow.

    steps are full TagUI lines, one string per line, including a starting URL when
    the script uses the browser. This uses the normal flow runner, so conditions,
    loops, and wait steps work. The JSON result includes work_directory, where
    snapshots and other files written beside the flow are left.
    """
    return _json(
        get_service().run_steps(
            steps,
            options=options,
            inputs=inputs,
            datatable=datatable,
            timeout_seconds=timeout_seconds,
        )
    )


@mcp.tool()
def browser_start(mode: str = "headless", startup_timeout: float = 90, engine: str = "") -> str:
    """Start a live TagUI Chrome session for later browser tools.

    mode is "headless" (default) or "chrome" (visible window). engine is "casper"
    (default, CasperJS plus PhantomJS) or "modern" (Chrome DevTools, no CasperJS).
    An empty engine follows TAGUI_ENGINE, then casper. The session keeps one
    browser open so later steps share page state.
    """
    return _json(get_service().browser_start(mode, startup_timeout=startup_timeout, engine=engine))


@mcp.tool()
def browser_stop() -> str:
    """Close the live browser session and its Chrome process."""
    return _json(get_service().browser_stop())


@mcp.tool()
def browser_go(url: str, timeout_seconds: float = 20) -> str:
    """Open an http(s) URL in the live session and wait until the location changes."""
    return _json(get_service().browser_go(url, timeout_seconds=timeout_seconds))


@mcp.tool()
def browser_click(selector: str, timeout_seconds: float = 45) -> str:
    """Click an element in the live session.

    selector is a TagUI identifier: xpath, css, id, name, class, visible text, or href.
    """
    return _json(get_service().browser_click(selector, timeout_seconds=timeout_seconds))


@mcp.tool()
def browser_type(selector: str, text: str, timeout_seconds: float = 45) -> str:
    """Type text into an element in the live session.

    [clear] at the start clears the field and [enter] presses enter, same as TagUI.
    Single quotes are escaped. A newline is a new TagUI step, so it is rejected.
    """
    return _json(get_service().browser_type(selector, text, timeout_seconds=timeout_seconds))


@mcp.tool()
def browser_read(target: str = "page", timeout_seconds: float = 45) -> str:
    """Read visible text from the live page or from one element.

    target "page" returns the visible page text (not the raw HTML). Any other
    target is a TagUI element identifier and returns that element's text.
    """
    return _json(get_service().browser_read(target, timeout_seconds=timeout_seconds))


@mcp.tool()
def page_state(timeout_seconds: float = 45) -> str:
    """Return the live page URL, title, and a truncated copy of the visible text."""
    return _json(get_service().page_state(timeout_seconds=timeout_seconds))


@mcp.tool()
def browser_step(step: str, timeout_seconds: float = 45) -> str:
    """Send one or more TagUI lines to the live session.

    wait N and run execute in the live browser. A py/js/dom/run/vision begin-finish
    block must be sent together so the sync marker stays outside the block.
    check, frame, popup, if, for, while, and { } are run by replaying the session
    transcript as a real flow, which closes the live browser. A step starting with
    done is refused because that closes live mode.
    """
    return _json(get_service().browser_step(step, timeout_seconds=timeout_seconds))


@mcp.tool()
def list_flows() -> str:
    """List .tagui files in the workspace directory (TAGUI_WORKSPACE, or src/tagui_mcp/workspace)."""
    return _json(get_service().list_flows())


@mcp.tool()
def read_flow(name: str) -> str:
    """Read a workspace flow, including 1-based line numbers for edit_flow."""
    return _json(get_service().read_flow(name))


@mcp.tool()
def create_flow(name: str, content: str) -> str:
    """Create a new workspace flow. Fails if the name already exists.

    content is the full TagUI script. A URL from a variable is a line that is just `url`.
    Use [iteration] for the current datatable row. echo run_result prints a run step.
    echo p1 prints an input, including one that contains spaces.
    For an API call, set api_config.header to an array of 'Name: value' strings, not an object.
    """
    return _json(get_service().create_flow(name, content))


@mcp.tool()
def save_flow(name: str, content: str) -> str:
    """Create or replace a workspace flow with the full script text."""
    return _json(get_service().save_flow(name, content))


@mcp.tool()
def edit_flow(name: str, op: str, line: int = 0, text: str = "") -> str:
    """Change lines in a workspace flow and return the numbered script.

    op is replace, insert_before, delete, or append. line is 1-based.
    append ignores line. text may contain newlines and then becomes several lines.
    Use the failing_line from a run as line.
    """
    return _json(get_service().edit_flow(name, op, line, text))


@mcp.tool()
def validate_flow(name: str) -> str:
    """Parse a workspace flow with tagui_parse.php and do not launch a browser.

    Reports syntax errors such as unbalanced { }, bad for loops, and inconsistent quotes.
    This does not prove the selectors exist on a page.
    """
    return _json(get_service().validate_flow(name))


@mcp.tool()
def run_workspace_flow(
    name: str,
    options: list[str] | None = None,
    inputs: list[str] | None = None,
    datatable: str | None = None,
    timeout_seconds: float = 120,
) -> str:
    """Run a workspace flow and return structured failure details.

    The JSON includes exit_code, failing_line, failing_step, error_message, log,
    screenshots, and hint. Inputs may contain spaces; they are passed as p1, p2, ...
    A missing element captures a screenshot beside the flow when Chrome or PhantomJS is used.
    """
    return _json(
        get_service().run_workspace_flow(
            name,
            options=options,
            inputs=inputs,
            datatable=datatable,
            timeout_seconds=timeout_seconds,
        )
    )


@mcp.tool()
def save_session(name: str, only_successful: bool = False) -> str:
    """Save the live session transcript as a workspace flow.

    only_successful drops steps that returned a TagUI ERROR, including a block
    that failed when it was replayed as a flow. Copy snapshots out before
    browser_stop; stopping deletes the live work directory, not this flow file.
    """
    return _json(get_service().save_session(name, only_successful=only_successful))


def main() -> None:
    """Serve MCP over stdin and stdout."""

    def _shutdown(signum: int, _frame: object) -> None:
        reset_service()
        raise SystemExit(128 + signum)

    signal.signal(signal.SIGTERM, _shutdown)
    signal.signal(signal.SIGINT, _shutdown)
    mcp.run(transport="stdio")
