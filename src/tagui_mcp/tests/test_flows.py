from __future__ import annotations

import shutil
from pathlib import Path

import pytest

from tagui_mcp.service import TaguiService

FAKE = Path(__file__).parent / "fake_tagui.py"


def test_create_edit_and_reject_bad_names(tmp_path: Path) -> None:
    service = TaguiService(tagui_bin=FAKE, workspace=tmp_path)
    created = service.create_flow("hello", 'echo "one"')
    assert created["ok"] is True
    assert created["lines"] == [{"line": 1, "text": 'echo "one"'}]
    again = service.create_flow("hello", "echo two")
    assert again["ok"] is False
    edited = service.edit_flow("hello", "append", text='echo "two"')
    assert edited["ok"] is True
    assert [row["text"] for row in edited["lines"]] == ['echo "one"', 'echo "two"']
    replaced = service.edit_flow("hello", "replace", line=2, text='echo "three"\necho "four"')
    assert [row["text"] for row in replaced["lines"]] == ['echo "one"', 'echo "three"', 'echo "four"']
    removed = service.edit_flow("hello", "delete", line=1)
    assert removed["lines"][0]["text"] == 'echo "three"'
    escaped = service.create_flow("../outside", "echo no")
    assert escaped["ok"] is False
    listed = service.list_flows()
    assert listed["flows"][0]["name"] == "hello.tagui"


def test_validate_flow_with_real_parser(tmp_path: Path) -> None:
    if shutil.which("php") is None:
        pytest.skip("php not installed")
    service = TaguiService(tagui_bin=FAKE, workspace=tmp_path)
    service.create_flow("hello", 'echo "hi"\n')
    checked = service.validate_flow("hello")
    assert checked["ok"] is True, checked
    service.create_flow("broken", "if total greater than 1\n{\necho \"x\"\n")
    broken = service.validate_flow("broken")
    assert broken["ok"] is False
    assert "tally" in str(broken["error"]) or "{" in str(broken["error"])


def test_empty_datatable_does_not_crash_php8(tmp_path: Path) -> None:
    if shutil.which("php") is None:
        pytest.skip("php not installed")
    service = TaguiService(tagui_bin=FAKE, workspace=tmp_path)
    (tmp_path / "tagui_local.csv").write_text("", encoding="utf-8")
    service.create_flow("hello", 'echo "hi"\n')
    checked = service.validate_flow("hello")
    output = str(checked["parser_output"])
    assert "TypeError" not in output
    assert "Fatal error" not in output
    # aisingapore/TagUI master guards count() (#1443) and treats fgetcsv()
    # false at EOF as the end of the file. A reported empty row is also fine.
    if checked["ok"] is False:
        assert "empty row" in str(checked["error"])
