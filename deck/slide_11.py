"""Slide 11 - delivered systems.

Light page: two case study cards, each an empty image slot beside a spec table,
then a row of four gauge stats and a footer line. Both cards are rasterised in
the source document, so their type and rules are rebuilt from shapes and live
text; the gauges are arcs stroked with the reference's mint gradient.
"""

from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import PP_ALIGN
from pptx.oxml import parse_xml
from pptx.oxml.ns import nsdecls, qn

from .kit import (
    ARIAL_BLACK,
    GREEN_DEEP,
    INK,
    LINE,
    MARGIN,
    MINT,
    MUTED,
    PAPER,
    TRACK_DISPLAY,
    add_pill,
    add_round,
    add_rule,
    add_shape,
    add_text,
    chrome,
    fill_gradient,
    fill_solid,
    gradient_line,
    image_slot,
    lines,
    mid,
    outline,
    plain,
    u,
)

TIMES = "Times New Roman"  # the source sets the CO2 subscript in Times

CARD_L, CARD_W = 60.75, 659.0
CARD_RADIUS = 23.0
SLOT_L, SLOT_DY, SLOT_W, SLOT_H = 88.5, 27.75, 135.0, 180.0
SLOT_RADIUS = 21.0
COLUMN_L, COLUMN_R = 247.4, 692.0  # the card's right hand column

TITLE_SIZE, TITLE_LH, TITLE_TRACK = 30.0, 34.5, 0.025
BODY_SIZE = 21.0
SUBTITLE_LH = 24.0
LABEL_LH, VALUE_LH = 24.0, 28.0
VALUE_DROP = 2.25  # value baselines sit this far below their row's label
HEADING_TRACK = -0.01  # the section heading is set very slightly tight

# The cards float on a very wide, very faint halo rather than a cast shadow:
# 0.06 alpha at the card edge, gone about 50 pt out.
SHADOW_BLUR, SHADOW_ALPHA = 60.0, 0.06

ARC_CY, ARC_R, ARC_STROKE = 1237.44, 58.5, 12.0
# The gauge gradient measures as a plain linear ramp whose axis runs down and to
# the left; these are its ends, extrapolated to the corners of the arc's box. It
# is written from the light end so that renderers which cannot stroke with a
# gradient fall back to a colour near the arc's average.
ARC_LIGHT = RGBColor(0xC6, 0xF6, 0xD7)
ARC_DEEP = RGBColor(0x42, 0xD9, 0x7E)
ARC_ANGLE = 116.45

# Each row is (rule above it or None, label lines, value lines, label baseline).
CASES = (
    {
        "top": 156.75,
        "height": 368.0,
        "slot": "IMG-12",
        "title": ("OFF-GRID FARM",),
        "title_baseline": 213.5,
        "subtitle": (
            "Customer Energy System: Remote Farm / Off-",
            "Grid Solar System",
        ),
        "subtitle_baseline": 243.5,
        "rows": (
            (None, ("System",), ("38 panels, 50 kW inverter",), 305.0),
            (322.5, ("Storage",), ("70 kWh",), 350.0),
            (
                367.25,
                ("Bill before \u2192", "after"),
                ("100% generator power \u2192 0", "AED/day"),
                395.0,
            ),
            (440.0, ("Backup", "autonomy"), ("Full overnight autonomy",), 467.75),
        ),
    },
    {
        "top": 562.75,
        "height": 440.0,
        "slot": "IMG-13",
        "title": ("HYBRID SYSTEM FOR", "VILLA"),
        "title_baseline": 619.25,
        "subtitle": (
            "Customer Energy System: 5-Bedroom Villa on",
            "Jubail Island",
        ),
        "subtitle_baseline": 683.75,
        "rows": (
            (None, ("System",), ("38 panels, 50 kW inverter",), 745.25),
            (762.75, ("Storage",), ("121 kWh",), 790.5),
            (
                807.75,
                ("Bill before \u2192", "after"),
                ("3,000 AED/mo \u2192 1,500", "AED (18,000 AED/yr", "savings)"),
                835.25,
            ),
            (
                909.0,
                ("Backup", "autonomy"),
                ("Seamless full-home", "backup during outages"),
                936.5,
            ),
        ),
    },
)

GAUGES = (
    (135.7, 82.98, "41,235", [plain("kWh of clean"), plain("electricity")]),
    (
        305.2,
        252.48,
        "28,000",
        [[("kg of CO", {}), ("\u2082", {"font": TIMES})], plain("avoided")],
    ),
    (474.7, 421.98, "10,375", [plain("litres of fuel not"), plain("burned")]),
    (644.2, 615.35, "717", [plain("trees planted,"), plain("equivalent")]),
)


def soft_shadow(shape, blur, alpha):
    """Blurred, undisplaced drop shadow.

    python-pptx can only inherit a shadow from the theme, so the effect list is
    written straight onto the shape properties, replacing the empty one the kit
    leaves behind when it turns the inherited shadow off.
    """
    properties = shape.shadow._element
    for effects in properties.findall(qn("a:effectLst")):
        properties.remove(effects)
    properties.append(
        parse_xml(
            '<a:effectLst %s><a:outerShdw blurRad="%d" dist="0" dir="0" '
            'rotWithShape="0"><a:srgbClr val="000000"><a:alpha val="%d"/>'
            "</a:srgbClr></a:outerShdw></a:effectLst>"
            % (nsdecls("a"), int(u(blur)), round(alpha * 100000))
        )
    )
    return shape


def baseline_center(baseline, count, line_height, type_size):
    """`center_y` that lands the block's first baseline on `baseline`.

    The card type is rasterised in the source, so it is measured by its
    baselines rather than by span boxes. A block set on exact leading is centred
    on its line boxes, which puts the first baseline one line height less one
    descender below the top of the block; Arial descends 0.212 em.
    """
    return baseline + (count - 2) * line_height / 2 + 0.212 * type_size


def case_card(slide, number, case):
    top = case["top"]
    card = outline(
        fill_solid(
            add_round(
                slide,
                f"Case card {number}",
                CARD_L,
                top,
                CARD_W,
                case["height"],
                CARD_RADIUS,
            ),
            PAPER,
        ),
        LINE,
    )
    soft_shadow(card, SHADOW_BLUR, SHADOW_ALPHA)

    image_slot(
        slide,
        case["slot"],
        SLOT_L,
        top + SLOT_DY,
        SLOT_W,
        SLOT_H,
        case["slot"],
        radius=SLOT_RADIUS,
    )

    title = case["title"]
    add_text(
        slide,
        f"Case {number} title",
        COLUMN_L,
        baseline_center(case["title_baseline"], len(title), TITLE_LH, TITLE_SIZE),
        COLUMN_R - COLUMN_L,
        lines(*title),
        type_size=TITLE_SIZE,
        font=ARIAL_BLACK,
        color=INK,
        tracking=TITLE_TRACK,
        line_height=TITLE_LH,
    )

    subtitle = case["subtitle"]
    add_text(
        slide,
        f"Case {number} subtitle",
        COLUMN_L,
        baseline_center(
            case["subtitle_baseline"], len(subtitle), SUBTITLE_LH, BODY_SIZE
        ),
        COLUMN_R - COLUMN_L,
        lines(*subtitle),
        type_size=BODY_SIZE,
        color=MUTED,
        line_height=SUBTITLE_LH,
    )

    for index, (rule, label, value, baseline) in enumerate(case["rows"], start=1):
        if rule is not None:
            add_rule(
                slide,
                f"Case {number} rule {index}",
                COLUMN_L,
                rule,
                COLUMN_R - COLUMN_L,
            )
        add_text(
            slide,
            f"Case {number} label {index}",
            COLUMN_L,
            baseline_center(baseline, len(label), LABEL_LH, BODY_SIZE),
            COLUMN_R - COLUMN_L,
            lines(*label),
            type_size=BODY_SIZE,
            color=MUTED,
            line_height=LABEL_LH,
        )
        add_text(
            slide,
            f"Case {number} value {index}",
            COLUMN_L,
            baseline_center(baseline + VALUE_DROP, len(value), VALUE_LH, BODY_SIZE),
            COLUMN_R - COLUMN_L,
            lines(*value),
            type_size=BODY_SIZE,
            bold=True,
            color=INK,
            align=PP_ALIGN.RIGHT,
            line_height=VALUE_LH,
        )


def gauge(slide, number, center_x, number_left, value, caption):
    arc = add_shape(
        slide,
        f"Gauge {number} arc",
        center_x - ARC_R,
        ARC_CY - ARC_R,
        2 * ARC_R,
        2 * ARC_R,
        MSO_SHAPE.ARC,
    )
    # The preset sweeps clockwise from its first angle to its second, counting
    # 0.6 per degree, so 108 to 0 is the top half of the circle.
    arc.adjustments[0] = 108.0
    arc.adjustments[1] = 0.0
    gradient_line(arc, ARC_LIGHT, ARC_DEEP, ARC_STROKE, angle=ARC_ANGLE)

    add_text(
        slide,
        f"Gauge {number} value",
        number_left,
        mid(1196.98, 1239.29),
        200,
        [plain(value)],
        type_size=30,
        font=ARIAL_BLACK,
        color=GREEN_DEEP,
        tracking=TRACK_DISPLAY,
    )
    add_text(
        slide,
        f"Gauge {number} caption",
        center_x - 110,
        mid(1253.74, 1304.95) - 2.4,  # optical centre of the two line caption
        220,
        caption,
        type_size=BODY_SIZE,
        color=MUTED,
        align=PP_ALIGN.CENTER,
        line_height=27.75,
    )


def build(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])  # blank

    chrome(slide, 11, "DELIVERED SYSTEMS")

    for number, case in enumerate(CASES, start=1):
        case_card(slide, number, case)

    add_text(
        slide,
        "Section heading",
        MARGIN,
        mid(1089.84, 1135.08) - 2.1,  # optical centre of the heading
        660,
        [plain("What your system gives back")],
        type_size=40.5,
        bold=True,
        color=INK,
        tracking=HEADING_TRACK,
    )

    for number, (center_x, number_left, value, caption) in enumerate(GAUGES, start=1):
        gauge(slide, number, center_x, number_left, value, caption)

    fill_gradient(
        add_pill(slide, "Footer accent dash", MARGIN, 1349.0, 42, 5), GREEN_DEEP, MINT
    )
    add_text(
        slide,
        "Footer note",
        117.0,
        mid(1337.27, 1364.09) - 1.2,  # optical centre of the footer line
        620,
        [plain("Every year your villa runs on sunlight instead of the grid.")],
        type_size=24,
        color=MUTED,
    )
    return slide
