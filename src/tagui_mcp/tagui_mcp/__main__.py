"""Command line entry point: stdio by default, Streamable HTTP with a bearer token on request."""

from __future__ import annotations

import argparse
import sys

from tagui_mcp.http_transport import DEFAULT_HOST, DEFAULT_PATH, DEFAULT_PORT, TokenError


def _parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(prog="tagui-mcp", description="TagUI MCP server")
    parser.add_argument(
        "--transport",
        choices=("stdio", "http"),
        default="stdio",
        help="stdio (default) or Streamable HTTP. HTTP requires TAGUI_MCP_TOKEN or TAGUI_MCP_TOKEN_FILE.",
    )
    parser.add_argument("--host", default=DEFAULT_HOST, help=f"HTTP bind address (default {DEFAULT_HOST})")
    parser.add_argument("--port", type=int, default=DEFAULT_PORT, help=f"HTTP port (default {DEFAULT_PORT})")
    parser.add_argument("--path", default=DEFAULT_PATH, help=f"HTTP endpoint path (default {DEFAULT_PATH})")
    parser.add_argument(
        "--allow-remote",
        action="store_true",
        help="allow a non-loopback --host. The connection is plain HTTP, so terminate TLS in front of it.",
    )
    parser.add_argument(
        "--allowed-hosts",
        help="comma-separated list of allowed Host header values (or * to disable DNS rebinding protection)",
    )
    parser.add_argument(
        "--allowed-origins",
        help="comma-separated list of allowed Origin header values (optional)",
    )
    return parser


def main(argv: list[str] | None = None) -> None:
    args = _parser().parse_args(argv)
    if args.transport == "stdio":
        from tagui_mcp.server import main as serve_stdio

        serve_stdio()
        return
    from tagui_mcp.http_transport import serve_http

    allowed_hosts = (
        [h.strip() for h in args.allowed_hosts.split(",") if h.strip()]
        if args.allowed_hosts
        else None
    )
    allowed_origins = (
        [o.strip() for o in args.allowed_origins.split(",") if o.strip()]
        if args.allowed_origins
        else None
    )

    try:
        serve_http(
            host=args.host,
            port=args.port,
            allow_remote=args.allow_remote,
            path=args.path,
            allowed_hosts=allowed_hosts,
            allowed_origins=allowed_origins,
        )
    except TokenError as exc:
        print(f"tagui-mcp: {exc}", file=sys.stderr)
        raise SystemExit(2) from exc


if __name__ == "__main__":
    main()
