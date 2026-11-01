from __future__ import annotations

from pathlib import Path

import pytest

from tagui_mcp.runner import TaguiFailure
from tagui_mcp.session import LiveSession, check_live_step, live_command

FAKE = Path(__file__).parent / "fake_tagui.py"


def test_live_command_selects_the_engine() -> None:
    classic = live_command("/tmp/tagui", "/tmp/flow", "headless")
    assert classic == ["/tmp/tagui", "/tmp/flow", "headless", "quiet"]
    modern = live_command("/tmp/tagui", "/tmp/flow", "chrome", "modern")
    assert modern[-1] == "modern"
    assert "headless" not in modern


def test_check_live_step_accepts_blocks_and_rejects_done() -> None:
    assert check_live_step("  echo hi  ") == "echo hi"
    assert check_live_step("echo one\necho two") == "echo one\necho two"
    with pytest.raises(TaguiFailure):
        check_live_step("done")
    with pytest.raises(TaguiFailure):
        check_live_step("py begin\nprint(1)")


def test_live_session_round_trip() -> None:
    session = LiveSession(tagui_bin=FAKE)
    session.start("headless", startup_timeout=5)
    try:
        assert session.running
        echoed = session.run_step('echo "from live"', timeout=5)
        assert echoed == "from live"
        workdir = session.workdir
        assert workdir is not None
        url = session.dump("url()", workdir / "url.txt", timeout=5)
        assert url == "about:blank"
        session.run_step("https://example.com", timeout=5)
        url = session.dump("url()", workdir / "url.txt", timeout=5)
        assert url == "https://example.com"
        title = session.dump("title()", workdir / "title.txt", timeout=5)
        assert title == "Example Domain"
    finally:
        session.stop()
    assert session.running is False


def test_live_step_timeout_closes_session() -> None:
    session = LiveSession(tagui_bin=FAKE)
    session.start("chrome", startup_timeout=5)
    try:
        with pytest.raises(TaguiFailure, match="timed out"):
            session.run_step("hang", timeout=0.4)
        assert session.running is False
    finally:
        session.stop()
