"""Workspace of reusable TagUI flow files.

Agents create a flow here, validate it with the PHP parser, run it, and edit
individual lines. Names stay inside the workspace directory.
"""

from __future__ import annotations

import os
import re
import subprocess
from pathlib import Path

from tagui_mcp.runner import TaguiFailure, resolve_tagui_executable

_NAME = re.compile(r"[A-Za-z0-9][A-Za-z0-9._-]{0,80}\Z")
_MAX_CHARS = 200_000
_MAX_LINES = 2_000


def workspace_dir(explicit: str | Path | None = None) -> Path:
    if explicit:
        root = Path(explicit).expanduser().resolve()
    else:
        env = os.environ.get("TAGUI_WORKSPACE")
        if env:
            root = Path(env).expanduser().resolve()
        else:
            root = Path(__file__).resolve().parents[1] / "workspace"
    root.mkdir(parents=True, exist_ok=True)
    return root


def flow_file(root: Path, name: str) -> Path:
    if not isinstance(name, str) or name.strip() == "" or name != name.strip():
        raise TaguiFailure("flow name must be a file name without surrounding spaces")
    if "/" in name or "\\" in name or name.startswith("."):
        raise TaguiFailure("flow name must be a single file name inside the workspace")
    if _NAME.fullmatch(name) is None:
        raise TaguiFailure(
            "flow name must start with a letter or digit and contain only letters, digits, dots, underscores, and hyphens"
        )
    filename = name if name.endswith(".tagui") else name + ".tagui"
    path = (root / filename).resolve()
    if path.parent != root.resolve():
        raise TaguiFailure("flow name escapes the workspace")
    return path


def _check_content(content: str) -> str:
    if not isinstance(content, str):
        raise TaguiFailure("flow content must be a string")
    if "\x00" in content:
        raise TaguiFailure("flow content cannot contain NUL characters")
    if len(content) > _MAX_CHARS:
        raise TaguiFailure(f"flow content exceeds {_MAX_CHARS} characters")
    if content.count("\n") > _MAX_LINES:
        raise TaguiFailure(f"flow content exceeds {_MAX_LINES} lines")
    if content == "" or content.endswith("\n"):
        return content
    return content + "\n"


def numbered(content: str) -> list[dict[str, object]]:
    rows = content.splitlines()
    return [{"line": index, "text": text} for index, text in enumerate(rows, start=1)]


def list_flows(root: Path) -> dict[str, object]:
    files = sorted(path for path in root.glob("*.tagui") if path.is_file())
    return {
        "ok": True,
        "workspace": str(root),
        "flows": [{"name": path.name, "path": str(path)} for path in files],
    }


def read_flow(root: Path, name: str) -> dict[str, object]:
    path = flow_file(root, name)
    if not path.is_file():
        raise TaguiFailure(f"cannot find flow {path.name} in the workspace")
    content = path.read_text(encoding="utf-8", errors="replace")
    return {
        "ok": True,
        "name": path.name,
        "path": str(path),
        "content": content,
        "lines": numbered(content),
    }


def create_flow(root: Path, name: str, content: str) -> dict[str, object]:
    path = flow_file(root, name)
    if path.exists():
        raise TaguiFailure(f"{path.name} already exists. Use save_flow to replace it or edit_flow to change lines.")
    path.write_text(_check_content(content), encoding="utf-8")
    return read_flow(root, path.name)


def save_flow(root: Path, name: str, content: str) -> dict[str, object]:
    path = flow_file(root, name)
    path.write_text(_check_content(content), encoding="utf-8")
    return read_flow(root, path.name)


def edit_flow(root: Path, name: str, op: str, line: int, text: str) -> dict[str, object]:
    path = flow_file(root, name)
    if not path.is_file():
        raise TaguiFailure(f"cannot find flow {path.name} in the workspace")
    rows = path.read_text(encoding="utf-8", errors="replace").splitlines()
    updated = _apply_edit(rows, op, line, text)
    body = "\n".join(updated)
    if body:
        body += "\n"
    path.write_text(_check_content(body), encoding="utf-8")
    return read_flow(root, path.name)


def _apply_edit(rows: list[str], op: str, line: int, text: str) -> list[str]:
    if op not in {"replace", "insert_before", "delete", "append"}:
        raise TaguiFailure("op must be replace, insert_before, delete, or append")
    incoming = [] if op == "delete" else _incoming_lines(text)
    if op == "append":
        return rows + incoming
    if not isinstance(line, int) or isinstance(line, bool) or line < 1:
        raise TaguiFailure("line must be a 1-based line number")
    if op == "insert_before":
        if line > len(rows) + 1:
            raise TaguiFailure(f"line {line} is past the end of the flow ({len(rows)} lines)")
        index = line - 1
        return rows[:index] + incoming + rows[index:]
    if line > len(rows):
        raise TaguiFailure(f"line {line} is past the end of the flow ({len(rows)} lines)")
    index = line - 1
    if op == "delete":
        return rows[:index] + rows[index + 1 :]
    return rows[:index] + incoming + rows[index + 1 :]


def _incoming_lines(text: str) -> list[str]:
    if not isinstance(text, str):
        raise TaguiFailure("text must be a string")
    if "\x00" in text:
        raise TaguiFailure("text cannot contain NUL characters")
    if text == "":
        raise TaguiFailure("text is empty. Use op delete to remove a line.")
    return text.splitlines() or [""]


def tagui_source_dir(tagui_bin: str | Path | None) -> Path:
    try:
        executable = resolve_tagui_executable(tagui_bin)
    except TaguiFailure:
        executable = None
    if executable is not None and (executable.parent / "tagui_parse.php").is_file():
        return executable.parent
    bundled = Path(__file__).resolve().parents[2]
    if (bundled / "tagui_parse.php").is_file():
        return bundled
    raise TaguiFailure("tagui_parse.php was not found. Set TAGUI_SRC to the TagUI src directory.")


def validate_flow(root: Path, name: str, tagui_bin: str | Path | None = None) -> dict[str, object]:
    """Parse a workspace flow with tagui_parse.php and do not launch a browser."""
    path = flow_file(root, name)
    if not path.is_file():
        raise TaguiFailure(f"cannot find flow {path.name} in the workspace")
    source = tagui_source_dir(tagui_bin)
    php = os.environ.get("TAGUI_PHP", "php")
    env = os.environ.copy()
    env.pop("custom_csv_file", None)
    try:
        completed = subprocess.run(
            [php, "-q", str(source / "tagui_parse.php"), str(path)],
            cwd=str(source),
            capture_output=True,
            text=True,
            timeout=30,
            env=env,
            check=False,
        )
    except OSError as exc:
        raise TaguiFailure(f"could not start the TagUI parser ({php}): {exc}") from exc
    except subprocess.TimeoutExpired as exc:
        raise TaguiFailure("TagUI parser exceeded 30s") from exc
    parser_output = ((completed.stdout or "") + ("\n" + completed.stderr if completed.stderr else "")).strip()
    problems = [
        line.strip()
        for line in parser_output.splitlines()
        if "ERROR -" in line or "Fatal error" in line or "TypeError" in line
    ]
    generated = [path.with_name(path.name + suffix) for suffix in (".js", ".raw")]
    for artifact in generated:
        if artifact.is_file():
            try:
                artifact.unlink()
            except OSError:
                pass
    ok = completed.returncode == 0 and not problems
    result: dict[str, object] = {
        "ok": ok,
        "name": path.name,
        "path": str(path),
        "parser_output": parser_output,
        "error_lines": problems[:50],
    }
    if not ok:
        result["error"] = problems[0] if problems else f"parser exited with code {completed.returncode}"
        result["hint"] = _parser_hint(result["error"])
    return result


def _parser_hint(error: object) -> str | None:
    text = str(error)
    if "number of step {" in text:
        return "Nested if, for, while, frame, and popup need { and } on their own lines."
    if "put { to next line" in text:
        return "Put the opening { on the line after if, for, frame, or popup."
    if "inconsistent quotes" in text:
        return "A step can use single quotes or double quotes, not both."
    if "TypeError" in text and "count()" in text:
        return "The datatable or tagui_local.csv has a row the parser could not read."
    if "empty row" in text:
        return "Remove the blank row from the datatable or tagui_local.csv."
    return None
