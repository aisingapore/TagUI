from __future__ import annotations

import os
import time
from pathlib import Path

from tagui_mcp.runner import _screenshots, diagnose_run, resolve_tagui_executable, run_flow, run_steps

FAKE = Path(__file__).parent / "fake_tagui.py"


def test_resolve_prefers_tagui_bin(monkeypatch, tmp_path: Path) -> None:
    fake = tmp_path / "tagui"
    fake.write_text("#!/bin/sh\n", encoding="utf-8")
    monkeypatch.setenv("TAGUI_BIN", str(fake))
    assert resolve_tagui_executable() == fake.resolve()


def test_resolve_bundled_script(monkeypatch) -> None:
    monkeypatch.delenv("TAGUI_BIN", raising=False)
    monkeypatch.delenv("TAGUI_SRC", raising=False)
    found = resolve_tagui_executable()
    assert found.name == "tagui"
    assert found.parent.name == "src"


def test_run_flow_echo(tmp_path: Path) -> None:
    flow = tmp_path / "hello"
    flow.write_text('echo "hello from tagui mcp"\n', encoding="utf-8")
    result = run_flow(str(flow), tagui_bin=FAKE)
    assert result["ok"] is True
    assert "hello from tagui mcp" in str(result["output"])
    assert result["exit_code"] == 0
    command = result["command"]
    assert isinstance(command, list)
    assert command[2:] == ["quiet"]


def test_run_flow_forwards_options_inputs_and_datatable(tmp_path: Path) -> None:
    flow = tmp_path / "hello.tagui"
    flow.write_text('echo "hi"\n', encoding="utf-8")
    table = tmp_path / "rows.csv"
    table.write_text("name,a\n", encoding="utf-8")
    result = run_flow(
        str(flow),
        options=["headless", "quiet"],
        inputs=["june"],
        datatable=str(table),
        tagui_bin=FAKE,
    )
    assert result["ok"] is True
    command = result["command"]
    assert isinstance(command, list)
    assert command[2:] == ["headless", "quiet", str(table.resolve()), "june"]


def test_run_flow_marks_error_lines(tmp_path: Path) -> None:
    flow = tmp_path / "bad.txt"
    flow.write_text('echo "ERROR - element missing"\n', encoding="utf-8")
    result = run_flow(str(flow), tagui_bin=FAKE)
    assert result["ok"] is False
    assert result["exit_code"] == 0
    assert result["error_lines"] == ["ERROR - element missing"]


def test_run_flow_marks_casper_syntax_error(tmp_path: Path) -> None:
    flow = tmp_path / "bad"
    flow.write_text("SyntaxError: Unexpected token 'else'\n", encoding="utf-8")
    result = run_flow(str(flow), tagui_bin=FAKE)
    assert result["ok"] is False
    assert "SyntaxError" in str(result["error"])


def test_run_flow_nonzero_exit(tmp_path: Path) -> None:
    flow = tmp_path / "bad"
    flow.write_text("exit-code-1\n", encoding="utf-8")
    result = run_flow(str(flow), tagui_bin=FAKE)
    assert result["ok"] is False
    assert result["exit_code"] == 1


def test_run_flow_rejects_bad_usage(tmp_path: Path) -> None:
    missing = run_flow(str(tmp_path / "nope"), tagui_bin=FAKE)
    assert missing["ok"] is False
    assert "cannot find" in str(missing["error"])

    bad_ext = tmp_path / "flow.py"
    bad_ext.write_text("echo hi\n", encoding="utf-8")
    rejected = run_flow(str(bad_ext), tagui_bin=FAKE)
    assert rejected["ok"] is False
    assert "extension" in str(rejected["error"])

    tag_flow = tmp_path / "legacy.tag"
    tag_flow.write_text("echo hi\n", encoding="utf-8")
    tag_ok = run_flow(str(tag_flow), tagui_bin=FAKE)
    assert tag_ok["ok"] is True
    assert str(tag_flow) in tag_ok["command"][1]

    flow = tmp_path / "hello"
    flow.write_text("echo hi\n", encoding="utf-8")
    option = run_flow(str(flow), options=["chrome", "headless"], tagui_bin=FAKE)
    assert option["ok"] is False
    modern = run_flow(str(flow), options=["headless", "modern", "quiet"], tagui_bin=FAKE)
    assert modern["ok"] is True
    assert "modern" in modern["command"]
    unknown = run_flow(str(flow), options=["sudo"], tagui_bin=FAKE)
    assert unknown["ok"] is False
    spaced = run_flow(str(flow), inputs=["has space"], tagui_bin=FAKE)
    assert spaced["ok"] is True
    assert spaced["parameter_source"] == "environment"
    assert "has space" not in spaced["command"]
    too_many = run_flow(
        str(flow),
        options=["quiet", "report", "speed", "debug"],
        inputs=["a", "b", "c", "d", "e"],
        tagui_bin=FAKE,
    )
    assert too_many["ok"] is True
    assert too_many["parameter_source"] == "environment"
    assert too_many["command"][2:] == ["quiet", "report", "speed", "debug"]


def test_run_flow_reports_failing_line(tmp_path: Path) -> None:
    flow = tmp_path / "bad.tagui"
    flow.write_text('echo "before"\nERROR - [LINE 2] cannot understand step nope\n', encoding="utf-8")
    result = run_flow(str(flow), tagui_bin=FAKE)
    assert result["ok"] is False
    assert result["failing_line"] == 2
    assert result["failing_step"] == "ERROR - [LINE 2] cannot understand step nope"
    assert "log" in result


def test_missing_element_error_points_at_the_step(tmp_path: Path) -> None:
    flow = tmp_path / "miss.tagui"
    flow.write_text("https://example.com\nclick h1\n", encoding="utf-8")
    result = diagnose_run(flow, ["ERROR - cannot find h1"], "", [])
    assert result["failing_line"] == 2
    assert result["failing_step"] == "click h1"


def test_screenshots_are_files_written_during_the_run(tmp_path: Path) -> None:
    started = time.time()
    shot = tmp_path / "snap1.png"
    shot.write_bytes(b"png")
    assert _screenshots(tmp_path, started) == [str(shot)]


def test_failure_captures_are_listed_and_older_shots_are_not(tmp_path: Path) -> None:
    started = time.time()
    tab = tmp_path / "failure_tab.png"
    desk = tmp_path / "failure_desktop.png"
    extra = tmp_path / "failure_tab_2.png"
    stale = tmp_path / "old.png"
    for path in (tab, desk, extra, stale):
        path.write_bytes(b"png")
    os.utime(stale, (started - 10, started - 10))
    found = _screenshots(tmp_path, started)
    assert found == sorted(str(path) for path in (tab, desk, extra))


def test_visual_timeout_hint_mentions_screenshots(tmp_path: Path) -> None:
    flow = tmp_path / "flow.tag"
    flow.write_text("click mark.png\n", encoding="utf-8")
    result = diagnose_run(
        flow,
        ["ERROR - [LINE 44] visual step timed out waiting for OculiX"],
        "",
        [str(tmp_path / "failure_tab.png"), str(tmp_path / "failure_desktop.png")],
    )
    assert result["failing_line"] == 44
    assert result["screenshots"] == [str(tmp_path / "failure_tab.png"), str(tmp_path / "failure_desktop.png")]
    assert "screenshot" in str(result["hint"])


def test_run_steps_keeps_work_directory(tmp_path: Path) -> None:
    result = run_steps(
        ['echo "from steps"'],
        directory=tmp_path,
        tagui_bin=FAKE,
    )
    assert result["ok"] is True
    assert "from steps" in str(result["output"])
    assert result["work_directory"] == str(tmp_path)
    assert (tmp_path / "mcp_steps").is_file()


def test_reaper_leaves_another_tagui_install_alone(monkeypatch, tmp_path: Path) -> None:
    import signal
    import subprocess

    from tagui_mcp import runner

    ours = tmp_path / "modern" / "src"
    ours.mkdir(parents=True)
    (ours / "tagui").write_text("#!/bin/sh\n", encoding="utf-8")
    theirs = tmp_path / "official" / "src"
    ps_output = "\n".join(
        [
            f"101 chrome --remote-debugging-port=9222 --user-data-dir={ours}/chrome/tagui_user_profile",
            f"102 chrome --remote-debugging-port=9222 --user-data-dir={theirs}/chrome/tagui_user_profile",
            "103 php -q tagui_chrome.php",
            "104 chrome --user-data-dir=/tmp/tagui_modern_profile_9_abcd",
            "105 chrome --remote-debugging-port=9222 --user-data-dir=/elsewhere/tagui_user_profile",
        ]
    )
    killed: list[int] = []
    monkeypatch.delenv("TAGUI_CHROME_PROFILE", raising=False)
    monkeypatch.setattr(
        subprocess,
        "run",
        lambda *args, **kwargs: subprocess.CompletedProcess(args, 0, stdout=ps_output, stderr=""),
    )
    monkeypatch.setattr(runner.os, "kill", lambda pid, sig: killed.append(pid) if sig == signal.SIGTERM else None)
    monkeypatch.setattr(runner.os, "readlink", lambda path: str(theirs) if "/102/" in path or "/103/" in path else str(ours))

    runner.reap_tagui_chrome(ours / "tagui")

    assert set(killed) == {101, 104}


def test_reaper_with_no_tagui_install_only_touches_modern_temp_profiles(monkeypatch, tmp_path: Path) -> None:
    import signal
    import subprocess

    from tagui_mcp import runner

    ps_output = "\n".join(
        [
            "201 chrome --remote-debugging-port=9222 --user-data-dir=/x/chrome/tagui_user_profile",
            "202 chrome --user-data-dir=/tmp/tagui_modern_profile_9_abcd",
        ]
    )
    killed: list[int] = []
    monkeypatch.delenv("TAGUI_BIN", raising=False)
    monkeypatch.delenv("TAGUI_SRC", raising=False)
    monkeypatch.delenv("TAGUI_CHROME_PROFILE", raising=False)
    monkeypatch.setattr(runner.shutil, "which", lambda name: None)
    monkeypatch.setattr(
        runner,
        "resolve_tagui_executable",
        lambda explicit=None: (_ for _ in ()).throw(runner.TaguiFailure("no tagui")),
    )
    monkeypatch.setattr(
        subprocess,
        "run",
        lambda *args, **kwargs: subprocess.CompletedProcess(args, 0, stdout=ps_output, stderr=""),
    )
    monkeypatch.setattr(runner.os, "kill", lambda pid, sig: killed.append(pid) if sig == signal.SIGTERM else None)

    runner.reap_tagui_chrome()

    assert set(killed) == {202}


def test_reaper_stops_a_chrome_on_the_configured_profile_only(monkeypatch, tmp_path: Path) -> None:
    import signal
    import subprocess

    from tagui_mcp import runner

    profile = tmp_path / "svc-profile"
    ps_output = "\n".join(
        [
            f"301 chrome --remote-debugging-port=41739 --user-data-dir={profile} --window-size=1366,842 about:blank",
            f"302 chrome --remote-debugging-port=41740 --user-data-dir={profile}-other about:blank",
            "303 chrome --remote-debugging-port=41741 --user-data-dir=/home/someone/profile about:blank",
        ]
    )
    killed: list[int] = []
    monkeypatch.setenv("TAGUI_CHROME_PROFILE", str(profile))
    monkeypatch.setattr(
        subprocess,
        "run",
        lambda *args, **kwargs: subprocess.CompletedProcess(args, 0, stdout=ps_output, stderr=""),
    )
    monkeypatch.setattr(runner.os, "kill", lambda pid, sig: killed.append(pid) if sig == signal.SIGTERM else None)

    runner.reap_tagui_chrome(tmp_path / "missing" / "tagui")

    assert set(killed) == {301}
