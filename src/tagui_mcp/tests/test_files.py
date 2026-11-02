from __future__ import annotations

import base64
import hashlib
import os
from pathlib import Path

import pytest

from tagui_mcp.service import TaguiService

FAKE = Path(__file__).parent / "fake_tagui.py"


@pytest.fixture
def root(tmp_path: Path, monkeypatch: pytest.MonkeyPatch) -> Path:
    allowed = tmp_path / "allowed"
    allowed.mkdir()
    monkeypatch.setenv("TAGUI_MCP_FILE_ROOTS", str(allowed))
    monkeypatch.delenv("TAGUI_MCP_FILE_MAX_BYTES", raising=False)
    return allowed.resolve()


def _service() -> TaguiService:
    return TaguiService(tagui_bin=FAKE)


def _fake_pdf(path: Path, size: int = 100_000) -> bytes:
    body = b"%PDF-1.4\n" + os.urandom(size - 15) + b"\n%%EOF\n"
    path.write_bytes(body)
    return body


def test_round_trip_and_short_last_chunk(root: Path) -> None:
    original = _fake_pdf(root / "doc.pdf")
    service = _service()
    info = service.file_info(str(root / "doc.pdf"), chunk_size=4096)
    assert info["ok"] is True
    assert info["mime"] == "application/pdf"
    assert info["size"] == len(original)
    assert info["chunk_count"] == 25
    parts = []
    for index in range(int(info["chunk_count"])):
        chunk = service.read_file_chunk(str(root / "doc.pdf"), index, 4096)
        assert chunk["ok"] is True
        assert chunk["sha256"] == info["sha256"]
        raw = base64.b64decode(str(chunk["data"]))
        assert hashlib.sha256(raw).hexdigest() == chunk["chunk_sha256"]
        assert chunk["offset"] == index * 4096
        assert chunk["length"] == len(raw)
        assert chunk["is_last"] is (index == 24)
        parts.append(raw)
    assert len(parts[-1]) == len(original) - 24 * 4096
    assert len(parts[-1]) < 4096
    joined = b"".join(parts)
    assert joined == original
    assert hashlib.sha256(joined).hexdigest() == info["sha256"]


def test_unknown_extension_falls_back_to_octet_stream(root: Path) -> None:
    (root / "blob.zzunknown").write_bytes(b"x" * 10)
    info = _service().file_info(str(root / "blob.zzunknown"))
    assert info["mime"] == "application/octet-stream"
    assert info["chunk_count"] == 1


def test_refuses_path_outside_root(root: Path, tmp_path: Path) -> None:
    outside = tmp_path / "secret.txt"
    outside.write_text("secret")
    result = _service().file_info(str(outside))
    assert result["ok"] is False
    assert "outside" in str(result["error"])
    assert _service().read_file_chunk(str(outside))["ok"] is False


def test_refuses_symlink_that_escapes(root: Path, tmp_path: Path) -> None:
    outside = tmp_path / "secret.txt"
    outside.write_text("secret")
    (root / "link.txt").symlink_to(outside)
    service = _service()
    assert service.file_info(str(root / "link.txt"))["ok"] is False
    assert service.read_file_chunk(str(root / "link.txt"))["ok"] is False
    listed = service.list_files(str(root))
    assert listed["files"] == []


def test_refuses_dotdot(root: Path, tmp_path: Path) -> None:
    outside = tmp_path / "secret.txt"
    outside.write_text("secret")
    result = _service().file_info(str(root / ".." / "secret.txt"))
    assert result["ok"] is False
    assert ".." in str(result["error"])


def test_refuses_directory(root: Path) -> None:
    (root / "sub").mkdir()
    result = _service().file_info(str(root / "sub"))
    assert result["ok"] is False
    assert "regular file" in str(result["error"])


def test_refuses_file_above_limit(root: Path, monkeypatch: pytest.MonkeyPatch) -> None:
    (root / "big.bin").write_bytes(b"0" * 2048)
    monkeypatch.setenv("TAGUI_MCP_FILE_MAX_BYTES", "1000")
    service = _service()
    result = service.file_info(str(root / "big.bin"))
    assert result["ok"] is False
    assert "limit" in str(result["error"])
    assert service.read_file_chunk(str(root / "big.bin"))["ok"] is False


@pytest.mark.parametrize("size", [0, 1023, 1048577, -5])
def test_refuses_invalid_chunk_size(root: Path, size: int) -> None:
    (root / "a.bin").write_bytes(b"abc")
    service = _service()
    assert service.file_info(str(root / "a.bin"), chunk_size=size)["ok"] is False
    assert service.read_file_chunk(str(root / "a.bin"), 0, size)["ok"] is False


@pytest.mark.parametrize("index", [-1, 3, 100])
def test_refuses_invalid_index(root: Path, index: int) -> None:
    (root / "a.bin").write_bytes(b"x" * 2500)  # 3 chunks of 1024 -> indexes 0..2
    result = _service().read_file_chunk(str(root / "a.bin"), index, 1024)
    assert result["ok"] is False
    assert "out of range" in str(result["error"])


def test_empty_file_has_one_empty_chunk(root: Path) -> None:
    (root / "empty.txt").write_bytes(b"")
    chunk = _service().read_file_chunk(str(root / "empty.txt"))
    assert chunk["ok"] is True
    assert chunk["chunk_count"] == 1
    assert chunk["length"] == 0
    assert chunk["is_last"] is True


def test_list_files_sorted_newest_first(root: Path) -> None:
    for name, mtime in (("old.pdf", 1_000_000), ("new.pdf", 3_000_000), ("mid.txt", 2_000_000)):
        path = root / name
        path.write_bytes(b"data")
        os.utime(path, (mtime, mtime))
    (root / "sub").mkdir()
    service = _service()
    listed = service.list_files(str(root))
    assert [item["name"] for item in listed["files"]] == ["new.pdf", "mid.txt", "old.pdf"]
    first = listed["files"][0]
    assert set(first) == {"name", "path", "size", "modified", "mime"}
    assert first["modified"].startswith("1970-02-")
    assert [i["name"] for i in service.list_files(str(root), "*.pdf")["files"]] == [
        "new.pdf",
        "old.pdf",
    ]
    assert len(service.list_files(str(root), limit=1)["files"]) == 1


def test_list_files_without_directory_returns_roots(root: Path) -> None:
    listed = _service().list_files()
    assert listed == {"ok": True, "roots": [str(root)]}


def test_list_files_refuses_outside_directory(root: Path, tmp_path: Path) -> None:
    assert _service().list_files(str(tmp_path))["ok"] is False


def test_default_roots_are_workspace_and_downloads(
    tmp_path: Path, monkeypatch: pytest.MonkeyPatch
) -> None:
    monkeypatch.delenv("TAGUI_MCP_FILE_ROOTS", raising=False)
    monkeypatch.setenv("HOME", str(tmp_path / "home"))
    workspace = tmp_path / "ws"
    service = TaguiService(tagui_bin=FAKE, workspace=workspace)
    roots = service.list_files()["roots"]
    assert roots == [str(workspace.resolve()), str((tmp_path / "home" / "Downloads").resolve())]


@pytest.mark.parametrize("bad", ["/", "/etc", "/proc/self"])
def test_refuses_system_path_as_root(bad: str, monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setenv("TAGUI_MCP_FILE_ROOTS", bad)
    result = _service().file_info("/etc/hostname")
    assert result["ok"] is False
    assert "system path" in str(result["error"])
