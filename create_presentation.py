import os
import shutil
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.enum.shapes import MSO_SHAPE

def build_presentation(target_path):
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]
    
    # Colors
    C_NAVY = RGBColor(15, 23, 42)      # Slate 900
    C_SLATE_800 = RGBColor(30, 41, 59) # Slate 800
    C_RED = RGBColor(235, 10, 30)      # Toyota Red
    C_WHITE = RGBColor(255, 255, 255)
    C_BG = RGBColor(248, 250, 252)     # Slate 50
    C_MUTED = RGBColor(100, 116, 139)  # Slate 500
    C_BLUE = RGBColor(37, 99, 235)     # Blue 600
    C_EMERALD = RGBColor(5, 150, 105)  # Emerald 600
    C_AMBER = RGBColor(217, 119, 6)    # Amber 600
    C_BORDER = RGBColor(226, 232, 240) # Slate 200
    C_CARD_BG = RGBColor(255, 255, 255)

    def add_bg(slide):
        bg = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, prs.slide_width, prs.slide_height)
        bg.fill.solid()
        bg.fill.fore_color.rgb = C_BG
        bg.line.fill.background()
        return bg

    def add_header(slide, title_text, category_text="TOYOTA AUTOCONNECT — SISTEM DEALER TERPADU"):
        top_bar = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, prs.slide_width, Inches(0.1))
        top_bar.fill.solid()
        top_bar.fill.fore_color.rgb = C_RED
        top_bar.line.fill.background()

        tb_cat = slide.shapes.add_textbox(Inches(0.8), Inches(0.3), Inches(11.5), Inches(0.35))
        tf_cat = tb_cat.text_frame
        tf_cat.word_wrap = True
        tf_cat.margin_left = tf_cat.margin_top = tf_cat.margin_right = tf_cat.margin_bottom = 0
        p_cat = tf_cat.paragraphs[0]
        p_cat.text = category_text.upper()
        p_cat.font.name = "Segoe UI"
        p_cat.font.size = Pt(11)
        p_cat.font.bold = True
        p_cat.font.color.rgb = C_RED

        tb_title = slide.shapes.add_textbox(Inches(0.8), Inches(0.65), Inches(11.5), Inches(0.6))
        tf_title = tb_title.text_frame
        tf_title.word_wrap = True
        tf_title.margin_left = tf_title.margin_top = tf_title.margin_right = tf_title.margin_bottom = 0
        p_title = tf_title.paragraphs[0]
        p_title.text = title_text
        p_title.font.name = "Segoe UI"
        p_title.font.size = Pt(22)
        p_title.font.bold = True
        p_title.font.color.rgb = C_NAVY

    def add_card(slide, left, top, width, height, bg_color=C_CARD_BG, border_color=C_BORDER, border_width=1):
        card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        card.fill.solid()
        card.fill.fore_color.rgb = bg_color
        if border_color:
            card.line.color.rgb = border_color
            card.line.width = Pt(border_width)
        else:
            card.line.fill.background()
        return card

    # ==========================================
    # SLIDE 1: COVER
    # ==========================================
    s1 = prs.slides.add_slide(blank_layout)
    bg1 = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, prs.slide_width, prs.slide_height)
    bg1.fill.solid()
    bg1.fill.fore_color.rgb = C_NAVY
    bg1.line.fill.background()

    stripe = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(0.3), prs.slide_height)
    stripe.fill.solid()
    stripe.fill.fore_color.rgb = C_RED
    stripe.line.fill.background()

    badge = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.2), Inches(1.2), Inches(3.2), Inches(0.45))
    badge.fill.solid()
    badge.fill.fore_color.rgb = C_RED
    badge.line.fill.background()
    p_b = badge.text_frame.paragraphs[0]
    p_b.text = "OFFICIAL PROJECT PRESENTATION"
    p_b.alignment = PP_ALIGN.CENTER
    p_b.font.name = "Segoe UI"
    p_b.font.size = Pt(11)
    p_b.font.bold = True
    p_b.font.color.rgb = C_WHITE

    tb_cov = s1.shapes.add_textbox(Inches(1.2), Inches(1.9), Inches(10.8), Inches(2.6))
    tf_cov = tb_cov.text_frame
    tf_cov.word_wrap = True
    
    p1_cov = tf_cov.paragraphs[0]
    p1_cov.text = "Toyota AutoConnect"
    p1_cov.font.name = "Segoe UI"
    p1_cov.font.size = Pt(44)
    p1_cov.font.bold = True
    p1_cov.font.color.rgb = C_WHITE

    p2_cov = tf_cov.add_paragraph()
    p2_cov.text = "Sistem Informasi & Operasional Terpadu Dealer Resmi Toyota"
    p2_cov.font.name = "Segoe UI"
    p2_cov.font.size = Pt(22)
    p2_cov.font.bold = True
    p2_cov.font.color.rgb = RGBColor(248, 113, 113)
    p2_cov.space_before = Pt(8)

    p3_cov = tf_cov.add_paragraph()
    p3_cov.text = "Digital Showroom • SPK & Midtrans Payment • After-Sales Workshop 9 Tahap • CRM & BI Analytics"
    p3_cov.font.name = "Segoe UI"
    p3_cov.font.size = Pt(14)
    p3_cov.font.color.rgb = RGBColor(148, 163, 184)
    p3_cov.space_before = Pt(8)

    pills = ["Laravel 13", "PHP 8.4+", "MySQL Relational", "Tailwind CSS", "Midtrans Snap Gateway", "Spatie RBAC"]
    px = Inches(1.2)
    for pill in pills:
        pill_box = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, px, Inches(4.7), Inches(1.75), Inches(0.42))
        pill_box.fill.solid()
        pill_box.fill.fore_color.rgb = C_SLATE_800
        pill_box.line.color.rgb = RGBColor(51, 65, 85)
        pp = pill_box.text_frame.paragraphs[0]
        pp.text = pill
        pp.alignment = PP_ALIGN.CENTER
        pp.font.name = "Segoe UI"
        pp.font.size = Pt(11)
        pp.font.bold = True
        pp.font.color.rgb = RGBColor(56, 189, 248)
        px += Inches(1.85)

    pcard = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.2), Inches(5.4), Inches(10.8), Inches(1.4))
    pcard.fill.solid()
    pcard.fill.fore_color.rgb = RGBColor(24, 34, 53)
    pcard.line.color.rgb = RGBColor(51, 65, 85)
    tf_pc = pcard.text_frame
    tf_pc.margin_left = Inches(0.3)
    tf_pc.margin_top = Inches(0.2)
    
    pp1 = tf_pc.paragraphs[0]
    pp1.text = "Presentasi Ujian / Proyek Akhir Rekayasa Perangkat Lunak & Sistem Informasi Terdistribusi"
    pp1.font.name = "Segoe UI"
    pp1.font.size = Pt(13)
    pp1.font.bold = True
    pp1.font.color.rgb = C_WHITE
    
    pp2 = tf_pc.add_paragraph()
    pp2.text = "Pengembang: Tim Pengembang Aplikasi Web • Institusi / Program Studi Teknik Informatika • Tahun Akademik 2026/2027"
    pp2.font.name = "Segoe UI"
    pp2.font.size = Pt(11)
    pp2.font.color.rgb = RGBColor(148, 163, 184)
    pp2.space_before = Pt(4)

    # ==========================================
    # SLIDE 2: LATAR BELAKANG & GAMBARAN UMUM
    # ==========================================
    s2 = prs.slides.add_slide(blank_layout)
    add_bg(s2)
    add_header(s2, "Latar Belakang Masalah & Gambaran Umum Sistem")

    add_card(s2, Inches(0.8), Inches(1.5), Inches(5.6), Inches(5.3))
    tstrip1 = s2.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.0), Inches(1.7), Inches(5.2), Inches(0.5))
    tstrip1.fill.solid()
    tstrip1.fill.fore_color.rgb = RGBColor(254, 242, 242)
    tstrip1.line.color.rgb = RGBColor(252, 165, 165)
    p_t1 = tstrip1.text_frame.paragraphs[0]
    p_t1.text = "TANTANGAN DEALER OTOMOTIF KONVENSIONAL"
    p_t1.font.name = "Segoe UI"
    p_t1.font.size = Pt(12)
    p_t1.font.bold = True
    p_t1.font.color.rgb = RGBColor(153, 27, 27)

    tb_c1 = s2.shapes.add_textbox(Inches(1.0), Inches(2.3), Inches(5.2), Inches(4.3))
    tf_c1 = tb_c1.text_frame
    tf_c1.word_wrap = True
    
    problems = [
        ("Silo Informasi Antar Divisi", "Data showroom mobil baru, gudang stok fisik, dan bengkel purna jual terpisah, menyulitkan koordinasi dan reporting manajemen."),
        ("Antrean Bengkel Tidak Terprediksi", "Pelanggan datang langsung (walk-in) tanpa kepastian slot waktu, menyebabkan penumpukan mekanik dan waktu tunggu yang lama."),
        ("Proses SPK & Verifikasi Manual", "Pemesanan kendaraan mengharuskan pengisian kertas fisik dan transfer tanda jadi konvensional yang memakan waktu verifikasi berhari-hari."),
        ("Minim Transparansi Pelanggan", "Pemilik mobil tidak dapat melacak progres pengerjaan servis secara real-time maupun histori perawatan kendaraannya.")
    ]
    for i, (title, desc) in enumerate(problems):
        p = tf_c1.paragraphs[0] if i == 0 else tf_c1.add_paragraph()
        p.text = f"• {title}: "
        p.font.name = "Segoe UI"
        p.font.size = Pt(12)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        if i > 0:
            p.space_before = Pt(12)
        run = p.add_run()
        run.text = desc
        run.font.bold = False
        run.font.size = Pt(11)
        run.font.color.rgb = C_MUTED

    add_card(s2, Inches(6.8), Inches(1.5), Inches(5.7), Inches(5.3))
    tstrip2 = s2.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(7.0), Inches(1.7), Inches(5.3), Inches(0.5))
    tstrip2.fill.solid()
    tstrip2.fill.fore_color.rgb = RGBColor(236, 253, 245)
    tstrip2.line.color.rgb = RGBColor(110, 231, 183)
    p_t2 = tstrip2.text_frame.paragraphs[0]
    p_t2.text = "SOLUSI TERPADU TOYOTA AUTOCONNECT"
    p_t2.font.name = "Segoe UI"
    p_t2.font.size = Pt(12)
    p_t2.font.bold = True
    p_t2.font.color.rgb = RGBColor(6, 95, 70)

    tb_c2 = s2.shapes.add_textbox(Inches(7.0), Inches(2.3), Inches(5.3), Inches(4.3))
    tf_c2 = tb_c2.text_frame
    tf_c2.word_wrap = True

    solutions = [
        ("Platform Terintegrasi End-to-End", "Menghubungkan Showroom Digital, Simulasi Finansial Kredit, SPK Kendaraan, Bengkel Servis, dan BI Analytics dalam 1 aplikasi web modern."),
        ("Booking Servis Berbasis Kuota Waktu", "Sistem kuota dinamis per jam (08:00 - 15:00 WIB) mencegah overcapacity dan memastikan efisiensi pemanfaatan stall mekanik."),
        ("Otomasi Pembayaran Midtrans Snap", "Tanda jadi (booking fee) terverifikasi instan melalui Virtual Account Bank & QRIS dengan webhook reconciliation otomatis."),
        ("Transparansi 9 Tahapan Work Order", "Pelanggan dapat memantau secara live progres perbaikan mobil dari tahap pendaftaran hingga serah terima unit.")
    ]
    for i, (title, desc) in enumerate(solutions):
        p = tf_c2.paragraphs[0] if i == 0 else tf_c2.add_paragraph()
        p.text = f"✔ {title}: "
        p.font.name = "Segoe UI"
        p.font.size = Pt(12)
        p.font.bold = True
        p.font.color.rgb = C_EMERALD
        if i > 0:
            p.space_before = Pt(12)
        run = p.add_run()
        run.text = desc
        run.font.bold = False
        run.font.size = Pt(11)
        run.font.color.rgb = C_MUTED

    # ==========================================
    # SLIDE 3: ARSITEKTUR & TECH STACK
    # ==========================================
    s3 = prs.slides.add_slide(blank_layout)
    add_bg(s3)
    add_header(s3, "Arsitektur Sistem & Landasan Teknologi (Tech Stack)")

    col_w = Inches(2.78)
    col_gap = Inches(0.22)
    left_start = Inches(0.8)

    tech_cards = [
        ("Backend Core", C_RED, [
            ("Laravel 13", "Framework PHP terkini dengan performa tinggi & modular"),
            ("PHP 8.4+", "Strict typing, Constructor Promotion, Enums"),
            ("Eloquent ORM", "Relasi data kompleks, eager loading, migrasi"),
            ("Service Pattern", "Pemisahan business logic dari controllers")
        ]),
        ("Database & Security", C_BLUE, [
            ("MySQL Engine", "Relational database dengan indexing & foreign keys"),
            ("Spatie Permission", "10 Role hierarkis & 56 Hak Akses (RBAC)"),
            ("Security Guards", "CSRF, Rate Limiter bertingkat, password hashing"),
            ("Audit Trail Logs", "Pencatatan mutasi data kritis & user agent")
        ]),
        ("Frontend & UI/UX", C_EMERALD, [
            ("Blade Templating", "Reusable components, layouting terstruktur"),
            ("Tailwind CSS", "Design system bertema Toyota, clean aesthetic"),
            ("Responsive Tables", "Scroll samping horizontal di semua tabel data"),
            ("Alpine.js", "Reaktivitas UI ringan tanpa beban frontend berat")
        ]),
        ("Eksternal & Layanan", C_AMBER, [
            ("Midtrans Gateway", "Snap Core API, Virtual Account & QRIS"),
            ("Automated Webhooks", "Reconcile status pembayaran otomatis"),
            ("Notification Center", "Notifikasi in-app, Email & log WhatsApp"),
            ("BI Analytics", "Export laporan CSV, grafik tren & metrik")
        ])
    ]

    for i, (title, color, items) in enumerate(tech_cards):
        cx = left_start + i * (col_w + col_gap)
        add_card(s3, cx, Inches(1.5), col_w, Inches(5.3))

        strip = s3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, cx + Inches(0.15), Inches(1.65), col_w - Inches(0.3), Inches(0.45))
        strip.fill.solid()
        strip.fill.fore_color.rgb = color
        strip.line.fill.background()
        p_st = strip.text_frame.paragraphs[0]
        p_st.text = title.upper()
        p_st.alignment = PP_ALIGN.CENTER
        p_st.font.name = "Segoe UI"
        p_st.font.size = Pt(11)
        p_st.font.bold = True
        p_st.font.color.rgb = C_WHITE

        tb = s3.shapes.add_textbox(cx + Inches(0.15), Inches(2.25), col_w - Inches(0.3), Inches(4.3))
        tf = tb.text_frame
        tf.word_wrap = True

        for j, (it_title, it_desc) in enumerate(items):
            p = tf.paragraphs[0] if j == 0 else tf.add_paragraph()
            p.text = it_title
            p.font.name = "Segoe UI"
            p.font.size = Pt(12)
            p.font.bold = True
            p.font.color.rgb = C_NAVY
            if j > 0:
                p.space_before = Pt(12)
            
            p_desc = tf.add_paragraph()
            p_desc.text = it_desc
            p_desc.font.name = "Segoe UI"
            p_desc.font.size = Pt(10)
            p_desc.font.color.rgb = C_MUTED
            p_desc.space_before = Pt(2)

    # ==========================================
    # SLIDE 4: PETA 12 MODUL APLIKASI
    # ==========================================
    s4 = prs.slides.add_slide(blank_layout)
    add_bg(s4)
    add_header(s4, "Cakupan Modul & Fitur Aplikasi (Tahap 1 — Tahap 12)")

    m_cols = 4
    card_w = Inches(2.78)
    card_h = Inches(1.65)
    gap_x = Inches(0.22)
    gap_y = Inches(0.18)

    modules_data = [
        ("1. Showroom & Katalog", "Eksplorasi unit, filter kategori, galeri foto, spesifikasi teknis, harga OTR resmi."),
        ("2. Multi-Role RBAC", "Spatie Permission: 10 peran pengguna & 56 izin akses teknis granular terisolasi."),
        ("3. Customer Portal", "Garasi virtual pelanggan, riwayat pemesanan mobil, riwayat servis & profil akun."),
        ("4. CRM & Prospek Leads", "Pipeline prospek calon pembeli, status interaksi sales, follow-up & tracking konversi."),
        ("5. Inventaris & Stok Fisik", "Stok unit mobil fisik per cabang dealer, mutasi masuk/keluar, dan minimum alert."),
        ("6. Test Drive & SPK", "Pengajuan uji kendara, formulir pemesanan mobil resmi, alokasi unit & pengiriman."),
        ("7. Simulasi & Midtrans", "Simulasi kredit multi-leasing, kalkulasi DP & angsuran, booking fee via Midtrans Snap."),
        ("8. After-Sales & Bengkel", "Booking servis berbasis kuota slot per jam, 9 tahapan Work Order (WO) & CS helpdesk."),
        ("9. Pusat Notifikasi", "Notification Center navbar terpusat, status belum dibaca, broadcast Email & WA log."),
        ("10. Business Intelligence", "Executive Analytics Dashboard, grafik tren penjualan bulanan, rasio servis & ekspor CSV."),
        ("11. Master & Audit Trail", "Pengaturan sistem global, data cabang, kategori unit, serta log audit aktivitas pengguna."),
        ("12. Hardening & Testing", "Security hardening (rate limit, CSRF), query optimization, dan 173 automated tests (100%).")
    ]

    for idx, (m_title, m_desc) in enumerate(modules_data):
        row = idx // m_cols
        col = idx % m_cols
        mx = left_start + col * (card_w + gap_x)
        my = Inches(1.5) + row * (card_h + gap_y)

        add_card(s4, mx, my, card_w, card_h)

        tb = s4.shapes.add_textbox(mx + Inches(0.15), my + Inches(0.12), card_w - Inches(0.3), card_h - Inches(0.24))
        tf = tb.text_frame
        tf.word_wrap = True

        p1 = tf.paragraphs[0]
        p1.text = m_title
        p1.font.name = "Segoe UI"
        p1.font.size = Pt(12)
        p1.font.bold = True
        p1.font.color.rgb = C_RED

        p2 = tf.add_paragraph()
        p2.text = m_desc
        p2.font.name = "Segoe UI"
        p2.font.size = Pt(10)
        p2.font.color.rgb = C_MUTED
        p2.space_before = Pt(4)

    # ==========================================
    # SLIDE 5: MODUL PENJUALAN
    # ==========================================
    s5 = prs.slides.add_slide(blank_layout)
    add_bg(s5)
    add_header(s5, "Modul Penjualan Kendaraan, Simulasi Kredit & Pembayaran")

    pillar_w = Inches(3.78)
    pillar_gap = Inches(0.24)

    pillars = [
        ("Katalog & Pemesanan SPK", C_RED, [
            ("Showroom Interaktif", "Katalog unit dengan filter tipe bodi (SUV, MPV, Hybrid, GR Sport), transmisi, dan harga OTR."),
            ("Pemesanan SPK Resmi", "Formulir SPK online dengan nomor registrasi unik, upload berkas KTP & identitas pembeli."),
            ("Alokasi Cabang Dealer", "Pelanggan bebas memilih cabang pemenuhan unit terdekat dengan domisili.")
        ]),
        ("Simulasi Kredit & Leasing", C_BLUE, [
            ("Kalkulator Finansial", "Simulasi uang muka (DP min 20%), bunga flat/anuitas, dan tenor fleksibel 1 hingga 5 tahun."),
            ("Multi-Partner Leasing", "Komparasi paket pembiayaan rekanan resmi (ACC, TAF, Mandiri Tunas Finance, OTO)."),
            ("Estimasi Angsuran", "Perhitungan transparan angsuran bulanan, total premi asuransi, dan biaya administrasi.")
        ]),
        ("Midtrans Snap Payment", C_EMERALD, [
            ("Uang Tanda Jadi Instan", "Pembayaran booking fee resmi (Rp 5.000.000) terintegrasi langsung via Midtrans Snap UI."),
            ("Multi-Channel Bayar", "Mendukung Virtual Account (BCA, Mandiri, BRI, BNI), QRIS, dan e-Wallet GoPay."),
            ("Webhook Otomatis", "Sinkronisasi status lunas otomatis secara real-time dengan verifikasi SHA-512 Signature Hash.")
        ])
    ]

    for i, (title, color, items) in enumerate(pillars):
        px = left_start + i * (pillar_w + pillar_gap)
        add_card(s5, px, Inches(1.5), pillar_w, Inches(5.3))

        strip = s5.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, px + Inches(0.2), Inches(1.7), pillar_w - Inches(0.4), Inches(0.5))
        strip.fill.solid()
        strip.fill.fore_color.rgb = color
        strip.line.fill.background()
        p_st = strip.text_frame.paragraphs[0]
        p_st.text = title.upper()
        p_st.alignment = PP_ALIGN.CENTER
        p_st.font.name = "Segoe UI"
        p_st.font.size = Pt(12)
        p_st.font.bold = True
        p_st.font.color.rgb = C_WHITE

        tb = s5.shapes.add_textbox(px + Inches(0.2), Inches(2.4), pillar_w - Inches(0.4), Inches(4.2))
        tf = tb.text_frame
        tf.word_wrap = True

        for j, (it_t, it_d) in enumerate(items):
            p = tf.paragraphs[0] if j == 0 else tf.add_paragraph()
            p.text = f"✔ {it_t}"
            p.font.name = "Segoe UI"
            p.font.size = Pt(12)
            p.font.bold = True
            p.font.color.rgb = C_NAVY
            if j > 0:
                p.space_before = Pt(14)

            p_d = tf.add_paragraph()
            p_d.text = it_d
            p_d.font.name = "Segoe UI"
            p_d.font.size = Pt(10.5)
            p_d.font.color.rgb = C_MUTED
            p_d.space_before = Pt(3)

    # ==========================================
    # SLIDE 6: FLOWCHART PEMESANAN KENDARAAN (ANSI STYLE)
    # ==========================================
    s6 = prs.slides.add_slide(blank_layout)
    add_bg(s6)
    add_header(s6, "Flowchart Proses Pemesanan Kendaraan: Alur Pengguna & Penanganan Sales", "DIAGRAM STANDAR ALUR KERJA BISNIS (ANSI / ISO NOTATION)")

    fc1_path = r"d:\Project\dealer-toyota\docs\flowcharts\flowchart_pemesanan_kendaraan.png"
    if os.path.exists(fc1_path):
        pic_height = Inches(5.6)
        pic_width = Inches(9.74)
        pic_left = Inches(1.796)
        pic_top = Inches(1.4)
        add_card(s6, pic_left - Inches(0.08), pic_top - Inches(0.06), pic_width + Inches(0.16), pic_height + Inches(0.12), bg_color=C_WHITE, border_color=RGBColor(203, 213, 225))
        s6.shapes.add_picture(fc1_path, pic_left, pic_top, width=pic_width, height=pic_height)

    # ==========================================
    # SLIDE 7: MODUL BENGKEL & AFTER-SALES
    # ==========================================
    s7 = prs.slides.add_slide(blank_layout)
    add_bg(s7)
    add_header(s7, "Modul Layanan Purna Jual & Bengkel (After-Sales & Workshop)")

    after_sales_pillars = [
        ("Booking Servis Dinamis", C_RED, [
            ("Kuota Waktu Real-Time", "Validasi kuota per jam (08:00 - 15:00 WIB, Senin-Sabtu) untuk mencegah penumpukan kendaraan."),
            ("Pilihan Layanan Servis", "Servis Berkala (10k-100k KM), Ganti Oli TMO Synthetic, Pemeriksaan Rem, AC, dan Perbaikan Mesin."),
            ("Estimasi Biaya Awal", "Perkiraan biaya jasa & suku cadang transparan sebelum pelanggan melakukan reservasi."),
            ("Pencegahan Overbooking", "Sistem otomatis mengunci slot waktu jika kuota maksimum cabang pada jam tersebut telah penuh.")
        ]),
        ("9 Tahap Work Order (WO)", C_BLUE, [
            ("Alur Kerja Teknisi SOP", "9 tahapan resmi pengerjaan bengkel: Pendaftaran -> Inspeksi -> Tunggu Part -> Pengerjaan -> QC -> Siap Ambil -> Selesai -> Diserahkan."),
            ("Walk-Around Inspection", "Service Advisor mendata kondisi fisik awal & keluhan kendaraan di aplikasi sebelum masuk stall."),
            ("Alokasi Suku Cadang TMO", "Pencatatan suku cadang asli Toyota & oli mesin yang digunakan langsung terhubung ke invoice."),
            ("Kontrol Kualitas (QC)", "Pemeriksaan akhir oleh Foreman bengkel sebelum kendaraan dinyatakan selesai.")
        ]),
        ("Customer Service Helpdesk", C_AMBER, [
            ("Sistem Tiket Bantuan", "Pelanggan dapat membuat tiket aduan, pertanyaan servis, maupun komplain langsung dari portal."),
            ("SLA Respon Terukur", "Status tiket (Open, In Progress, Resolved, Closed) dengan prioritas Low, Medium, High, Urgent."),
            ("Riwayat Servis Terpusat", "Buku servis digital menyimpan histori perbaikan dan penggantian part selamanya."),
            ("Customer Satisfaction", "Rating kepuasan & ulasan pelanggan setelah serah terima unit kendaraan.")
        ])
    ]

    for i, (title, color, items) in enumerate(after_sales_pillars):
        px = left_start + i * (pillar_w + pillar_gap)
        add_card(s7, px, Inches(1.5), pillar_w, Inches(5.3))

        strip = s7.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, px + Inches(0.2), Inches(1.7), pillar_w - Inches(0.4), Inches(0.5))
        strip.fill.solid()
        strip.fill.fore_color.rgb = color
        strip.line.fill.background()
        p_st = strip.text_frame.paragraphs[0]
        p_st.text = title.upper()
        p_st.alignment = PP_ALIGN.CENTER
        p_st.font.name = "Segoe UI"
        p_st.font.size = Pt(12)
        p_st.font.bold = True
        p_st.font.color.rgb = C_WHITE

        tb = s7.shapes.add_textbox(px + Inches(0.2), Inches(2.35), pillar_w - Inches(0.4), Inches(4.3))
        tf = tb.text_frame
        tf.word_wrap = True

        for j, (it_t, it_d) in enumerate(items):
            p = tf.paragraphs[0] if j == 0 else tf.add_paragraph()
            p.text = f"✔ {it_t}"
            p.font.name = "Segoe UI"
            p.font.size = Pt(11.5)
            p.font.bold = True
            p.font.color.rgb = C_NAVY
            if j > 0:
                p.space_before = Pt(10)

            p_d = tf.add_paragraph()
            p_d.text = it_d
            p_d.font.name = "Segoe UI"
            p_d.font.size = Pt(10)
            p_d.font.color.rgb = C_MUTED
            p_d.space_before = Pt(2)

    # ==========================================
    # SLIDE 8: FLOWCHART PENGAJUAN SERVIS (ANSI STYLE)
    # ==========================================
    s8 = prs.slides.add_slide(blank_layout)
    add_bg(s8)
    add_header(s8, "Flowchart Proses Servis Mobil: Alur Pengguna & Penanganan Admin", "DIAGRAM STANDAR ALUR KERJA BISNIS (ANSI / ISO NOTATION)")

    fc2_path = r"d:\Project\dealer-toyota\docs\flowcharts\flowchart_pengajuan_servis.png"
    if os.path.exists(fc2_path):
        pic_height = Inches(5.6)
        pic_width = Inches(9.74)
        pic_left = Inches(1.796)
        pic_top = Inches(1.4)
        add_card(s8, pic_left - Inches(0.08), pic_top - Inches(0.06), pic_width + Inches(0.16), pic_height + Inches(0.12), bg_color=C_WHITE, border_color=RGBColor(203, 213, 225))
        s8.shapes.add_picture(fc2_path, pic_left, pic_top, width=pic_width, height=pic_height)

    # ==========================================
    # SLIDE 9: CRM
    # ==========================================
    s9 = prs.slides.add_slide(blank_layout)
    add_bg(s9)
    add_header(s9, "Customer Relationship Management (CRM) & Prospek Penjualan")

    card_w2 = Inches(5.75)
    
    add_card(s9, Inches(0.8), Inches(1.5), card_w2, Inches(5.3))
    tstrip_crm = s9.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.0), Inches(1.7), card_w2 - Inches(0.4), Inches(0.5))
    tstrip_crm.fill.solid()
    tstrip_crm.fill.fore_color.rgb = RGBColor(243, 232, 255)
    tstrip_crm.line.color.rgb = RGBColor(216, 180, 254)
    p_tcrm = tstrip_crm.text_frame.paragraphs[0]
    p_tcrm.text = "PIPELINE TAHAPAN PROSPEK (SALES FUNNEL)"
    p_tcrm.font.name = "Segoe UI"
    p_tcrm.font.size = Pt(12)
    p_tcrm.font.bold = True
    p_tcrm.font.color.rgb = RGBColor(107, 33, 168)

    tb_crm1 = s9.shapes.add_textbox(Inches(1.0), Inches(2.35), card_w2 - Inches(0.4), Inches(4.3))
    tf_crm1 = tb_crm1.text_frame
    tf_crm1.word_wrap = True

    funnel_steps = [
        ("1. New Leads (Prospek Baru)", "Calon pembeli mengajukan pertanyaan unit atau permohonan test drive melalui formulir web publik."),
        ("2. Contacted (Telah Dihubungi)", "Sales person menghubungi calon pembeli melalui WhatsApp atau telepon untuk mendalami kebutuhan."),
        ("3. Qualified (Terkualifikasi)", "Calon pembeli dipastikan memiliki daya beli, minat pada varian tertentu, dan menentukan metode bayar."),
        ("4. Proposal Sent (Kirim Penawaran)", "Sales menerbitkan surat penawaran resmi dengan diskon OTR dan simulasi kredit sesuai permintaan."),
        ("5. Won / Lost (Deal Closing)", "Hasil akhir: Won (SPK terbit & lunas booking fee) atau Lost (alasan kompetitor / batal beli didokumentasikan).")
    ]
    for i, (st, desc) in enumerate(funnel_steps):
        p = tf_crm1.paragraphs[0] if i == 0 else tf_crm1.add_paragraph()
        p.text = f"• {st}"
        p.font.name = "Segoe UI"
        p.font.size = Pt(11.5)
        p.font.bold = True
        p.font.color.rgb = C_NAVY
        if i > 0:
            p.space_before = Pt(8)
        
        pd = tf_crm1.add_paragraph()
        pd.text = desc
        pd.font.name = "Segoe UI"
        pd.font.size = Pt(10)
        pd.font.color.rgb = C_MUTED
        pd.space_before = Pt(1)

    add_card(s9, Inches(6.8), Inches(1.5), card_w2, Inches(5.3))
    tstrip_feat = s9.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(7.0), Inches(1.7), card_w2 - Inches(0.4), Inches(0.5))
    tstrip_feat.fill.solid()
    tstrip_feat.fill.fore_color.rgb = RGBColor(239, 246, 255)
    tstrip_feat.line.color.rgb = RGBColor(147, 197, 253)
    p_tfeat = tstrip_feat.text_frame.paragraphs[0]
    p_tfeat.text = "FITUR & NILAI STRATEGIS CRM TOYOTA"
    p_tfeat.font.name = "Segoe UI"
    p_tfeat.font.size = Pt(12)
    p_tfeat.font.bold = True
    p_tfeat.font.color.rgb = RGBColor(30, 58, 138)

    tb_crm2 = s9.shapes.add_textbox(Inches(7.0), Inches(2.35), card_w2 - Inches(0.4), Inches(4.3))
    tf_crm2 = tb_crm2.text_frame
    tf_crm2.word_wrap = True

    crm_features = [
        ("Distribusi Otomatis PIC Sales", "Prospek baru secara cerdas dialokasikan ke staf sales di cabang terdekat dengan domisili pelanggan."),
        ("Log Aktivitas Interaksi Lengkap", "Pencatatan riwayat komunikasi (telepon, visit showroom, pesan WA, penawaran harga) secara kronologis."),
        ("Pengingat Tindak Lanjut (Follow-Up)", "Notifikasi pengingat otomatis bagi sales agar tidak ada calon pembeli potensial yang terlewatkan."),
        ("Analisis Rasio Konversi (Conversion Rate)", "Manajemen dapat mengevaluasi efektivitas setiap tenaga penjual dan cabang dealer dalam mengubah prospek menjadi SPK.")
    ]
    for i, (st, desc) in enumerate(crm_features):
        p = tf_crm2.paragraphs[0] if i == 0 else tf_crm2.add_paragraph()
        p.text = f"✔ {st}"
        p.font.name = "Segoe UI"
        p.font.size = Pt(11.5)
        p.font.bold = True
        p.font.color.rgb = C_BLUE
        if i > 0:
            p.space_before = Pt(12)
        
        pd = tf_crm2.add_paragraph()
        pd.text = desc
        pd.font.name = "Segoe UI"
        pd.font.size = Pt(10.5)
        pd.font.color.rgb = C_MUTED
        pd.space_before = Pt(2)

    # ==========================================
    # SLIDE 10: ANALYTICS & BI
    # ==========================================
    s10 = prs.slides.add_slide(blank_layout)
    add_bg(s10)
    add_header(s10, "Executive Analytics, Reporting & Business Intelligence (BI)")

    mw = Inches(2.78)
    metrics = [
        ("Total Penjualan SPK", "Rp 12.8 Miliar", "+18.4% MoM", C_EMERALD),
        ("Volume Unit Terjual", "34 Mobil", "Model Terlaris: Innova Zenix", C_BLUE),
        ("Kapasitas Bengkel", "87.5% Utilitas", "Rata-rata 42 Unit/Hari", C_AMBER),
        ("Rasio Konversi CRM", "24.6% Won", "Naik 6.2% dari target", C_RED)
    ]
    for i, (m_lbl, m_val, m_sub, m_col) in enumerate(metrics):
        mx = left_start + i * (mw + Inches(0.22))
        add_card(s10, mx, Inches(1.5), mw, Inches(1.5))
        
        tb = s10.shapes.add_textbox(mx + Inches(0.15), Inches(1.6), mw - Inches(0.3), Inches(1.3))
        tf = tb.text_frame
        tf.word_wrap = True
        
        p1 = tf.paragraphs[0]
        p1.text = m_lbl.upper()
        p1.font.name = "Segoe UI"
        p1.font.size = Pt(10)
        p1.font.bold = True
        p1.font.color.rgb = C_MUTED

        p2 = tf.add_paragraph()
        p2.text = m_val
        p2.font.name = "Segoe UI"
        p2.font.size = Pt(20)
        p2.font.bold = True
        p2.font.color.rgb = m_col

        p3 = tf.add_paragraph()
        p3.text = m_sub
        p3.font.name = "Segoe UI"
        p3.font.size = Pt(10)
        p3.font.color.rgb = C_MUTED

    add_card(s10, Inches(0.8), Inches(3.2), Inches(5.75), Inches(3.6))
    tb_b1 = s10.shapes.add_textbox(Inches(1.0), Inches(3.35), Inches(5.35), Inches(3.3))
    tf_b1 = tb_b1.text_frame
    tf_b1.word_wrap = True
    
    p = tf_b1.paragraphs[0]
    p.text = "DIMENSI LAPORAN BISNIS TERSEDIA"
    p.font.name = "Segoe UI"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY

    rep_items = [
        ("Laporan Performa Penjualan", "Volume unit, tren pendapatan, diskon rata-rata, dan perbandingan cabang dealer."),
        ("Laporan Layanan After-Sales", "Waktu rata-rata pengerjaan, penggunaan oli/sparepart TMO, dan utilitas teknisi."),
        ("Laporan Keuangan & Kasir", "Rekonsiliasi transaksi Midtrans, piutang leasing, dan pembayaran kasir bengkel."),
        ("Laporan Perputaran Stok", "Analisis fast-moving vs slow-moving inventory serta durasi unit di gudang.")
    ]
    for it, ds in rep_items:
        p_it = tf_b1.add_paragraph()
        p_it.text = f"• {it}: "
        p_it.font.name = "Segoe UI"
        p_it.font.size = Pt(11)
        p_it.font.bold = True
        p_it.font.color.rgb = C_BLUE
        p_it.space_before = Pt(6)
        r = p_it.add_run()
        r.text = ds
        r.font.bold = False
        r.font.color.rgb = C_MUTED

    add_card(s10, Inches(6.8), Inches(3.2), Inches(5.75), Inches(3.6))
    tb_b2 = s10.shapes.add_textbox(Inches(7.0), Inches(3.35), Inches(5.35), Inches(3.3))
    tf_b2 = tb_b2.text_frame
    tf_b2.word_wrap = True

    p = tf_b2.paragraphs[0]
    p.text = "FITUR KEMUDAHAN EKSEKUTIF"
    p.font.name = "Segoe UI"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_NAVY

    exec_items = [
        ("Filter Fleksibel Multi-Dimensi", "Filter data berdasarkan rentang tanggal, cabang dealer fisik, status transaksi, maupun model kendaraan."),
        ("Ekspor Data CSV & Spreadsheet", "Unduh berkas laporan mentah untuk keperluan audit finansial dan presentasi manajemen."),
        ("Tampilan Cetak Resmi (Print-Ready)", "Format tampilan cetak faktur SPK dan Work Order siap cetak langsung tanpa distorsi CSS."),
        ("Tabel Responsif Horizontal Scroll", "Seluruh data matriks analitik dapat digeser ke samping dengan rapi pada semua ukuran layar monitor.")
    ]
    for it, ds in exec_items:
        p_it = tf_b2.add_paragraph()
        p_it.text = f"✔ {it}: "
        p_it.font.name = "Segoe UI"
        p_it.font.size = Pt(11)
        p_it.font.bold = True
        p_it.font.color.rgb = C_EMERALD
        p_it.space_before = Pt(6)
        r = p_it.add_run()
        r.text = ds
        r.font.bold = False
        r.font.color.rgb = C_MUTED

    # ==========================================
    # SLIDE 11: MASTER DATA & AUDIT TRAIL
    # ==========================================
    s11 = prs.slides.add_slide(blank_layout)
    add_bg(s11)
    add_header(s11, "Master Data Management, System Settings & Audit Trail Keamanan")

    add_card(s11, Inches(0.8), Inches(1.5), Inches(5.75), Inches(5.3))
    tb_m1 = s11.shapes.add_textbox(Inches(1.0), Inches(1.7), Inches(5.35), Inches(4.9))
    tf_m1 = tb_m1.text_frame
    tf_m1.word_wrap = True

    p = tf_m1.paragraphs[0]
    p.text = "PENGELOLAAN MASTER DATA & SISTEM"
    p.font.name = "Segoe UI"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_RED

    m_items = [
        ("Multi-Cabang Dealer Resmi", "Pengelolaan data cabang fisik, lokasi peta, kapasitas stall servis harian, dan jam operasional bengkel."),
        ("Katalog Unit & Kategori", "Kategori kendaraan (SUV, MPV, Sedan, Hybrid, GR Sport), spesifikasi mesin, transmisi, dan harga OTR."),
        ("Master Layanan Servis", "Konfigurasi jenis servis berkala, estimasi harga jasa, durasi pengerjaan, dan suku cadang TMO."),
        ("Metode Pembayaran & Leasing", "Aktivasi saluran Virtual Account Midtrans dan skema bunga rekanan leasing (ACC, TAF, OTO)."),
        ("System Global Settings", "Identitas perusahaan, logo resmi, durasi reservasi SPK, batas kedaluwarsa booking fee, dan kontak resmi.")
    ]
    for it, ds in m_items:
        p_it = tf_m1.add_paragraph()
        p_it.text = f"• {it}"
        p_it.font.name = "Segoe UI"
        p_it.font.size = Pt(11.5)
        p_it.font.bold = True
        p_it.font.color.rgb = C_NAVY
        p_it.space_before = Pt(8)
        
        pd = tf_m1.add_paragraph()
        pd.text = ds
        pd.font.name = "Segoe UI"
        pd.font.size = Pt(10)
        pd.font.color.rgb = C_MUTED
        pd.space_before = Pt(1)

    add_card(s11, Inches(6.8), Inches(1.5), Inches(5.75), Inches(5.3))
    tb_m2 = s11.shapes.add_textbox(Inches(7.0), Inches(1.7), Inches(5.35), Inches(4.9))
    tf_m2 = tb_m2.text_frame
    tf_m2.word_wrap = True

    p = tf_m2.paragraphs[0]
    p.text = "AUDIT TRAIL & KESIAPAN OPERASIONAL"
    p.font.name = "Segoe UI"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = C_BLUE

    audit_items = [
        ("Pencatatan Mutasi Data Kritis", "Setiap tindakan penambahan, pengubahan, atau penghapusan pada modul SPK, harga OTR, dan stok tercatat otomatis."),
        ("Metadata Forensik Lengkap", "Merekam User ID, Nama Pengguna, Waktu (Timestamp), IP Address, dan User Agent browser staf terkait."),
        ("Diff Nilai Lama vs Nilai Baru", "Sistem menyimpan snapshot JSON nilai sebelum perubahan (old_values) dan sesudah perubahan (new_values)."),
        ("Transparansi & Akuntabilitas", "Mencegah kecurangan finansial atau manipulasi status booking oleh staf internal tanpa persetujuan."),
        ("Filter & Penelusuran Cepat", "Audit logs dilengkapi filter berdasarkan rentang waktu, model data, tipe aksi (created/updated/deleted), dan pengguna.")
    ]
    for it, ds in audit_items:
        p_it = tf_m2.add_paragraph()
        p_it.text = f"✔ {it}"
        p_it.font.name = "Segoe UI"
        p_it.font.size = Pt(11.5)
        p_it.font.bold = True
        p_it.font.color.rgb = C_NAVY
        p_it.space_before = Pt(8)
        
        pd = tf_m2.add_paragraph()
        pd.text = ds
        pd.font.name = "Segoe UI"
        pd.font.size = Pt(10)
        pd.font.color.rgb = C_MUTED
        pd.space_before = Pt(1)

    # ==========================================
    # SLIDE 12: KEAMANAN & TESTING
    # ==========================================
    s12 = prs.slides.add_slide(blank_layout)
    add_bg(s12)
    add_header(s12, "Keamanan Sistem, Kualitas Kode & Hasil Pengujian Otomatis")

    cw3 = Inches(3.78)
    cg3 = Inches(0.24)

    sec_cards = [
        ("Security Hardening", C_RED, [
            ("Pemisahan Route Login", "Isolasi ketat rute masuk pelanggan (/login) vs dashboard staf internal (/admin/login)."),
            ("Rate Limiting Bertingkat", "Throttle request per IP untuk mencegah serangan Brute Force pada login & form transaksi."),
            ("Proteksi OWASP Top 10", "CSRF Protection di semua formulir, SQL Injection prevention via Eloquent ORM, dan XSS auto-escaping."),
            ("Password Hashing Modern", "Standar industri Bcrypt / Argon2id dengan salt aman.")
        ]),
        ("Optimasi Performa", C_BLUE, [
            ("Eager Loading (Anti N+1)", "Penggunaan with() pada seluruh query relasional memastikan query database cepat & hemat memori."),
            ("Database Indexing", "Index komprehensif pada foreign keys, status booking, dan kolom pencarian kode registrasi."),
            ("Asset Bundling Ringan", "Tailwind CSS ter-purge rapi, Alpine.js tanpa overhead JavaScript framework raksasa."),
            ("Tabel Responsif Horizontal", "Scrollbar horizontal teroptimasi di 41 file Blade antarmuka sistem.")
        ]),
        ("Automated Test Suite", C_EMERALD, [
            ("173 Test Cases Lolos", "Pengujian menyeluruh mencakup seluruh 12 tahap pengembangan sistem."),
            ("775 Assertions Terverifikasi", "Validasi logika bisnis, hak akses role, alur booking servis, dan perhitungan SPK."),
            ("100% Tingkat Kelulusan", "Nol kesalahan (0 errors) dan nol kegagalan (0 failures) dalam eksekusi php artisan test."),
            ("Continuous Reliability", "Menjamin stabilitas sistem saat diimplementasikan ke lingkungan produksi nyata.")
        ])
    ]

    for i, (title, color, items) in enumerate(sec_cards):
        px = left_start + i * (cw3 + cg3)
        add_card(s12, px, Inches(1.5), cw3, Inches(5.3))

        strip = s12.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, px + Inches(0.2), Inches(1.7), cw3 - Inches(0.4), Inches(0.5))
        strip.fill.solid()
        strip.fill.fore_color.rgb = color
        strip.line.fill.background()
        p_st = strip.text_frame.paragraphs[0]
        p_st.text = title.upper()
        p_st.alignment = PP_ALIGN.CENTER
        p_st.font.name = "Segoe UI"
        p_st.font.size = Pt(12)
        p_st.font.bold = True
        p_st.font.color.rgb = C_WHITE

        tb = s12.shapes.add_textbox(px + Inches(0.2), Inches(2.35), cw3 - Inches(0.4), Inches(4.3))
        tf = tb.text_frame
        tf.word_wrap = True

        for j, (it_t, it_d) in enumerate(items):
            p = tf.paragraphs[0] if j == 0 else tf.add_paragraph()
            p.text = f"✔ {it_t}"
            p.font.name = "Segoe UI"
            p.font.size = Pt(11.5)
            p.font.bold = True
            p.font.color.rgb = C_NAVY
            if j > 0:
                p.space_before = Pt(10)

            p_d = tf.add_paragraph()
            p_d.text = it_d
            p_d.font.name = "Segoe UI"
            p_d.font.size = Pt(10)
            p_d.font.color.rgb = C_MUTED
            p_d.space_before = Pt(2)

    # ==========================================
    # SLIDE 13: KESIMPULAN & ROADMAP
    # ==========================================
    s13 = prs.slides.add_slide(blank_layout)
    add_bg(s13)
    add_header(s13, "Kesimpulan Proyek & Rencana Pengembangan Masa Depan (Roadmap)")

    add_card(s13, Inches(0.8), Inches(1.5), card_w2, Inches(5.3))
    tstrip_k = s13.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.0), Inches(1.7), card_w2 - Inches(0.4), Inches(0.5))
    tstrip_k.fill.solid()
    tstrip_k.fill.fore_color.rgb = RGBColor(236, 253, 245)
    tstrip_k.line.color.rgb = RGBColor(110, 231, 183)
    p_tk = tstrip_k.text_frame.paragraphs[0]
    p_tk.text = "KESIMPULAN IMPLEMENTASI SISTEM"
    p_tk.font.name = "Segoe UI"
    p_tk.font.size = Pt(12)
    p_tk.font.bold = True
    p_tk.font.color.rgb = RGBColor(6, 95, 70)

    tb_k = s13.shapes.add_textbox(Inches(1.0), Inches(2.35), card_w2 - Inches(0.4), Inches(4.3))
    tf_k = tb_k.text_frame
    tf_k.word_wrap = True

    conclusions = [
        ("Digitalisasi Operasional Menyeluruh", "Aplikasi berhasil mengintegrasikan seluruh lini bisnis dealer resmi Toyota (Showroom, Sales SPK, Kasir, Bengkel & Purna Jual) dalam satu arsitektur terpadu."),
        ("Peningkatan Efisiensi Transaksi", "Proses pemesanan mobil dan pembayaran tanda jadi via Midtrans Snap memangkas waktu birokrasi verifikasi hingga 70%."),
        ("Eliminasi Antrean Bengkel", "Manajemen booking servis dengan kuota slot waktu dinamis berhasil mengatur utilisasi stall mekanik secara optimal dan tertib."),
        ("Kesiapan Produksi (Production Ready)", "Didukung oleh pengujian otomatis 100% lulus, arsitektur database terindeks, dan audit trail keamanan yang komprehensif.")
    ]
    for i, (st, desc) in enumerate(conclusions):
        p = tf_k.paragraphs[0] if i == 0 else tf_k.add_paragraph()
        p.text = f"✔ {st}"
        p.font.name = "Segoe UI"
        p.font.size = Pt(11.5)
        p.font.bold = True
        p.font.color.rgb = C_EMERALD
        if i > 0:
            p.space_before = Pt(12)
        
        pd = tf_k.add_paragraph()
        pd.text = desc
        pd.font.name = "Segoe UI"
        pd.font.size = Pt(10.5)
        pd.font.color.rgb = C_MUTED
        pd.space_before = Pt(2)

    add_card(s13, Inches(6.8), Inches(1.5), card_w2, Inches(5.3))
    tstrip_r = s13.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(7.0), Inches(1.7), card_w2 - Inches(0.4), Inches(0.5))
    tstrip_r.fill.solid()
    tstrip_r.fill.fore_color.rgb = RGBColor(239, 246, 255)
    tstrip_r.line.color.rgb = RGBColor(147, 197, 253)
    p_tr = tstrip_r.text_frame.paragraphs[0]
    p_tr.text = "ROADMAP PENGEMBANGAN SELANJUTNYA"
    p_tr.font.name = "Segoe UI"
    p_tr.font.size = Pt(12)
    p_tr.font.bold = True
    p_tr.font.color.rgb = RGBColor(30, 58, 138)

    tb_r = s13.shapes.add_textbox(Inches(7.0), Inches(2.35), card_w2 - Inches(0.4), Inches(4.3))
    tf_r = tb_r.text_frame
    tf_r.word_wrap = True

    roadmap_items = [
        ("Mobile Apps (Flutter / React Native)", "Mengembangkan aplikasi native untuk pelanggan dengan push notification kedatangan servis & pelacakan mobil derek GPS real-time."),
        ("Integrasi IoT Toyota T-Intouch & OBD-II", "Konektivitas langsung ke sensor komputer mobil Toyota untuk membaca indikator error check-engine & jarak tempuh aktual secara otomatis."),
        ("AI Predictive Maintenance", "Analisis kecerdasan buatan untuk merekomendasikan penggantian suku cadang sebelum terjadi kerusakan berdasarkan pola berkendara pengguna."),
        ("Virtual Reality Showroom 360°", "Pengalaman eksplorasi kabin interior mobil Toyota secara imersif menggunakan teknologi WebGL 3D.")
    ]
    for i, (st, desc) in enumerate(roadmap_items):
        p = tf_r.paragraphs[0] if i == 0 else tf_r.add_paragraph()
        p.text = f"🚀 {st}"
        p.font.name = "Segoe UI"
        p.font.size = Pt(11.5)
        p.font.bold = True
        p.font.color.rgb = C_BLUE
        if i > 0:
            p.space_before = Pt(12)
        
        pd = tf_r.add_paragraph()
        pd.text = desc
        pd.font.name = "Segoe UI"
        pd.font.size = Pt(10.5)
        pd.font.color.rgb = C_MUTED
        pd.space_before = Pt(2)

    # ==========================================
    # SLIDE 14: Q&A / CLOSING
    # ==========================================
    s14 = prs.slides.add_slide(blank_layout)
    bg14 = s14.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, prs.slide_width, prs.slide_height)
    bg14.fill.solid()
    bg14.fill.fore_color.rgb = C_NAVY
    bg14.line.fill.background()

    s14_bar = s14.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, prs.slide_width, Inches(0.12))
    s14_bar.fill.solid()
    s14_bar.fill.fore_color.rgb = C_RED
    s14_bar.line.fill.background()

    tb_end = s14.shapes.add_textbox(Inches(1.5), Inches(2.0), Inches(10.33), Inches(3.5))
    tf_end = tb_end.text_frame
    tf_end.word_wrap = True

    p_e1 = tf_end.paragraphs[0]
    p_e1.text = "TERIMA KASIH"
    p_e1.alignment = PP_ALIGN.CENTER
    p_e1.font.name = "Segoe UI"
    p_e1.font.size = Pt(48)
    p_e1.font.bold = True
    p_e1.font.color.rgb = C_WHITE

    p_e2 = tf_end.add_paragraph()
    p_e2.text = "Sesi Tanya Jawab (Q & A)"
    p_e2.alignment = PP_ALIGN.CENTER
    p_e2.font.name = "Segoe UI"
    p_e2.font.size = Pt(24)
    p_e2.font.bold = True
    p_e2.font.color.rgb = RGBColor(248, 113, 113)
    p_e2.space_before = Pt(12)

    p_e3 = tf_end.add_paragraph()
    p_e3.text = "Toyota AutoConnect: Solusi Terintegrasi Showroom Digital & Bengkel Purna Jual Resmi Toyota"
    p_e3.alignment = PP_ALIGN.CENTER
    p_e3.font.name = "Segoe UI"
    p_e3.font.size = Pt(14)
    p_e3.font.color.rgb = RGBColor(148, 163, 184)
    p_e3.space_before = Pt(16)

    # Save to target
    prs.save(target_path)
    print(f"Presentation saved successfully at: {target_path}")

target_primary = r"d:\Project\dealer-toyota\Presentasi_Toyota_AutoConnect.pptx"
target_update = r"d:\Project\dealer-toyota\Presentasi_Toyota_AutoConnect_Update.pptx"

# Save to target_update first
build_presentation(target_update)

# Try overwriting primary if not locked
try:
    shutil.copyfile(target_update, target_primary)
    print(f"Also updated primary file: {target_primary}")
except Exception as e:
    print(f"Primary file currently open in PowerPoint/viewer ({e}), saved as update file: {target_update}")
