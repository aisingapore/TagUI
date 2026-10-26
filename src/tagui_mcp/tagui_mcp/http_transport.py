"""Streamable HTTP transport for the MCP server, behind a mandatory bearer token.

stdio stays the default transport. This module only runs when the server is started
with ``--transport http``. Every HTTP request must carry ``Authorization: Bearer
<token>``. The token comes from TAGUI_MCP_TOKEN or from the file named by
TAGUI_MCP_TOKEN_FILE, and the server refuses to start without one. The token is
never logged or echoed. The server binds to 127.0.0.1 unless --allow-remote is
given; the connection itself is plain HTTP, so put TLS in front of it before
reaching it across a network.
"""

from __future__ import annotations

import hmac
import ipaddress
import os
import signal
import stat
from collections.abc import Awaitable, Callable, MutableMapping
from pathlib import Path
from typing import Any

TOKEN_ENV = "TAGUI_MCP_TOKEN"
TOKEN_FILE_ENV = "TAGUI_MCP_TOKEN_FILE"
ALLOWED_HOSTS_ENV = "TAGUI_MCP_ALLOWED_HOSTS"
ALLOWED_ORIGINS_ENV = "TAGUI_MCP_ALLOWED_ORIGINS"
PATH_ENV = "TAGUI_MCP_PATH"
MIN_TOKEN_LENGTH = 32
DEFAULT_HOST = "127.0.0.1"
DEFAULT_PORT = 8765
DEFAULT_PATH = "/mcp"

Scope = MutableMapping[str, Any]
Receive = Callable[[], Awaitable[MutableMapping[str, Any]]]
Send = Callable[[MutableMapping[str, Any]], Awaitable[None]]

_UNAUTHORIZED_BODY = b'{"error":"unauthorized"}'


class TokenError(Exception):
    """The bearer token is missing, too short, or stored where others can read it."""


def load_token(env: MutableMapping[str, str] | None = None) -> str:
    """Return the bearer token from the environment or a mode-0600 file."""
    source = os.environ if env is None else env
    inline = source.get(TOKEN_ENV, "").strip()
    file_name = source.get(TOKEN_FILE_ENV, "").strip()
    if inline and file_name:
        raise TokenError(f"set only one of {TOKEN_ENV} and {TOKEN_FILE_ENV}")
    token = inline
    if file_name:
        path = Path(file_name).expanduser()
        try:
            info = path.stat()
        except OSError as exc:
            raise TokenError(f"cannot read {TOKEN_FILE_ENV} file {path}: {exc.strerror}") from exc
        if os.name == "posix" and info.st_mode & (stat.S_IRWXG | stat.S_IRWXO):
            raise TokenError(f"{path} must not be accessible by group or others (chmod 600)")
        try:
            token = path.read_text(encoding="utf-8").strip()
        except OSError as exc:
            raise TokenError(f"cannot read {TOKEN_FILE_ENV} file {path}: {exc.strerror}") from exc
    if not token:
        raise TokenError(f"HTTP transport needs {TOKEN_ENV} or {TOKEN_FILE_ENV}")
    if len(token) < MIN_TOKEN_LENGTH:
        raise TokenError(f"the token must be at least {MIN_TOKEN_LENGTH} characters")
    return token


def is_loopback(host: str) -> bool:
    if host == "localhost":
        return True
    try:
        return ipaddress.ip_address(host).is_loopback
    except ValueError:
        return False


class BearerTokenMiddleware:
    """ASGI wrapper that answers 401 unless the request carries the bearer token."""

    def __init__(self, app: Callable[[Scope, Receive, Send], Awaitable[None]], token: str) -> None:
        if not token:
            raise TokenError("an empty token would accept every request")
        self._app = app
        self._token = token.encode("utf-8")

    def _authorized(self, scope: Scope) -> bool:
        supplied = b""
        for name, value in scope.get("headers", []):
            if name == b"authorization":
                scheme, _, credentials = value.partition(b" ")
                if scheme.lower() == b"bearer":
                    supplied = credentials.strip()
                break
        return hmac.compare_digest(supplied, self._token)

    async def __call__(self, scope: Scope, receive: Receive, send: Send) -> None:
        kind = scope["type"]
        if kind == "lifespan":
            await self._app(scope, receive, send)
            return
        if kind == "http" and self._authorized(scope):
            await self._app(scope, receive, send)
            return
        if kind == "http":
            await send(
                {
                    "type": "http.response.start",
                    "status": 401,
                    "headers": [
                        (b"content-type", b"application/json"),
                        (b"content-length", str(len(_UNAUTHORIZED_BODY)).encode("ascii")),
                        (b"www-authenticate", b'Bearer realm="tagui-mcp"'),
                    ],
                }
            )
            await send({"type": "http.response.body", "body": _UNAUTHORIZED_BODY})
            return
        if kind == "websocket":
            await send({"type": "websocket.close", "code": 1008})


def _parse_list(val: str | None) -> list[str]:
    if not val:
        return []
    return [item.strip() for item in val.split(",") if item.strip()]


def _build_transport_security(
    host: str,
    allowed_hosts: list[str] | None = None,
    allowed_origins: list[str] | None = None,
) -> Any:
    from mcp.server.streamable_http import TransportSecuritySettings

    env_hosts = _parse_list(os.environ.get(ALLOWED_HOSTS_ENV))
    env_origins = _parse_list(os.environ.get(ALLOWED_ORIGINS_ENV))

    hosts = list(allowed_hosts) if allowed_hosts is not None else (env_hosts if env_hosts else None)
    origins = list(allowed_origins) if allowed_origins is not None else (env_origins if env_origins else None)

    if hosts is None and origins is None:
        return None

    if hosts and ("*" in hosts):
        return TransportSecuritySettings(enable_dns_rebinding_protection=False)

    normalized_hosts: list[str] = []
    if hosts:
        for h in hosts:
            if h not in normalized_hosts:
                normalized_hosts.append(h)
            if not h.endswith(":*") and ":" not in h:
                wildcard = f"{h}:*"
                if wildcard not in normalized_hosts:
                    normalized_hosts.append(wildcard)

    if is_loopback(host):
        for default_h in ("127.0.0.1:*", "localhost:*", "[::1]:*"):
            if default_h not in normalized_hosts:
                normalized_hosts.append(default_h)

    normalized_origins: list[str] = []
    if origins:
        normalized_origins.extend(origins)
    else:
        for h in normalized_hosts:
            base = h[:-2] if h.endswith(":*") else h
            for scheme in ("http://", "https://"):
                entry = f"{scheme}{base}"
                if entry not in normalized_origins:
                    normalized_origins.append(entry)
                if not base.endswith(":*") and ":" not in base:
                    entry_wc = f"{scheme}{base}:*"
                    if entry_wc not in normalized_origins:
                        normalized_origins.append(entry_wc)

    return TransportSecuritySettings(
        enable_dns_rebinding_protection=True,
        allowed_hosts=normalized_hosts,
        allowed_origins=normalized_origins,
    )


def build_app(
    token: str,
    host: str = DEFAULT_HOST,
    path: str = DEFAULT_PATH,
    allowed_hosts: list[str] | None = None,
    allowed_origins: list[str] | None = None,
) -> BearerTokenMiddleware:
    """The MCP Streamable HTTP app, wrapped so no request reaches it without the token."""
    from tagui_mcp.server import mcp

    transport_security = _build_transport_security(
        host, allowed_hosts=allowed_hosts, allowed_origins=allowed_origins
    )
    return BearerTokenMiddleware(
        mcp.streamable_http_app(
            streamable_http_path=path, host=host, transport_security=transport_security
        ),
        token,
    )


def serve_http(
    host: str = DEFAULT_HOST,
    port: int = DEFAULT_PORT,
    allow_remote: bool = False,
    path: str = DEFAULT_PATH,
    allowed_hosts: list[str] | None = None,
    allowed_origins: list[str] | None = None,
) -> None:
    """Serve MCP over HTTP until stopped. Raises TokenError before binding if the token is bad."""
    import uvicorn

    from tagui_mcp.runner import reap_tagui_chrome
    from tagui_mcp.service import reset_service

    if path == DEFAULT_PATH and PATH_ENV in os.environ:
        path = os.environ[PATH_ENV].strip() or DEFAULT_PATH

    if not allow_remote and not is_loopback(host):
        raise TokenError(
            f"{host} is not a loopback address. Pass --allow-remote only behind a TLS proxy "
            "or another trusted tunnel."
        )
    app = build_app(
        load_token(),
        host=host,
        path=path,
        allowed_hosts=allowed_hosts,
        allowed_origins=allowed_origins,
    )
    # Chrome moves itself out of the service's cgroup, so a restart can leave one running.
    reap_tagui_chrome()

    def _shutdown(signum: int, _frame: object) -> None:
        reset_service()
        raise SystemExit(128 + signum)

    # uvicorn re-raises SIGTERM/SIGINT after it drains, so a handler that closes the
    # live browser has to be installed first or the session would be left running.
    signal.signal(signal.SIGTERM, _shutdown)
    signal.signal(signal.SIGINT, _shutdown)
    access_log = bool(int(os.environ.get("TAGUI_MCP_ACCESS_LOG", "1")))
    uvicorn.run(app, host=host, port=port, log_level="info", access_log=access_log)
