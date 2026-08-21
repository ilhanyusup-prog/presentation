"""Rebuild the Almuhib solar proposal deck as an editable PowerPoint file.

The reference document is 810 x 1440 pt, so the deck keeps its portrait 9:16
proportions: 7.5 x 13.333 in, which is the reference at exactly two thirds
scale. Each page lives in its own module under `deck/`, written in reference
units against `deck/kit.py`.

Run:  python build_presentation.py   ->  presentation.pptx
"""

from importlib import import_module
from pathlib import Path

from pptx import Presentation

from deck.kit import SLIDE_COUNT, SLIDE_H, SLIDE_W

OUTPUT = Path(__file__).resolve().parent / "presentation.pptx"


def build(output=OUTPUT, numbers=None):
    """Write the deck. `numbers` builds just those pages, for a quick check."""
    prs = Presentation()
    prs.slide_width = SLIDE_W
    prs.slide_height = SLIDE_H
    for number in numbers or range(1, SLIDE_COUNT + 1):
        try:
            module = import_module(f"deck.slide_{number:02d}")
        except ModuleNotFoundError:
            if numbers:
                raise
            continue  # page not built yet
        module.build(prs)
    prs.save(output)
    return output, len(prs.slides._sldIdLst)


if __name__ == "__main__":
    path, count = build()
    print(f"saved {path} with {count} slide(s)")
