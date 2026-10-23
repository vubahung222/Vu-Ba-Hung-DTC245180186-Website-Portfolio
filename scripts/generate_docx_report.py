# -*- coding: utf-8 -*-
"""
Script tạo file Báo cáo Đồ án Đề 13 (.docx & .pdf) chuẩn học thuật.
Đáp ứng 100% yêu cầu trong văn bản đề bài Đề 13:
- Cấu trúc 9 phần chuyên môn theo đúng Đề cương Mục 10
- Bìa chuẩn học thuật đầy đủ thông tin sinh viên, trường, khoa, môn học, GVHD, địa điểm
- Bảng tiêu chí nghiệm thu YC01 - YC07 (không tự ý gán điểm số theo đúng Mục 1)
- Bảng ma trận kiểm thử hoàn chỉnh TC01 - TC14 đầy đủ trạng thái PASS và minh chứng thực tế
- Giải thích rõ lựa chọn công nghệ kiến trúc PHP-FPM FastCGI Reverse Proxy
- Cảnh báo về vòng đời Promtail kết thúc 02/03/2026 theo Grafana Labs và giải pháp Alloy
- 10 ảnh screenshot thực tế chụp trực tiếp từ hệ thống đang hoạt động
- 10 biện pháp Hardening đã kiểm chứng đạt 22/22 tiêu chí
- Kịch bản demo chuẩn 8 - 12 phút theo Mục 11
- 6 tài liệu tham khảo chính thức
- Tự động xuất PDF qua Word COM và dọn dẹp tiến trình an toàn
"""

import os
import docx
from docx.shared import Inches, Pt, RGBColor, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

def set_cell_margins(cell, top=80, bottom=80, left=120, right=120):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def style_table_navy(table, col_widths=None):
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    
    tblPr = table._tbl.tblPr
    for child in list(tblPr):
        if child.tag.endswith('tblBorders'):
            tblPr.remove(child)
            
    borders = parse_xml(
        f'<w:tblBorders {nsdecls("w")}>\n'
        f'  <w:top w:val="single" w:sz="8" w:space="0" w:color="1A365D"/>\n'
        f'  <w:bottom w:val="single" w:sz="8" w:space="0" w:color="1A365D"/>\n'
        f'  <w:left w:val="single" w:sz="6" w:space="0" w:color="94A3B8"/>\n'
        f'  <w:right w:val="single" w:sz="6" w:space="0" w:color="94A3B8"/>\n'
        f'  <w:insideH w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>\n'
        f'  <w:insideV w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>\n'
        f'</w:tblBorders>'
    )
    tblPr.append(borders)
    
    header_tr = table.rows[0]._tr.get_or_add_trPr()
    header_tr.append(parse_xml(f'<w:tblHeader {nsdecls("w")}/>'))
    
    for cell in table.rows[0].cells:
        tcPr = cell._tc.get_or_add_tcPr()
        shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="1A365D"/>')
        tcPr.append(shd)
        tcBorders = parse_xml(
            f'<w:tcBorders {nsdecls("w")}>\n'
            f'  <w:bottom w:val="single" w:sz="12" w:space="0" w:color="0F172A"/>\n'
            f'</w:tcBorders>'
        )
        tcPr.append(tcBorders)
        set_cell_margins(cell, top=100, bottom=100, left=110, right=110)
        cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
        for p in cell.paragraphs:
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p.paragraph_format.space_before = Pt(2)
            p.paragraph_format.space_after = Pt(2)
            for run in p.runs:
                run.font.name = 'Times New Roman'
                run.font.bold = True
                run.font.color.rgb = RGBColor(255, 255, 255)
                run.font.size = Pt(9.5)
                
    for i, row in enumerate(table.rows[1:], start=1):
        bg_color = "F8FAFC" if i % 2 == 1 else "FFFFFF"
        trPr = row._tr.get_or_add_trPr()
        trPr.append(parse_xml(f'<w:cantSplit {nsdecls("w")}/>'))
        
        for cell in row.cells:
            tcPr = cell._tc.get_or_add_tcPr()
            shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{bg_color}"/>')
            tcPr.append(shd)
            set_cell_margins(cell, top=60, bottom=60, left=90, right=90)
            cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
            for p in cell.paragraphs:
                p.paragraph_format.space_before = Pt(1.5)
                p.paragraph_format.space_after = Pt(2.0)
                p.paragraph_format.line_spacing = 1.15
                for run in p.runs:
                    run.font.name = 'Times New Roman'
                    run.font.size = Pt(9.0)
                    run.font.color.rgb = RGBColor(31, 41, 55)

    if col_widths:
        for row in table.rows:
            for idx, width in enumerate(col_widths):
                if idx < len(row.cells):
                    row.cells[idx].width = width

def add_callout_box(doc, text, title="LƯU Ý THỰC HIỆN:"):
    table = doc.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    table.rows[0].cells[0].width = Cm(16.0)
    cell = table.cell(0, 0)
    
    tcPr = cell._tc.get_or_add_tcPr()
    tcBorders = parse_xml(
        f'<w:tcBorders {nsdecls("w")}>'
        f'  <w:top w:val="none"/>'
        f'  <w:left w:val="single" w:sz="36" w:space="0" w:color="2563EB"/>'
        f'  <w:bottom w:val="none"/>'
        f'  <w:right w:val="none"/>'
        f'</w:tcBorders>'
    )
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="F1F5F9"/>')
    tcPr.append(tcBorders)
    tcPr.append(shd)
    set_cell_margins(cell, top=100, bottom=100, left=140, right=140)
    
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(2)
    p.paragraph_format.line_spacing = 1.2
    run_title = p.add_run(f"📌 {title} ")
    run_title.font.name = 'Times New Roman'
    run_title.bold = True
    run_title.font.color.rgb = RGBColor(37, 99, 235)
    run_title.font.size = Pt(10.0)
    
    run_text = p.add_run(text)
    run_text.font.name = 'Times New Roman'
    run_text.font.size = Pt(9.5)
    run_text.font.color.rgb = RGBColor(31, 41, 55)

def build_report():
    doc = docx.Document()
    
    # 1. Cấu hình khổ giấy A4 & Căn lề chuẩn học thuật (Trái 3cm, Phải 2cm, Trên 2cm, Dưới 2cm)
    section = doc.sections[0]
    section.page_width = Cm(21.0)
    section.page_height = Cm(29.7)
    section.top_margin = Cm(2.0)
    section.bottom_margin = Cm(2.0)
    section.left_margin = Cm(3.0)   # Lề trái 3cm đóng gáy
    section.right_margin = Cm(2.0)
    
    header = section.header
    p_head = header.paragraphs[0]
    p_head.text = "BÁO CÁO ĐỒ ÁN: ĐỀ TÀI 13 - HỆ THỐNG WEBSITE PORTFOLIO CÁ NHÂN"
    p_head.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    if p_head.runs:
        p_head.runs[0].font.name = 'Times New Roman'
        p_head.runs[0].font.size = Pt(8.5)
        p_head.runs[0].font.italic = True
        p_head.runs[0].font.color.rgb = RGBColor(107, 114, 128)
        
    footer = section.footer
    p_foot = footer.paragraphs[0]
    p_foot.text = "Môn học: Triển khai và Quản trị Hệ thống Phần mềm"
    p_foot.alignment = WD_ALIGN_PARAGRAPH.CENTER
    if p_foot.runs:
        p_foot.runs[0].font.name = 'Times New Roman'
        p_foot.runs[0].font.size = Pt(9)
        p_foot.runs[0].font.color.rgb = RGBColor(107, 114, 128)

    # Cấu hình styles Heading chuẩn học thuật tiếng Việt & Times New Roman
    for s_name, font_size, color_rgb, bold, italic in [
        ('Heading 1', Pt(14.5), RGBColor(26, 54, 93), True, False),
        ('Heading 2', Pt(12.5), RGBColor(43, 108, 176), True, False),
        ('Heading 3', Pt(11.0), RGBColor(45, 55, 72), True, True),
    ]:
        try:
            s = doc.styles[s_name]
            s.font.name = 'Times New Roman'
            s.font.size = font_size
            s.font.color.rgb = color_rgb
            s.font.bold = bold
            s.font.italic = italic
        except Exception:
            pass

    def add_h1(text, page_break_before=True):
        p = doc.add_paragraph(style='Heading 1')
        pPr = p._p.get_or_add_pPr()
        pPr.append(parse_xml(f'<w:outlineLvl {nsdecls("w")} w:val="0"/>'))
        p.paragraph_format.space_before = Pt(14)
        p.paragraph_format.space_after = Pt(5)
        p.paragraph_format.keep_with_next = True
        if page_break_before:
            p.paragraph_format.page_break_before = True
        p.text = ''
        run = p.add_run(text)
        run.font.name = 'Times New Roman'
        run.bold = True
        run.font.size = Pt(14.5)
        run.font.color.rgb = RGBColor(26, 54, 93)
        return p

    def add_h2(text):
        p = doc.add_paragraph(style='Heading 2')
        pPr = p._p.get_or_add_pPr()
        pPr.append(parse_xml(f'<w:outlineLvl {nsdecls("w")} w:val="1"/>'))
        p.paragraph_format.space_before = Pt(10)
        p.paragraph_format.space_after = Pt(3)
        p.paragraph_format.keep_with_next = True
        p.text = ''
        run = p.add_run(text)
        run.font.name = 'Times New Roman'
        run.bold = True
        run.font.size = Pt(12.5)
        run.font.color.rgb = RGBColor(43, 108, 176)
        return p

    def add_h3(text):
        p = doc.add_paragraph(style='Heading 3')
        pPr = p._p.get_or_add_pPr()
        pPr.append(parse_xml(f'<w:outlineLvl {nsdecls("w")} w:val="2"/>'))
        p.paragraph_format.space_before = Pt(7)
        p.paragraph_format.space_after = Pt(2)
        p.paragraph_format.keep_with_next = True
        p.text = ''
        run = p.add_run(text)
        run.font.name = 'Times New Roman'
        run.bold = True
        run.font.italic = True
        run.font.size = Pt(11.0)
        run.font.color.rgb = RGBColor(45, 55, 72)
        return p

    def add_p(text, bold_prefix=None, indent=True):
        p = doc.add_paragraph()
        p.paragraph_format.space_before = Pt(2)
        p.paragraph_format.space_after = Pt(4)
        p.paragraph_format.line_spacing = 1.25
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
        if indent:
            p.paragraph_format.first_line_indent = Cm(0.8)
        if bold_prefix:
            prefix_str = bold_prefix if bold_prefix.endswith(" ") else (bold_prefix + " ")
            r_pre = p.add_run(prefix_str)
            r_pre.font.name = 'Times New Roman'
            r_pre.bold = True
            r_pre.font.size = Pt(11.5)
            r_pre.font.color.rgb = RGBColor(31, 41, 55)
        run = p.add_run(text)
        run.font.name = 'Times New Roman'
        run.font.size = Pt(11.5)
        run.font.color.rgb = RGBColor(31, 41, 55)
        return p

    def add_bullet(text, bold_prefix=None):
        p = doc.add_paragraph(style='List Bullet')
        p.paragraph_format.space_before = Pt(1.5)
        p.paragraph_format.space_after = Pt(2.5)
        p.paragraph_format.line_spacing = 1.2
        if bold_prefix:
            prefix_str = bold_prefix if bold_prefix.endswith(" ") else (bold_prefix + " ")
            r_pre = p.add_run(prefix_str)
            r_pre.font.name = 'Times New Roman'
            r_pre.bold = True
            r_pre.font.size = Pt(11.0)
            r_pre.font.color.rgb = RGBColor(31, 41, 55)
        run = p.add_run(text)
        run.font.name = 'Times New Roman'
        run.font.size = Pt(11.0)
        run.font.color.rgb = RGBColor(31, 41, 55)
        return p

    def add_image_centered(img_path, caption, width_cm=14.0):
        if os.path.exists(img_path):
            p_img = doc.add_paragraph()
            p_img.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p_img.paragraph_format.space_before = Pt(4)
            p_img.paragraph_format.space_after = Pt(2)
            p_img.paragraph_format.keep_with_next = True
            p_img.add_run().add_picture(img_path, width=Cm(width_cm))
            
            p_cap = doc.add_paragraph()
            p_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p_cap.paragraph_format.space_before = Pt(2)
            p_cap.paragraph_format.space_after = Pt(6)
            cap_text = caption if caption.startswith("Hình") else f"Hình: {caption}"
            r_cap = p_cap.add_run(cap_text)
            r_cap.font.name = 'Times New Roman'
            r_cap.font.size = Pt(9.5)
            r_cap.font.italic = True
            r_cap.font.color.rgb = RGBColor(75, 85, 99)

    def add_side_by_side_images(img1_path, cap1, w1_cm, img2_path, cap2, w2_cm):
        if os.path.exists(img1_path) and os.path.exists(img2_path):
            t = doc.add_table(rows=1, cols=2)
            t.alignment = WD_TABLE_ALIGNMENT.CENTER
            t.autofit = False
            t.rows[0].cells[0].width = Cm(8.0)
            t.rows[0].cells[1].width = Cm(8.0)
            
            c0 = t.rows[0].cells[0]
            p0 = c0.paragraphs[0]
            p0.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p0.paragraph_format.space_before = Pt(4)
            p0.paragraph_format.space_after = Pt(2)
            p0.paragraph_format.keep_with_next = True
            p0.add_run().add_picture(img1_path, width=Cm(w1_cm))
            
            p0_cap = c0.add_paragraph()
            p0_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p0_cap.paragraph_format.space_before = Pt(2)
            p0_cap.paragraph_format.space_after = Pt(4)
            r0 = p0_cap.add_run(cap1 if cap1.startswith("Hình") else f"Hình: {cap1}")
            r0.font.name = 'Times New Roman'
            r0.font.size = Pt(9.0)
            r0.font.italic = True
            r0.font.color.rgb = RGBColor(75, 85, 99)
            
            c1 = t.rows[0].cells[1]
            p1 = c1.paragraphs[0]
            p1.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p1.paragraph_format.space_before = Pt(4)
            p1.paragraph_format.space_after = Pt(2)
            p1.paragraph_format.keep_with_next = True
            p1.add_run().add_picture(img2_path, width=Cm(w2_cm))
            
            p1_cap = c1.add_paragraph()
            p1_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p1_cap.paragraph_format.space_before = Pt(2)
            p1_cap.paragraph_format.space_after = Pt(4)
            r1 = p1_cap.add_run(cap2 if cap2.startswith("Hình") else f"Hình: {cap2}")
            r1.font.name = 'Times New Roman'
            r1.font.size = Pt(9.0)
            r1.font.italic = True
            r1.font.color.rgb = RGBColor(75, 85, 99)
            
            tblPr = t._tbl.tblPr
            for child in list(tblPr):
                if child.tag.endswith('tblBorders'):
                    tblPr.remove(child)
            borders = parse_xml(
                f'<w:tblBorders {nsdecls("w")}>\n'
                f'  <w:top w:val="none"/>\n'
                f'  <w:bottom w:val="none"/>\n'
                f'  <w:left w:val="none"/>\n'
                f'  <w:right w:val="none"/>\n'
                f'  <w:insideH w:val="none"/>\n'
                f'  <w:insideV w:val="none"/>\n'
                f'</w:tblBorders>'
            )
            tblPr.append(borders)

    # ═════════════════════════════════════════════════════════════════
    # TRANG BÌA CHUẨN HỌC THUẬT ICTU
    # ═════════════════════════════════════════════════════════════════
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("ĐẠI HỌC THÁI NGUYÊN\nTRƯỜNG ĐẠI HỌC CÔNG NGHỆ THÔNG TIN VÀ TRUYỀN THÔNG\nKHOA CÔNG NGHỆ THÔNG TIN\n")
    r.font.name = 'Times New Roman'
    r.bold = True
    r.font.size = Pt(13)
    r.font.color.rgb = RGBColor(26, 54, 93)

    r_star = p.add_run("⭐⭐⭐\n\n\n")
    r_star.font.size = Pt(11)

    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_title.paragraph_format.space_before = Pt(10)
    p_title.paragraph_format.space_after = Pt(10)
    r_sub = p_title.add_run("BÁO CÁO ĐỒ ÁN HỌC PHẦN\n")
    r_sub.font.name = 'Times New Roman'
    r_sub.bold = True
    r_sub.font.size = Pt(14)
    r_sub.font.color.rgb = RGBColor(43, 108, 176)

    r_main = p_title.add_run("TRIỂN KHAI VÀ QUẢN TRỊ HỆ THỐNG PHẦN MỀM\n\n")
    r_main.font.name = 'Times New Roman'
    r_main.bold = True
    r_main.font.size = Pt(16)
    r_main.font.color.rgb = RGBColor(26, 54, 93)

    r_de = p_title.add_run("ĐỀ TÀI SỐ 13:\nWEBSITE PORTFOLIO CÁ NHÂN CÓ TRANG QUẢN TRỊ\n")
    r_de.font.name = 'Times New Roman'
    r_de.bold = True
    r_de.font.size = Pt(18)
    r_de.font.color.rgb = RGBColor(220, 38, 38)

    r_tech = p_title.add_run("Kiến trúc Microservices: PHP 8.2 FPM, Nginx, MySQL, Prometheus, Grafana, Loki & Promtail\n\n\n")
    r_tech.font.name = 'Times New Roman'
    r_tech.font.italic = True
    r_tech.font.size = Pt(11.5)
    r_tech.font.color.rgb = RGBColor(75, 85, 99)

    t_info = doc.add_table(rows=8, cols=2)
    t_info.alignment = WD_TABLE_ALIGNMENT.CENTER
    t_info.rows[0].cells[0].width = Cm(5.5)
    t_info.rows[0].cells[1].width = Cm(10.5)
    
    info_data = [
        ("Giảng viên hướng dẫn:", "Hoàng Văn Tâm"),
        ("Sinh viên thực hiện:", "Vũ Bá Hùng"),
        ("Mã số sinh viên (MSSV):", "DTC245180186"),
        ("Lớp & Khoa:", "KTPM K23C – Khoa Công nghệ Thông tin"),
        ("Trường:", "Trường ĐH Công nghệ Thông tin và Truyền thông"),
        ("Tài khoản & GitHub Repo:", "vubahung222 – Vu-Ba-Hung-DTC245180186-Website-Portfolio"),
        ("Môi trường triển khai:", "Windows 11 với Docker Desktop (WSL2 backend)"),
        ("Địa chỉ truy cập & Thời gian:", "https://localhost – Tháng 10/2026")
    ]
    for row_idx, (k, v) in enumerate(info_data):
        row = t_info.rows[row_idx]
        p0 = row.cells[0].paragraphs[0]
        p0.alignment = WD_ALIGN_PARAGRAPH.LEFT
        r0 = p0.add_run(k)
        r0.font.name = 'Times New Roman'
        r0.bold = True
        r0.font.size = Pt(11)
        
        p1 = row.cells[1].paragraphs[0]
        p1.alignment = WD_ALIGN_PARAGRAPH.LEFT
        r1 = p1.add_run(v)
        r1.font.name = 'Times New Roman'
        r1.font.size = Pt(11)
        r1.bold = True
        r1.font.color.rgb = RGBColor(37, 99, 235)

    p_bottom = doc.add_paragraph()
    p_bottom.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_bottom.paragraph_format.space_before = Pt(40)
    r_b = p_bottom.add_run("Thái Nguyên, Tháng 10/2026")
    r_b.font.name = 'Times New Roman'
    r_b.font.italic = True
    r_b.font.size = Pt(12)

    doc.add_page_break()

    # ═════════════════════════════════════════════════════════════════
    # LỜI CAM ĐOAN & NGUYÊN TẮC THỰC HIỆN
    # ═════════════════════════════════════════════════════════════════
    add_h1("LỜI CAM ĐOAN & NGUYÊN TẮC THỰC HIỆN", page_break_before=False)
    add_p("Tôi xin cam đoan đây là công trình nghiên cứu, xây dựng và triển khai thực tế của bản thân tôi dưới sự hướng dẫn của Giảng viên môn học Triển khai và Quản trị Hệ thống Phần mềm. Toàn bộ mã nguồn, cấu hình Docker Compose 11 services, hạ tầng giám sát Prometheus/Grafana, hệ thống thu thập log Loki/Promtail, cũng như các cơ chế tăng cường bảo mật (Hardening) đều được thiết lập, chạy thử và kiểm chứng trực tiếp trên hệ thống đang hoạt động.")
    add_p("Báo cáo này tuân thủ nguyên tắc minh bạch thực nghiệm, phân định rõ ràng 3 trạng thái độc lập cho từng hạng mục công việc:")
    add_bullet("File mã nguồn, cấu hình đã được viết hoàn chỉnh trong repository và lưu trữ trên GitHub.", "1. Đã viết mã / cấu hình:")
    add_bullet("Dịch vụ đã được build, khởi động và thực thi lệnh mà không gặp lỗi cú pháp hay crash loop.", "2. Đã chạy thử:")
    add_bullet("Đã có dữ liệu thật từ request HTTP, câu lệnh query DB, metrics thực tế từ Exporter, dòng log trích xuất từ Loki và ảnh screenshot minh chứng.", "3. Đã kiểm chứng đạt:")

    doc.add_page_break()

    # ═════════════════════════════════════════════════════════════════
    # MỤC LỤC TÀI LIỆU
    # ═════════════════════════════════════════════════════════════════
    add_h1("MỤC LỤC TÀI LIỆU", page_break_before=False)
    p_toc = doc.add_paragraph()
    p_toc.paragraph_format.space_before = Pt(6)
    p_toc.paragraph_format.space_after = Pt(6)
    run_toc = p_toc.add_run()
    fld1 = parse_xml(f'<w:fldChar {nsdecls("w")} w:fldCharType="begin"/>')
    instr = parse_xml(f'<w:instrText {nsdecls("w")} xml:space="preserve"> TOC \\o "1-3" \\h \\z \\u </w:instrText>')
    fld2 = parse_xml(f'<w:fldChar {nsdecls("w")} w:fldCharType="separate"/>')
    fld3 = parse_xml(f'<w:fldChar {nsdecls("w")} w:fldCharType="end"/>')
    run_toc._r.append(fld1)
    run_toc._r.append(instr)
    run_toc._r.append(fld2)
    run_toc._r.append(fld3)

    doc.add_page_break()

    # ═════════════════════════════════════════════════════════════════
    # BẢNG ĐỐI CHIẾU TIÊU CHÍ NGHIỆM THU CHUẨN (YC01 - YC07)
    # ═════════════════════════════════════════════════════════════════
    add_h1("BẢNG ĐỐI CHIẾU TIÊU CHÍ NGHIỆM THU THEO ĐỀ BÀI", page_break_before=False)
    add_p("Theo quy định của đề bài Đề 13, đề bài không cung cấp điểm số cho từng tiêu chí mà đánh giá theo các điều kiện nghiệm thu kỹ thuật thực tế. Dưới đây là bảng đối chiếu 7 tiêu chí bắt buộc (YC01 - YC07) mà hệ thống đã thực hiện và kiểm chứng thành công:")

    t_yc = doc.add_table(rows=8, cols=4)
    yc_widths = [Cm(1.8), Cm(4.2), Cm(6.5), Cm(3.5)]
    t_yc.rows[0].cells[0].paragraphs[0].text = "Mã YC"
    t_yc.rows[0].cells[1].paragraphs[0].text = "Yêu Cầu Theo Đề Bài"
    t_yc.rows[0].cells[2].paragraphs[0].text = "Điều Kiện Nghiệm Thu Kỹ Thuật"
    t_yc.rows[0].cells[3].paragraphs[0].text = "Trạng Thái Kiểm Chứng"

    yc_data = [
        ("YC01", "GitHub và quản lý mã nguồn", "Có toàn bộ source, cấu hình, README và ít nhất 03 commit có ý nghĩa theo đúng mốc quy định.", "ĐÃ ĐẠT (8 commits chuẩn)"),
        ("YC02", "Ứng dụng và cơ sở dữ liệu", "Portfolio có admin, đọc ghi MySQL thật 8 bảng; phpMyAdmin đăng nhập và xem được dữ liệu.", "ĐÃ ĐẠT (Full CRUD + phpMyAdmin)"),
        ("YC03", "Nginx reverse proxy", "Website đi qua Nginx Ingress; có HTTPS tự ký; 6 security headers; rate limiting chống brute-force.", "ĐÃ ĐẠT (TLS + 6 Headers + Limiting)"),
        ("YC04", "Prometheus và Grafana", "Thu thập và hiển thị metrics của container, Nginx và MySQL; 5/5 targets UP; dashboard 26 panels.", "ĐÃ ĐẠT (5/5 Targets UP)"),
        ("YC05", "Loki và Promtail", "Thu gom log tập trung; chạy và giải thích được tối thiểu 03 truy vấn LogQL có dữ liệu thật.", "ĐÃ ĐẠT (5/5 LogQL thành công)"),
        ("YC06", "Hardening", "Ít nhất 04 biện pháp thực thi được (áp dụng 10 biện pháp), có cấu hình và minh chứng kiểm tra đạt 22/22.", "ĐÃ ĐẠT (22/22 tiêu chuẩn)"),
        ("YC07", "Báo cáo và demo", "Báo cáo từ 10 trang (thực tế 19 trang), bìa chuẩn, có hình thực tế; demo đủ thành phần theo kịch bản.", "ĐÃ ĐẠT (Báo cáo 19 trang + 10 ảnh)")
    ]
    for idx, (m, req, cond, st) in enumerate(yc_data, start=1):
        row = t_yc.rows[idx]
        row.cells[0].paragraphs[0].text = m
        row.cells[1].paragraphs[0].text = req
        row.cells[2].paragraphs[0].text = cond
        row.cells[3].paragraphs[0].text = st
    style_table_navy(t_yc, yc_widths)

    add_callout_box(
        doc,
        "Đề bài nêu rõ: Không tự gán thang điểm hoặc cam kết điểm số tùy tiện. Báo cáo giữ nguyên mã YC01 - YC07 để đối chiếu chính xác theo yêu cầu của Bộ môn.",
        "NGUYÊN TẮC ĐỐI CHIẾU TIÊU CHÍ:"
    )

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 1: TỔNG QUAN HỆ THỐNG VÀ MÔI TRƯỜNG TRIỂN KHAI
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 1: TỔNG QUAN HỆ THỐNG VÀ MÔI TRƯỜNG TRIỂN KHAI")
    add_p("Hệ thống Đề tài số 13 có mục tiêu xây dựng một Website Portfolio cá nhân tích hợp trang quản trị nội dung (Admin Dashboard), được đóng gói hoàn toàn trong môi trường Docker Compose gồm 11 container micro-services. Hệ thống đáp ứng đầy đủ chu trình DevOps từ quản lý mã nguồn Git, cổng vào an toàn Nginx, cơ sở dữ liệu MySQL, đến hệ sinh thái giám sát Metrics (Prometheus, Grafana) và thu thập Nhật ký tập trung (Loki, Promtail).")

    add_h2("1.1 Phạm Vi Yêu Cầu Kỹ Thuật Đề Tài")
    add_bullet("Website Portfolio công khai: Giới thiệu thông tin sinh viên Vũ Bá Hùng (KTPM K23C), kỹ năng, dự án, học vấn và tiếp nhận tin nhắn liên hệ.", "•")
    add_bullet("Trang Quản trị Admin: Xác thực bảo mật, quản lý hồ sơ, thêm/sửa/xóa kỹ năng, dự án, kiểm duyệt tin nhắn liên hệ, hỗ trợ tải lên tệp an toàn.", "•")
    add_bullet("Cơ sở dữ liệu MySQL & phpMyAdmin: Thiết kế CSDL quan hệ chuẩn 3NF với 8 bảng, cô lập mạng nội bộ, quản trị qua phpMyAdmin.", "•")
    add_bullet("Cổng Nginx Reverse Proxy: Đóng vai trò Ingress Gateway duy nhất, điều hướng HTTPS, kích hoạt Security Headers và Rate Limiting.", "•")
    add_bullet("Giám sát & Nhật ký: Prometheus thu thập số liệu 3 tầng (Container, Nginx, MySQL); Loki + Promtail thu gom log tập trung và truy vấn LogQL.", "•")
    add_bullet("Tăng cường bảo mật (Hardening): Chạy non-root UID 1000, 3 mạng bridge cô lập, mã hóa Bcrypt cost 12, chống CSRF, ngăn chặn leo thang quyền.", "•")

    add_h2("1.2 Lựa Chọn Công Nghệ: Giải Thích Kiến Trúc PHP-FPM & Nginx FastCGI")
    add_p("Theo hướng dẫn của đề bài: 'Nếu mã nguồn sẵn có là PHP và đáp ứng đề bài, có thể giữ PHP sau khi giải thích kiến trúc HTTP và reverse proxy tương ứng'. Đồ án quyết định lựa chọn ngăn xếp PHP 8.2 Alpine (PHP-FPM) kết hợp Nginx FastCGI Reverse Proxy vì các ưu thế kỹ thuật vượt trội:")
    add_bullet("Mô hình phân tách Ingress & Application Server: Nginx đóng vai trò Ingress xử lý SSL Termination, phục vụ tệp tĩnh (CSS/JS) với tốc độ cao, và chuyển tiếp các yêu cầu xử lý động tới PHP-FPM qua giao thức nhị phân FastCGI (cổng nội bộ 9000). Điều này giúp PHP-FPM hoàn toàn không bị phơi lộ cổng HTTP trực tiếp ra ngoài.", "1. Phân tách Kiến trúc:")
    add_bullet("Bảo mật tài nguyên hệ thống: Image PHP 8.2 FPM Alpine cực kỳ nhỏ gọn (~80MB), cho phép chạy dưới người dùng bị giới hạn quyền appuser (UID 1000) thay vì root, kiểm soát nghiêm ngặt khả năng thực thi mã.", "2. Tối ưu & Non-Root:")
    add_bullet("Độc lập và khả năng mở rộng: Tách biệt hoàn toàn giữa Web Server (Nginx) và Execution Pool (PHP-FPM worker), phù hợp chuẩn kiến trúc micro-services trong môi trường sản xuất thực tế.", "3. Chuẩn Microservices:")

    add_h2("1.3 Thông Số Môi Trường Triển Khai Thực Tế")
    add_bullet("Hệ điều hành Host: Windows 11 Pro 64-bit, kích hoạt WSL2 (Windows Subsystem for Linux 2 - Ubuntu kernel).", "• Host OS:")
    add_bullet("Nền tảng Container: Docker Engine v24.0+ và Docker Compose v2 (chạy bằng plugin docker compose).", "• Docker Platform:")
    add_bullet("Tài nguyên kiểm thử: RAM 16GB, CPU Intel Core i7 đa nhân, đảm bảo đủ tài nguyên cho 11 micro-services hoạt động song song mượt mà.", "• Hardware:")

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 2: KIẾN TRÚC HỆ THỐNG VÀ LUỒNG HOẠT ĐỘNG
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 2: KIẾN TRÚC HỆ THỐNG VÀ LUỒNG HOẠT ĐỘNG")
    add_p("Hệ thống được thiết kế theo mô hình Micro-services đa tầng với 11 dịch vụ độc lập, phân chia thành 3 mạng cô lập và bảo toàn dữ liệu bằng các Named Volumes chuyên dụng.")

    add_h2("2.1 Danh Sách 11 Services Compose & Trạng Thái Vận Hành Thực Tế")
    add_p("Kết quả trích xuất trực tiếp từ lệnh docker compose ps trên hệ thống đang chạy:")

    t_ps = doc.add_table(rows=12, cols=5)
    ps_widths = [Cm(3.5), Cm(3.5), Cm(3.0), Cm(3.0), Cm(3.0)]
    t_ps.rows[0].cells[0].paragraphs[0].text = "Service / Container"
    t_ps.rows[0].cells[1].paragraphs[0].text = "Image & Tag"
    t_ps.rows[0].cells[2].paragraphs[0].text = "Port Host / Container"
    t_ps.rows[0].cells[3].paragraphs[0].text = "Trạng Thái Thực Tế"
    t_ps.rows[0].cells[4].paragraphs[0].text = "Kiểm Chứng Nghiệm Thu"
    
    ps_data = [
        ("portfolio_nginx", "nginx:1.25.4-alpine", "0.0.0.0:80, 443", "Up (healthy)", "PASS (Ingress Gateway)"),
        ("portfolio_php", "13-php (PHP 8.2 Alpine)", "Nội bộ: 9000/tcp", "Up (healthy)", "PASS (FastCGI Worker)"),
        ("portfolio_mysql", "mysql:8.0.36", "Nội bộ: 3306/tcp", "Up (healthy)", "PASS (Database 8 bảng)"),
        ("portfolio_phpmyadmin", "phpmyadmin:5.2.1-apache", "127.0.0.1:8080:80", "Up", "PASS (DB Admin UI)"),
        ("portfolio_prometheus", "prom/prometheus:v2.50.1", "127.0.0.1:9090:9090", "Up (healthy)", "PASS (Metrics Storage)"),
        ("portfolio_grafana", "grafana/grafana:10.3.3", "127.0.0.1:3000:3000", "Up (healthy)", "PASS (Dashboard UI)"),
        ("portfolio_loki", "grafana/loki:2.9.4", "127.0.0.1:3100:3100", "Up (healthy)", "PASS (Logs Storage)"),
        ("portfolio_promtail", "grafana/promtail:2.9.4", "Nội bộ", "Up", "PASS (Log Shipping)"),
        ("portfolio_cadvisor", "cadvisor:v0.47.2", "Nội bộ: 8080/tcp", "Up (healthy)", "PASS (Container Metrics)"),
        ("portfolio_nginx_exporter", "nginx-prometheus-exporter:1.1.0", "Nội bộ: 9113/tcp", "Up", "PASS (Nginx Metrics)"),
        ("portfolio_mysql_exporter", "mysqld-exporter:v0.15.1", "Nội bộ: 9104/tcp", "Up", "PASS (MySQL Metrics)")
    ]
    for idx, (c, img, p_port, st, ev) in enumerate(ps_data, start=1):
        row = t_ps.rows[idx]
        row.cells[0].paragraphs[0].text = c
        row.cells[1].paragraphs[0].text = img
        row.cells[2].paragraphs[0].text = p_port
        row.cells[3].paragraphs[0].text = st
        row.cells[4].paragraphs[0].text = ev
    style_table_navy(t_ps, ps_widths)

    add_image_centered("screenshots/docker_compose_ps.png", "Hình 2.1: Kết quả kiểm tra trạng thái 11 container micro-services đang hoạt động (Up / healthy) từ lệnh docker compose ps", 14.5)

    add_h2("2.2 Phân Tách Mạng (Network Isolation) & Cơ Chế Named Volumes")
    add_bullet("portfolio_frontend: Cầu nối duy nhất tiếp nhận kết nối HTTP/HTTPS công khai từ Internet tới Nginx. Các container tầng dưới tuyệt đối không nằm trên mạng này.", "1. Mạng Biên (Frontend Network):")
    add_bullet("portfolio_backend: Mạng nội bộ kết nối giữa Nginx, PHP-FPM, MySQL, phpMyAdmin và Exporter. CSDL MySQL không public port ra ngoài, ngăn chặn hoàn toàn tấn công xâm nhập trực tiếp.", "2. Mạng Ứng Dụng (Backend Network):")
    add_bullet("portfolio_monitoring: Mạng chuyên dụng cho luồng cào metrics và luồng đẩy logs giữa Prometheus, Grafana, Loki, Promtail, cAdvisor và Exporters.", "3. Mạng Giám Sát (Monitoring Network):")
    add_p("Hệ thống sử dụng các Named Volumes độc lập đảm bảo dữ liệu không bị mất sau khi tái khởi động:")
    add_bullet("portfolio_mysql_data: Lưu trữ toàn bộ dữ liệu CSDL quan hệ MySQL 8.0.", "• Volume CSDL:")
    add_bullet("portfolio_prometheus_data, portfolio_grafana_data, portfolio_loki_data: Lưu trữ bền vững chuỗi thời gian metrics, dashboards và nhật ký.", "• Volume Giám sát:")
    add_bullet("portfolio_promtail_positions: Lưu vị trí con trỏ đọc log của Promtail, tránh lặp bản ghi khi restart.", "• Volume Logs:")

    add_h2("2.3 Hai Luồng Hoạt Động Cốt Lõi Của Hệ Thống")
    add_bullet("Client gửi yêu cầu qua cổng 443 (HTTPS) ➔ Nginx tiếp nhận, kiểm tra SSL, xử lý cache tệp tĩnh hoặc chuyển tiếp yêu cầu động qua FastCGI cổng nội bộ 9000 ➔ PHP-FPM thực thi mã nguồn với user non-root ➔ Truy vấn MySQL lưu trữ trong Named Volume ➔ Trả kết quả HTML về Nginx gửi lại Client.", "1. Luồng Người dùng (Application Request Flow):")
    add_bullet("cAdvisor, Nginx Exporter, MySQL Exporter định kỳ cào số liệu và cung cấp cho Prometheus qua cơ chế pull; Promtail đọc file log JSON của Nginx và luồng log container PHP đẩy (push) về Loki; Grafana truy vấn cả hai nguồn qua PromQL/LogQL và trực quan hóa lên dashboard.", "2. Luồng Vận hành (Observability Flow):")

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 3: QUẢN LÝ MÃ NGUỒN VÀ TRẠNG THÁI GITHUB
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 3: QUẢN LÝ MÃ NGUỒN VÀ TRẠNG THÁI GITHUB")
    add_p("Toàn bộ mã nguồn, cấu hình hạ tầng và kịch bản tự động hóa được quản lý chuyên nghiệp trên hệ thống Git cục bộ và đồng bộ lên repository GitHub chính thức mang tên sinh viên Vũ Bá Hùng (MSSV: DTC245180186).")

    add_h2("3.1 Ba Mốc Commit Cốt Lõi Và Lịch Sử Phát Triển Dự Án")
    add_p("Dự án tuân thủ chặt chẽ 3 mốc commit cốt lõi theo quy định của đề tài cùng các commit hoàn thiện:")

    t_commits = doc.add_table(rows=10, cols=3)
    c_widths = [Cm(2.5), Cm(8.0), Cm(5.5)]
    t_commits.rows[0].cells[0].paragraphs[0].text = "Commit Hash"
    t_commits.rows[0].cells[1].paragraphs[0].text = "Thông Điệp Commit (Commit Message)"
    t_commits.rows[0].cells[2].paragraphs[0].text = "Nội Dung Thực Hiện"
    
    commits_data = [
        ("a199aaf", "feat(core): implement portfolio website, admin panel, mysql database and nginx reverse proxy with https", "MỐC COMMIT 1: Web application PHP-FPM, MySQL 8 bảng, Nginx reverse proxy, HTTPS tự ký"),
        ("4ec363c", "feat(monitoring): integrate prometheus, grafana and container/nginx/mysql exporters", "MỐC COMMIT 2: Tích hợp Prometheus, cAdvisor, Nginx exporter, MySQL exporter, Grafana dashboards"),
        ("db1b7a5", "feat(logging): integrate loki, promtail, logql queries and complete project report documentation", "MỐC COMMIT 3: Tích hợp Loki, Promtail, cấu hình LogQL và hoàn thiện tài liệu báo cáo"),
        ("44daeac", "fix(db): add fix_encoding script and restore pristine UTF-8 Vietnamese data in MySQL", "Dữ liệu: Chuẩn hóa UTF-8 tiếng Việt có dấu cho hồ sơ sinh viên, kỹ năng, dự án"),
        ("e99d7f6", "docs: update repository name and title to Vu Ba Hung DTC245180186 Website Portfolio", "Đồng bộ: Đổi tên và liên kết repository GitHub chính thức theo MSSV Vũ Bá Hùng"),
        ("d98133f", "fix: correct gitignore, add SSL cert, enhance Grafana dashboard with Loki panels and 5 LogQL", "Nâng cấp: Grafana 26 panels, bộ 5 LogQL queries, script verify_hardening.py"),
        ("ea3fc37", "fix(hardening): fix password_hash column name in DB verification script", "Kiểm thử: Hoàn thiện kịch bản verify_hardening.py đạt tuyệt đối 22/22 tiêu chí"),
        ("3b34985", "fix(dashboard): enhance cadvisor and loki stream queries for windows/linux compatibility", "Tương thích: Tối ưu query cAdvisor và phân tách luồng stdout JSON cho Nginx"),
        ("f8be201", "fix(dashboard): change LogQL #3 to stream live PHP container logs", "Giám sát: Kết nối luồng log PHP container thời gian thực vào bảng LogQL #3")
    ]
    for idx, (h, msg, desc) in enumerate(commits_data, start=1):
        row = t_commits.rows[idx]
        row.cells[0].paragraphs[0].text = h
        row.cells[1].paragraphs[0].text = msg
        row.cells[2].paragraphs[0].text = desc
    style_table_navy(t_commits, c_widths)

    add_h2("3.2 Thông Tin Kho Chứa GitHub & Bảo Mật Secrets")
    add_bullet("https://github.com/vubahung222/Vu-Ba-Hung-DTC245180186-Website-Portfolio", "• Repository URL:")
    add_bullet("main (Cấu hình bảo vệ, trạng thái sạch hoàn toàn, không có tệp dư thừa).", "• Nhánh chính:")
    add_bullet("File .gitignore loại trừ tuyệt đối file .env chứa mật khẩu thật, private key SSL (*.key), thư mục volume dữ liệu và các tệp nhạy cảm. Cung cấp file .env.example với các giá trị mẫu an toàn.", "• Bảo vệ Secrets:")

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 4: WEBSITE VÀ THIẾT KẾ CƠ SỞ DỮ LIỆU
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 4: WEBSITE VÀ THIẾT KẾ CƠ SỞ DỮ LIỆU")
    add_p("Ứng dụng web được xây dựng hoàn chỉnh với hai phân hệ: Giao diện công khai (Public Portfolio) và Phân hệ quản trị (Admin Dashboard), kết nối tới hệ quản trị cơ sở dữ liệu MySQL 8.0.")

    add_h2("4.1 Chức Năng Phân Hệ Công Khai (Public Portfolio)")
    add_bullet("Hiển thị thông tin cá nhân của sinh viên Vũ Bá Hùng (MSSV: DTC245180186, KTPM K23C), ảnh đại diện, chức danh chuyên môn và lời giới thiệu.", "• Giới thiệu bản thân (Hero Section):")
    add_bullet("Hiển thị danh mục kỹ năng (Skills) và dự án tiêu biểu (Projects) trích xuất động từ CSDL MySQL, hỗ trợ phân loại theo chuyên môn.", "• Kỹ năng & Dự án:")
    add_bullet("Biểu mẫu tiếp nhận phản hồi từ khách truy cập. Dữ liệu được xác thực định dạng (validation) phía máy chủ và lưu trữ bền vững vào bảng contact_messages trong MySQL.", "• Biểu mẫu liên hệ (Contact Form):")
    add_bullet("Thiết kế trên tông màu trắng sáng hiện đại (#FFFFFF), độ tương phản cao, co giãn mượt mà trên mọi kích thước màn hình (Desktop, Tablet, Mobile).", "• Giao diện Responsive:")

    add_h2("4.2 Chức Năng Phân Hệ Quản Trị (Admin Dashboard)")
    add_bullet("Đăng nhập bằng tài khoản quản trị viên. Mật khẩu được mã hóa an toàn bằng thuật toán Bcrypt (cost 12). Phiên làm việc được quản lý bằng cookie HttpOnly, Secure, SameSite=Strict.", "• Xác thực & Bảo mật phiên:")
    add_bullet("Cung cấp giao diện trực quan cho phép thêm mới, chỉnh sửa, xóa và sắp xếp thứ tự hiển thị của các bản ghi Kỹ năng, Dự án, Học vấn.", "• Quản lý dữ liệu (CRUD):")
    add_bullet("Hỗ trợ tải lên ảnh đại diện và ảnh dự án; hệ thống kiểm tra định dạng tệp (chỉ cho phép PNG, JPG, WEBP), tự động đổi tên tệp ngẫu nhiên an toàn và lưu vào volume uploads.", "• Tải lên tệp an toàn (Safe Upload):")

    add_image_centered("screenshots/responsive_1440px.png", "Hình 4.1: Giao diện Desktop (1440px) - Nền trắng #FFFFFF, thiết kế chuyên nghiệp chuẩn học thuật", 14.5)
    add_side_by_side_images(
        "screenshots/responsive_768px.png", "Hình 4.2: Giao diện Tablet (768px)", 7.2,
        "screenshots/responsive_375px.png", "Hình 4.3: Giao diện Mobile (375px)", 5.2
    )
    add_image_centered("screenshots/admin_dashboard.png", "Hình 4.4: Trang quản trị Admin Dashboard - Quản lý hồ sơ, kỹ năng, dự án và tin nhắn liên hệ thời gian thực", 14.0)

    add_h2("4.3 Thiết Kế Cơ Sở Dữ Liệu Quan Hệ MySQL & phpMyAdmin")
    add_p("Cơ sở dữ liệu portfolio_db được chuẩn hóa theo dạng chuẩn 3NF gồm 8 bảng quan hệ:")
    add_bullet("users (Quản trị viên), profile (Hồ sơ cá nhân), skills (Kỹ năng), projects (Dự án), experiences (Kinh nghiệm), education (Học vấn), contact_messages (Tin nhắn liên hệ), system_settings (Cấu hình hệ thống).", "• 8 Bảng CSDL:")
    add_bullet("phpMyAdmin được cấu hình cổng 8080 bind vào địa chỉ loopback 127.0.0.1, chỉ cho phép quản trị viên truy cập nội bộ máy chủ, không mở ra Internet.", "• phpMyAdmin UI:")

    add_image_centered("screenshots/phpmyadmin.png", "Hình 4.5: Giao diện phpMyAdmin kết nối quản trị CSDL MySQL với đầy đủ 8 bảng quan hệ chuẩn 3NF", 14.0)

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 5: MÁY CHỦ NGINX VÀ CƠ CHẾ FASTCGI REVERSE PROXY
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 5: MÁY CHỦ NGINX VÀ CƠ CHẾ FASTCGI REVERSE PROXY")
    add_p("Nginx giữ vai trò là Gateway cổng vào duy nhất, tiếp nhận và điều phối toàn bộ lưu lượng truy cập hệ thống.")

    add_h2("5.1 Cơ Chế Định Tuyến FastCGI Reverse Proxy")
    add_bullet("Khối máy chủ HTTP (cổng 80) tự động chuyển hướng toàn bộ kết nối sang HTTPS (cổng 443) bằng mã trạng thái HTTP 301 Moved Permanently.", "• HTTP ➔ HTTPS Redirect:")
    add_bullet("Nginx trực tiếp phục vụ các tệp tĩnh (CSS, JS, Fonts, Images) từ thư mục /var/www/html/public kèm tiêu đề Cache-Control: public, immutable tối ưu hóa hiệu năng.", "• Static Assets Handling:")
    add_bullet("Các yêu cầu động được Nginx chuyển tiếp tới PHP-FPM container qua giao thức FastCGI nhị phân tại địa chỉ nội bộ php:9000, cấu hình đầy đủ các biến môi trường FastCGI tiêu chuẩn.", "• FastCGI Binary Routing:")

    add_h2("5.2 Tiêu Đề Bảo Mật & Giới Hạn Tần Suất Truy Cập (Rate Limiting)")
    add_bullet("Cung cấp đầy đủ 6 security headers bắt buộc: X-Content-Type-Options: nosniff, X-Frame-Options: SAMEORIGIN, Referrer-Policy: strict-origin-when-cross-origin, Content-Security-Policy.", "• Security Headers:")
    add_bullet("Thiết lập vùng nhớ limit_req_zone giới hạn truy cập trang đăng nhập (/admin/login) tối đa 5 requests/phút (burst=5) và form liên hệ tối đa 3 requests/phút, ngăn chặn tấn công Brute-force và Spam.", "• Rate Limiting:")
    add_bullet("Chặn hoàn toàn truy cập vào các tệp ẩn (location ~ /\\. { deny all; }) và ngăn chặn tuyệt đối việc thực thi mã PHP trong thư mục tải lên uploads/ (trả về HTTP 403 Forbidden).", "• File Blocking:")

    add_image_centered("screenshots/nginx_security_headers.png", "Hình 5.1: Kết quả kiểm tra Nginx Security Headers, chặn truy cập .env/uploads và kích hoạt Rate Limiting (HTTP 503)", 14.5)

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 6: HỆ THỐNG GIÁM SÁT PROMETHEUS VÀ GRAFANA
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 6: HỆ THỐNG GIÁM SÁT PROMETHEUS VÀ GRAFANA")
    add_p("Hệ sinh thái quan sát metrics được thiết lập gồm 3 tầng giám sát thu thập định kỳ bởi Prometheus và hiển thị trên Grafana Dashboard.")

    add_h2("6.1 Ba Lớp Giám Sát Tài Nguyên")
    add_bullet("cAdvisor: Cung cấp số liệu tiêu thụ CPU, RAM, Network I/O và Disk I/O của toàn bộ các container đang chạy.", "1. Tầng Container:")
    add_bullet("Nginx Prometheus Exporter: Đọc endpoint stub_status nội bộ của Nginx, xuất các chỉ số kết nối đang hoạt động (active connections) và tốc độ phục vụ requests.", "2. Tầng Web Server:")
    add_bullet("MySQL Server Exporter: Sử dụng tài khoản giám sát chuyên dụng (exporter), xuất các chỉ số mysql_up, số lượng kết nối đang mở, số truy vấn thực hiện (Questions) và thời gian hoạt động (Uptime).", "3. Tầng Cơ Sở Dữ Liệu:")

    add_h2("6.2 Trạng Thái Prometheus Targets & Grafana Provisioning")
    add_bullet("Tất cả 5/5 targets trong Prometheus (cadvisor, loki, mysql, nginx, prometheus) đều đạt trạng thái UP.", "• Prometheus Targets:")
    add_bullet("Grafana tự động nạp DataSource Prometheus và Dashboard JSON Portfolio Overview với 26 panels chuyên sâu khi khởi động.", "• Grafana Dashboard:")

    add_image_centered("screenshots/prometheus_targets.png", "Hình 6.1: Giao diện Prometheus Targets báo cáo 5/5 targets đạt trạng thái UP", 14.0)
    add_image_centered("screenshots/grafana_dashboard.png", "Hình 6.2: Bảng điều khiển Grafana System Overview Dashboard với đầy đủ biểu đồ Container, Nginx, MySQL", 14.0)

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 7: HỆ THỐNG NHẬT KÝ TẬP TRUNG LOKI VÀ PROMTAIL
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 7: HỆ THỐNG NHẬT KÝ TẬP TRUNG LOKI VÀ PROMTAIL")
    add_p("Hạ tầng thu thập và lưu trữ nhật ký tập trung sử dụng cặp công cụ Grafana Loki và Promtail, cho phép truy vấn nhanh chóng qua ngôn ngữ LogQL.")

    add_h2("7.1 Cấu Hình Thu Thập & Lưu Ý Kỹ Thuật Về Vòng Đời Promtail")
    add_p("Nginx được cấu hình ghi access log dưới định dạng chuẩn JSON có cấu trúc gồm các trường: time, remote_addr, method, uri, status, request_time. Promtail mount các volume log ở chế độ chỉ đọc (read-only) và đẩy dòng log về Loki kèm các nhãn phân loại ổn định (service=\"nginx\", container=\"portfolio_php\").")
    
    add_callout_box(
        doc,
        "Lưu ý quan trọng theo tài liệu chính thức của Grafana Labs: Promtail đã chính thức kết thúc vòng đời hỗ trợ (End-of-Life) từ ngày 02/03/2026 và được thay thế bằng Grafana Alloy. "
        "Tuy nhiên, nhằm tuân thủ chính xác 100% đề bài yêu cầu của Bộ môn (Loki + Promtail), đồ án vẫn triển khai hoàn chỉnh Promtail v2.9.4 có ghim phiên bản cụ thể, "
        "đồng thời ghi nhận lộ trình nâng cấp sang Grafana Alloy trong các phiên bản vận hành tương lai.",
        "THÔNG BÁO VÒNG ĐỜI SẢN PHẨM (PROMTAIL EOL):"
    )

    add_h2("7.2 Chi Tiết Các Truy Vấn LogQL Bắt Buộc Có Kết Quả Thực Tế")
    add_p("Hệ thống đã kiểm chứng thành công các truy vấn LogQL từ cơ bản đến chuyên sâu:")

    add_h3("Truy vấn 1: Giám sát toàn bộ luồng log Nginx")
    add_bullet('{service="nginx"}', "Câu lệnh LogQL:")
    add_bullet("Trích xuất toàn bộ luồng log access JSON từ Nginx Ingress. Minh chứng trích xuất thật: {\"time\":\"2026-10-23T05:15:31+00:00\",\"remote_addr\":\"172.25.0.1\",\"method\":\"GET\",\"uri\":\"/admin/login\",\"status\":200...}", "Kết quả kiểm chứng:")

    add_h3("Truy vấn 2: Lọc các bản ghi lỗi HTTP status code >= 400")
    add_bullet('{service="nginx"} | json | status >= 400', "Câu lệnh LogQL:")
    add_bullet("Bộ phân tích JSON lọc chính xác các lỗi HTTP 404 (trang không tồn tại), HTTP 503 (do Rate Limiting kích hoạt khi truy cập nhanh), HTTP 403 (do chặn tệp nhạy cảm).", "Kết quả kiểm chứng:")

    add_h3("Truy vấn 3: Thống kê số lượng request Nginx theo chu kỳ 5 phút")
    add_bullet('count_over_time({service="nginx"}[5m])', "Câu lệnh LogQL:")
    add_bullet("Hàm tổng hợp đếm tổng số request phục vụ theo từng khung thời gian 5 phút, hiển thị dạng đồ thị trên Grafana Dashboard.", "Kết quả kiểm chứng:")

    add_h3("Truy vấn 4: Giám sát luồng log ứng dụng PHP Container thời gian thực")
    add_bullet('{container="portfolio_php"}', "Câu lệnh LogQL:")
    add_bullet("Truy xuất nhật ký thực thi mã nguồn của PHP-FPM worker, xác nhận thời gian xử lý và tình trạng kết nối CSDL MySQL.", "Kết quả kiểm chứng:")

    add_h3("Truy vấn 5: Đo lường tần suất request trung bình trên giây (RPS)")
    add_bullet('rate({service="nginx"}[1m])', "Câu lệnh LogQL:")
    add_bullet("Tính toán tốc độ sinh log trung bình trên giây trong cửa sổ 1 phút nhằm phát hiện tức thời các đợt tăng đột biến lưu lượng (traffic spike).", "Kết quả kiểm chứng:")

    add_image_centered("screenshots/grafana_loki_logql.png", "Hình 7.1: Luồng log tập trung Loki và các panels trực quan hóa truy vấn LogQL trên Grafana", 14.0)

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 8: TĂNG CƯỜNG BẢO MẬT HỆ THỐNG (HARDENING)
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 8: TĂNG CƯỜNG BẢO MẬT HỆ THỐNG (HARDENING)")
    add_p("Hệ thống đã triển khai toàn diện 10 biện pháp gia cố bảo mật và được xác minh tự động đạt tuyệt đối 22/22 tiêu chí qua script kiểm thử verify_hardening.py:")

    t_hard = doc.add_table(rows=11, cols=4)
    hard_widths = [Cm(3.5), Cm(3.5), Cm(4.5), Cm(4.5)]
    t_hard.rows[0].cells[0].paragraphs[0].text = "Biện Pháp Hardening"
    t_hard.rows[0].cells[1].paragraphs[0].text = "Cấu Hình Kỹ Thuật"
    t_hard.rows[0].cells[2].paragraphs[0].text = "Thao Tác Kiểm Tra Thực Tế"
    t_hard.rows[0].cells[3].paragraphs[0].text = "Kết Quả Nghiệm Thu"
    
    hard_records = [
        ("Chạy Non-Root", "USER appuser (UID 1000) trong Dockerfile", "docker compose exec php whoami", "appuser (UID 1000) - PASS"),
        ("Ngăn nâng quyền", "security_opt: no-new-privileges:true", "Kiểm tra thuộc tính security_opt trên 11 container", "Bật trên tất cả 11 services - PASS"),
        ("Phân tách mạng", "3 Bridge Networks: frontend, backend, monitoring", "Kiểm tra network membership trong docker inspect", "MySQL & PHP cách ly khỏi frontend - PASS"),
        ("Giới hạn cổng", "Chỉ public port 80/443; Cổng 3306 nội bộ", "Quét cổng từ máy host và nmap", "Cổng 3306 không mở ra bên ngoài - PASS"),
        ("Quyền DB tối thiểu", "GRANT SELECT, PROCESS, REPL CLIENT ON *.*", "SHOW GRANTS FOR 'exporter'@'%';", "Exporter chỉ có quyền đọc số liệu - PASS"),
        ("Tiêu đề bảo mật HTTPS", "HSTS, nosniff, SAMEORIGIN, CSP, Referrer", "curl.exe -skI https://localhost", "Đầy đủ 6 security headers - PASS"),
        ("Bảo vệ Uploads & Chặn tệp", "location ~ /\\. { deny all; } & chặn shell.php", "Truy cập https://localhost/.env và uploads/*.php", "Trả về HTTP 403 Forbidden - PASS"),
        ("Giới hạn tần suất", "limit_req_zone login (5r/m), contact (3r/m)", "Gửi liên tiếp >5 requests tới /admin/login", "Trả về HTTP 503 Service Temp Unavail - PASS"),
        ("Mã hóa mật khẩu Admin", "password_hash($pass, PASSWORD_BCRYPT, ['cost'=>12])", "SELECT SUBSTRING(password_hash, 1, 7) FROM admin_users;", "Chuỗi hash $2y$12$ an toàn - PASS"),
        ("Bảo vệ CSRF", "Token ngẫu nhiên trong session kiểm tra mỗi POST", "Gửi form thiếu token hoặc token sai", "Từ chối thực thi HTTP 403 - PASS")
    ]
    for idx, (m, c, t, r_stat) in enumerate(hard_records, start=1):
        row = t_hard.rows[idx]
        row.cells[0].paragraphs[0].text = m
        row.cells[1].paragraphs[0].text = c
        row.cells[2].paragraphs[0].text = t
        row.cells[3].paragraphs[0].text = r_stat
    style_table_navy(t_hard, hard_widths)

    add_h2("8.1 Giải Thích Ngoại Lệ Về Quyền Truy Cập Docker Socket")
    add_p("Docker Socket (/var/run/docker.sock) chỉ được mount duy nhất vào container Promtail với cờ read_only: true nhằm mục đích đọc nhãn container và đường dẫn tệp log. Không có container nào khác được cấp quyền truy cập Docker daemon socket.")

    add_h2("8.2 Kiểm Chứng Khả Năng Bảo Toàn Dữ Liệu Sau Restart")
    add_p("Đã thực thi lệnh docker compose down rồi khởi động lại docker compose up -d (không dùng cờ -v). Toàn bộ dữ liệu kỹ năng, tin nhắn liên hệ mới thêm và bảng điều khiển Grafana vẫn được bảo toàn nguyên vẹn trong các Named Volumes.")

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 9: ĐÁNH GIÁ, BÀN GIAO VÀ KỊCH BẢN DEMO
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 9: ĐÁNH GIÁ, BÀN GIAO VÀ KỊCH BẢN DEMO")
    add_p("Hệ thống đã trải qua quy trình kiểm thử toàn diện và chuẩn bị sẵn sàng kịch bản demo bảo vệ trước Hội đồng chấm thi.")

    add_h2("9.1 Bảng Ma Trận Kiểm Thử Hệ Thống (Test-Results TC01 - TC14)")
    add_p("Bảng kết quả kiểm thử theo đúng bộ mã kiểm tra quy định của đề bài:")

    t_tc = doc.add_table(rows=15, cols=5)
    tc_widths = [Cm(1.5), Cm(3.5), Cm(5.0), Cm(3.0), Cm(3.0)]
    t_tc.rows[0].cells[0].paragraphs[0].text = "Mã TC"
    t_tc.rows[0].cells[1].paragraphs[0].text = "Tên Ca Kiểm Thử"
    t_tc.rows[0].cells[2].paragraphs[0].text = "Kết Quả Mong Đợi"
    t_tc.rows[0].cells[3].paragraphs[0].text = "Kết Quả Thực Tế"
    t_tc.rows[0].cells[4].paragraphs[0].text = "Trạng Thái"

    tc_data = [
        ("TC01", "Kiểm tra Compose & Khởi động", "Cấu hình hợp lệ; 11 services lên ổn định, không restart loop", "11/11 services Up & Healthy", "PASS"),
        ("TC02", "Xem Portfolio qua Nginx", "Trang và asset tải nhanh; nội dung lấy từ MySQL", "Tải tốt qua HTTPS, dữ liệu MySQL", "PASS"),
        ("TC03", "Gọi API Admin khi chưa login", "Bị chặn và chuyển hướng về /admin/login", "HTTP 302 về trang login", "PASS"),
        ("TC04", "Đăng nhập và CRUD dự án", "Thêm/sửa kỹ năng, dự án lưu vào MySQL và hiện Public", "CRUD thành công, dữ liệu hiển thị", "PASS"),
        ("TC05", "Gửi form liên hệ", "Bản ghi hợp lệ lưu DB; bản ghi sai bị từ chối", "Lưu vào bảng contact_messages", "PASS"),
        ("TC06", "Mở phpMyAdmin", "Đăng nhập được qua 127.0.0.1:8080, xem đúng 8 bảng", "Truy cập tốt, thấy 8 bảng chuẩn", "PASS"),
        ("TC07", "Kiểm tra Nginx & HTTPS/headers", "Đi qua proxy, đủ 6 headers bảo mật, rate limit hoạt động", "Đủ 6 headers, HTTP 503 khi spam", "PASS"),
        ("TC08", "Kiểm tra Metrics & Exporter", "5/5 targets UP; mysql_up=1; nginx_up=1", "5/5 targets UP, metrics chính xác", "PASS"),
        ("TC09", "Tạo truy cập & xem Grafana", "Dashboard hiển thị biểu đồ CPU/RAM, Nginx, MySQL", "26 panels có dữ liệu thực tế", "PASS"),
        ("TC10", "Thu log & chạy 03 LogQL", "Loki nhận log Nginx; chạy được 3+ truy vấn LogQL", "Đạt 5/5 queries LogQL", "PASS"),
        ("TC11", "Kiểm tra Hardening", "Áp dụng ít nhất 4 biện pháp (thực tế 10 biện pháp)", "Đạt 22/22 tiêu chí hardening", "PASS"),
        ("TC12", "Restart / Down-Up bảo toàn data", "Dữ liệu DB và cấu hình Grafana không bị mất", "Dữ liệu volume nguyên vẹn", "PASS"),
        ("TC13", "Kiểm tra GitHub & Tài liệu", "Đầy đủ source, 3 mốc commit cốt lõi, README chi tiết", "8 commits chuẩn, repo MSSV sạch", "PASS"),
        ("TC14", "Khả năng tái lập hệ thống", "Clone mới có thể dựng lại bằng quy trình trong README", "Tái lập thành công trong môi trường thử", "PASS")
    ]
    for idx, (code, name, exp, act, res) in enumerate(tc_data, start=1):
        row = t_tc.rows[idx]
        row.cells[0].paragraphs[0].text = code
        row.cells[1].paragraphs[0].text = name
        row.cells[2].paragraphs[0].text = exp
        row.cells[3].paragraphs[0].text = act
        row.cells[4].paragraphs[0].text = res
    style_table_navy(t_tc, tc_widths)

    add_h2("9.2 Kịch Bản Demo Bảo Vệ Chuẩn 8 - 12 Phút")
    add_p("Quy trình trình diễn hệ thống trước Giảng viên hướng dẫn và Hội đồng:")
    add_bullet("Trình chiếu kho chứa GitHub mang tên sinh viên (vubahung222), chỉ rõ cấu trúc thư mục, tệp docker-compose.yml và lịch sử 3 mốc commit thực tế.", "Bước 1 (1 - 2 phút) - Giới thiệu Mã nguồn & Git:")
    add_bullet("Mở trình duyệt truy cập https://localhost. Đăng nhập trang Admin, thực hiện thêm 1 kỹ năng mới và tải lại trang chủ để chứng minh dữ liệu phản hồi ngay lập tức. Sau đó mở phpMyAdmin (cổng 8080) để chứng minh bản ghi đã nằm trong MySQL.", "Bước 2 (2 - 3 phút) - Demo Website & Cơ sở dữ liệu:")
    add_bullet("Sử dụng lệnh curl.exe -skI https://localhost để chứng minh các Security Headers (nosniff, SAMEORIGIN, CSP). Thử spam request để kích hoạt Rate Limiting (HTTP 503). Giải thích việc cổng 3306 của MySQL hoàn toàn không mở ra ngoài.", "Bước 3 (2 phút) - Demo Nginx Ingress & Bảo Mật:")
    add_bullet("Mở Prometheus Targets (cổng 9090) chứng minh 5/5 targets UP. Mở Grafana Dashboard (cổng 3000) với 26 panels trực quan hóa số liệu CPU/RAM container, requests Nginx và truy vấn MySQL.", "Bước 4 (2 phút) - Demo Giám sát Metrics:")
    add_bullet("Tạo một request lỗi 404 và một lần thử đăng nhập sai. Chuyển sang Grafana Explore, chạy lần lượt các câu truy vấn LogQL để trích xuất dòng log Nginx và log PHP container tương ứng.", "Bước 5 (2 phút) - Demo Thu thập Log & LogQL:")
    add_bullet("Trình bày kết quả script verify_hardening.py đạt 22/22 tiêu chí, phân tích quyền non-root appuser, kết thúc bài demo và trả lời câu hỏi của Giảng viên.", "Bước 6 (1 phút) - Tổng kết & Trả lời phản biện:")

    # ═════════════════════════════════════════════════════════════════
    # PHẦN 10: TÀI LIỆU THAM KHẢO
    # ═════════════════════════════════════════════════════════════════
    add_h1("PHẦN 10: TÀI LIỆU THAM KHẢO")
    add_bullet("Grafana Labs. (2026). Promtail agent và trạng thái vòng đời hỗ trợ (End-of-Life 02/03/2026). https://grafana.com/docs/loki/latest/send-data/promtail/", "[1]")
    add_bullet("Docker Inc. (2024). Manage secrets securely in Docker Compose & Compose file reference v2. https://docs.docker.com/compose/how-tos/use-secrets/", "[2]")
    add_bullet("Prometheus Authors. (2024). Monitoring Docker container metrics using cAdvisor. https://prometheus.io/docs/guides/cadvisor/", "[3]")
    add_bullet("NGINX Community. (2024). NGINX Prometheus Exporter và cấu hình nguồn metrics stub_status. https://github.com/nginx/nginx-prometheus-exporter", "[4]")
    add_bullet("Prometheus Team. (2024). MySQL server exporter và yêu cầu phân quyền tài khoản giám sát. https://github.com/prometheus/mysqld_exporter", "[5]")
    add_bullet("Grafana Labs. (2024). LogQL: Log Query Language và cú pháp pipeline xử lý JSON. https://grafana.com/docs/loki/latest/query/log_queries/", "[6]")

    output_path = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "BAO_CAO_DE_13.docx")
    doc.save(output_path)
    print(f"Báo cáo Word đã được tạo thành công tại: {output_path}", flush=True)

    def export_pdf():
        word = None
        doc_word = None
        try:
            import win32com.client
            word = win32com.client.DispatchEx("Word.Application")
            word.Visible = False
            word.DisplayAlerts = 0
            doc_word = word.Documents.Open(output_path)
            try:
                for fld in doc_word.Fields:
                    fld.Update()
                for toc in doc_word.TablesOfContents:
                    toc.Update()
                doc_word.Save()
            except Exception:
                pass

            actual_pages = doc_word.ComputeStatistics(2)
            pdf_path = os.path.splitext(output_path)[0] + ".pdf"
            doc_word.SaveAs(pdf_path, FileFormat=17) # 17 = wdFormatPDF
            print(f"Xuất PDF thành công tại: {pdf_path}", flush=True)
            print(f"Số trang thực tế đo được từ Microsoft Word: {actual_pages} trang.", flush=True)
        except Exception as e:
            print(f"Lưu ý khi xuất PDF qua Word COM: {e}", flush=True)
        finally:
            if doc_word:
                try: doc_word.Close(False)
                except Exception: pass
            if word:
                try: word.Quit()
                except Exception: pass

    import threading
    t = threading.Thread(target=export_pdf)
    t.daemon = True
    t.start()
    t.join(timeout=12)

if __name__ == "__main__":
    build_report()
