#!/usr/bin/env python3
# Regression for Jython reading tagui_sikuli.in, and for typing non-ASCII text.
# The Sikuli file is Jython 2. The helpers above the runtime marker are valid
# when `unicode` is `str`, so this test loads that prefix under Python 3.

import codecs
import pathlib
import sys
import tempfile

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


class Calls:
    def __init__(self):
        self.events = []

    def click(self, target):
        self.events.append(('click', target))
        return 1

    def type(self, *args):
        self.events.append(('type',) + args)
        return 1

    def paste(self, text):
        self.events.append(('paste', text))
        return 1


def main():
    source, helpers = load_helpers()
    read_utf8 = helpers['read_utf8']
    write_utf8 = helpers['write_utf8']
    intent_text = helpers['intent_text']
    text_needs_paste = helpers['text_needs_paste']
    deliver = helpers['deliver_typed_text']

    if "read_utf8('tagui.sikuli/tagui_sikuli.in')" not in source:
        raise SystemExit('main loop still reads tagui_sikuli.in without UTF-8')
    if 'return deliver_typed_text(param1, param2, modifier_keys)' not in source:
        raise SystemExit('type_intent does not deliver typed text')
    if 'return deliver_typed_text(None, param2, modifier_keys)' not in source:
        raise SystemExit('page.png type does not deliver typed text')

    word = '测试'
    image = '测试.png'
    with tempfile.TemporaryDirectory() as directory:
        root = pathlib.Path(directory)
        inbox = root / 'tagui_sikuli.in'
        outbox = root / 'tagui_sikuli.out'
        noted = root / 'tagui_sikuli.txt'
        type_line = '[1] type ' + image + ' as ' + word
        tap_line = '[2] tap ' + image
        inbox.write_bytes(type_line.encode('utf-8'))
        decoded = read_utf8(str(inbox))
        check('utf-8 in', decoded, type_line)
        check('latin-1 mojibake differs', inbox.read_bytes().decode('latin-1') == type_line, False)
        check('type intent', intent_text(decoded), 'type ' + image + ' as ' + word)
        inbox.write_bytes(tap_line.encode('utf-8'))
        check('tap intent', intent_text(read_utf8(str(inbox))), 'tap ' + image)

        write_utf8(str(outbox), '[1] SUCCESS ' + image + ' ' + word)
        write_utf8(str(noted), word)
        check('out bytes', outbox.read_bytes(), ('[1] SUCCESS ' + image + ' ' + word).encode('utf-8'))
        check('txt bytes', noted.read_bytes(), word.encode('utf-8'))
        check('out roundtrip', read_utf8(str(outbox)), '[1] SUCCESS ' + image + ' ' + word)
        check('txt roundtrip', read_utf8(str(noted)), word)

    check('cjk pastes', text_needs_paste(word), True)
    check('ascii types', text_needs_paste('github'), False)
    check('enter types', text_needs_paste('\n'), False)
    check('sikuli key types', text_needs_paste('\ue000'), False)
    check('mixed pastes', text_needs_paste(word + '\n'), True)

    calls = Calls()
    helpers['click'] = calls.click
    helpers['type'] = calls.type
    helpers['paste'] = calls.paste
    check('image paste result', deliver(image, word, 0), 1)
    check('image paste events', calls.events, [('click', image), ('paste', word)])

    calls = Calls()
    helpers['click'] = calls.click
    helpers['type'] = calls.type
    helpers['paste'] = calls.paste
    check('page paste result', deliver(None, word, 0), 1)
    check('page paste events', calls.events, [('paste', word)])

    calls = Calls()
    helpers['click'] = calls.click
    helpers['type'] = calls.type
    helpers['paste'] = calls.paste
    check('ascii result', deliver(image, 'github', 0), 1)
    check('ascii events', calls.events, [('type', image, 'github')])

    calls = Calls()
    helpers['click'] = calls.click
    helpers['type'] = calls.type
    helpers['paste'] = calls.paste
    check('enter result', deliver(None, '\n', 0), 1)
    check('enter events', calls.events, [('type', '\n')])

    calls = Calls()
    helpers['click'] = calls.click
    helpers['type'] = calls.type
    helpers['paste'] = calls.paste
    check('special key result', deliver(image, '\ue007', 0), 1)
    check('special key events', calls.events, [('type', image, '\ue007')])

    calls = Calls()
    helpers['click'] = calls.click
    helpers['type'] = calls.type
    helpers['paste'] = calls.paste
    check('modifier result', deliver(image, word, 1), 1)
    check('modifier events', calls.events, [('type', image, word, 1)])

    calls = Calls()
    helpers['click'] = calls.click
    helpers['type'] = calls.type
    helpers['paste'] = calls.paste
    check('mixed result', deliver(image, word + '\n', 0), 1)
    check('mixed events', calls.events, [('click', image), ('paste', word), ('type', '\n')])
    print('ok sikuli utf-8')


if __name__ == '__main__':
    main()
