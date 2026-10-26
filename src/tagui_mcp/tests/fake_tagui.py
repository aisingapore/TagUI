#!/usr/bin/env -S python3 -u
"""Stand-in for the tagui CLI used by tests.

Batch mode prints echoed text and records a .log file. A flow that contains a
`live` step speaks the live-mode line protocol: banner, then one stdin line at
a time, with `done` exiting.
"""

from __future__ import annotations

import sys
import time
from pathlib import Path


def main() -> int:
    if len(sys.argv) < 2:
        print("tagui fake: missing flow file", flush=True)
        return 1
    flow_path = Path(sys.argv[1])
    print("ARGS:" + " ".join(sys.argv[2:]), flush=True)
    if not flow_path.is_file():
        print(f"ERROR - cannot find {flow_path}", flush=True)
        return 1
    lines = flow_path.read_text(encoding="utf-8").splitlines()
    if any(line.strip() == "live" for line in lines):
        return live_mode()
    return batch(flow_path, lines)


def batch(flow_path: Path, lines: list[str]) -> int:
    rendered: list[str] = []
    for line in lines:
        stripped = line.strip()
        if stripped.startswith("echo "):
            rendered.append(echo_text(stripped))
        elif stripped and not stripped.startswith("//"):
            rendered.append(stripped)
    rendered.append("FINISH - automation finished")
    sys.stdout.write("\n".join(rendered) + "\n")
    sys.stdout.flush()
    log_path = Path(str(flow_path) + ".log")
    log_path.write_text("\n".join(rendered) + "\n", encoding="utf-8")
    if any(line.strip() == "exit-code-1" for line in lines):
        return 1
    return 0


def live_mode() -> int:
    state = {"url": "about:blank", "title": "about:blank", "text": ""}
    print("LIVE MODE - type done to quit", flush=True)
    while True:
        raw = sys.stdin.readline()
        if raw == "":
            return 0
        line = raw.rstrip("\n")
        if line.startswith("done"):
            return 0
        handle(line, state)


def handle(line: str, state: dict[str, str]) -> None:
    stripped = line.strip()
    if stripped == "":
        return
    if stripped == "hang":
        time.sleep(5)
        return
    if stripped.startswith("echo "):
        print(echo_text(stripped), flush=True)
        return
    if stripped.startswith("http://") or stripped.startswith("https://"):
        state["url"] = stripped
        if "example.com" in stripped:
            state["title"] = "Example Domain"
            state["text"] = "Example Domain"
        else:
            state["title"] = "Page"
            state["text"] = "Page"
        return
    if stripped.startswith("dump "):
        dump(stripped, state)
        return
    if stripped.startswith("show "):
        target = stripped[5:].strip()
        if target == "page":
            print(state["text"], flush=True)
        elif target == "missing-element":
            print("ERROR - cannot find missing-element", flush=True)
        else:
            print(f"text-of-{target}", flush=True)
        return
    if stripped.startswith("click "):
        target = stripped[6:].strip()
        if target == "" or target == "missing-element":
            print(f"ERROR - cannot find {target or 'target'}", flush=True)
        return
    if stripped.startswith("type "):
        if " as " not in stripped:
            print("ERROR - target/text missing for type", flush=True)
        return
    if stripped == "wait" or stripped.startswith("wait "):
        return
    if stripped.startswith("run "):
        print(stripped[4:].strip(), flush=True)
        return
    if stripped.startswith("snap ") and " to " in stripped:
        Path(stripped.split(" to ", 1)[1].strip()).write_bytes(b"png")
        return
    print(f"ERROR - cannot understand step {stripped}", flush=True)


def dump(stripped: str, state: dict[str, str]) -> None:
    marker = " to "
    if marker not in stripped:
        print("ERROR - target missing for dump", flush=True)
        return
    expression, _, dest = stripped[5:].partition(marker)
    expression = expression.strip()
    dest_path = Path(dest.strip())
    values = {"url()": state["url"], "title()": state["title"], "text()": state["text"]}
    if expression not in values:
        print(f"ERROR - cannot dump {expression}", flush=True)
        return
    dest_path.write_text(values[expression], encoding="utf-8")


def echo_text(stripped: str) -> str:
    rest = stripped[5:].strip()
    if len(rest) >= 2 and rest[0] == rest[-1] and rest[0] in "\"'":
        return rest[1:-1]
    return rest


if __name__ == "__main__":
    sys.exit(main())
