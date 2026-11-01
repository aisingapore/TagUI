from __future__ import annotations

from pathlib import Path

from tagui_mcp.service import TaguiService

FAKE = Path(__file__).parent / "fake_tagui.py"


def _service() -> TaguiService:
    return TaguiService(tagui_bin=FAKE)


def test_flow_is_blocked_while_browser_is_open(tmp_path: Path) -> None:
    service = _service()
    try:
        started = service.browser_start("headless", startup_timeout=5)
        assert started["ok"] is True
        assert started["work_directory"]
        flow = tmp_path / "hello"
        flow.write_text('echo "hi"\n', encoding="utf-8")
        blocked = service.run_flow(str(flow))
        assert blocked["ok"] is False
        assert "browser session" in str(blocked["error"])
        stopped = service.browser_stop()
        assert stopped["ok"] is True
        opened = service.run_flow(str(flow))
        assert opened["ok"] is True
    finally:
        service.close()


def test_browser_tools_against_fake_live_mode() -> None:
    service = _service()
    try:
        assert service.browser_go("https://example.com")["ok"] is False
        started = service.browser_start("headless", startup_timeout=5)
        assert started["ok"] is True
        state = service.page_state()
        assert state["ok"] is True
        assert state["url"] == "about:blank"
        opened = service.browser_go("https://example.com")
        assert opened["ok"] is True
        assert opened["url"] == "https://example.com"
        state = service.page_state()
        assert state["title"] == "Example Domain"
        assert state["text"] == "Example Domain"
        read = service.browser_read("h1")
        assert read["ok"] is True
        assert read["text"] == "text-of-h1"
        missing = service.browser_click("missing-element")
        assert missing["ok"] is False
        typed = service.browser_type("search-box", "github")
        assert typed["ok"] is True
        quoted = service.browser_type("search-box", "it's")
        assert quoted["ok"] is True
        assert "screenshot" in missing
        waited = service.browser_step("wait 1")
        assert waited["ok"] is True
        ran = service.browser_step("run echo hello-from-run")
        assert ran["ok"] is True
        assert "echo hello-from-run" in str(ran["output"])
        stepped = service.browser_step('echo "from live"')
        assert stepped["ok"] is True
        assert stepped["output"] == "from live"
        refused = service.browser_step("not a real step")
        assert refused["ok"] is False
        again = service.browser_start("chrome")
        assert again["ok"] is False
    finally:
        service.close()


def test_flow_parser_steps_replay_and_session_can_be_saved(tmp_path: Path) -> None:
    service = TaguiService(tagui_bin=FAKE, workspace=tmp_path)
    try:
        assert service.browser_start("headless", startup_timeout=5)["ok"] is True
        opened = service.browser_go("https://example.com")
        assert opened["ok"] is True
        unclosed = service.browser_step("py begin")
        assert unclosed["ok"] is False
        assert "block" in str(unclosed["error"])
        replayed = service.browser_step('if total greater than 1\necho "inside"')
        assert replayed["executed_as"] == "flow"
        assert replayed["browser_running"] is False
        assert replayed["ok"] is True
        assert "inside" in str(replayed["output"])
        saved = service.save_session("from_live")
        assert saved["ok"] is True
        text = Path(str(saved["path"])).read_text(encoding="utf-8")
        assert "https://example.com" in text
        assert "if total greater than 1" in text
    finally:
        service.close()


def test_rejects_bad_mode_and_url() -> None:
    service = _service()
    try:
        bad_mode = service.browser_start("firefox")
        assert bad_mode["ok"] is False
        service.browser_start("headless", startup_timeout=5)
        bad_url = service.browser_go("javascript:alert(1)")
        assert bad_url["ok"] is False
        file_url = service.browser_go("file:///etc/passwd")
        assert file_url["ok"] is False
    finally:
        service.close()
