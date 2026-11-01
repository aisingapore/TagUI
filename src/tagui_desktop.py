#!/usr/bin/env python3
"""Grab the X11 root window to a PNG.

Uses libX11 through ctypes and the standard library. TagUI calls this when
import, scrot, gnome-screenshot, maim, and ffmpeg are not available. The only
argument is the output path. Nothing from the page is printed.
"""

from __future__ import annotations

import ctypes
import ctypes.util
import os
import struct
import sys
import zlib


def _mask_shift(mask: int) -> tuple[int, int]:
    if mask == 0:
        return 0, 0
    shift = 0
    while (mask & 1) == 0:
        mask >>= 1
        shift += 1
    bits = 0
    while (mask & 1) == 1:
        mask >>= 1
        bits += 1
    return shift, max(bits, 1)


def _png(width: int, height: int, rgb: bytes) -> bytes:
    def chunk(tag: bytes, data: bytes) -> bytes:
        return struct.pack(">I", len(data)) + tag + data + struct.pack(">I", zlib.crc32(tag + data) & 0xFFFFFFFF)

    row = width * 3
    raw = bytearray()
    for y in range(height):
        raw.append(0)
        raw.extend(rgb[y * row : (y + 1) * row])
    ihdr = struct.pack(">IIBBBBB", width, height, 8, 2, 0, 0, 0)
    return b"\x89PNG\r\n\x1a\n" + chunk(b"IHDR", ihdr) + chunk(b"IDAT", zlib.compress(bytes(raw), 1)) + chunk(b"IEND", b"")


def capture(path: str) -> int:
    libname = ctypes.util.find_library("X11")
    if not libname:
        return 1
    x11 = ctypes.CDLL(libname)

    class Funcs(ctypes.Structure):
        _fields_ = [
            ("create_image", ctypes.c_void_p),
            ("destroy_image", ctypes.c_void_p),
            ("get_pixel", ctypes.c_void_p),
            ("put_pixel", ctypes.c_void_p),
            ("sub_image", ctypes.c_void_p),
            ("add_pixel", ctypes.c_void_p),
        ]

    class XImage(ctypes.Structure):
        _fields_ = [
            ("width", ctypes.c_int),
            ("height", ctypes.c_int),
            ("xoffset", ctypes.c_int),
            ("format", ctypes.c_int),
            ("data", ctypes.c_void_p),
            ("byte_order", ctypes.c_int),
            ("bitmap_unit", ctypes.c_int),
            ("bitmap_bit_order", ctypes.c_int),
            ("bitmap_pad", ctypes.c_int),
            ("depth", ctypes.c_int),
            ("bytes_per_line", ctypes.c_int),
            ("bits_per_pixel", ctypes.c_int),
            ("red_mask", ctypes.c_ulong),
            ("green_mask", ctypes.c_ulong),
            ("blue_mask", ctypes.c_ulong),
            ("obdata", ctypes.c_void_p),
            ("f", Funcs),
        ]

    x11.XOpenDisplay.argtypes = [ctypes.c_char_p]
    x11.XOpenDisplay.restype = ctypes.c_void_p
    x11.XDefaultScreen.argtypes = [ctypes.c_void_p]
    x11.XDefaultScreen.restype = ctypes.c_int
    x11.XRootWindow.argtypes = [ctypes.c_void_p, ctypes.c_int]
    x11.XRootWindow.restype = ctypes.c_ulong
    x11.XDisplayWidth.argtypes = [ctypes.c_void_p, ctypes.c_int]
    x11.XDisplayWidth.restype = ctypes.c_int
    x11.XDisplayHeight.argtypes = [ctypes.c_void_p, ctypes.c_int]
    x11.XDisplayHeight.restype = ctypes.c_int
    x11.XGetImage.argtypes = [
        ctypes.c_void_p,
        ctypes.c_ulong,
        ctypes.c_int,
        ctypes.c_int,
        ctypes.c_uint,
        ctypes.c_uint,
        ctypes.c_ulong,
        ctypes.c_int,
    ]
    x11.XGetImage.restype = ctypes.POINTER(XImage)
    x11.XCloseDisplay.argtypes = [ctypes.c_void_p]

    display = x11.XOpenDisplay(None)
    if not display:
        return 1
    screen = x11.XDefaultScreen(display)
    root = x11.XRootWindow(display, screen)
    width = x11.XDisplayWidth(display, screen)
    height = x11.XDisplayHeight(display, screen)
    if width < 1 or height < 1 or width * height > 20000000:
        x11.XCloseDisplay(display)
        return 1
    image = x11.XGetImage(display, root, 0, 0, width, height, ctypes.c_ulong(-1).value, 2)
    if not image:
        x11.XCloseDisplay(display)
        return 1
    info = image.contents
    bpp = info.bits_per_pixel
    if bpp not in (24, 32) or not info.data or info.bytes_per_line < 1:
        x11.XCloseDisplay(display)
        return 1
    stride = info.bytes_per_line
    buf = ctypes.string_at(info.data, stride * info.height)
    rshift, rbits = _mask_shift(int(info.red_mask))
    gshift, gbits = _mask_shift(int(info.green_mask))
    bshift, bbits = _mask_shift(int(info.blue_mask))
    pixels = bytearray(width * height * 3)
    out = 0
    little = info.byte_order == 0
    sample = 4 if bpp == 32 else 3
    for y in range(height):
        row = y * stride
        for x in range(width):
            start = row + x * sample
            pixel = int.from_bytes(buf[start : start + sample], "little" if little else "big")
            pixels[out] = ((pixel & info.red_mask) >> rshift) * 255 // ((1 << rbits) - 1)
            pixels[out + 1] = ((pixel & info.green_mask) >> gshift) * 255 // ((1 << gbits) - 1)
            pixels[out + 2] = ((pixel & info.blue_mask) >> bshift) * 255 // ((1 << bbits) - 1)
            out += 3
    x11.XCloseDisplay(display)
    partial = path + ".partial"
    with open(partial, "wb") as handle:
        handle.write(_png(width, height, bytes(pixels)))
    os.replace(partial, path)
    return 0


def main(argv: list[str]) -> int:
    if len(argv) != 2 or argv[1] == "":
        return 2
    try:
        return capture(argv[1])
    except Exception:
        partial = argv[1] + ".partial"
        if os.path.exists(partial):
            try:
                os.remove(partial)
            except OSError:
                pass
        return 1


if __name__ == "__main__":
    sys.exit(main(sys.argv))
