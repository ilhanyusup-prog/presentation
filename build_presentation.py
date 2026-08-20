"""Rebuild the Almuhib solar proposal deck as an editable 16:9 PowerPoint file.

The source proposal is a 9:16 portrait document (810 x 1440 pt). Every slide is
re-composed here for a 13.333 x 7.5 in (960 x 540 pt) canvas while keeping the
original type scale, palette, spacing rhythm and block order.

Slide 1 (cover): the hero render moves from the top of the portrait page to the
right side of the landscape frame; the eyebrow, headline and the two meta
columns keep their original left-aligned stack at 0.70 of the source scale.

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
from pptx.util import Pt

ROOT = Path(__file__).resolve().parent
ASSETS = ROOT / "assets"
OUTPUT = ROOT / "presentation.pptx"

SLIDE_W = Pt(960)  # 13.333 in
SLIDE_H = Pt(540)  # 7.5 in

# Palette sampled from the source document.
INK = RGBColor(0x08, 0x24, 0x1E)  # page background
WHITE = RGBColor(0xFF, 0xFF, 0xFF)
MINT = RGBColor(0x6F, 0xE3, 0x9C)  # accent
MINT_DEEP = RGBColor(0x0F, 0x9D, 0x66)  # accent gradient start
RAIL_DEEP = RGBColor(0x08, 0x38, 0x2A)  # progress fill gradient start
RAIL_TRACK = RGBColor(0xE2, 0xEF, 0xE7)
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
    shape = slide.shapes.add_shape(autoshape, Pt(left), Pt(top), Pt(width), Pt(height))
    shape.name = name
    shape.line.fill.background()
    shape.shadow.inherit = False
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
    size,
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
    step = line_height or size * 1.25
    height = len(lines) * step + 8
    box = slide.shapes.add_textbox(
        Pt(left), Pt(center_y - height / 2), Pt(width), Pt(height)
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
            paragraph.line_spacing = Pt(line_height)
        for text, style in runs:
            run = paragraph.add_run()
            run.text = text
            run_size = style.get("size", size)
            run.font.name = style.get("font", font)
            run.font.size = Pt(run_size)
            run.font.bold = style.get("bold", bold)
            run.font.color.rgb = style.get("color", color)
            track = style.get("tracking", tracking)
            if track:
                run.font._rPr.set("spc", str(round(track * run_size * 100)))
    return box


def plain(text):
    """Shorthand for a single unstyled run."""
    return [(text, {})]


def faded_left_edge(path, fade_fraction):
    """Return the image as PNG bytes, its left edge faded out to transparent.

    The render has to dissolve into the flat background of the text column. A
    gradient shape on top of it would work in PowerPoint but not in every
    renderer, so the alpha ramp is baked into the picture instead.
    """
    image = Image.open(path).convert("RGBA")
    mask = image.getchannel("A")
    pixels = mask.load()
    fade_px = round(image.width * fade_fraction)
    for x in range(min(fade_px, image.width)):
        t = x / fade_px
        factor = t * t * (3 - 2 * t)  # smoothstep, no visible seam at either end
        for y in range(image.height):
            pixels[x, y] = round(pixels[x, y] * factor)
    image.putalpha(mask)
    buffer = BytesIO()
    image.save(buffer, format="PNG")
    buffer.seek(0)
    return buffer


# --------------------------------------------------------------------------- #
# slide 1 - cover
# --------------------------------------------------------------------------- #
# Left text column, mirroring the 48 pt margin of the source at 0.70 scale.
COL_X = 56.0
COL2_X = 310.7
COL_RIGHT = 497.3

# Hero panel: the 941 x 1672 px render is cropped to rows 205-1221, the band
# that holds the whole sun ring and the villa, and bled off the right, top and
# bottom edges of the slide.
HERO_X = 460.0
HERO_W = SLIDE_W.pt - HERO_X
HERO_CROP = dict(left=0.0, right=0.0, top=0.1226, bottom=0.2697)
HERO_FADE = 190.0  # width of the dissolve into the text column, in points

# Progress rail on the right edge: slide 1 of 13.
RAIL_X = 920.0
RAIL_W = 4.0
RAIL_TOP = 28.0
RAIL_H = 484.0
RAIL_FILL_H = RAIL_H / 13
DOT_Y = RAIL_TOP + RAIL_FILL_H


def build_slide_1(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])  # blank

    fill_solid(add_shape(slide, "Background", 0, 0, SLIDE_W.pt, SLIDE_H.pt), INK)

    hero = slide.shapes.add_picture(
        faded_left_edge(ASSETS / "hero_villa.png", HERO_FADE / HERO_W),
        Pt(HERO_X),
        0,
        Pt(HERO_W),
        SLIDE_H,
    )
    hero.name = "Hero render - villa with solar array"
    hero.crop_left = HERO_CROP["left"]
    hero.crop_right = HERO_CROP["right"]
    hero.crop_top = HERO_CROP["top"]
    hero.crop_bottom = HERO_CROP["bottom"]

    logo = slide.shapes.add_picture(
        str(ASSETS / "logo_apm.png"), Pt(COL_X), Pt(34), Pt(100), Pt(80.4)
    )
    logo.name = "Almuhib Solar Energy logo"

    fill_gradient(
        add_shape(
            slide, "Accent dash", COL_X, 234.5, 29.4, 4.2, MSO_SHAPE.ROUNDED_RECTANGLE
        ),
        MINT_DEEP,
        MINT,
        angle=0,
    ).adjustments[0] = 0.5

    add_text(
        slide,
        "Eyebrow - reference",
        98.0,
        236.2,
        400,
        [plain("SOLAR PROPOSAL \u00b7 NFX-[0000-0000]")],
        size=14.5,
        bold=True,
        color=EYEBROW_GREY,
        tracking=TRACK_LABEL,
    )

    add_text(
        slide,
        "Headline",
        COL_X,
        323.8,
        460,
        [plain("Power your villa"), plain("with solar")],
        size=52.5,
        font=ARIAL_BLACK,
        color=WHITE,
        tracking=TRACK_DISPLAY,
        line_height=53.6,
    )

    add_text(
        slide,
        "Label - prepared for",
        COL_X,
        419.0,
        220,
        [plain("PREPARED FOR")],
        size=14.5,
        bold=True,
        color=LABEL_GREY,
        tracking=TRACK_LABEL,
    )
    add_text(
        slide,
        "Client name",
        COL_X,
        444.6,
        220,
        [plain("Mr Ahmed")],
        size=21,
        bold=True,
    )
    add_text(
        slide,
        "Client community",
        COL_X,
        471.3,
        220,
        [plain("Jumeirah Golf Estates")],
        size=16.8,
        color=BODY_GREY,
    )
    add_text(
        slide,
        "Proposal date",
        COL_X,
        503.2,
        220,
        [plain("Aug 19, 2026")],
        size=16.8,
        color=LABEL_GREY,
    )

    fill_solid(
        add_shape(slide, "Meta divider", 286.4, 410.9, 1.2, 108.2), WHITE, opacity=0.28
    )

    add_text(
        slide,
        "Label - system size",
        COL2_X,
        419.0,
        220,
        [plain("SYSTEM SIZE")],
        size=14.5,
        bold=True,
        color=LABEL_GREY,
        tracking=TRACK_LABEL,
    )
    add_text(
        slide,
        "System size",
        COL2_X,
        473.3,
        COL_RIGHT - COL2_X + 20,
        [
            plain("12 kW system \u00b7"),
            [
                ("covers ", {}),
                ("84%", {"font": ARIAL_BLACK, "color": MINT}),
                (" of your", {}),
            ],
            plain("home"),
        ],
        size=21,
        line_height=28.7,
    )

    fill_solid(
        add_shape(
            slide,
            "Progress rail track",
            RAIL_X,
            RAIL_TOP,
            RAIL_W,
            RAIL_H,
            MSO_SHAPE.ROUNDED_RECTANGLE,
        ),
        RAIL_TRACK,
        opacity=0.30,
    ).adjustments[0] = 0.5

    fill_gradient(
        add_shape(
            slide,
            "Progress rail fill",
            RAIL_X,
            RAIL_TOP,
            RAIL_W,
            RAIL_FILL_H,
            MSO_SHAPE.ROUNDED_RECTANGLE,
        ),
        RAIL_DEEP,
        MINT,
        angle=270,
    ).adjustments[0] = 0.5

    for diameter, opacity in ((24, 0.10), (18, 0.20)):
        fill_solid(
            add_shape(
                slide,
                f"Progress dot glow {diameter}",
                RAIL_X + RAIL_W / 2 - diameter / 2,
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
            RAIL_X + RAIL_W / 2 - 5,
            DOT_Y - 5,
            10,
            10,
            MSO_SHAPE.OVAL,
        ),
        MINT,
    )

    add_text(
        slide,
        "Slide number",
        859.5,
        DOT_Y,
        50,
        [plain("01")],
        size=14.5,
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
