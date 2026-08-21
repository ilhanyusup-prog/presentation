"""Slide 7 - financial analysis.

Light page: three dark metric cards over three pale mint ones, then the
cumulative savings chart - twenty five bars on a baseline, a dashed payback
marker, an axis, a two item legend and a footnote.
"""

from pptx.dml.color import RGBColor
from pptx.enum.dml import MSO_LINE_DASH_STYLE
from pptx.enum.shapes import MSO_CONNECTOR
from pptx.oxml.ns import qn

from .kit import (
    ARIAL_BLACK,
    CONTENT_R,
    GREEN,
    GREEN_DEEP,
    H1_SIZE,
    INK,
    MARGIN,
    MINT,
    MINT_SOFT,
    MUTED,
    TINT,
    WHITE,
    add_pill,
    add_round,
    add_rule,
    add_text,
    chrome,
    fill_gradient,
    fill_solid,
    lines,
    mid,
    plain,
    u,
)

# The card captions are white laid over the ink card at 62 per cent.
CARD_CAPTION = RGBColor(0x9F, 0xAB, 0xA7)

# The source tightens its display type; the Arial Black figures more than the
# headline, and the larger figures more than the smaller ones.
TRACK_H1 = -0.0093
TRACK_FIGURE_LARGE = -0.0341
TRACK_FIGURE_SMALL = -0.0185

CARD_RADIUS = 24.0

DARK_TOP, DARK_BOTTOM = 238.5, 432.8
DARK_NUMBER_SPAN = (252.1, 345.2)
DARK_CAPTION_TOP = mid(345.0, 371.8)

# left, right, text x0, headline number, caption lines
DARK_CARDS = (
    (60.0, 267.8, 87.0, "5.5", ("years to", "payback")),
    (285.8, 494.2, 313.0, "25", ("years of", "service life")),
    (512.2, 720.0, 539.0, "25%", ("return on", "investment")),
)

MINT_TOP, MINT_BOTTOM = 468.8, 634.5
MINT_NUMBER_SPAN = (487.2, 544.3)
MINT_CAPTION_TOP = mid(546.8, 573.6)

MINT_CARDS = (
    (60.0, 259.5, 87.0, "12 kW", ("system size",)),
    (277.5, 477.8, 304.8, "84%", ("of your home", "covered")),
    (495.8, 720.0, 522.7, "113,206", ("AED total", "project cost")),
)

CAPTION_STEP = 31.5
CAPTION_LIFT = 1.35  # exact line spacing sinks a block in the renderers

BASELINE = 1034.25
BAR_RADIUS = 4.0

# left, top, right, bottom, fill - the first four hang below the baseline
BARS = (
    (60.00, 1034.25, 81.00, 1077.00, "06251C"),
    (87.00, 1034.25, 107.25, 1067.25, "06251C"),
    (113.25, 1034.25, 134.25, 1057.50, "06251C"),
    (140.25, 1034.25, 160.50, 1047.00, "06251C"),
    (166.50, 1034.25, 187.50, 1037.25, "06251C"),
    (193.50, 1026.00, 213.75, 1034.25, "0B5F45"),
    (219.75, 1014.75, 240.75, 1034.25, "0C6A4C"),
    (246.75, 1003.50, 267.00, 1034.25, "0D7652"),
    (273.00, 991.50, 294.00, 1034.25, "0F8159"),
    (300.00, 979.50, 320.25, 1034.25, "108D60"),
    (326.25, 967.50, 347.25, 1034.25, "119866"),
    (353.25, 955.50, 373.50, 1034.25, "12A26E"),
    (379.50, 942.75, 400.50, 1034.25, "13AA78"),
    (406.50, 929.25, 426.75, 1034.25, "14B182"),
    (432.75, 915.75, 453.75, 1034.25, "15B88C"),
    (459.75, 902.25, 480.00, 1034.25, "17C096"),
    (486.00, 888.00, 507.00, 1034.25, "21C79A"),
    (513.00, 873.75, 533.25, 1034.25, "34CD9B"),
    (539.25, 858.75, 560.25, 1034.25, "46D49B"),
    (566.25, 843.75, 586.50, 1034.25, "59DB9B"),
    (592.50, 828.00, 613.50, 1034.25, "6BE29C"),
    (619.50, 812.25, 639.75, 1034.25, "7EE7A5"),
    (645.75, 795.75, 666.75, 1034.25, "91ECB1"),
    (672.75, 779.25, 693.00, 1034.25, "A3F0BC"),
    (699.00, 762.00, 720.00, 1034.25, "B6F5C8"),
)

AXIS_LABELS = (("5", 168.7), ("10", 295.1), ("15", 428.9), ("20", 562.8), ("25", 696.6))
AXIS_LABEL_SPAN = (1097.7, 1121.2)

SWATCH = 16.5
SWATCH_TOP = 1149.8
LEGEND_SPAN = (1145.7, 1169.2)
LEGEND = (
    ("cost", "Cost not yet returned", 87.0),
    ("savings", "Savings after payback", 340.1),
)


def flatten(slide):
    """Drop the theme style reference python-pptx puts on every autoshape.

    Every shape on this page is formatted explicitly, and the inherited
    `effectRef` and `lnRef` make renderers other than PowerPoint itself add a
    drop shadow and ignore the dashed strokes.
    """
    for shape in slide.shapes:
        for child in list(shape._element):
            if child.tag == qn("p:style"):
                shape._element.remove(child)


def dashed_line(slide, name, x0, y0, x1, y1, color, width, dash):
    connector = slide.shapes.add_connector(
        MSO_CONNECTOR.STRAIGHT, u(x0), u(y0), u(x1), u(y1)
    )
    connector.name = name
    connector.line.color.rgb = color
    connector.line.width = u(width)
    connector.line.dash_style = dash
    return connector


def add_metric_card(
    slide,
    index,
    card,
    *,
    label,
    top,
    bottom,
    tint,
    number_span,
    number_size,
    number_track,
    number_color,
    caption_top,
    caption_color,
):
    left, right, text_x, number, caption = card
    fill_solid(
        add_round(
            slide,
            f"{label} card {index}",
            left,
            top,
            right - left,
            bottom - top,
            CARD_RADIUS,
        ),
        tint,
    )
    add_text(
        slide,
        f"{label} card {index} number",
        text_x,
        mid(*number_span),
        right - text_x,
        [plain(number)],
        type_size=number_size,
        font=ARIAL_BLACK,
        color=number_color,
        tracking=number_track,
    )
    add_text(
        slide,
        f"{label} card {index} caption",
        text_x,
        caption_top + (len(caption) - 1) * CAPTION_STEP / 2 - CAPTION_LIFT,
        right - text_x + 40,
        lines(*caption),
        type_size=24,
        color=caption_color,
        line_height=CAPTION_STEP,
    )


def build(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])  # blank

    chrome(slide, 7, "NUMBERS")
    add_text(
        slide,
        "Headline",
        MARGIN,
        mid(156.8, 202.1),
        CONTENT_R - MARGIN + 40,
        [plain("Financial analysis")],
        type_size=H1_SIZE,
        bold=True,
        tracking=TRACK_H1,
    )

    for index, card in enumerate(DARK_CARDS, start=1):
        add_metric_card(
            slide,
            index,
            card,
            label="Metric",
            top=DARK_TOP,
            bottom=DARK_BOTTOM,
            tint=INK,
            number_span=DARK_NUMBER_SPAN,
            number_size=66,
            number_track=TRACK_FIGURE_LARGE,
            number_color=WHITE,
            caption_top=DARK_CAPTION_TOP,
            caption_color=CARD_CAPTION,
        )

    for index, card in enumerate(MINT_CARDS, start=1):
        add_metric_card(
            slide,
            index,
            card,
            label="System",
            top=MINT_TOP,
            bottom=MINT_BOTTOM,
            tint=TINT,
            number_span=MINT_NUMBER_SPAN,
            number_size=40.5,
            number_track=TRACK_FIGURE_SMALL,
            number_color=INK,
            caption_top=MINT_CAPTION_TOP,
            caption_color=MUTED,
        )

    add_text(
        slide,
        "Chart heading",
        MARGIN,
        mid(670.3, 737.6),
        400,
        lines("Cumulative savings", "over 25 years"),
        type_size=30,
        bold=True,
        line_height=33.8,
    )
    add_text(
        slide,
        "Chart total",
        409.7,
        mid(671.1, 704.9),
        CONTENT_R - 409.7,
        [plain("AED 584,415 by year 25")],
        type_size=24,
        font=ARIAL_BLACK,
        color=GREEN_DEEP,
    )

    for index, (left, top, right, bottom, color) in enumerate(BARS, start=1):
        height = bottom - top
        fill_solid(
            add_round(
                slide,
                f"Bar {index}",
                left,
                top,
                right - left,
                height,
                min(BAR_RADIUS, height / 2),
            ),
            RGBColor.from_string(color),
        )

    add_rule(slide, "Chart baseline", MARGIN, BASELINE, CONTENT_R - MARGIN)

    dashed_line(
        slide,
        "Payback marker",
        206.6,
        762.0,
        206.6,
        1086.0,
        MUTED,
        2.25,
        MSO_LINE_DASH_STYLE.SQUARE_DOT,
    )
    fill_solid(add_pill(slide, "Payback pill", 217.5, 769.5, 123.0, 36.0), MINT_SOFT)
    add_text(
        slide,
        "Payback pill label",
        230.7,
        mid(772.6, 802.3),
        123.0,
        [plain("payback")],
        type_size=21,
        font=ARIAL_BLACK,
        color=GREEN_DEEP,
    )

    for text, left in AXIS_LABELS:
        add_text(
            slide,
            f"Axis label {text}",
            left,
            mid(*AXIS_LABEL_SPAN),
            60,
            [plain(text)],
            type_size=21,
            color=MUTED,
        )

    fill_solid(
        add_round(slide, "Legend swatch 1", MARGIN, SWATCH_TOP, SWATCH, SWATCH, 4.0),
        INK,
    )
    fill_gradient(
        add_round(slide, "Legend swatch 2", 313.1, SWATCH_TOP, SWATCH, SWATCH, 4.0),
        GREEN,
        MINT,
        angle=340,  # the reference runs the swatch gradient down and to the right
    )
    for key, text, left in LEGEND:
        add_text(
            slide,
            f"Legend label - {key}",
            left,
            mid(*LEGEND_SPAN),
            300,
            [plain(text)],
            type_size=21,
            color=MUTED,
        )

    fill_gradient(
        add_pill(slide, "Footnote accent dash", MARGIN, 1317.0, 42.0, 4.5),
        GREEN_DEEP,
        MINT,
    )
    add_text(
        slide,
        "Footnote",
        117.0,
        mid(1309.2, 1362.0) - 2.1,  # same leading correction as the card captions
        CONTENT_R - 117.0 + 40,
        lines(
            "Cashflow accounts for 0.55% annual panel degradation and 3%",
            "annual inflation.",
        ),
        type_size=21,
        color=MUTED,
        line_height=29.3,
    )

    flatten(slide)
    return slide
