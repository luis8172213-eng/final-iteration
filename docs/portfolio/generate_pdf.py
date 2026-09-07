from pathlib import Path
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer
from reportlab.lib.enums import TA_JUSTIFY
from reportlab.lib import colors

base = Path(__file__).resolve().parent
src = base / 'campus-reserve-portfolio.txt'
out = base / 'output' / 'campus-reserve-portfolio.pdf'
out.parent.mkdir(parents=True, exist_ok=True)

text = src.read_text(encoding='utf-8')

styles = getSampleStyleSheet()
title_style = ParagraphStyle(
    'title',
    parent=styles['Title'],
    fontName='Helvetica',
    fontSize=20,
    leading=24,
    spaceAfter=12,
    textColor=colors.HexColor('#0f172a')
)
heading_style = ParagraphStyle(
    'heading',
    parent=styles['Heading2'],
    fontName='Helvetica',
    fontSize=12,
    leading=14,
    spaceBefore=10,
    spaceAfter=6,
    textColor=colors.HexColor('#1e293b')
)
body_style = ParagraphStyle(
    'body',
    parent=styles['BodyText'],
    fontName='Helvetica',
    fontSize=10.5,
    leading=14,
    alignment=TA_JUSTIFY,
    textColor=colors.HexColor('#111827')
)

story = []
story.append(Paragraph('Campus Reserve', title_style))
story.append(Paragraph('Capstone Portfolio Document', ParagraphStyle('subtitle', parent=styles['Heading3'], fontName='Helvetica', fontSize=11, leading=14, spaceAfter=12, textColor=colors.HexColor('#475569'))))

headings = (
    'Project Overview',
    'Problem Statement',
    'Objectives',
    'Main Features',
    'System Architecture',
    'User Roles and Functional Flow',
    'Security and Administration',
    'Development Tools and Technologies',
    'Maintenance and Future Improvement',
    'Conclusion'
)

for para in text.split('\n\n'):
    line = para.strip()
    if not line:
        continue
    if line.startswith(headings):
        story.append(Paragraph(line, heading_style))
    else:
        story.append(Paragraph(line.replace('\n', ' '), body_style))
    story.append(Spacer(1, 6))


doc = SimpleDocTemplate(str(out), pagesize=letter, rightMargin=54, leftMargin=54, topMargin=54, bottomMargin=54)
doc.build(story)
print(f'PDF created: {out}')
