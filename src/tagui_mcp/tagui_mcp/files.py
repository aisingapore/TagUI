"""Read-only file delivery for remote agents.

A remote agent cannot read the local disk, and a whole PDF does not fit in one
tool result. These helpers list files in allowed roots and hand one back as
base64 slices that the client joins in order and checks against the sha256 of
the whole file. Nothing here writes, deletes, or moves a file.
"""

from __future__ import annotations

import base64
import fnmatch
import hashlib
import mimetypes
import os
from datetime import datetime, timezone
from pathlib import Path

from tagui_mcp.flows import workspace_dir
from tagui_mcp.runner import TaguiFailure

DEFAULT_CHUNK_SIZE = 32768
MIN_CHUNK_SIZE = 1024
MAX_CHUNK_SIZE = 1048576
DEFAULT_MAX_BYTES = 50 * 1024 * 1024
MAX_LIST_LIMIT = 500
# A root may not be the filesystem root or one of these system directories, nor sit under one.
SYSTEM_DIRS = ("/etc", "/proc", "/sys", "/dev", "/boot", "/bin", "/sbin", "/lib", "/lib64")


def file_roots(workspace: str | Path | None = None) -> list[Path]:
    """Allowed roots: TAGUI_MCP_FILE_ROOTS, else the workspace and ~/Downloads."""
    env = os.environ.get("TAGUI_MCP_FILE_ROOTS")
    if env is not None and env.strip() != "":
        raw = [Path(part).expanduser() for part in env.split(os.pathsep) if part.strip()]
    else:
        raw = [workspace_dir(workspace), Path.home() / "Downloads"]
    roots: list[Path] = []
    for item in raw:
        resolved = item.resolve()
        if _system_path(resolved):
            raise TaguiFailure(f"refusing system path as a file root: {resolved}")
        if resolved not in roots:
            roots.append(resolved)
    return roots


def _system_path(path: Path) -> bool:
    if path == Path(path.anchor):
        return True
    windir = os.environ.get("SystemRoot") or os.environ.get("WINDIR")
    system = [Path(item) for item in SYSTEM_DIRS]
    if windir:
        system.append(Path(windir))
    return any(path == item or item in path.parents for item in system)


def max_file_bytes() -> int:
    env = os.environ.get("TAGUI_MCP_FILE_MAX_BYTES")
    if env is None or env.strip() == "":
        return DEFAULT_MAX_BYTES
    try:
        value = int(env)
    except ValueError:
        raise TaguiFailure("TAGUI_MCP_FILE_MAX_BYTES must be an integer") from None
    if value < 1:
        raise TaguiFailure("TAGUI_MCP_FILE_MAX_BYTES must be positive")
    return value


def _inside(path: Path, roots: list[Path]) -> bool:
    return any(path == root or root in path.parents for root in roots)


def _resolve(raw: str, roots: list[Path]) -> Path:
    if not isinstance(raw, str) or raw.strip() == "":
        raise TaguiFailure("path is empty")
    if "\x00" in raw:
        raise TaguiFailure("path contains a null byte")
    candidate = Path(raw).expanduser()
    if ".." in candidate.parts:
        raise TaguiFailure("path must not contain '..'")
    resolved = candidate.resolve()
    if not candidate.is_absolute():
        # A relative path is looked up under each allowed root in order.
        for root in roots:
            attempt = (root / candidate).resolve()
            if attempt.exists():
                resolved = attempt
                break
    if not _inside(resolved, roots):
        raise TaguiFailure(
            "path is outside the allowed roots: " + ", ".join(str(root) for root in roots)
        )
    return resolved


def _regular_file(raw: str, roots: list[Path]) -> tuple[Path, os.stat_result]:
    path = _resolve(raw, roots)
    if not path.exists():
        raise TaguiFailure(f"file not found: {path}")
    if not path.is_file():
        raise TaguiFailure(f"not a regular file: {path}")
    stat = path.stat()
    limit = max_file_bytes()
    if stat.st_size > limit:
        raise TaguiFailure(f"file is {stat.st_size} bytes, above the {limit} byte limit")
    return path, stat


def _chunk_size(value: int) -> int:
    if isinstance(value, bool) or not isinstance(value, int):
        raise TaguiFailure("chunk_size must be an integer")
    if value < MIN_CHUNK_SIZE or value > MAX_CHUNK_SIZE:
        raise TaguiFailure(f"chunk_size must be between {MIN_CHUNK_SIZE} and {MAX_CHUNK_SIZE}")
    return value


def _chunk_count(size: int, chunk_size: int) -> int:
    return max(1, -(-size // chunk_size))


def _iso(timestamp: float) -> str:
    return datetime.fromtimestamp(timestamp, tz=timezone.utc).isoformat()


def _mime(path: Path) -> str:
    return mimetypes.guess_type(path.name)[0] or "application/octet-stream"


def _sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for block in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(block)
    return digest.hexdigest()


def list_files(
    roots: list[Path], directory: str = "", pattern: str = "*", limit: int = 50
) -> dict[str, object]:
    if isinstance(limit, bool) or not isinstance(limit, int) or not 1 <= limit <= MAX_LIST_LIMIT:
        raise TaguiFailure(f"limit must be an integer between 1 and {MAX_LIST_LIMIT}")
    if not isinstance(pattern, str) or pattern == "":
        raise TaguiFailure("pattern is empty")
    if directory.strip() == "":
        return {"ok": True, "roots": [str(root) for root in roots]}
    folder = _resolve(directory, roots)
    if not folder.is_dir():
        raise TaguiFailure(f"not a directory: {folder}")
    entries: list[tuple[float, dict[str, object]]] = []
    for child in folder.iterdir():
        if not fnmatch.fnmatch(child.name, pattern):
            continue
        try:
            real = child.resolve()
            if not _inside(real, roots) or not real.is_file():
                continue
            stat = real.stat()
        except OSError:
            continue
        entries.append(
            (
                stat.st_mtime,
                {
                    "name": child.name,
                    "path": str(real),
                    "size": stat.st_size,
                    "modified": _iso(stat.st_mtime),
                    "mime": _mime(real),
                },
            )
        )
    entries.sort(key=lambda item: (item[0], str(item[1]["name"])), reverse=True)
    files = [item[1] for item in entries[:limit]]
    return {
        "ok": True,
        "directory": str(folder),
        "count": len(files),
        "total": len(entries),
        "files": files,
    }


def file_info(
    roots: list[Path], path: str, chunk_size: int = DEFAULT_CHUNK_SIZE
) -> dict[str, object]:
    size_per_chunk = _chunk_size(chunk_size)
    resolved, stat = _regular_file(path, roots)
    return {
        "ok": True,
        "name": resolved.name,
        "path": str(resolved),
        "mime": _mime(resolved),
        "size": stat.st_size,
        "sha256": _sha256(resolved),
        "modified": _iso(stat.st_mtime),
        "chunk_size": size_per_chunk,
        "chunk_count": _chunk_count(stat.st_size, size_per_chunk),
    }


def read_file_chunk(
    roots: list[Path], path: str, index: int = 0, chunk_size: int = DEFAULT_CHUNK_SIZE
) -> dict[str, object]:
    size_per_chunk = _chunk_size(chunk_size)
    if isinstance(index, bool) or not isinstance(index, int):
        raise TaguiFailure("index must be an integer")
    resolved, _stat = _regular_file(path, roots)
    digest = hashlib.sha256()
    offset = max(index, 0) * size_per_chunk
    data = b""
    size = 0
    with resolved.open("rb") as handle:
        for block in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(block)
            start = offset - size
            if index >= 0 and start < len(block) and len(data) < size_per_chunk:
                data += block[max(start, 0) : max(start, 0) + size_per_chunk - len(data)]
            size += len(block)
    count = _chunk_count(size, size_per_chunk)
    if index < 0 or index >= count:
        raise TaguiFailure(f"index {index} is out of range; chunk_count is {count} (0-based)")
    return {
        "ok": True,
        "index": index,
        "chunk_count": count,
        "offset": offset,
        "length": len(data),
        "chunk_sha256": hashlib.sha256(data).hexdigest(),
        "size": size,
        "sha256": digest.hexdigest(),
        "is_last": index == count - 1,
        "data": base64.b64encode(data).decode("ascii"),
    }
