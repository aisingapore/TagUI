"""Run TagUI flow files through the existing command-line runner.

Options and a datatable still share the shell's forwarded arguments. Inputs
that contain whitespace, or that would overflow that list, are passed as
TAGUI_PARAM_n. tagui_header.js copies those into p1, p2, ... when the
matching CLI slot is empty.
"""

from __future__ import annotations

import os
import re
import shutil
import signal
import subprocess
import tempfile
import time
from pathlib import Path

ALLOWED_OPTIONS = frozenset(
    {
        "chrome",
        "headless",
        "firefox",
        "report",
        "upload",
        "speed",
        "quiet",
        "debug",
        "test",
        "baseline",
        "modern",
    }
)
BROWSER_OPTIONS = frozenset({"chrome", "headless", "firefox"})
ALLOWED_EXTENSIONS = frozenset({"", "txt", "js", "tagui", "tag", "gui"})
MAX_FORWARDED_ARGS = 8
MAX_INPUTS = 16
MAX_OUTPUT_CHARS = 100_000
MAX_STDERR_CHARS = 8_000
_LINE_NUMBER = re.compile(r"\[LINE\s+(\d+)\]")


class TaguiFailure(Exception):
    """Expected TagUI or usage failure that a tool should report to the caller."""

    def __init__(self, message: str, **details: object) -> None:
        super().__init__(message)
        self.details = details


def tagui_script_name() -> str:
    if os.name == "nt":
        return "tagui.cmd"
    return "tagui"


def resolve_tagui_executable(explicit: str | Path | None = None) -> Path:
    """Locate the tagui script shipped with this repo, or one named by the environment."""
    candidates: list[Path] = []
    if explicit:
        candidates.append(Path(explicit))
    env_bin = os.environ.get("TAGUI_BIN")
    if env_bin:
        candidates.append(Path(env_bin))
    env_src = os.environ.get("TAGUI_SRC")
    if env_src:
        candidates.append(Path(env_src) / tagui_script_name())
    candidates.append(Path(__file__).resolve().parents[2] / tagui_script_name())
    discovered = shutil.which("tagui")
    if discovered:
        candidates.append(Path(discovered))

    for candidate in candidates:
        if candidate.is_file():
            return candidate.resolve()
    raise TaguiFailure(
        "TagUI executable not found. Set TAGUI_SRC to the directory that contains "
        "the tagui script, or TAGUI_BIN to the script itself."
    )


def validate_options(options: list[str] | None) -> list[str]:
    """Return the CLI flags to pass through. None means quiet mode only."""
    if options is None:
        return ["quiet"]
    chosen: list[str] = []
    for option in options:
        if option not in ALLOWED_OPTIONS:
            allowed = ", ".join(sorted(ALLOWED_OPTIONS))
            raise TaguiFailure(f"unsupported option {option!r}. Allowed: {allowed}")
        if option in chosen:
            raise TaguiFailure(f"duplicate option {option!r}")
        chosen.append(option)
    browsers = [item for item in chosen if item in BROWSER_OPTIONS]
    if len(browsers) > 1:
        raise TaguiFailure("choose only one of chrome, headless, firefox")
    return chosen


def validate_inputs(inputs: list[str] | None) -> list[str]:
    if not inputs:
        return []
    if len(inputs) > MAX_INPUTS:
        raise TaguiFailure(f"at most {MAX_INPUTS} inputs are supported (p1-p{MAX_INPUTS})")
    cleaned: list[str] = []
    for value in inputs:
        if not isinstance(value, str) or value == "":
            raise TaguiFailure("inputs must be non-empty strings")
        if "\x00" in value or "\n" in value or "\r" in value:
            raise TaguiFailure("an input cannot contain newlines or NUL characters")
        if value in ALLOWED_OPTIONS:
            raise TaguiFailure(
                f"input {value!r} is a TagUI option name and would be consumed as a flag"
            )
        if value.lower().endswith(".csv"):
            raise TaguiFailure("inputs cannot end in .csv; pass a datatable with the datatable argument")
        cleaned.append(value)
    return cleaned


def inputs_use_environment(options: list[str], inputs: list[str], has_datatable: bool) -> bool:
    """True when inputs must travel as TAGUI_PARAM_n instead of shell words."""
    if not inputs:
        return False
    if any(any(character.isspace() for character in value) for value in inputs):
        return True
    if len(options) + len(inputs) + (1 if has_datatable else 0) > MAX_FORWARDED_ARGS:
        return True
    return False


def check_flow_file(flow_file: str) -> Path:
    if not flow_file or not flow_file.strip():
        raise TaguiFailure("flow_file is empty")
    path = Path(flow_file).expanduser().resolve()
    if not path.is_file():
        raise TaguiFailure(f"cannot find flow file: {path}")
    extension = path.suffix.lower().lstrip(".")
    if extension not in ALLOWED_EXTENSIONS:
        raise TaguiFailure(
            f"flow file extension .{extension} is not accepted by TagUI. "
            "Use no extension, .txt, .js, .tag, or .tagui"
        )
    return path


def check_datatable(datatable: str | None) -> Path | None:
    if datatable is None or datatable == "":
        return None
    path = Path(datatable).expanduser().resolve()
    if path.suffix.lower() != ".csv":
        raise TaguiFailure("datatable must be a .csv file")
    if not path.is_file():
        raise TaguiFailure(f"cannot find datatable: {path}")
    return path


def _truncate(text: str, limit: int) -> tuple[str, bool]:
    if len(text) <= limit:
        return text, False
    return text[:limit] + "\n...[truncated]", True


def _error_lines(*chunks: str) -> list[str]:
    found: list[str] = []
    seen: set[str] = set()
    for chunk in chunks:
        for line in chunk.splitlines():
            if "ERROR -" not in line:
                continue
            cleaned = line.strip()
            if cleaned in seen:
                continue
            seen.add(cleaned)
            found.append(cleaned)
            if len(found) == 50:
                return found
    return found


def _child_environment(parameters: list[str]) -> dict[str, str]:
    env = os.environ.copy()
    for key in list(env):
        if key.startswith("TAGUI_PARAM_"):
            del env[key]
    for index, value in enumerate(parameters, start=1):
        env[f"TAGUI_PARAM_{index}"] = value
    return env


def _hint(error: str) -> str | None:
    if "cannot find" in error:
        return (
            "TagUI tries xpath, then css, id, name, class, title, aria-label, visible text, and href. "
            "A bare HTML tag often does not match. screenshots lists images captured when the step failed."
        )
    if "visual step" in error or "OculiX" in error:
        return "screenshots lists the browser tab and desktop images captured when the step failed."
    if "number of step {" in error or "put { to next line" in error:
        return "Nested if, for, while, frame, and popup need { and } on their own lines."
    if "inconsistent quotes" in error:
        return "Use only one quote style in a step. echo run_result prints the output of a run step."
    if "command to run" in error:
        return "A run step stores output in run_result. Add echo run_result on the next line to see it."
    return None


def _screenshots(directory: Path, started: float) -> list[str]:
    if not directory.is_dir():
        return []
    found: list[str] = []
    for path in directory.iterdir():
        if path.suffix.lower() not in {".png", ".jpg", ".jpeg"}:
            continue
        try:
            if path.stat().st_mtime >= started - 0.25:
                found.append(str(path))
        except OSError:
            continue
    return sorted(found)


def diagnose_run(
    flow_path: Path,
    error_lines: list[str],
    log_text: str,
    screenshots: list[str],
) -> dict[str, object]:
    """Pull the failing line, the step text, and a repair hint out of a run."""
    failing_line: int | None = None
    error_message: str | None = error_lines[0] if error_lines else None
    for line in error_lines:
        match = _LINE_NUMBER.search(line)
        if match:
            failing_line = int(match.group(1))
            error_message = line
            break
    failing_step: str | None = None
    try:
        source = flow_path.read_text(encoding="utf-8", errors="replace").splitlines()
    except OSError:
        source = []
    if failing_line is None and error_message and "cannot find " in error_message:
        target = error_message.split("cannot find ", 1)[1].strip().strip(".")
        for index, line in enumerate(source, start=1):
            if target and target in line:
                failing_line = index
                break
    if failing_line is not None and 1 <= failing_line <= len(source):
        failing_step = source[failing_line - 1]
    log, log_truncated = _truncate(log_text, MAX_OUTPUT_CHARS)
    return {
        "failing_line": failing_line,
        "failing_step": failing_step,
        "error_message": error_message,
        "log": log,
        "log_truncated": log_truncated,
        "screenshots": screenshots,
        "hint": _hint(error_message or ""),
    }


def _runtime_problem(stderr: str) -> str | None:
    for needle in ("command not found", "No such file or directory", "cannot find Chrome"):
        if needle in stderr:
            return needle
    return None


def _kill_process_group(pid: int) -> None:
    try:
        os.killpg(pid, signal.SIGTERM)
    except (ProcessLookupError, PermissionError):
        return
    try:
        os.killpg(pid, signal.SIGKILL)
    except (ProcessLookupError, PermissionError):
        return


def _belongs_to_install(pid: int, command: str, install_dir: Path | None, use_cwd: bool) -> bool:
    """True when a process was started from this TagUI install, not another one.

    The launcher passes an absolute --user-data-dir under its own src directory.
    tagui_chrome.php is started with a relative name, so its working directory decides.
    """
    if install_dir is None:
        return False
    if f"{install_dir}{os.sep}" in command:
        return True
    if not use_cwd:
        return False
    try:
        return Path(os.readlink(f"/proc/{pid}/cwd")) == install_dir
    except OSError:
        return False


def reap_tagui_chrome(tagui_bin: str | Path | None = None) -> None:
    """Stop a Chrome left behind on TagUI's debugging port and profile.

    TagUI launches Chrome in the background, and Chrome often leaves the
    process group, so killing the runner does not always close the browser.
    The match is the same pair of markers src/end_processes uses, limited to
    processes that belong to the TagUI install this server runs. Another TagUI
    install under the same user, for example a scheduled official run that is
    using port 9222, is not touched.

    When TAGUI_CHROME_PROFILE is set, a Chrome started with exactly that
    --user-data-dir is also stopped. The modern engine opens it on a free port,
    and a leftover browser on a profile blocks the next launch.
    """
    if os.name != "posix":
        return
    try:
        install_dir: Path | None = resolve_tagui_executable(tagui_bin).parent
    except TaguiFailure:
        install_dir = None
    profile = os.environ.get("TAGUI_CHROME_PROFILE", "").strip()
    dedicated = re.compile(r"--user-data-dir=" + re.escape(profile) + r"(\s|$)") if profile else None
    try:
        listed = subprocess.run(
            ["ps", "x", "-o", "pid=,command="],
            capture_output=True,
            text=True,
            timeout=5,
            check=False,
        )
    except (OSError, subprocess.TimeoutExpired):
        return
    for line in listed.stdout.splitlines():
        chrome = "remote-debugging-port=9222" in line and "tagui_user_profile" in line
        helper = "tagui_chrome.php" in line
        modern = "tagui_modern_profile_" in line
        owned = dedicated is not None and dedicated.search(line) is not None
        if not chrome and not helper and not modern and not owned:
            continue
        pid_text = line.strip().split(maxsplit=1)[0]
        if not pid_text.isdigit():
            continue
        if not (modern or owned) and not _belongs_to_install(int(pid_text), line, install_dir, use_cwd=helper):
            continue
        try:
            os.kill(int(pid_text), signal.SIGTERM)
        except (ProcessLookupError, PermissionError):
            continue


def run_flow(
    flow_file: str,
    options: list[str] | None = None,
    inputs: list[str] | None = None,
    datatable: str | None = None,
    timeout_seconds: float = 120,
    tagui_bin: str | Path | None = None,
) -> dict[str, object]:
    """Execute one flow file. Returns a JSON-ready result and does not raise TaguiFailure."""
    try:
        return _run_flow(flow_file, options, inputs, datatable, timeout_seconds, tagui_bin)
    except TaguiFailure as exc:
        payload: dict[str, object] = {"ok": False, "error": str(exc)}
        payload.update(exc.details)
        return payload


def _run_flow(
    flow_file: str,
    options: list[str] | None,
    inputs: list[str] | None,
    datatable: str | None,
    timeout_seconds: float,
    tagui_bin: str | Path | None,
) -> dict[str, object]:
    if timeout_seconds <= 0:
        raise TaguiFailure("timeout_seconds must be greater than 0")
    flow_path = check_flow_file(flow_file)
    chosen = validate_options(options)
    parameters = validate_inputs(inputs)
    table_path = check_datatable(datatable)
    via_environment = inputs_use_environment(chosen, parameters, table_path is not None)
    flag_slots = len(chosen) + (1 if table_path is not None else 0)
    if flag_slots > MAX_FORWARDED_ARGS:
        raise TaguiFailure(
            "options and the datatable still share TagUI's 8 shell arguments. "
            "Drop a flag or the datatable."
        )
    if not via_environment and flag_slots + len(parameters) > MAX_FORWARDED_ARGS:
        via_environment = True
    executable = resolve_tagui_executable(tagui_bin)
    command = [str(executable), str(flow_path), *chosen]
    if table_path is not None:
        command.append(str(table_path))
    if not via_environment:
        command.extend(parameters)
    child_env = _child_environment(parameters if via_environment else [])

    started = time.time()
    try:
        process = subprocess.Popen(
            command,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            start_new_session=True,
            cwd=str(flow_path.parent),
            env=child_env,
        )
    except OSError as exc:
        raise TaguiFailure(f"could not start TagUI: {exc}") from exc

    timed_out = False
    try:
        stdout, stderr = process.communicate(timeout=timeout_seconds)
    except subprocess.TimeoutExpired:
        timed_out = True
        _kill_process_group(process.pid)
        reap_tagui_chrome(executable)
        stdout, stderr = process.communicate()
    exit_code = process.returncode
    output, output_truncated = _truncate(stdout or "", MAX_OUTPUT_CHARS)
    error_output, stderr_truncated = _truncate(stderr or "", MAX_STDERR_CHARS)
    log_path = Path(str(flow_path) + ".log")
    log_text = ""
    if log_path.is_file():
        try:
            log_text = log_path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            log_text = ""
    problems = _error_lines(output, log_text, error_output)
    runtime_problem = _runtime_problem(error_output)
    syntax_error = next(
        (
            line.strip()
            for line in (output + "\n" + log_text).splitlines()
            if "SyntaxError:" in line
        ),
        None,
    )
    ok = (
        exit_code == 0
        and not problems
        and runtime_problem is None
        and syntax_error is None
        and not timed_out
    )
    diagnosis = diagnose_run(
        flow_path,
        problems,
        log_text,
        _screenshots(flow_path.parent, started),
    )
    result: dict[str, object] = {
        "ok": ok,
        "exit_code": exit_code,
        "output": output,
        "stderr": error_output,
        "error_lines": problems,
        "log_file": str(log_path) if log_path.is_file() else None,
        "flow_file": str(flow_path),
        "command": command,
        "parameter_source": "environment" if via_environment else "cli",
        "truncated": output_truncated or stderr_truncated,
        "timed_out": timed_out,
    }
    result.update(diagnosis)
    if timed_out:
        result["error"] = f"TagUI run exceeded {timeout_seconds:g}s and was stopped"
    elif runtime_problem is not None:
        result["error"] = f"TagUI dependency problem ({runtime_problem}). See stderr."
    elif problems:
        result["error"] = problems[0]
    elif syntax_error is not None:
        result["error"] = syntax_error
        result["error_message"] = syntax_error
        result["hint"] = "The generated CasperJS script did not parse. This is a TagUI translator bug, not a flow step."
    elif exit_code not in (0, None):
        result["error"] = f"TagUI exited with code {exit_code}"
    return result


def write_steps_flow(directory: Path, steps: list[str]) -> Path:
    if not steps:
        raise TaguiFailure("steps is empty")
    if len(steps) > 500:
        raise TaguiFailure("steps is limited to 500 lines")
    body = "\n".join(steps)
    if len(body) > 200_000:
        raise TaguiFailure("steps exceed 200000 characters")
    if "\x00" in body:
        raise TaguiFailure("steps cannot contain NUL characters")
    directory.mkdir(parents=True, exist_ok=True)
    flow_path = directory / "mcp_steps"
    flow_path.write_text(body + "\n", encoding="utf-8")
    return flow_path


def run_steps(
    steps: list[str],
    options: list[str] | None = None,
    inputs: list[str] | None = None,
    datatable: str | None = None,
    timeout_seconds: float = 120,
    directory: Path | None = None,
    tagui_bin: str | Path | None = None,
) -> dict[str, object]:
    """Write steps to a temporary flow and run it. The directory is kept for output files."""
    try:
        if directory is None:
            directory = Path(tempfile.mkdtemp(prefix="tagui_mcp_flow_"))
        flow_path = write_steps_flow(directory, steps)
    except TaguiFailure as exc:
        return {"ok": False, "error": str(exc)}
    result = run_flow(
        str(flow_path),
        options=options,
        inputs=inputs,
        datatable=datatable,
        timeout_seconds=timeout_seconds,
        tagui_bin=tagui_bin,
    )
    result["work_directory"] = str(directory)
    return result
