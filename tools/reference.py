"""Print the measurable contents of a reference page.

    python tools/reference.py 7            # text, drawings and images of page 7
    python tools/reference.py 7 --text     # text spans only

Coordinates are reference points (the page is 810 x 1440), which is the unit the
slide modules are written in. Colours are printed as hex so they can be dropped
straight into a palette.
"""

import argparse
import os
from pathlib import Path

import pymupdf

DEFAULT_REFERENCE = os.environ.get(
    "REFERENCE_PDF",
    str(Path.home() / ".cursor/projects/workspace/uploads/Almuhab_85e4.pdf"),
)


def open_reference(path=None):
    return pymupdf.open(path or DEFAULT_REFERENCE)


def hexcolor(triple):
    if triple is None:
        return "-"
    return "#%02X%02X%02X" % tuple(round(channel * 255) for channel in triple)


def box(rect):
    return "(%7.1f %7.1f %7.1f %7.1f)" % tuple(rect)


def dump_text(page):
    print("--- text spans: bbox, font, size, colour, text")
    for block in page.get_text("dict")["blocks"]:
        if block["type"] != 0:
            continue
        for line in block["lines"]:
            for span in line["spans"]:
                print(
                    box(span["bbox"]),
                    f'{span["font"]:18}',
                    f'{span["size"]:6.2f}',
                    "#%06X" % span["color"],
                    repr(span["text"]),
                )


def dump_drawings(page):
    print("--- drawings: kind, bbox, fill, stroke, width, opacity, item types")
    for index, drawing in enumerate(page.get_drawings()):
        kinds = sorted({item[0] for item in drawing["items"]})
        print(
            f'{index:4} {drawing["type"]:2}',
            box(drawing["rect"]),
            "fill",
            f'{hexcolor(drawing["fill"]):8}',
            "stroke",
            f'{hexcolor(drawing["color"]):8}',
            "w",
            f'{drawing["width"] or 0:5.2f}',
            "fop",
            f'{drawing.get("fill_opacity") or 0:4.2f}',
            "sop",
            f'{drawing.get("stroke_opacity") or 0:4.2f}',
            f'items {len(drawing["items"]):4}',
            kinds,
        )


def dump_images(page):
    print("--- images: xref, pixels, bbox  (xref 0 = gradient or masked fill)")
    for info in page.get_image_info(xrefs=True):
        print(
            f'{info["xref"]:6}',
            f'{info["width"]:5}x{info["height"]:<5}',
            box(info["bbox"]),
        )


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("page", type=int, help="1 based page number")
    parser.add_argument("--reference", default=None)
    parser.add_argument("--text", action="store_true", help="text spans only")
    args = parser.parse_args()

    page = open_reference(args.reference)[args.page - 1]
    print(f"=== reference page {args.page}  {box(page.rect)}")
    dump_text(page)
    if not args.text:
        dump_drawings(page)
        dump_images(page)


if __name__ == "__main__":
    main()
