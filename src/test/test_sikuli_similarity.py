#!/usr/bin/env python3
# Regression for the best similarity note on a missed image. The Sikuli file is
# Jython 2. The helpers above the runtime marker are valid when `unicode` is
# `str`, so this test loads that prefix under Python 3 with stubbed Sikuli calls.

import contextlib
import io
import pathlib
import sys

ROOT = pathlib.Path(__file__).resolve().parents[1]
SCRIPT = ROOT / 'tagui.sikuli' / 'tagui.py'
MARKER = '# sikuli runtime below'


def load_helpers():
    source = SCRIPT.read_text(encoding='utf-8')
    if MARKER not in source:
        raise SystemExit('missing helper marker in tagui.py')
    head = source.split(MARKER, 1)[0]
    saved_out, saved_err = sys.stdout, sys.stderr
    namespace = {'unicode': str}
    exec(compile(head, str(SCRIPT), 'exec'), namespace)
    sys.stdout, sys.stderr = saved_out, saved_err
    return source, namespace


def check(label, actual, expected):
    if actual != expected:
        raise SystemExit(label + ' failed: ' + repr(actual) + ' != ' + repr(expected))


class Match:
    def __init__(self, score):
        self.score = score

    def getScore(self):
        return self.score


class Pattern:
    def __init__(self, target):
        self.target = target
        self.similarity = None

    def similar(self, value):
        self.similarity = value
        return self


class Screen:
    def __init__(self, present, best):
        self.present = present
        self.best = best
        self.events = []

    def exists(self, target):
        self.events.append(('exists', target))
        return self.present

    def find(self, pattern):
        self.events.append(('find', pattern.target, pattern.similarity))
        if isinstance(self.best, Exception):
            raise self.best
        return None if self.best is None else Match(self.best)

    def setAutoWaitTimeout(self, value):
        self.events.append(('timeout', value))


def run(helpers, screen, target):
    helpers['exists'] = screen.exists
    helpers['find'] = screen.find
    helpers['Pattern'] = Pattern
    helpers['setAutoWaitTimeout'] = screen.setAutoWaitTimeout
    helpers['wait_timeout'] = 10
    out = io.StringIO()
    with contextlib.redirect_stdout(out):
        found = helpers['image_found'](target)
    return found, out.getvalue(), helpers['tagui_result_note']


def main():
    source, helpers = load_helpers()

    if source.count('if image_found(') != 11:
        raise SystemExit('expected 11 image steps to use image_found')
    if 'if exists(params):' not in source.split('def visible_intent', 1)[1].split('def get_intent', 1)[0]:
        raise SystemExit('visible_intent should keep the plain exists() probe')
    if "intent_result_string + ' ' + tagui_result_note" not in source:
        raise SystemExit('main loop does not add the note to the out-file')

    screen = Screen(1, 0.5)
    found, printed, note = run(helpers, screen, 'button.png')
    check('hit result', found, True)
    check('hit events', screen.events, [('exists', 'button.png')])
    check('hit output', printed, '')
    check('hit note', note, '')

    screen = Screen(None, 0.6234)
    found, printed, note = run(helpers, screen, 'button.png')
    check('miss result', found, False)
    check('miss events', screen.events, [
        ('exists', 'button.png'), ('timeout', 0.4),
        ('find', 'button.png', 0.01), ('timeout', 10)])
    check('miss note', note, 'not on screen, best similarity 0.62')
    check('miss output', printed,
          '[tagui] NOTFOUND - button.png not on screen, best similarity 0.62\n')

    screen = Screen(None, None)
    found, printed, note = run(helpers, screen, '测试.png')
    check('blank result', found, False)
    check('blank note', note, 'not on screen')
    check('blank output', printed, '[tagui] NOTFOUND - 测试.png not on screen\n')

    screen = Screen(None, RuntimeError('FindFailed'))
    found, printed, note = run(helpers, screen, 'button.png')
    check('error result', found, False)
    check('error note', note, 'not on screen')
    check('error restores timeout', screen.events[-1], ('timeout', 10))

    screen = Screen(1, 0.5)
    run(helpers, screen, 'button.png')
    check('note cleared on hit', helpers['tagui_result_note'], '')
    print('ok sikuli similarity')


if __name__ == '__main__':
    main()
