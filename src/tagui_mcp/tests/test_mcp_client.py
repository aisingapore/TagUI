"""Drive the server over the MCP stdio transport, the same way a desktop client does."""

from __future__ import annotations

import asyncio
import json
import sys
from pathlib import Path

from mcp.client.session import ClientSession
from mcp.client.stdio import StdioServerParameters, stdio_client

ROOT = Path(__file__).resolve().parents[1]
FAKE = Path(__file__).resolve().parent / "fake_tagui.py"

EXPECTED_TOOLS = {
    "tagui_status",
    "run_flow",
    "run_steps",
    "browser_start",
    "browser_stop",
    "browser_go",
    "browser_click",
    "browser_type",
    "browser_read",
    "page_state",
    "browser_step",
    "list_flows",
    "read_flow",
    "create_flow",
    "save_flow",
    "edit_flow",
    "validate_flow",
    "run_workspace_flow",
    "save_session",
}


def _text(result: object) -> str:
    content = getattr(result, "content")
    return content[0].text


def test_stdio_client_runs_flow_and_browser_tools(tmp_path: Path) -> None:
    asyncio.run(_run(tmp_path))


async def _run(tmp_path: Path) -> None:
    flow = tmp_path / "hello.tagui"
    flow.write_text('echo "hello from tagui mcp"\n', encoding="utf-8")
    params = StdioServerParameters(
        command=sys.executable,
        args=["-m", "tagui_mcp"],
        env={
            "PYTHONPATH": str(ROOT),
            "TAGUI_BIN": str(FAKE),
            "TAGUI_WORKSPACE": str(tmp_path / "flows"),
        },
        cwd=str(tmp_path),
    )
    async with stdio_client(params) as (read, write):
        async with ClientSession(read, write) as session:
            await session.initialize()
            listed = await session.list_tools()
            assert {tool.name for tool in listed.tools} == EXPECTED_TOOLS

            status = json.loads(_text(await session.call_tool("tagui_status", {})))
            assert status["ok"] is True
            assert status["tagui"] == str(FAKE.resolve())

            ran = json.loads(
                _text(await session.call_tool("run_flow", {"flow_file": str(flow)}))
            )
            assert ran["ok"] is True
            assert "hello from tagui mcp" in ran["output"]

            steps = json.loads(
                _text(
                    await session.call_tool(
                        "run_steps",
                        {"steps": ['echo "from an ad-hoc step"']},
                    )
                )
            )
            assert steps["ok"] is True
            assert "from an ad-hoc step" in steps["output"]

            created = json.loads(
                _text(
                    await session.call_tool(
                        "create_flow",
                        {"name": "sample", "content": 'echo "from workspace"\n'},
                    )
                )
            )
            assert created["ok"] is True
            edited = json.loads(
                _text(
                    await session.call_tool(
                        "edit_flow",
                        {
                            "name": "sample",
                            "op": "replace",
                            "line": 1,
                            "text": 'echo "edited workspace"',
                        },
                    )
                )
            )
            assert edited["lines"][0]["text"] == 'echo "edited workspace"'
            workspace_run = json.loads(
                _text(await session.call_tool("run_workspace_flow", {"name": "sample"}))
            )
            assert workspace_run["ok"] is True
            assert "edited workspace" in workspace_run["output"]

            started = json.loads(
                _text(
                    await session.call_tool(
                        "browser_start",
                        {"mode": "headless", "startup_timeout": 10},
                    )
                )
            )
            assert started["ok"] is True
            state = json.loads(_text(await session.call_tool("page_state", {})))
            assert state["url"] == "about:blank"
            opened = json.loads(
                _text(
                    await session.call_tool(
                        "browser_go", {"url": "https://example.com"}
                    )
                )
            )
            assert opened["ok"] is True
            assert "example.com" in opened["url"]
            promoted = json.loads(
                _text(await session.call_tool("save_session", {"name": "promoted"}))
            )
            assert promoted["ok"] is True
            assert "https://example.com" in promoted["content"]
            read = json.loads(
                _text(await session.call_tool("browser_read", {"target": "h1"}))
            )
            assert read["text"] == "text-of-h1"
            stopped = json.loads(_text(await session.call_tool("browser_stop", {})))
            assert stopped["ok"] is True
