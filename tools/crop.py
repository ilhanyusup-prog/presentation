"""Cut a region out of a reference page into an image asset.

    python tools/crop.py 5 60 238.5 145.5 324 assets/icon_permits.png

Arguments are the 1 based page number, the reference rectangle
(left top right bottom, in reference points) and the output path. Used for the
line art icons and for illustration clusters that are not worth rebuilding as
shapes; everything structural stays a native PowerPoint shape.

The crop is rendered at high resolution so it stays crisp when the deck is
printed or zoomed.
"""

import argparse

import pymupdf

from reference import open_reference


def crop(page, rect, path, dpi=600, trim=False):
    pixmap = page.get_pixmap(dpi=dpi, clip=pymupdf.Rect(*rect))
    if trim:
        pixmap.set_alpha(bytes(255 for _ in range(pixmap.width * pixmap.height)))
    pixmap.save(path)
    return pixmap


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("page", type=int)
    parser.add_argument("left", type=float)
    parser.add_argument("top", type=float)
    parser.add_argument("right", type=float)
    parser.add_argument("bottom", type=float)
    parser.add_argument("out")
    parser.add_argument("--dpi", type=int, default=600)
    parser.add_argument("--reference", default=None)
    args = parser.parse_args()

    page = open_reference(args.reference)[args.page - 1]
    pixmap = crop(
        page, (args.left, args.top, args.right, args.bottom), args.out, args.dpi
    )
    print(f"wrote {args.out} {pixmap.width}x{pixmap.height}")


if __name__ == "__main__":
    main()
