"""Slide 2 - savings.

Light page: a dark gradient card carries the monthly and annual savings over an
empty roof photo slot, a donut gauge and its gradient "84%" report the coverage,
two bill rows compare the current bill with the new one, two bordered cards hold
the consumption figures and four icon tiles close the page.
"""

from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import PP_ALIGN
from pptx.oxml.ns import qn

from .kit import (
    ARIAL_BLACK,
    ASSETS,
    GREEN_DEEP,
    H1_SIZE,
    INK,
    LINE,
    MARGIN,
    MINT_SOFT,
    MUTED,
    TINT,
    TRACK_DISPLAY,
    WHITE,
    add_oval,
    add_picture,
    add_pill,
    add_round,
    add_shape,
    add_text,
    chrome,
    fill_gradient,
    fill_solid,
    gradient_line,
    gradient_text,
    image_slot,
    lines,
    mid,
    outline,
    plain,
    set_opacity,
)

# Gradient ends, sampled off the reference page.
CARD_LIGHT = RGBColor(0x10, 0x95, 0x65)  # bottom right corner of the savings card
GAUGE_DARK = RGBColor(0x0E, 0x7E, 0x57)  # ends of the 84% arc
GAUGE_LIGHT = RGBColor(0x7C, 0xE6, 0xA4)
COVER_DARK = RGBColor(0x0E, 0x7F, 0x53)  # ends of the gradient filling "84%"
COVER_LIGHT = RGBColor(0x15, 0xB7, 0x83)
BILL_DARK = RGBColor(0x13, 0xA7, 0x74)  # ends of the new bill pill
BILL_LIGHT = RGBColor(0x7C, 0xE7, 0xA4)

PANEL = RGBColor(0x7F, 0x7F, 0x7F)  # the neutral the source veils panels with

TRACK_H1 = -0.009  # the source tightens its headlines less than its display type

CARD = (MARGIN, 214.0, 660.0, 292.0)
CARD_RADIUS = 24.0
CARD_COL = 267.0  # text column inside the savings card
CARD_RULE = (267.0, 359.2, 426.0, 1.6)

GAUGE_X, GAUGE_Y = 162.0, 631.5
GAUGE_D, GAUGE_STROKE = 180.0, 25.5
COVERAGE = 0.84
# python-pptx scales an adjustment by 100000 and DrawingML writes angles in
# 60000ths of a degree, so an angle adjustment is written as degrees * 0.6.
ADJ_DEGREE = 0.6
ARC_START = 270.0  # 12 o'clock

BAR = (220.5, 294.0, 33.0)  # left, width, height of a bill bar
BAR_RADIUS = 6.0
NEW_BILL_FILL = 154.5
AMOUNT_R = 720.0

STAT = (914.25, 316.5, 159.0, 21.0)  # top, width, height, corner radius
STAT_PAD = 33.75  # left padding of the type inside a stat card
STATS = (  # card left, label, value
    (60.75, "Current consumption", "83.930 kWh"),
    (402.75, "Solar generation", "35.00 kWh"),
)
# The stat cards are rasterised in the source document, so their type is measured
# off the raster rather than read from the text layer.
STAT_LABEL_Y, STAT_VALUE_Y = 962.6, 1014.7
STAT_VALUE_SIZE = 35.25

TILE_D, TILE_Y, TILE_ICON = 85.5, 1252.5, 36.0
TILE_LABEL_W, TILE_LABEL_Y, TILE_LINE = 200.0, 1323.25, 27.7
TILES = (  # icon asset, tile centre, label lines
    ("no_bills", 135.75, ("No more bills",)),
    ("warranty", 305.25, ("25-year", "warranty")),
    ("uae", 474.75, ("Built for the", "UAE")),
    ("powered", 644.25, ("Always", "powered")),
)


def clear_theme_effects(slide):
    """Point every autoshape's theme effect reference at nothing.

    kit gives its shapes an empty <a:effectLst/>, which is what PowerPoint
    reads, but renderers that overlook it fall back on the theme effect style
    and draw its drop shadow, so the reference is cleared as well.
    """
    for shape in slide.shapes:
        style = shape._element.find(qn("p:style"))
        if style is not None:
            style.find(qn("a:effectRef")).set("idx", "0")


def build(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])  # blank

    chrome(slide, 2, "SAVINGS")
    add_text(
        slide,
        "Headline",
        MARGIN,
        mid(144.8, 190.1),
        700,
        [plain("Your estimated savings")],
        type_size=H1_SIZE,
        bold=True,
        tracking=TRACK_H1,
    )

    fill_gradient(
        add_round(slide, "Savings card", *CARD, CARD_RADIUS), INK, CARD_LIGHT, angle=312
    )
    image_slot(
        slide,
        "Roof array",
        87,
        285,
        150,
        150,
        "Roof array detail",
        radius=21,
        tint=PANEL,
        dark=True,
    )
    # The slot is a 7.8% veil, so the card gradient reads through it.
    slot_frame = next(s for s in slide.shapes if s.name == "Roof array frame")
    set_opacity(slot_frame.fill.fore_color, 0.078)

    add_text(
        slide,
        "Monthly savings label",
        CARD_COL,
        mid(241.5, 268.3),
        360,
        [plain("Monthly savings")],
        type_size=24,
        color=WHITE,
    )
    add_text(
        slide,
        "Monthly savings value",
        CARD_COL,
        mid(269.4, 341.3),
        400,
        [plain("6000 AED")],
        type_size=51,
        font=ARIAL_BLACK,
        color=WHITE,
        tracking=TRACK_DISPLAY,
    )
    fill_solid(add_shape(slide, "Savings card rule", *CARD_RULE), WHITE, opacity=0.22)
    add_text(
        slide,
        "Annual savings label",
        CARD_COL,
        mid(378.8, 405.6),
        360,
        [plain("Annual savings")],
        type_size=24,
        color=WHITE,
    )
    add_text(
        slide,
        "Annual savings value",
        CARD_COL,
        mid(406.6, 478.5),
        420,
        [plain("70,100 AED")],
        type_size=51,
        font=ARIAL_BLACK,
        color=WHITE,
        tracking=TRACK_DISPLAY,
    )

    outline(
        add_oval(slide, "Donut track", GAUGE_X, GAUGE_Y, GAUGE_D), LINE, GAUGE_STROKE
    )
    arc = add_shape(
        slide,
        "Donut arc - 84%",
        GAUGE_X - GAUGE_D / 2,
        GAUGE_Y - GAUGE_D / 2,
        GAUGE_D,
        GAUGE_D,
        MSO_SHAPE.ARC,
    )
    arc.adjustments[0] = ARC_START * ADJ_DEGREE
    arc.adjustments[1] = (ARC_START + 360 * COVERAGE) % 360 * ADJ_DEGREE
    gradient_line(arc, GAUGE_DARK, GAUGE_LIGHT, GAUGE_STROKE, angle=225)

    coverage = add_text(
        slide,
        "Coverage",
        300,
        mid(545.9, 683.4),
        420,
        [plain("84%")],
        type_size=97.5,
        font=ARIAL_BLACK,
        tracking=TRACK_DISPLAY,
    )
    gradient_text(
        coverage.text_frame.paragraphs[0].runs[0], COVER_DARK, COVER_LIGHT, angle=17
    )
    add_text(
        slide,
        "Coverage caption",
        300,
        mid(669.8, 696.6),
        360,
        [plain("of your home covered")],
        type_size=24,
        color=MUTED,
    )

    bar_left, bar_width, bar_height = BAR
    add_text(
        slide,
        "Current bill label",
        MARGIN,
        mid(765.0, 791.8),
        200,
        [plain("Current bill")],
        type_size=24,
        color=MUTED,
    )
    fill_solid(
        add_round(
            slide, "Current bill bar", bar_left, 762.0, bar_width, bar_height, BAR_RADIUS
        ),
        INK,
    )
    add_text(
        slide,
        "Current bill amount",
        AMOUNT_R - 300,
        mid(757.5, 799.8),
        300,
        [plain("2,300 AED")],
        type_size=30,
        font=ARIAL_BLACK,
        align=PP_ALIGN.RIGHT,
    )

    add_text(
        slide,
        "New bill label",
        MARGIN,
        mid(855.0, 881.8),
        200,
        [plain("New bill")],
        type_size=24,
        color=MUTED,
    )
    fill_solid(
        add_round(
            slide, "New bill track", bar_left, 852.0, bar_width, bar_height, BAR_RADIUS
        ),
        TINT,
    )
    fill_gradient(
        add_round(
            slide, "New bill bar", bar_left, 852.0, NEW_BILL_FILL, bar_height, BAR_RADIUS
        ),
        BILL_DARK,
        BILL_LIGHT,
    )
    add_text(
        slide,
        "New bill amount",
        AMOUNT_R - 300,
        mid(847.5, 889.8),
        300,
        [plain("1,200 AED")],
        type_size=30,
        font=ARIAL_BLACK,
        align=PP_ALIGN.RIGHT,
    )

    fill_solid(
        add_pill(slide, "Saving pill", 386.2, 814.5, 161.3, 39.0), MINT_SOFT
    )
    add_text(
        slide,
        "Saving amount",
        401,
        mid(819.1, 848.8),
        200,
        [plain("\u22121,100 AED")],
        type_size=21,
        font=ARIAL_BLACK,
        color=GREEN_DEEP,
    )

    top, width, height, radius = STAT
    for index, (left, label, value) in enumerate(STATS, start=1):
        card = add_round(slide, f"Stat card {index}", left, top, width, height, radius)
        outline(fill_solid(card, WHITE), LINE)
        add_text(
            slide,
            f"Stat card {index} label",
            left + STAT_PAD,
            STAT_LABEL_Y,
            width,
            [plain(label)],
            type_size=24,
            color=MUTED,
        )
        add_text(
            slide,
            f"Stat card {index} value",
            left + STAT_PAD,
            STAT_VALUE_Y,
            width,
            [plain(value)],
            type_size=STAT_VALUE_SIZE,
            font=ARIAL_BLACK,
        )

    for index, (asset, center_x, label) in enumerate(TILES, start=1):
        fill_solid(add_oval(slide, f"Tile {index} disc", center_x, TILE_Y, TILE_D), TINT)
        add_picture(
            slide,
            f"Tile {index} icon",
            ASSETS / f"icon_p02_{asset}.png",
            center_x - TILE_ICON / 2,
            TILE_Y - TILE_ICON / 2,
            TILE_ICON,
            TILE_ICON,
        )
        add_text(
            slide,
            f"Tile {index} label",
            center_x - TILE_LABEL_W / 2,
            TILE_LABEL_Y + (len(label) - 1) * TILE_LINE / 2,
            TILE_LABEL_W,
            lines(*label),
            type_size=21,
            align=PP_ALIGN.CENTER,
            line_height=TILE_LINE,
        )

    clear_theme_effects(slide)
    return slide
