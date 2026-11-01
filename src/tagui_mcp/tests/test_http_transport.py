"""Bearer-token protection for the Streamable HTTP transport."""

from __future__ import annotations

import asyncio
import json
import os
import socket
import subprocess
import sys
import time
import urllib.error
import urllib.request
from pathlib import Path

import pytest

from tagui_mcp.http_transport import (
    ALLOWED_HOSTS_ENV,
    ALLOWED_ORIGINS_ENV,
    BearerTokenMiddleware,
    TokenError,
    _build_transport_security,
    is_loopback,
    load_token,
)

ROOT = Path(__file__).resolve().parents[1]
FAKE = Path(__file__).resolve().parent / "fake_tagui.py"
TOKEN = "t" * 40


def _call(headers: list[tuple[bytes, bytes]]) -> tuple[int, bool]:
    reached = {"inner": False}
    sent: list[dict] = []

    async def inner(scope, receive, send):
        reached["inner"] = True
        await send({"type": "http.response.start", "status": 200, "headers": []})
        await send({"type": "http.response.body", "body": b"ok"})

    wrapped = BearerTokenMiddleware(inner, TOKEN)

    async def receive():
        return {"type": "http.request", "body": b""}

    async def send(message):
        sent.append(message)

    asyncio.run(wrapped({"type": "http", "headers": headers}, receive, send))
    return sent[0]["status"], reached["inner"]


def test_missing_wrong_and_malformed_credentials_are_rejected() -> None:
    for headers in (
        [],
        [(b"authorization", b"Bearer " + b"x" * 40)],
        [(b"authorization", b"Bearer")],
        [(b"authorization", b"Basic " + TOKEN.encode())],
        [(b"authorization", TOKEN.encode())],
        [(b"x-api-key", TOKEN.encode())],
    ):
        status, reached = _call(headers)
        assert status == 401
        assert reached is False


def test_matching_token_reaches_the_app() -> None:
    status, reached = _call([(b"authorization", b"Bearer " + TOKEN.encode())])
    assert (status, reached) == (200, True)
    status, reached = _call([(b"authorization", b"bearer " + TOKEN.encode())])
    assert (status, reached) == (200, True)


def test_unauthorized_response_advertises_bearer_and_hides_the_token() -> None:
    sent: list[dict] = []

    async def inner(scope, receive, send):
        raise AssertionError("must not be reached")

    async def receive():
        return {"type": "http.request", "body": b""}

    async def send(message):
        sent.append(message)

    asyncio.run(BearerTokenMiddleware(inner, TOKEN)({"type": "http", "headers": []}, receive, send))
    headers = dict(sent[0]["headers"])
    assert headers[b"www-authenticate"].startswith(b"Bearer")
    assert TOKEN.encode() not in b"".join(
        [sent[1]["body"], *headers.keys(), *headers.values()]
    )


def test_lifespan_is_not_gated() -> None:
    seen: list[str] = []

    async def inner(scope, receive, send):
        seen.append(scope["type"])

    async def receive():
        return {}

    async def send(message):
        return None

    asyncio.run(BearerTokenMiddleware(inner, TOKEN)({"type": "lifespan"}, receive, send))
    assert seen == ["lifespan"]


def test_empty_token_cannot_build_the_middleware() -> None:
    async def inner(scope, receive, send):
        return None

    with pytest.raises(TokenError):
        BearerTokenMiddleware(inner, "")


def test_token_is_required_and_long_enough() -> None:
    with pytest.raises(TokenError, match="needs"):
        load_token({})
    with pytest.raises(TokenError, match="at least"):
        load_token({"TAGUI_MCP_TOKEN": "short"})
    assert load_token({"TAGUI_MCP_TOKEN": f"  {TOKEN}\n"}) == TOKEN


def test_token_file_must_be_private(tmp_path: Path) -> None:
    path = tmp_path / "token"
    path.write_text(TOKEN + "\n", encoding="utf-8")
    path.chmod(0o644)
    with pytest.raises(TokenError, match="chmod 600"):
        load_token({"TAGUI_MCP_TOKEN_FILE": str(path)})
    path.chmod(0o600)
    assert load_token({"TAGUI_MCP_TOKEN_FILE": str(path)}) == TOKEN
    with pytest.raises(TokenError, match="only one"):
        load_token({"TAGUI_MCP_TOKEN_FILE": str(path), "TAGUI_MCP_TOKEN": TOKEN})
    with pytest.raises(TokenError, match="cannot read"):
        load_token({"TAGUI_MCP_TOKEN_FILE": str(tmp_path / "missing")})


def test_loopback_detection() -> None:
    assert is_loopback("127.0.0.1") and is_loopback("::1") and is_loopback("localhost")
    assert not is_loopback("0.0.0.0") and not is_loopback("10.0.0.5") and not is_loopback("example.com")


def _env(tmp_path: Path, **extra: str) -> dict[str, str]:
    env = {
        key: value
        for key, value in os.environ.items()
        if not key.startswith("TAGUI_MCP_")
    }
    env.update(
        PYTHONPATH=str(ROOT),
        TAGUI_BIN=str(FAKE),
        TAGUI_WORKSPACE=str(tmp_path / "flows"),
    )
    env.update(extra)
    return env


def _run_cli(tmp_path: Path, *args: str, **env: str) -> subprocess.CompletedProcess[str]:
    return subprocess.run(
        [sys.executable, "-m", "tagui_mcp", *args],
        env=_env(tmp_path, **env),
        capture_output=True,
        text=True,
        timeout=30,
        cwd=str(tmp_path),
        check=False,
    )


def test_http_server_refuses_to_start_without_a_token(tmp_path: Path) -> None:
    result = _run_cli(tmp_path, "--transport", "http", "--port", "0")
    assert result.returncode == 2
    assert "TAGUI_MCP_TOKEN" in result.stderr


def test_http_server_refuses_a_public_bind_without_the_flag(tmp_path: Path) -> None:
    result = _run_cli(
        tmp_path, "--transport", "http", "--host", "0.0.0.0", "--port", "0", TAGUI_MCP_TOKEN=TOKEN
    )
    assert result.returncode == 2
    assert "--allow-remote" in result.stderr
    assert TOKEN not in result.stdout + result.stderr


def _free_port() -> int:
    with socket.socket() as probe:
        probe.bind(("127.0.0.1", 0))
        return int(probe.getsockname()[1])


def _post(url: str, token: str | None, host_header: str | None = None) -> int:
    headers = {
        "content-type": "application/json",
        "accept": "application/json, text/event-stream",
    }
    if token is not None:
        headers["authorization"] = f"Bearer {token}"
    if host_header is not None:
        headers["host"] = host_header
    body = json.dumps(
        {
            "jsonrpc": "2.0",
            "id": 1,
            "method": "initialize",
            "params": {
                "protocolVersion": "2025-06-18",
                "capabilities": {},
                "clientInfo": {"name": "t", "version": "0"},
            },
        }
    ).encode()
    request = urllib.request.Request(url, data=body, headers=headers, method="POST")
    try:
        with urllib.request.urlopen(request, timeout=10) as response:
            return response.status
    except urllib.error.HTTPError as exc:
        return exc.code


def test_http_server_end_to_end(tmp_path: Path) -> None:
    from mcp.client.session import ClientSession
    from mcp.client.streamable_http import streamable_http_client

    import httpx2

    port = _free_port()
    process = subprocess.Popen(
        [sys.executable, "-m", "tagui_mcp", "--transport", "http", "--port", str(port)],
        env=_env(tmp_path, TAGUI_MCP_TOKEN=TOKEN),
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        text=True,
        cwd=str(tmp_path),
    )
    url = f"http://127.0.0.1:{port}/mcp"
    try:
        deadline = time.time() + 30
        while time.time() < deadline:
            try:
                socket.create_connection(("127.0.0.1", port), timeout=0.5).close()
                break
            except OSError:
                assert process.poll() is None, process.stderr.read()
                time.sleep(0.2)
        assert _post(url, None) == 401
        assert _post(url, "x" * 40) == 401
        assert _post(url, TOKEN) == 200

        async def run() -> set[str]:
            client = httpx2.AsyncClient(headers={"authorization": f"Bearer {TOKEN}"})
            async with client:
                async with streamable_http_client(url, http_client=client) as (read, write):
                    async with ClientSession(read, write) as session:
                        await session.initialize()
                        listed = await session.list_tools()
                        return {tool.name for tool in listed.tools}

        assert "tagui_status" in asyncio.run(run())
    finally:
        process.terminate()
        try:
            process.wait(timeout=15)
        except subprocess.TimeoutExpired:
            process.kill()
            process.wait()
    output = process.stdout.read() + process.stderr.read()
    assert TOKEN not in output


def test_build_transport_security_defaults_and_overrides(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.delenv(ALLOWED_HOSTS_ENV, raising=False)
    monkeypatch.delenv(ALLOWED_ORIGINS_ENV, raising=False)
    assert _build_transport_security("127.0.0.1") is None

    # Wildcard disables rebinding protection
    disabled = _build_transport_security("127.0.0.1", allowed_hosts=["*"])
    assert disabled is not None
    assert disabled.enable_dns_rebinding_protection is False

    # Specific hosts get wildcard ports and origins
    sec = _build_transport_security("127.0.0.1", allowed_hosts=["cloud.cidadejardim.com"])
    assert sec is not None
    assert sec.enable_dns_rebinding_protection is True
    assert "cloud.cidadejardim.com" in sec.allowed_hosts
    assert "cloud.cidadejardim.com:*" in sec.allowed_hosts
    assert "127.0.0.1:*" in sec.allowed_hosts
    assert "https://cloud.cidadejardim.com" in sec.allowed_origins
    assert "https://cloud.cidadejardim.com:*" in sec.allowed_origins

    # From env vars
    monkeypatch.setenv(ALLOWED_HOSTS_ENV, "myhost.local, other.local")
    monkeypatch.setenv(ALLOWED_ORIGINS_ENV, "https://myhost.local")
    from_env = _build_transport_security("127.0.0.1")
    assert from_env is not None
    assert "myhost.local" in from_env.allowed_hosts
    assert "other.local" in from_env.allowed_hosts
    assert from_env.allowed_origins == ["https://myhost.local"]


def test_http_server_allowed_hosts_and_dns_rebinding(tmp_path: Path) -> None:
    port = _free_port()
    process = subprocess.Popen(
        [
            sys.executable,
            "-m",
            "tagui_mcp",
            "--transport",
            "http",
            "--port",
            str(port),
            "--allowed-hosts",
            "cloud.cidadejardim.com",
        ],
        env=_env(tmp_path, TAGUI_MCP_TOKEN=TOKEN),
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        text=True,
        cwd=str(tmp_path),
    )
    url = f"http://127.0.0.1:{port}/mcp"
    try:
        deadline = time.time() + 30
        while time.time() < deadline:
            try:
                socket.create_connection(("127.0.0.1", port), timeout=0.5).close()
                break
            except OSError:
                assert process.poll() is None, process.stderr.read()
                time.sleep(0.2)
        # Disallowed host -> 421 Invalid Host header
        assert _post(url, TOKEN, host_header="evil.com") == 421
        # Allowed host -> 200
        assert _post(url, TOKEN, host_header="cloud.cidadejardim.com") == 200
        # Port-suffixed allowed host -> 200
        assert _post(url, TOKEN, host_header="cloud.cidadejardim.com:443") == 200
    finally:
        process.terminate()
        try:
            process.wait(timeout=15)
        except subprocess.TimeoutExpired:
            process.kill()
            process.wait()
