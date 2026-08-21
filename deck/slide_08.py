"""Slide 8 - track record.

Light page: the eyebrow sits over a gradient filled "100+", the bold claim and a
short paragraph, then a three by three grid of empty image slots fills the lower
two thirds of the page.
"""

from pptx.dml.color import RGBColor

from .kit import (
    ARIAL_BLACK,
    MARGIN,
    MUTED,
    TRACK_DISPLAY,
    add_h1,
    add_text,
    chrome,
    gradient_text,
    image_slot,
    lines,
    mid,
    plain,
)

# Ends of the left to right gradient that fills the "100+", sampled off the
# rendered reference page at the two ends of the type.
NUMBER_START = RGBColor(0x0C, 0x6B, 0x4B)
NUMBER_END = RGBColor(0x10, 0x93, 0x64)

SLOT_COLUMNS = ((60.0, 207.8), (285.8, 208.4), (512.2, 207.8))
SLOT_ROWS = (861.0, 1035.0, 1209.0)
SLOT_HEIGHT = 156.0
SLOT_RADIUS = 21.0
FIRST_SLOT = 3  # the deck numbers its picture frames across the whole document


def build(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])  # blank
    chrome(slide, 8, "TRACK RECORD")

    number = add_text(
        slide,
        "Project count",
        MARGIN,
        mid(135.7, 273.2),
        400,
        [plain("100+")],
        type_size=97.5,
        font=ARIAL_BLACK,
        tracking=TRACK_DISPLAY,
    )
    gradient_text(number.text_frame.paragraphs[0].runs[0], NUMBER_START, NUMBER_END)

    add_h1(slide, "projects in Emirates", (260.3, 305.6))

    add_text(
        slide,
        "Communities lead",
        MARGIN,
        mid(347.1, 424.1) - 5.2,  # optical centre of the two line block
        620,
        lines("The primary communities where we easily", "secure permits."),
        type_size=30,
        line_height=43.5,
    )
    add_text(
        slide,
        "Community names",
        MARGIN,
        mid(469.5, 496.3),
        680,
        [
            plain(
                "Emaar \u00b7 Nakheel \u00b7 Meraas \u00b7 Damac \u00b7 Dubai Holding \u00b7 Sobha"
            )
        ],
        type_size=24,
        color=MUTED,
    )

    slot = FIRST_SLOT
    for top in SLOT_ROWS:
        for left, width in SLOT_COLUMNS:
            name = f"IMG-{slot:02d}"
            image_slot(
                slide,
                name,
                left,
                top,
                width,
                SLOT_HEIGHT,
                name,
                radius=SLOT_RADIUS,
            )
            slot += 1
    return slide
