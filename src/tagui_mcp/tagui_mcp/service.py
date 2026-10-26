"""Operations exposed by the MCP tools.

A single service owns the live browser session. Flow runs and the live session
both use TagUI's Chrome debugging port, so they are not allowed to overlap.
"""

from __future__ import annotations

import re
import threading
import time
from collections.abc import Callable
from pathlib import Path

from tagui_mcp.flows import (
    create_flow,
    edit_flow,
    list_flows,
    read_flow,
    save_flow,
    validate_flow,
    workspace_dir,
)
from tagui_mcp.runner import TaguiFailure, resolve_tagui_executable, run_flow, run_steps
from tagui_mcp.session import (
    BrowserMode,
    LiveSession,
    check_live_step,
    line_needs_flow_parser,
    normalize_engine,
)

_URL = re.compile(r"^https?://\S+$", re.IGNORECASE)
_UNSAFE_SELECTOR = re.compile(r"[\r\n\x00`]")
_UNSAFE_TEXT = re.compile(r"[\r\n\x00`]")
_PAGE_TEXT_LIMIT = 4000


class TaguiService:
    def __init__(
        self,
        tagui_bin: str | Path | None = None,
        workspace: str | Path | None = None,
    ) -> None:
        self.tagui_bin = tagui_bin
        self.workspace = workspace
        self.session = LiveSession(tagui_bin=tagui_bin)
        self._lock = threading.Lock()

    def close(self) -> None:
        with self._lock:
            self.session.stop()

    def status(self) -> dict[str, object]:
        with self._lock:
            try:
                executable = resolve_tagui_executable(self.tagui_bin)
            except TaguiFailure as exc:
                return {"ok": False, "error": str(exc), "browser_running": False}
            return {
                "ok": True,
                "tagui": str(executable),
                "browser_running": self.session.running,
                "browser_mode": self.session.mode,
                "workspace": str(self._workspace()),
                "transcript": list(self.session.transcript),
            }

    def run_flow(
        self,
        flow_file: str,
        options: list[str] | None = None,
        inputs: list[str] | None = None,
        datatable: str | None = None,
        timeout_seconds: float = 120,
    ) -> dict[str, object]:
        with self._lock:
            return self._guard(
                lambda: self._run_flow(flow_file, options, inputs, datatable, timeout_seconds)
            )

    def run_steps(
        self,
        steps: list[str],
        options: list[str] | None = None,
        inputs: list[str] | None = None,
        datatable: str | None = None,
        timeout_seconds: float = 120,
    ) -> dict[str, object]:
        with self._lock:
            return self._guard(
                lambda: self._run_steps(steps, options, inputs, datatable, timeout_seconds)
            )

    def browser_start(
        self, mode: str = "headless", startup_timeout: float = 90, engine: str = ""
    ) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: self._browser_start(mode, startup_timeout, engine))

    def browser_stop(self) -> dict[str, object]:
        with self._lock:
            self.session.stop()
            return {"ok": True, "browser_running": False}

    def browser_go(self, url: str, timeout_seconds: float = 20) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: self._browser_go(url, timeout_seconds))

    def browser_click(self, selector: str, timeout_seconds: float = 45) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: self._step_result(f"click {self._selector(selector)}", timeout_seconds))

    def browser_type(
        self, selector: str, text: str, timeout_seconds: float = 45
    ) -> dict[str, object]:
        with self._lock:
            return self._guard(
                lambda: self._step_result(
                    f"type {self._selector(selector)} as {self._text(text)}",
                    timeout_seconds,
                )
            )

    def browser_read(self, target: str = "page", timeout_seconds: float = 45) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: self._browser_read(target, timeout_seconds))

    def page_state(self, timeout_seconds: float = 45) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: self._page_state(timeout_seconds))

    def browser_step(self, step: str, timeout_seconds: float = 45) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: self._browser_step(step, timeout_seconds))

    def list_flows(self) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: list_flows(self._workspace()))

    def read_flow(self, name: str) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: read_flow(self._workspace(), name))

    def create_flow(self, name: str, content: str) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: create_flow(self._workspace(), name, content))

    def save_flow(self, name: str, content: str) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: save_flow(self._workspace(), name, content))

    def edit_flow(self, name: str, op: str, line: int = 0, text: str = "") -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: edit_flow(self._workspace(), name, op, line, text))

    def validate_flow(self, name: str) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: validate_flow(self._workspace(), name, self.tagui_bin))

    def run_workspace_flow(
        self,
        name: str,
        options: list[str] | None = None,
        inputs: list[str] | None = None,
        datatable: str | None = None,
        timeout_seconds: float = 120,
    ) -> dict[str, object]:
        with self._lock:
            return self._guard(
                lambda: self._run_workspace_flow(name, options, inputs, datatable, timeout_seconds)
            )

    def save_session(self, name: str, only_successful: bool = False) -> dict[str, object]:
        with self._lock:
            return self._guard(lambda: self._save_session(name, only_successful))

    def _run_flow(
        self,
        flow_file: str,
        options: list[str] | None,
        inputs: list[str] | None,
        datatable: str | None,
        timeout_seconds: float,
    ) -> dict[str, object]:
        self._ensure_idle()
        return run_flow(
            flow_file,
            options=options,
            inputs=inputs,
            datatable=datatable,
            timeout_seconds=timeout_seconds,
            tagui_bin=self.tagui_bin,
        )

    def _run_steps(
        self,
        steps: list[str],
        options: list[str] | None,
        inputs: list[str] | None,
        datatable: str | None,
        timeout_seconds: float,
    ) -> dict[str, object]:
        self._ensure_idle()
        return run_steps(
            steps,
            options=options,
            inputs=inputs,
            datatable=datatable,
            timeout_seconds=timeout_seconds,
            tagui_bin=self.tagui_bin,
        )

    def _browser_start(self, mode: str, startup_timeout: float, engine: str = "") -> dict[str, object]:
        if self.session.running:
            raise TaguiFailure("browser session already running; call browser_stop first")
        selected = parse_mode(mode)
        chosen = normalize_engine(engine)
        self.session.start(selected, startup_timeout=startup_timeout, engine=chosen)
        workdir = self.session.workdir
        return {
            "ok": True,
            "mode": selected,
            "engine": chosen,
            "browser_running": True,
            "work_directory": str(workdir) if workdir is not None else None,
        }

    def _browser_go(self, url: str, timeout_seconds: float) -> dict[str, object]:
        self._ensure_running()
        if not isinstance(url, str) or _URL.match(url.strip()) is None:
            raise TaguiFailure("url must start with http:// or https:// and contain no spaces")
        target = url.strip()
        output = self.session.run_step(target, timeout=timeout_seconds)
        if "ERROR -" in output:
            return {"ok": False, "error": output.strip(), "output": output}
        deadline = time.monotonic() + timeout_seconds
        seen = ""
        while time.monotonic() < deadline:
            seen = self._dump("url()")
            if seen.startswith("http://") or seen.startswith("https://"):
                return {"ok": True, "url": seen, "output": output}
            time.sleep(0.3)
        return {
            "ok": False,
            "error": "navigation did not leave about:blank before the timeout",
            "url": seen,
            "output": output,
        }

    def _browser_read(self, target: str, timeout_seconds: float) -> dict[str, object]:
        self._ensure_running()
        if target.strip().lower() == "page":
            text = self._dump("text()")
            truncated = len(text) > _PAGE_TEXT_LIMIT
            if truncated:
                text = text[:_PAGE_TEXT_LIMIT]
            return {"ok": True, "target": "page", "text": text, "truncated": truncated}
        selector = self._selector(target)
        output = self.session.run_step(f"show {selector}", timeout=timeout_seconds)
        ok = "ERROR -" not in output
        result: dict[str, object] = {"ok": ok, "target": selector, "text": output}
        if not ok:
            result["error"] = output.strip()
        return result

    def _page_state(self, timeout_seconds: float) -> dict[str, object]:
        self._ensure_running()
        url = self._dump("url()", timeout_seconds)
        title = self._dump("title()", timeout_seconds)
        text = self._dump("text()", timeout_seconds)
        truncated = len(text) > _PAGE_TEXT_LIMIT
        if truncated:
            text = text[:_PAGE_TEXT_LIMIT]
        return {
            "ok": True,
            "mode": self.session.mode,
            "url": url,
            "title": title,
            "text": text,
            "truncated": truncated,
        }

    def _browser_step(self, step: str, timeout_seconds: float) -> dict[str, object]:
        self._ensure_running()
        checked = check_live_step(step)
        lines = checked.split("\n")
        if any(line_needs_flow_parser(line) for line in lines):
            return self._replay_as_flow(lines, timeout_seconds)
        return self._step_result(checked, timeout_seconds)

    def _replay_as_flow(self, lines: list[str], timeout_seconds: float) -> dict[str, object]:
        """Run the session transcript plus this block through the real flow parser."""
        self.session.transcript.extend(lines)
        self.session.outcomes.extend([True] * len(lines))
        steps = list(self.session.transcript)
        mode = self.session.mode or "headless"
        self.session.stop()
        result = run_steps(
            steps,
            options=[mode, "quiet"],
            timeout_seconds=max(timeout_seconds, 90),
            tagui_bin=self.tagui_bin,
        )
        result["executed_as"] = "flow"
        result["browser_running"] = False
        result["transcript"] = steps
        if not result.get("ok"):
            self.session.mark_last(len(lines), False)
        hint = (
            "Live mode cannot compile check, frame, popup, if, for, while, or { } blocks. "
            "The transcript was run as a flow file. Save it with save_session and keep editing that file."
        )
        if result.get("hint"):
            result["hint"] = hint + " " + str(result["hint"])
        else:
            result["hint"] = hint
        return result

    def _step_result(self, step: str, timeout_seconds: float) -> dict[str, object]:
        self._ensure_running()
        before = len(self.session.transcript)
        output = self.session.run_step(step, timeout=timeout_seconds)
        ok = "ERROR -" not in output
        if not ok:
            self.session.mark_last(len(self.session.transcript) - before, False)
        result: dict[str, object] = {
            "ok": ok,
            "output": output,
            "executed_as": "live",
            "transcript": list(self.session.transcript),
        }
        if not ok:
            result["error"] = output.strip()
            result.update(self._failure_context())
        return result

    def _failure_context(self) -> dict[str, object]:
        """Page state and a screenshot after a live step reports ERROR."""
        context: dict[str, object] = {}
        try:
            context["url"] = self._dump("url()")
            context["title"] = self._dump("title()")
            text = self._dump("text()")
            context["text"] = text[:_PAGE_TEXT_LIMIT]
            context["truncated"] = len(text) > _PAGE_TEXT_LIMIT
        except TaguiFailure as exc:
            context["page_state_error"] = str(exc)
            return context
        workdir = self.session.workdir
        if workdir is None:
            return context
        dest = workdir / "failure.png"
        try:
            self.session.run_step(f"snap page to {dest}", timeout=20, record=False)
        except TaguiFailure:
            return context
        if dest.is_file():
            context["screenshot"] = str(dest)
        return context

    def _run_workspace_flow(
        self,
        name: str,
        options: list[str] | None,
        inputs: list[str] | None,
        datatable: str | None,
        timeout_seconds: float,
    ) -> dict[str, object]:
        info = read_flow(self._workspace(), name)
        result = self._run_flow(
            str(info["path"]),
            options,
            inputs,
            datatable,
            timeout_seconds,
        )
        result["name"] = info["name"]
        return result

    def _save_session(self, name: str, only_successful: bool) -> dict[str, object]:
        steps = list(self.session.transcript)
        if only_successful:
            steps = [
                step
                for step, succeeded in zip(self.session.transcript, self.session.outcomes)
                if succeeded
            ]
        if not steps:
            raise TaguiFailure("the live session has no steps to save. Run a browser step first.")
        content = "\n".join(steps) + "\n"
        saved = save_flow(self._workspace(), name, content)
        saved["source"] = "live_session"
        return saved

    def _workspace(self) -> Path:
        return workspace_dir(self.workspace)

    def _dump(self, expression: str, timeout_seconds: float = 45) -> str:
        workdir = self.session.workdir
        if workdir is None:
            raise TaguiFailure("browser session is not running")
        dest = workdir / f"mcp_{expression.split('(')[0]}.txt"
        return self.session.dump(expression, dest, timeout=timeout_seconds)

    def _ensure_idle(self) -> None:
        if self.session.running:
            raise TaguiFailure(
                "a browser session is active; call browser_stop before running a flow"
            )

    def _ensure_running(self) -> None:
        if not self.session.running:
            raise TaguiFailure("browser session is not running; call browser_start first")

    @staticmethod
    def _guard(action: Callable[[], dict[str, object]]) -> dict[str, object]:
        try:
            return action()
        except TaguiFailure as exc:
            payload: dict[str, object] = {"ok": False, "error": str(exc)}
            payload.update(exc.details)
            return payload

    @staticmethod
    def _selector(selector: str) -> str:
        if not isinstance(selector, str):
            raise TaguiFailure("selector must be a string")
        value = selector.strip()
        if value == "":
            raise TaguiFailure("selector is empty")
        if _UNSAFE_SELECTOR.search(value):
            raise TaguiFailure(
                "selector contains a newline or a backtick. "
                "Use browser_step and put each TagUI line on its own line."
            )
        if " as " in value or " to " in value:
            raise TaguiFailure("selector cannot contain ' as ' or ' to '")
        if len(value) > 1000:
            raise TaguiFailure("selector exceeds 1000 characters")
        return value

    @staticmethod
    def _text(text: str) -> str:
        if not isinstance(text, str) or text == "":
            raise TaguiFailure("text is empty")
        if _UNSAFE_TEXT.search(text):
            raise TaguiFailure(
                "text cannot contain a newline or a backtick. "
                "A newline is a separate TagUI step; [enter] presses enter."
            )
        if len(text) > 2000:
            raise TaguiFailure("text exceeds 2000 characters")
        return text


def parse_mode(mode: str) -> BrowserMode:
    if mode == "headless":
        return "headless"
    if mode == "chrome":
        return "chrome"
    raise TaguiFailure("mode must be 'headless' or 'chrome'")


_SERVICE: TaguiService | None = None
_SERVICE_LOCK = threading.Lock()


def get_service() -> TaguiService:
    global _SERVICE
    with _SERVICE_LOCK:
        if _SERVICE is None:
            _SERVICE = TaguiService()
        return _SERVICE


def reset_service() -> None:
    """Stop the process-wide session. Used by tests and process shutdown."""
    global _SERVICE
    with _SERVICE_LOCK:
        if _SERVICE is not None:
            _SERVICE.close()
        _SERVICE = None
