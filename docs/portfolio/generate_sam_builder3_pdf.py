from pathlib import Path
from xml.sax.saxutils import escape

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY, TA_LEFT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import (
    Paragraph,
    PageBreak,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


base = Path(__file__).resolve().parents[2]
source = base / "System_Administration_Plan_Portfolio_Builder_2.txt"
output = base / "campus-reserve-sam-builders.pdf"

styles = getSampleStyleSheet()
title_style = ParagraphStyle(
    "PlanTitle",
    parent=styles["Title"],
    fontName="Helvetica-Bold",
    fontSize=15,
    leading=19,
    alignment=TA_CENTER,
    textColor=colors.black,
    spaceAfter=8,
)
subtitle_style = ParagraphStyle(
    "PlanSubtitle",
    parent=styles["Normal"],
    fontName="Helvetica",
    fontSize=11,
    leading=14,
    alignment=TA_CENTER,
    textColor=colors.black,
    spaceAfter=18,
)
heading_style = ParagraphStyle(
    "PlanHeading",
    parent=styles["Heading2"],
    fontName="Helvetica-Bold",
    fontSize=11,
    leading=14,
    textColor=colors.black,
    spaceBefore=10,
    spaceAfter=6,
)
subheading_style = ParagraphStyle(
    "PlanSubheading",
    parent=styles["Heading3"],
    fontName="Helvetica-Bold",
    fontSize=11,
    leading=14,
    textColor=colors.black,
    spaceBefore=8,
    spaceAfter=4,
)
body_style = ParagraphStyle(
    "PlanBody",
    parent=styles["BodyText"],
    fontName="Helvetica",
    fontSize=9.5,
    leading=13,
    alignment=TA_JUSTIFY,
    textColor=colors.black,
    spaceAfter=5,
)
bullet_style = ParagraphStyle(
    "PlanBullet",
    parent=body_style,
    leftIndent=14,
    firstLineIndent=-8,
    alignment=TA_LEFT,
)


def paragraph(text, style=body_style):
    return Paragraph(escape(text).replace("\n", "<br/>"), style)


def make_table(lines):
    rows = []
    for line in lines:
        cells = [cell.strip() for cell in line.strip().strip("|").split("|")]
        if cells and any(cells):
            rows.append(cells)

    if len(rows) < 2:
        return []

    columns = max(len(row) for row in rows)
    normalized = [row + [""] * (columns - len(row)) for row in rows]
    table_data = [
        [paragraph(cell, ParagraphStyle("TableHeader", parent=body_style, fontName="Helvetica-Bold", textColor=colors.black, alignment=TA_LEFT))
         for cell in row]
        for row in normalized
    ]
    table = Table(table_data, repeatRows=1, colWidths=[(7.2 / columns) * inch] * columns)
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), colors.white),
        ("TEXTCOLOR", (0, 0), (-1, 0), colors.black),
        ("GRID", (0, 0), (-1, -1), 0.35, colors.black),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 5),
        ("RIGHTPADDING", (0, 0), (-1, -1), 5),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    return [Spacer(1, 6), table, Spacer(1, 8)]


lines = source.read_text(encoding="utf-8").splitlines()
story = [
    paragraph("Campus Reserve", title_style),
    paragraph("System Administration Plan - Portfolio Builders 1-4", subtitle_style),
]

i = 0
while i < len(lines):
    line = lines[i].strip()
    if not line:
        i += 1
        continue

    if line.startswith("|"):
        table_lines = []
        while i < len(lines) and lines[i].strip().startswith("|"):
            current = lines[i].strip()
            if not set(current.replace("|", "").replace("-", "").replace(":", "").strip()):
                i += 1
                continue
            table_lines.append(current)
            i += 1
        story.extend(make_table(table_lines))
        continue

    if line == "CONCLUSION" or (line[0].isdigit() and ". " in line[:4]):
        story.append(paragraph(line, heading_style))
    elif line[:2].isdigit() and ". " in line[:4]:
        story.append(paragraph(line, subheading_style))
    elif line.startswith("---"):
        story.append(Spacer(1, 10))
    elif line.startswith("- "):
        story.append(paragraph("- " + line[2:], bullet_style))
    elif len(line) <= 80 and line.endswith(":"):
        story.append(paragraph(line, subheading_style))
    elif line.startswith("Prepared for:") or line.startswith("Prepared by:") or line.startswith("Course/Project:") or line.startswith("Date:"):
        story.append(paragraph(line, body_style))
    else:
        story.append(paragraph(line))
    i += 1


document = SimpleDocTemplate(
    str(output),
    pagesize=letter,
    rightMargin=0.65 * inch,
    leftMargin=0.65 * inch,
    topMargin=0.6 * inch,
    bottomMargin=0.65 * inch,
    title="Campus Reserve System Administration Plan - Portfolio Builder 3",
)
document.build(story)
print(f"PDF created: {output}")
