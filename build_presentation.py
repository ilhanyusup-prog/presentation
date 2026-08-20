"""Rebuild the Almuhib solar proposal deck as an editable PowerPoint file.

The reference document is 810 x 1440 pt, so the deck keeps its portrait 9:16
proportions: 7.5 x 13.333 in, which is the reference at exactly two thirds
scale. Every position, size, type size and letter spacing below is written in
reference units and converted once through `u()` / `size()`, so the code can be
read straight against the source page.

Run:  python build_presentation.py   ->  presentation.pptx
"""

from io import BytesIO
from pathlib import Path

from PIL import Image
from pptx import Presentation
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import MSO_ANCHOR, PP_ALIGN
from pptx.oxml import parse_xml
from pptx.oxml.ns import nsdecls
from pptx.util import Inches, Pt

ROOT = Path(__file__).resolve().parent
ASSETS = ROOT / "assets"
OUTPUT = ROOT / "presentation.pptx"

REF_W, REF_H = 810.0, 1440.0  # reference page, points
SLIDE_W, SLIDE_H = Inches(7.5), Inches(13.333)  # 9:16 portrait
SCALE = 2 / 3  # 540 pt / 810 pt

# Palette sampled from the reference page.
INK = RGBColor(0x08, 0x24, 0x1E)  # page background
WHITE = RGBColor(0xFF, 0xFF, 0xFF)
MINT = RGBColor(0x6F, 0xE3, 0x9C)  # accent
MINT_DEEP = RGBColor(0x0F, 0x9D, 0x66)  # accent gradient start
RAIL_DEEP = RGBColor(0x08, 0x38, 0x2A)  # progress fill gradient start
RAIL_TRACK = RGBColor(0xE2, 0xEF, 0xE7)
PANEL_GREY = RGBColor(0x7F, 0x7F, 0x7F)  # 7.8% veil behind the logo
EYEBROW_GREY = RGBColor(0xA3, 0xAB, 0xAA)
LABEL_GREY = RGBColor(0x85, 0x92, 0x90)
BODY_GREY = RGBColor(0xAA, 0xB3, 0xB1)
NUMBER_GREY = RGBColor(0xBC, 0xC2, 0xC1)

ARIAL = "Arial"
ARIAL_BLACK = "Arial Black"

# Uppercase micro-labels in the source are tracked out by 0.18 em, the display
# headline is tightened by 0.03 em.
TRACK_LABEL = 0.18
TRACK_DISPLAY = -0.03


# --------------------------------------------------------------------------- #
# reference units
# --------------------------------------------------------------------------- #
def u(value):
    """Convert a length in reference points to a slide length."""
    return Pt(value * SCALE)


def size(value):
    """Convert a type size in reference points to a slide type size."""
    return Pt(value * SCALE)


def mid(top, bottom):
    """Centre of a span measured off the reference page."""
    return (top + bottom) / 2


# --------------------------------------------------------------------------- #
# low level helpers
# --------------------------------------------------------------------------- #
def set_opacity(color_format, opacity):
    """Apply `opacity` (0.0 - 1.0) to an already assigned RGB colour.

    python-pptx has no public API for fill transparency, so the alpha child is
    written straight onto the <a:srgbClr> element.
    """
    color_format._color._xClr.append(
        parse_xml('<a:alpha %s val="%d"/>' % (nsdecls("a"), round(opacity * 100000)))
    )


def add_shape(slide, name, left, top, width, height, autoshape=MSO_SHAPE.RECTANGLE):
    shape = slide.shapes.add_shape(autoshape, u(left), u(top), u(width), u(height))
    shape.name = name
    shape.line.fill.background()
    shape.shadow.inherit = False
    return shape


def add_pill(slide, name, left, top, width, height):
    """Fully rounded rectangle, the shape the accent dash and the rail use."""
    shape = add_shape(
        slide, name, left, top, width, height, MSO_SHAPE.ROUNDED_RECTANGLE
    )
    shape.adjustments[0] = 0.5
    return shape


def fill_solid(shape, color, opacity=None):
    shape.fill.solid()
    shape.fill.fore_color.rgb = color
    if opacity is not None:
        set_opacity(shape.fill.fore_color, opacity)
    return shape


def fill_gradient(shape, start, end, angle=0):
    """Two stop linear gradient. `angle` 0 = left to right, 270 = top to bottom."""
    shape.fill.gradient()
    shape.fill.gradient_angle = angle
    first, last = shape.fill.gradient_stops[0], shape.fill.gradient_stops[1]
    first.position, last.position = 0.0, 1.0
    first.color.rgb, last.color.rgb = start, end
    return shape


def add_text(
    slide,
    name,
    left,
    center_y,
    width,
    lines,
    *,
    type_size,
    font=ARIAL,
    bold=False,
    color=WHITE,
    align=PP_ALIGN.LEFT,
    tracking=None,
    line_height=None,
):
    """Place a text box whose type block is vertically centred on `center_y`.

    `lines` is a list of lines, each line a list of (text, style) run tuples.
    Centring the block instead of anchoring its top keeps the type on the same
    optical line across renderers, which measure ascenders slightly differently.
    """
    step = line_height or type_size * 1.25
    height = len(lines) * step + 12
    box = slide.shapes.add_textbox(
        u(left), u(center_y - height / 2), u(width), u(height)
    )
    box.name = name
    frame = box.text_frame
    # Boxes are sized wider than their longest line, so wrapping stays on: with
    # wrapping off some renderers grow the box symmetrically and the left edge
    # of the type no longer matches the column.
    frame.word_wrap = True
    frame.margin_left = frame.margin_right = 0
    frame.margin_top = frame.margin_bottom = 0
    frame.vertical_anchor = MSO_ANCHOR.MIDDLE

    for index, runs in enumerate(lines):
        paragraph = frame.paragraphs[0] if index == 0 else frame.add_paragraph()
        paragraph.alignment = align
        if line_height:
            paragraph.line_spacing = u(line_height)
        for text, style in runs:
            run = paragraph.add_run()
            run.text = text
            run_size = style.get("size", type_size)
            run.font.name = style.get("font", font)
            run.font.size = size(run_size)
            run.font.bold = style.get("bold", bold)
            run.font.color.rgb = style.get("color", color)
            track = style.get("tracking", tracking)
            if track:
                spacing = track * run_size * SCALE  # points of extra letter spacing
                run.font._rPr.set("spc", str(round(spacing * 100)))
    return box


def plain(text):
    """Shorthand for a single unstyled run."""
    return [(text, {})]


def veiled(path, profile):
    """Return the image as PNG bytes, dimmed top and bottom like the reference.

    The source page lays an ink gradient over the render so the headline and the
    meta block read against it. Gradient shapes with per-stop transparency only
    survive in PowerPoint itself, so the measured curve is baked into the alpha
    channel of the picture instead, over the same flat ink background.

    `profile` is a sequence of (fraction of image height, opacity) samples taken
    off the reference page; values in between are interpolated linearly.
    """
    image = Image.open(path).convert("RGBA")
    stops = sorted(profile)

    def opacity_at(fraction):
        for (y0, a0), (y1, a1) in zip(stops, stops[1:]):
            if fraction <= y1:
                span = y1 - y0
                return a0 if span == 0 else a0 + (a1 - a0) * (fraction - y0) / span
        return stops[-1][1]

    column = Image.new("L", (1, image.height))
    column.putdata(
        [
            round(255 * opacity_at(y / (image.height - 1)))
            for y in range(image.height)
        ]
    )
    image.putalpha(column.resize(image.size, Image.BILINEAR))

    buffer = BytesIO()
    image.save(buffer, format="PNG")
    buffer.seek(0)
    return buffer


# --------------------------------------------------------------------------- #
# slide 1 - cover
# --------------------------------------------------------------------------- #
# Ink veil over the hero render, sampled off the reference page as
# (fraction of page height, opacity of the render).
HERO_VEIL = (
    (0.000, 0.54),
    (0.042, 0.75),
    (0.083, 0.77),
    (0.125, 0.82),
    (0.167, 0.89),
    (0.208, 0.94),
    (0.250, 0.97),
    (0.292, 1.00),
    (0.542, 1.00),
    (0.583, 0.97),
    (0.625, 0.83),
    (0.667, 0.76),
    (0.708, 0.64),
    (0.750, 0.43),
    (0.792, 0.29),
    (0.833, 0.21),
    (0.875, 0.20),
    (0.917, 0.07),
    (0.958, 0.00),
    (1.000, 0.00),
)

COL_X = 48.0  # left margin of the text column
COL2_X = 411.8  # second meta column
RAIL_X, RAIL_W = 772.5, 4.5  # progress rail, right edge
RAIL_TOP, RAIL_H = 75.0, 1290.0
RAIL_FILL_H = RAIL_H / 13  # slide 1 of 13
DOT_X, DOT_Y, DOT_D = RAIL_X + RAIL_W / 2, RAIL_TOP + RAIL_FILL_H, 11.6


def build_slide_1(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])  # blank

    fill_solid(add_shape(slide, "Background", 0, 0, REF_W, REF_H), INK)

    # The render bleeds a fraction of a point past both side edges, as it does
    # on the reference page, so its own aspect ratio is preserved.
    hero = slide.shapes.add_picture(
        veiled(ASSETS / "hero_villa.png", HERO_VEIL),
        u(-0.21),
        0,
        u(810.75),
        u(REF_H),
    )
    hero.name = "Hero render - villa with solar array"

    fill_solid(
        add_shape(
            slide, "Logo panel", 339.75, 75, 130.5, 105, MSO_SHAPE.ROUNDED_RECTANGLE
        ),
        PANEL_GREY,
        opacity=0.078,
    ).adjustments[0] = 12 / 105  # 12 pt corner radius
    logo = slide.shapes.add_picture(
        str(ASSETS / "logo_apm.png"), u(339.75), u(75.14), u(130.5), u(105)
    )
    logo.name = "Almuhib Solar Energy logo"

    fill_gradient(
        add_pill(slide, "Accent dash", COL_X, 1020, 42, 6), MINT_DEEP, MINT, angle=0
    )
    add_text(
        slide,
        "Eyebrow - reference",
        108,
        mid(1010.7, 1034.2),
        520,
        [plain("SOLAR PROPOSAL \u00b7 NFX-[0000-0000]")],
        type_size=21,
        bold=True,
        color=EYEBROW_GREY,
        tracking=TRACK_LABEL,
    )

    add_text(
        slide,
        "Headline",
        COL_X,
        mid(1050.0, 1232.2) + 6,  # optical centre of the two line block
        640,
        [plain("Power your villa"), plain("with solar")],
        type_size=75,
        font=ARIAL_BLACK,
        tracking=TRACK_DISPLAY,
        line_height=76.5,
    )

    add_text(
        slide,
        "Label - prepared for",
        COL_X,
        mid(1271.7, 1295.2),
        320,
        [plain("PREPARED FOR")],
        type_size=21,
        bold=True,
        color=LABEL_GREY,
        tracking=TRACK_LABEL,
    )
    add_text(
        slide,
        "Client name",
        COL_X,
        mid(1303.3, 1336.9),
        320,
        [plain("Mr Ahmed")],
        type_size=30,
        bold=True,
    )
    add_text(
        slide,
        "Client community",
        COL_X,
        mid(1344.8, 1371.6),
        320,
        [plain("Jumeirah Golf Estates")],
        type_size=24,
        color=BODY_GREY,
    )
    add_text(
        slide,
        "Proposal date",
        COL_X,
        mid(1390.5, 1417.3),
        320,
        [plain("Aug 19, 2026")],
        type_size=24,
        color=LABEL_GREY,
    )

    fill_solid(
        add_shape(slide, "Meta divider", 377.25, 1272, 1.5, 154.5), WHITE, opacity=0.28
    )

    add_text(
        slide,
        "Label - system size",
        COL2_X,
        mid(1271.7, 1295.2),
        290,
        [plain("SYSTEM SIZE")],
        type_size=21,
        bold=True,
        color=LABEL_GREY,
        tracking=TRACK_LABEL,
    )
    add_text(
        slide,
        "System size",
        COL2_X,
        mid(1307.1, 1423.1) - 4,  # optical centre of the three line block
        290,
        [
            plain("12 kW system \u00b7"),
            [
                ("covers ", {}),
                ("84%", {"font": ARIAL_BLACK, "color": MINT}),
                (" of your", {}),
            ],
            plain("home"),
        ],
        type_size=30,
        line_height=41,
    )

    fill_solid(
        add_pill(slide, "Progress rail track", RAIL_X, RAIL_TOP, RAIL_W, RAIL_H),
        RAIL_TRACK,
        opacity=0.30,
    )
    fill_gradient(
        add_pill(slide, "Progress rail fill", RAIL_X, RAIL_TOP, RAIL_W, RAIL_FILL_H),
        RAIL_DEEP,
        MINT,
        angle=270,
    )
    for scale, opacity in ((2.1, 0.10), (1.55, 0.20)):
        diameter = DOT_D * scale
        fill_solid(
            add_shape(
                slide,
                f"Progress dot glow {scale:g}x",
                DOT_X - diameter / 2,
                DOT_Y - diameter / 2,
                diameter,
                diameter,
                MSO_SHAPE.OVAL,
            ),
            MINT,
            opacity=opacity,
        )
    fill_solid(
        add_shape(
            slide,
            "Progress dot",
            DOT_X - DOT_D / 2,
            DOT_Y - DOT_D / 2,
            DOT_D,
            DOT_D,
            MSO_SHAPE.OVAL,
        ),
        MINT,
    )

    add_text(
        slide,
        "Slide number",
        657.5,
        mid(160.2, 183.7),
        100,
        [plain("01")],
        type_size=21,
        bold=True,
        color=NUMBER_GREY,
        align=PP_ALIGN.RIGHT,
    )

    return slide


def build():
    prs = Presentation()
    prs.slide_width = SLIDE_W
    prs.slide_height = SLIDE_H
    build_slide_1(prs)
    prs.save(OUTPUT)
    return OUTPUT


if __name__ == "__main__":
    print(f"saved {build()}")
