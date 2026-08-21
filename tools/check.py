"""Compare built slides against the reference document.

    python tools/check.py 7          # check slide 7 on its own
    python tools/check.py --all      # check every slide that exists

For each slide it prints how far every text run sits from its reference
position, the mean per pixel difference against the reference page, and writes a
side by side image to /tmp/check/. It needs LibreOffice (`soffice`) to turn the
deck into a PDF, and the reference PDF (see tools/reference.py for the path).

A mean difference of 2-4 out of 255 is what font antialiasing and image
resampling alone produce, so anything in that range means the page matches.
Each slide is built on its own, so a page under construction never blocks the
check of another one.
"""

import argparse
import subprocess
import sys
from pathlib import Path

import numpy as np
import pymupdf
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(Path(__file__).resolve().parent))

from build_presentation import build  # noqa: E402
from reference import open_reference  # noqa: E402

OUT = Path("/tmp/check")
SCALE = 2 / 3


def render(number):
    """Build just this slide and return it as a PDF page."""
    OUT.mkdir(parents=True, exist_ok=True)
    pptx = OUT / f"slide_{number:02d}.pptx"
    pdf = pptx.with_suffix(".pdf")
    pdf.unlink(missing_ok=True)
    build(output=pptx, numbers=[number])
    subprocess.run(
        [
            "soffice",
            # a private profile per slide, so several checks can run at once
            f"-env:UserInstallation=file:///tmp/check/soffice_{number:02d}",
            "--headless",
            "--convert-to",
            "pdf",
            str(pptx),
            "--outdir",
            str(OUT),
        ],
        check=True,
        capture_output=True,
    )
    return pymupdf.open(pdf)[0]


def spans(page):
    out = []
    for block in page.get_text("dict")["blocks"]:
        if block["type"] != 0:
            continue
        for line in block["lines"]:
            for span in line["spans"]:
                key = "".join(span["text"].split())
                if key:
                    out.append((key, span["bbox"], span["size"]))
    return out


def compare_text(reference, built):
    print("    text runs (reference position at 2/3 scale vs built):")
    built_by_key = {}
    for key, bbox, type_size in built:
        built_by_key.setdefault(key, []).append((bbox, type_size))

    worst = 0.0
    for key, bbox, type_size in reference:
        candidates = built_by_key.get(key)
        if not candidates:
            print(f"      MISSING  {key[:46]!r}")
            continue
        expected = [value * SCALE for value in bbox]
        got, got_size = min(
            candidates,
            key=lambda item: abs(item[0][0] - expected[0])
            + abs(item[0][1] - expected[1]),
        )
        delta = (got[0] - expected[0], got[1] - expected[1])
        worst = max(worst, abs(delta[0]), abs(delta[1]))
        flag = "  <-- off" if max(abs(delta[0]), abs(delta[1])) > 2 else ""
        print(
            f"      dx {delta[0]:+6.1f}  dy {delta[1]:+6.1f}  "
            f"size {type_size * SCALE:5.1f}/{got_size:<5.1f} {key[:40]!r}{flag}"
        )

    for key in sorted(set(built_by_key) - {key for key, _, _ in reference}):
        print(f"      extra    {key[:46]!r}")
    return worst


def compare_pixels(reference_page, built_page, number):
    ref = reference_page.get_pixmap(dpi=72)
    out = built_page.get_pixmap(dpi=108)
    a = Image.frombytes("RGB", (ref.width, ref.height), ref.samples)
    b = Image.frombytes("RGB", (out.width, out.height), out.samples).resize(a.size)

    canvas = Image.new("RGB", (a.width * 2 + 24, a.height), (255, 255, 255))
    canvas.paste(a, (0, 0))
    canvas.paste(b, (a.width + 24, 0))
    path = OUT / f"p{number:02d}.png"
    canvas.save(path)

    diff = np.abs(np.asarray(a).astype(int) - np.asarray(b).astype(int)).mean()
    return diff, path


def check(number, reference):
    print(f"=== slide {number}")
    built = render(number)
    reference_page = reference[number - 1]
    worst = compare_text(spans(reference_page), spans(built))
    diff, path = compare_pixels(reference_page, built, number)
    print(f"    worst text offset {worst:.1f} pt, mean pixel difference {diff:.2f}")
    print(f"    side by side (reference left, built right): {path}")
    return diff


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("slides", nargs="*", type=int)
    parser.add_argument("--all", action="store_true")
    parser.add_argument("--reference", default=None)
    args = parser.parse_args()

    reference = open_reference(args.reference)
    numbers = args.slides
    if args.all or not numbers:
        numbers = [
            number
            for number in range(1, reference.page_count + 1)
            if (ROOT / "deck" / f"slide_{number:02d}.py").exists()
        ]
    for number in numbers:
        check(number, reference)


if __name__ == "__main__":
    main()
