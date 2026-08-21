"""Shared toolkit for rebuilding the Almuhib proposal deck.

The reference document is 810 x 1440 pt, and the deck keeps those portrait 9:16
proportions at 7.5 x 13.333 in, which is the reference at exactly two thirds
scale. Every slide module writes positions, sizes, type sizes and letter
spacing in reference points and converts them once through `u()` and `size()`,
so the code can be read straight against the source page.
"""

from io import BytesIO
from pathlib import Path

from PIL import Image
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import MSO_ANCHOR, PP_ALIGN
from pptx.oxml import parse_xml
from pptx.oxml.ns import nsdecls, qn
from pptx.util import Inches, Pt

ROOT = Path(__file__).resolve().parent.parent
ASSETS = ROOT / "assets"

REF_W, REF_H = 810.0, 1440.0  # reference page, points
SLIDE_W, SLIDE_H = Inches(7.5), Inches(13.333)  # 9:16 portrait
SCALE = 2 / 3  # 540 pt / 810 pt

# Palette sampled from the reference pages.
PAPER = RGBColor(0xFF, 0xFF, 0xFF)
INK = RGBColor(0x06, 0x25, 0x1C)  # dark backgrounds and headings
GREEN = RGBColor(0x12, 0xA0, 0x6B)  # eyebrows, icon accents
GREEN_DEEP = RGBColor(0x0B, 0x5F, 0x45)
MINT = RGBColor(0x6F, 0xE3, 0x9C)
MINT_DEEP = RGBColor(0x0F, 0x9D, 0x66)
MINT_SOFT = RGBColor(0xC7, 0xF7, 0xD8)  # pills
TINT = RGBColor(0xF2, 0xFB, 0xF5)  # icon tiles, soft cards
LINE = RGBColor(0xE2, 0xEF, 0xE7)  # hairlines, progress rail track
MUTED = RGBColor(0x6B, 0x82, 0x79)  # secondary copy
SLOT = RGBColor(0xF5, 0xF5, 0xF5)  # empty image slots
SLOT_LABEL = RGBColor(0x40, 0x58, 0x4F)  # slot labels, on a light frame
SLOT_LABEL_DARK = RGBColor(0x06, 0x2A, 0x1F)  # slot labels, on a dark frame
WHITE = PAPER

ARIAL = "Arial"
ARIAL_BLACK = "Arial Black"
UI = "Segoe UI"  # the source tool labels empty image slots in its own UI font

# Uppercase micro-labels in the source are tracked out by 0.18 em, display
# headlines are tightened by 0.03 em.
TRACK_LABEL = 0.18
TRACK_DISPLAY = -0.03

# Shared page furniture, identical on every inner page.
MARGIN = 60.0  # left margin of the content column
CONTENT_R = 720.0  # right edge of the content column
LOGO_BOX = (60.0, 75.0, 72.0, 45.0)
EYEBROW_X = 156.0
EYEBROW_SPAN = (85.2, 108.7)
H1_SIZE = 40.5
RAIL_X, RAIL_W = 772.5, 4.5
RAIL_TOP, RAIL_H = 75.0, 1290.0
DOT_D = 11.6
SLIDE_COUNT = 13


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
# fills
# --------------------------------------------------------------------------- #
def set_opacity(color_format, opacity):
    """Apply `opacity` (0.0 - 1.0) to an already assigned RGB colour.

    python-pptx has no public API for fill transparency, so the alpha child is
    written straight onto the <a:srgbClr> element.
    """
    color_format._color._xClr.append(
        parse_xml('<a:alpha %s val="%d"/>' % (nsdecls("a"), round(opacity * 100000)))
    )


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


FILL_TAGS = tuple(
    qn(f"a:{tag}") for tag in ("noFill", "solidFill", "gradFill", "pattFill", "blipFill")
)


def _gradient_fill(start, end, angle):
    """A two stop linear <a:gradFill>, `angle` in degrees left to right."""
    return parse_xml(
        "<a:gradFill %s><a:gsLst>"
        '<a:gs pos="0"><a:srgbClr val="%s"/></a:gs>'
        '<a:gs pos="100000"><a:srgbClr val="%s"/></a:gs>'
        '</a:gsLst><a:lin ang="%d" scaled="0"/></a:gradFill>'
        % (nsdecls("a"), start, end, round(angle * 60000) % 21600000)
    )


def _replace_fill(element, fill):
    for child in list(element):
        if child.tag in FILL_TAGS:
            element.remove(child)
    element.insert(0, fill)


def gradient_text(run, start, end, angle=0):
    """Gradient filled type, the way the reference sets its big green numbers."""
    _replace_fill(run.font._rPr, _gradient_fill(start, end, angle))
    return run


def gradient_line(shape, start, end, width, angle=0, cap="rnd"):
    """Stroke a shape with a gradient, used for the ring and gauge arcs."""
    line = shape.line._get_or_add_ln()
    line.set("w", str(int(u(width))))
    line.set("cap", cap)
    _replace_fill(line, _gradient_fill(start, end, angle))
    return shape


def outline(shape, color, width=1.5, opacity=None):
    shape.line.color.rgb = color
    shape.line.width = u(width)
    if opacity is not None:
        set_opacity(shape.line.color, opacity)
    return shape


# --------------------------------------------------------------------------- #
# shapes
# --------------------------------------------------------------------------- #
def add_shape(slide, name, left, top, width, height, autoshape=MSO_SHAPE.RECTANGLE):
    """Autoshape with no fill, no outline and no shadow: a blank starting point."""
    shape = slide.shapes.add_shape(autoshape, u(left), u(top), u(width), u(height))
    shape.name = name
    shape.fill.background()
    shape.line.fill.background()
    shape.shadow.inherit = False
    return shape


def add_round(slide, name, left, top, width, height, radius):
    """Rounded rectangle with the corner radius given in reference points."""
    shape = add_shape(
        slide, name, left, top, width, height, MSO_SHAPE.ROUNDED_RECTANGLE
    )
    shape.adjustments[0] = radius / min(width, height)
    return shape


def add_pill(slide, name, left, top, width, height):
    """Fully rounded rectangle: accent dashes, bars, the progress rail."""
    shape = add_shape(
        slide, name, left, top, width, height, MSO_SHAPE.ROUNDED_RECTANGLE
    )
    shape.adjustments[0] = 0.5
    return shape


def add_oval(slide, name, center_x, center_y, diameter):
    return add_shape(
        slide,
        name,
        center_x - diameter / 2,
        center_y - diameter / 2,
        diameter,
        diameter,
        MSO_SHAPE.OVAL,
    )


def add_rule(slide, name, left, top, width, height=1.5, color=LINE):
    """Hairline rule."""
    return fill_solid(add_shape(slide, name, left, top, width, height), color)


def add_picture(slide, name, path, left, top, width, height):
    picture = slide.shapes.add_picture(
        str(path) if isinstance(path, (str, Path)) else path,
        u(left),
        u(top),
        u(width),
        u(height),
    )
    picture.name = name
    return picture


# --------------------------------------------------------------------------- #
# text
# --------------------------------------------------------------------------- #
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
    color=INK,
    align=PP_ALIGN.LEFT,
    tracking=None,
    line_height=None,
    anchor=MSO_ANCHOR.MIDDLE,
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
    frame.vertical_anchor = anchor

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
            if style.get("underline"):
                run.font.underline = True
            track = style.get("tracking", tracking)
            if track:
                spacing = track * run_size * SCALE  # points of extra letter spacing
                run.font._rPr.set("spc", str(round(spacing * 100)))
    return box


def plain(text):
    """Shorthand for a single unstyled run."""
    return [(text, {})]


def lines(*texts):
    """Shorthand for several unstyled lines."""
    return [plain(text) for text in texts]


# --------------------------------------------------------------------------- #
# pictures
# --------------------------------------------------------------------------- #
def veiled(path, profile):
    """Return the image as PNG bytes, dimmed along its height like the reference.

    The dark pages lay an ink gradient over the render so the type reads against
    it. Gradient shapes with per-stop transparency only survive in PowerPoint
    itself, so the measured curve is baked into the alpha channel of the picture
    instead, over the same flat ink background.

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
        [round(255 * opacity_at(y / (image.height - 1))) for y in range(image.height)]
    )
    image.putalpha(column.resize(image.size, Image.BILINEAR))

    buffer = BytesIO()
    image.save(buffer, format="PNG")
    buffer.seek(0)
    return buffer


# --------------------------------------------------------------------------- #
# page furniture
# --------------------------------------------------------------------------- #
def add_background(slide, color=PAPER):
    return fill_solid(add_shape(slide, "Background", 0, 0, REF_W, REF_H), color)


def add_logo(slide, box=LOGO_BOX, panel=True):
    """Logo with the faint panel the reference sets behind it."""
    left, top, width, height = box
    if panel:
        fill_solid(
            add_round(slide, "Logo panel", left, top, width, height, height * 0.114),
            RGBColor(0x7F, 0x7F, 0x7F),
            opacity=0.078,
        )
    return add_picture(
        slide, "Almuhib Solar Energy logo", ASSETS / "logo_mark.png", *box
    )


def add_progress_rail(slide, number, *, track_opacity=None, count=SLIDE_COUNT):
    """Right hand progress rail, its filled head and the page number.

    The rail is the full page height; the filled part covers `number`/`count` of
    it and ends in the glowing dot that the page number sits next to.
    """
    filled = RAIL_H * number / count
    dot_x, dot_y = RAIL_X + RAIL_W / 2, RAIL_TOP + filled

    fill_solid(
        add_pill(slide, "Progress rail track", RAIL_X, RAIL_TOP, RAIL_W, RAIL_H),
        LINE,
        opacity=track_opacity,
    )
    fill_gradient(
        add_pill(slide, "Progress rail fill", RAIL_X, RAIL_TOP, RAIL_W, filled),
        INK,
        MINT,
        angle=270,
    )
    for scale, opacity in ((2.1, 0.10), (1.55, 0.20)):
        fill_solid(
            add_oval(slide, f"Progress dot glow {scale:g}x", dot_x, dot_y, DOT_D * scale),
            MINT,
            opacity=opacity,
        )
    fill_solid(add_oval(slide, "Progress dot", dot_x, dot_y, DOT_D), MINT)
    return dot_y


def add_page_number(slide, number, center_y, color=MUTED):
    return add_text(
        slide,
        "Page number",
        657.5,
        center_y,
        100,
        [plain(f"{number:02d}")],
        type_size=21,
        bold=True,
        color=color,
        align=PP_ALIGN.RIGHT,
    )


def add_eyebrow(slide, text, color=GREEN, left=EYEBROW_X):
    return add_text(
        slide,
        "Eyebrow",
        left,
        mid(*EYEBROW_SPAN),
        REF_W - left,
        [plain(text)],
        type_size=21,
        bold=True,
        color=color,
        tracking=TRACK_LABEL,
    )


def add_h1(slide, text, span, *, color=INK, left=MARGIN, width=None, line_height=None):
    """Page headline. `span` is the reference bbox (top, bottom) of the type."""
    return add_text(
        slide,
        "Headline",
        left,
        mid(*span),
        width or CONTENT_R - left + 40,
        lines(*text) if isinstance(text, (list, tuple)) else [plain(text)],
        type_size=H1_SIZE,
        bold=True,
        color=color,
        line_height=line_height,
    )


def chrome(slide, number, eyebrow, *, background=PAPER, eyebrow_color=GREEN):
    """Everything every inner page shares: paper, logo, eyebrow, rail, number."""
    add_background(slide, background)
    add_logo(slide)
    add_eyebrow(slide, eyebrow, color=eyebrow_color)
    dot_y = add_progress_rail(slide, number)
    add_page_number(slide, number, dot_y)


def image_slot(
    slide, name, left, top, width, height, label, *, radius=12.0, tint=SLOT, dark=False
):
    """Empty image slot, drawn the way the source document leaves them.

    The reference shows unfilled picture frames as a soft rounded box with a
    picture glyph, the slot name and an "or browse files" link. The furniture is
    the same fixed size in every slot, whatever the frame measures. Built from
    shapes and live text, so a real photo can replace it later.
    """
    fill_solid(add_round(slide, f"{name} frame", left, top, width, height, radius), tint)
    center_x, center_y = left + width / 2, top + height / 2
    glyph, label_color = 15.75, SLOT_LABEL_DARK if dark else SLOT_LABEL
    add_picture(
        slide,
        f"{name} glyph",
        ASSETS / ("icon_image_slot_dark.png" if dark else "icon_image_slot.png"),
        center_x - glyph / 2,
        center_y - 16.0 - glyph / 2,
        glyph,
        glyph,
    )
    add_text(
        slide,
        f"{name} label",
        left,
        center_y + 4.5,
        width,
        [plain(label)],
        type_size=9.75,
        font=UI,
        bold=True,
        color=label_color,
        align=PP_ALIGN.CENTER,
    )
    add_text(
        slide,
        f"{name} link",
        left,
        center_y + 20.6,
        width,
        [[("or ", {}), ("browse files", {"underline": True})]],
        type_size=8.25,
        font=UI,
        color=label_color,
        align=PP_ALIGN.CENTER,
    )
