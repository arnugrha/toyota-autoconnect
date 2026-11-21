import os
from PIL import Image, ImageDraw, ImageFont

def get_font(size=13, bold=False):
    name = "segoeuib.ttf" if bold else "segoeui.ttf"
    fallback = "arialbd.ttf" if bold else "arial.ttf"
    paths = [
        os.path.join(r"C:\Windows\Fonts", name),
        os.path.join(r"C:\Windows\Fonts", fallback),
    ]
    for p in paths:
        if os.path.exists(p):
            try:
                return ImageFont.truetype(p, size)
            except Exception:
                pass
    return ImageFont.load_default()

def draw_text_centered(draw, center, text, font, fill="#000000"):
    cx, cy = center
    lines = text.split("\n")
    
    line_metrics = []
    total_h = 0
    for l in lines:
        bbox = draw.textbbox((0, 0), l, font=font)
        w = bbox[2] - bbox[0]
        h = bbox[3] - bbox[1]
        line_metrics.append((w, h))
        total_h += h + 3
    total_h -= 3
    
    cur_y = cy - total_h / 2
    for i, l in enumerate(lines):
        w, h = line_metrics[i]
        draw.text((cx - w / 2, cur_y), l, fill=fill, font=font)
        cur_y += h + 3

def draw_oval(draw, center, size, text, fill="#F8BBD0", outline="#000000", width=2):
    cx, cy = center
    w, h = size
    box = [cx - w/2, cy - h/2, cx + w/2, cy + h/2]
    draw.rounded_rectangle(box, radius=h/2, fill=fill, outline=outline, width=width)
    f = get_font(14, bold=True)
    draw_text_centered(draw, center, text, f, fill="#000000")

def draw_parallelogram(draw, center, size, text, skew=18, fill="#BBDEFB", outline="#000000", width=2):
    cx, cy = center
    w, h = size
    hw, hh = w/2, h/2
    pts = [
        (cx - hw + skew, cy - hh),
        (cx + hw, cy - hh),
        (cx + hw - skew, cy + hh),
        (cx - hw, cy + hh)
    ]
    draw.polygon(pts, fill=fill, outline=outline)
    f = get_font(12, bold=False)
    draw_text_centered(draw, center, text, f, fill="#000000")

def draw_process(draw, center, size, text, fill="#FFF59D", outline="#000000", width=2, radius=6):
    cx, cy = center
    w, h = size
    box = [cx - w/2, cy - h/2, cx + w/2, cy + h/2]
    draw.rounded_rectangle(box, radius=radius, fill=fill, outline=outline, width=width)
    f = get_font(12, bold=False)
    draw_text_centered(draw, center, text, f, fill="#000000")

def draw_diamond(draw, center, size, text, fill="#FFE0B2", outline="#000000", width=2):
    cx, cy = center
    w, h = size
    hw, hh = w/2, h/2
    pts = [
        (cx, cy - hh),
        (cx + hw, cy),
        (cx, cy + hh),
        (cx - hw, cy)
    ]
    draw.polygon(pts, fill=fill, outline=outline)
    f = get_font(11, bold=True)
    draw_text_centered(draw, center, text, f, fill="#000000")

def draw_connector(draw, center, radius=16, fill="#FFCCBC", outline="#000000", width=2):
    cx, cy = center
    draw.ellipse([cx - radius, cy - radius, cx + radius, cy + radius], fill=fill, outline=outline, width=width)
    off = radius * 0.55
    draw.line([cx - off, cy - off, cx + off, cy + off], fill=outline, width=2)
    draw.line([cx - off, cy + off, cx + off, cy - off], fill=outline, width=2)

def draw_arrow(draw, points, color="#000000", width=2, arrow_size=7, label=None, label_pos=None):
    for i in range(len(points) - 1):
        draw.line([points[i], points[i+1]], fill=color, width=width)
    
    p_last2 = points[-2]
    p_last = points[-1]
    x1, y1 = p_last2
    x2, y2 = p_last
    
    sz = arrow_size
    if x1 == x2: # Vertical
        if y2 > y1: # Down
            draw.polygon([(x2, y2), (x2 - sz, y2 - sz*1.4), (x2 + sz, y2 - sz*1.4)], fill=color)
        else: # Up
            draw.polygon([(x2, y2), (x2 - sz, y2 + sz*1.4), (x2 + sz, y2 + sz*1.4)], fill=color)
    elif y1 == y2: # Horizontal
        if x2 > x1: # Right
            draw.polygon([(x2, y2), (x2 - sz*1.4, y2 - sz), (x2 - sz*1.4, y2 + sz)], fill=color)
        else: # Left
            draw.polygon([(x2, y2), (x2 + sz*1.4, y2 - sz), (x2 + sz*1.4, y2 + sz)], fill=color)
            
    if label and label_pos:
        f_lbl = get_font(11, bold=True)
        lx, ly = label_pos
        bbox = draw.textbbox((0, 0), label, font=f_lbl)
        lw, lh = bbox[2] - bbox[0], bbox[3] - bbox[1]
        draw.rectangle([lx - lw/2 - 2, ly - lh/2 - 1, lx + lw/2 + 2, ly + lh/2 + 1], fill="#FFFFFF")
        draw.text((lx - lw/2, ly - lh/2), label, fill="#000000", font=f_lbl)

def build_service_flowchart(output_path):
    # Pure white background like reference image
    w_img, h_img = 2000, 1150
    img = Image.new("RGB", (w_img, h_img), "#FFFFFF")
    draw = ImageDraw.Draw(img)
    
    # Title like reference image
    f_title = get_font(42, bold=True)
    bbox_t = draw.textbbox((0, 0), "Flowchart", font=f_title)
    tw = bbox_t[2] - bbox_t[0]
    draw.text(((w_img - tw) / 2, 25), "Flowchart", fill="#000000", font=f_title)
    
    f_sub = get_font(14, bold=False)
    sub_text = "Alur Pengajuan Servis Mobil oleh Pengguna & Penanganan Service Order oleh Admin Bengkel"
    bbox_s = draw.textbbox((0, 0), sub_text, font=f_sub)
    sw = bbox_s[2] - bbox_s[0]
    draw.text(((w_img - sw) / 2, 78), sub_text, fill="#475569", font=f_sub)

    # 4 Column Lane Header Badges
    col_xs = [260, 740, 1240, 1720]
    col_labels = [
        "1. PENGAJUAN SERVIS (PELANGGAN)",
        "2. VERIFIKASI & WORK ORDER (ADMIN SA)",
        "3. PENGERJAAN BENGKEL & SUKU CADANG",
        "4. QC, FAKTUR & SERAH TERIMA"
    ]
    f_badge = get_font(11, bold=True)
    for cx, lbl in zip(col_xs, col_labels):
        bbox_b = draw.textbbox((0, 0), lbl, font=f_badge)
        bw = bbox_b[2] - bbox_b[0] + 20
        draw.rounded_rectangle([cx - bw/2, 112, cx + bw/2, 136], radius=4, fill="#F1F5F9", outline="#CBD5E1", width=1)
        draw.text((cx - (bbox_b[2] - bbox_b[0])/2, 117), lbl, fill="#334155", font=f_badge)

    # Standard box sizes
    sz_oval = (160, 48)
    sz_p = (230, 54)
    sz_rect = (230, 54)
    sz_dia = (190, 68)

    # ==========================================
    # COLUMN 1: PELANGGAN (X = 260)
    # ==========================================
    cx1 = col_xs[0]
    draw_oval(draw, (cx1, 175), sz_oval, "Start (Mulai)")
    draw_parallelogram(draw, (cx1, 260), sz_p, "Login Akun &\nBuka Menu Servis")
    draw_parallelogram(draw, (cx1, 345), sz_p, "Pilih Kendaraan Terdaftar\n(Plat Nomor & KM)")
    draw_parallelogram(draw, (cx1, 430), sz_p, "Pilih Cabang Bengkel &\nJenis Layanan Servis")
    draw_parallelogram(draw, (cx1, 515), sz_p, "Pilih Tanggal Kedatangan\n(Senin - Sabtu)")
    draw_process(draw, (cx1, 605), sz_rect, "Sistem Cek Kuota Slot\nper Jam via AJAX")
    draw_diamond(draw, (cx1, 705), sz_dia, "Slot Waktu\nTersedia?")
    draw_parallelogram(draw, (cx1, 815), sz_p, "Pilih Jam Kedatangan\n(08:00 - 15:00 WIB)")
    draw_parallelogram(draw, (cx1, 900), sz_p, "Input Keluhan / Catatan\nKondisi Kendaraan")
    draw_process(draw, (cx1, 985), sz_rect, "Kirim Permohonan Booking\n(Status: Pending)")
    draw_parallelogram(draw, (cx1, 1070), sz_p, "Terima Kode Booking &\nNotifikasi (Email/WA)")

    # Connectors Col 1
    draw_arrow(draw, [(cx1, 199), (cx1, 233)])
    draw_arrow(draw, [(cx1, 287), (cx1, 318)])
    draw_arrow(draw, [(cx1, 372), (cx1, 403)])
    draw_arrow(draw, [(cx1, 457), (cx1, 488)])
    draw_arrow(draw, [(cx1, 542), (cx1, 578)])
    draw_arrow(draw, [(cx1, 632), (cx1, 671)])
    
    # Diamond: Tidak (Slot Penuh -> Loopback to Pilih Tanggal)
    draw_arrow(draw, [
        (cx1 - sz_dia[0]/2, 705),
        (cx1 - 160, 705),
        (cx1 - 160, 515),
        (cx1 - sz_p[0]/2, 515)
    ], label="No (Penuh)", label_pos=(cx1 - 160, 610))
    
    # Diamond: Ya (Slot Ada -> Lanjut)
    draw_arrow(draw, [(cx1, 739), (cx1, 788)], label="Yes", label_pos=(cx1 + 18, 760))
    draw_arrow(draw, [(cx1, 842), (cx1, 873)])
    draw_arrow(draw, [(cx1, 927), (cx1, 958)])
    draw_arrow(draw, [(cx1, 1012), (cx1, 1043)])

    # Bridge Col 1 to Col 2 (with Connector Circle X like reference)
    conn1_pos = (500, 175)
    draw_arrow(draw, [
        (cx1 + sz_p[0]/2, 1070),
        (500, 1070),
        (500, 175 + 16) # touches connector bottom
    ])
    draw_connector(draw, conn1_pos)
    draw_arrow(draw, [(500 + 16, 175), (col_xs[1] - sz_p[0]/2, 175)])

    # ==========================================
    # COLUMN 2: ADMIN / SERVICE ADVISOR (X = 740)
    # ==========================================
    cx2 = col_xs[1]
    draw_parallelogram(draw, (cx2, 175), sz_p, "Notifikasi Booking Masuk\ndi Feed Dashboard")
    draw_process(draw, (cx2, 260), sz_rect, "SA Verifikasi Jadwal &\nKapasitas Stall Servis")
    draw_diamond(draw, (cx2, 360), sz_dia, "Konfirmasi\nBooking?")
    
    # Rejected Branch (to the right of diamond)
    draw_process(draw, (cx2 + 200, 360), (170, 54), "Update Status:\nDitolak / Reschedule")
    draw_parallelogram(draw, (cx2 + 200, 450), (170, 54), "Kirim Notifikasi\nAlasan ke Pelanggan")
    
    # Accepted Branch (Down)
    draw_process(draw, (cx2, 470), sz_rect, "Update Status Booking:\n'Confirmed'")
    draw_process(draw, (cx2, 555), sz_rect, "Terbitkan Service Order (WO)\n& Alokasi Mekanik")
    draw_parallelogram(draw, (cx2, 645), sz_p, "Pelanggan Datang ke Bengkel\n& Serahkan Kunci")
    draw_process(draw, (cx2, 735), sz_rect, "Service Advisor (SA)\nWalk-Around Inspection")
    draw_process(draw, (cx2, 825), sz_rect, "Catat Kondisi Fisik Mobil &\nKeluhan Tambahan di WO")
    draw_process(draw, (cx2, 915), sz_rect, "Mobil Masuk Stall Bengkel\nke Teknisi Bersertifikat")

    # Connectors Col 2
    draw_arrow(draw, [(cx2, 202), (cx2, 233)])
    draw_arrow(draw, [(cx2, 287), (cx2, 326)])
    
    # Diamond Col 2: No (Ditolak)
    draw_arrow(draw, [(cx2 + sz_dia[0]/2, 360), (cx2 + 200 - 85, 360)], label="No", label_pos=(cx2 + 105, 345))
    draw_arrow(draw, [(cx2 + 200, 387), (cx2 + 200, 423)])
    
    # Diamond Col 2: Yes (Dikonfirmasi)
    draw_arrow(draw, [(cx2, 394), (cx2, 443)], label="Yes", label_pos=(cx2 + 18, 415))
    draw_arrow(draw, [(cx2, 497), (cx2, 528)])
    draw_arrow(draw, [(cx2, 582), (cx2, 618)])
    draw_arrow(draw, [(cx2, 672), (cx2, 708)])
    draw_arrow(draw, [(cx2, 762), (cx2, 798)])
    draw_arrow(draw, [(cx2, 852), (cx2, 888)])

    # Bridge Col 2 to Col 3
    conn2_pos = (990, 175)
    draw_arrow(draw, [
        (cx2, 942),
        (cx2, 995),
        (990, 995),
        (990, 175 + 16)
    ])
    draw_connector(draw, conn2_pos)
    draw_arrow(draw, [(990 + 16, 175), (col_xs[2] - sz_rect[0]/2, 175)])

    # ==========================================
    # COLUMN 3: MEKANIK & SUKU CADANG (X = 1240)
    # ==========================================
    cx3 = col_xs[2]
    draw_process(draw, (cx3, 175), sz_rect, "Mekanik Eksekusi Servis\nSesuai SOP Resmi Toyota")
    draw_process(draw, (cx3, 260), sz_rect, "Ganti Oli TMO Synthetic &\nSuku Cadang Asli Toyota")
    draw_process(draw, (cx3, 345), sz_rect, "Update Progres 9 Tahapan WO\ndi Sistem secara Real-Time")
    draw_diamond(draw, (cx3, 445), sz_dia, "Ada Kerusakan\nTambahan?")
    
    # Branch Part Tambahan (Horizontal Decision Chain like reference!)
    draw_parallelogram(draw, (cx3 + 195, 445), (170, 54), "SA Hubungi Pelanggan\nKonfirmasi Estimasi Biaya")
    draw_diamond(draw, (cx3 + 195, 545), (150, 60), "Pelanggan\nSetuju?")
    draw_process(draw, (cx3 + 195, 635), (170, 50), "Tambahkan Part ke WO\n& Lanjut Servis")
    
    # Connectors Col 3
    draw_arrow(draw, [(cx3, 202), (cx3, 233)])
    draw_arrow(draw, [(cx3, 287), (cx3, 318)])
    draw_arrow(draw, [(cx3, 372), (cx3, 411)])
    
    # Part Tambahan: Yes
    draw_arrow(draw, [(cx3 + sz_dia[0]/2, 445), (cx3 + 195 - 85, 445)], label="Yes", label_pos=(cx3 + 102, 432))
    draw_arrow(draw, [(cx3 + 195, 472), (cx3 + 195, 515)])
    
    # Pelanggan Setuju? Yes -> Tambahkan part & loop back to mekanik
    draw_arrow(draw, [(cx3 + 195, 575), (cx3 + 195, 610)], label="Yes", label_pos=(cx3 + 215, 592))
    draw_arrow(draw, [
        (cx3 + 195, 660),
        (cx3 + 195, 700),
        (cx3 + 100, 700),
        (cx3 + 100, 260),
        (cx3 + sz_rect[0]/2, 260)
    ])
    
    # Pelanggan Setuju? No -> Lewati part tambahan, langsung ke QC
    draw_arrow(draw, [
        (cx3 + 195 + 75, 545),
        (cx3 + 295, 545),
        (cx3 + 295, 785),
        (cx3 + sz_rect[0]/2, 785)
    ], label="No", label_pos=(cx3 + 285, 532))

    # Main stream: No (Tidak ada kerusakan tambahan)
    draw_process(draw, (cx3, 565), sz_rect, "Pengerjaan Servis Selesai\noleh Teknisi")
    draw_process(draw, (cx3, 675), sz_rect, "Final Quality Control (QC) &\nUji Fungsi oleh Foreman")
    draw_process(draw, (cx3, 785), sz_rect, "Pembersihan Kendaraan &\nPengecekan Akhir")

    draw_arrow(draw, [(cx3, 479), (cx3, 538)], label="No", label_pos=(cx3 + 18, 508))
    draw_arrow(draw, [(cx3, 592), (cx3, 648)])
    draw_arrow(draw, [(cx3, 702), (cx3, 758)])

    # Bridge Col 3 to Col 4
    conn3_pos = (1490, 175)
    draw_arrow(draw, [
        (cx3, 812),
        (cx3, 860),
        (1490, 860),
        (1490, 175 + 16)
    ])
    draw_connector(draw, conn3_pos)
    draw_arrow(draw, [(1490 + 16, 175), (col_xs[3] - sz_rect[0]/2, 175)])

    # ==========================================
    # COLUMN 4: FAKTUR & PENYERAHAN (X = 1720)
    # ==========================================
    cx4 = col_xs[3]
    draw_process(draw, (cx4, 175), sz_rect, "Kasir Hitung Biaya Servis &\nCetak Faktur Resmi")
    draw_parallelogram(draw, (cx4, 265), sz_p, "Pelanggan Melakukan Pembayaran\n(Tunai / Kartu / QRIS)")
    draw_parallelogram(draw, (cx4, 355), sz_p, "SA Jelaskan Rincian Pengerjaan\n& Garansi Servis Resmi")
    draw_parallelogram(draw, (cx4, 445), sz_p, "Serah Terima Kendaraan &\nKunci Mobil ke Pelanggan")
    draw_process(draw, (cx4, 535), sz_rect, "Update Status Service Order:\n'Delivered / Selesai'")
    draw_parallelogram(draw, (cx4, 625), sz_p, "Riwayat Servis Otomatis\nTersimpan di Customer Portal")
    draw_parallelogram(draw, (cx4, 715), sz_p, "Pelanggan Memberikan Rating &\nUlasan Kepuasan Layanan")
    draw_oval(draw, (cx4, 805), sz_oval, "End (Selesai)")

    # Also link rejected branch from Col 2 down to End!
    draw_arrow(draw, [
        (cx2 + 200, 477),
        (cx2 + 200, 1110),
        (cx4, 1110),
        (cx4, 805 + sz_oval[1]/2)
    ])

    # Connectors Col 4
    draw_arrow(draw, [(cx4, 202), (cx4, 238)])
    draw_arrow(draw, [(cx4, 292), (cx4, 328)])
    draw_arrow(draw, [(cx4, 382), (cx4, 418)])
    draw_arrow(draw, [(cx4, 472), (cx4, 508)])
    draw_arrow(draw, [(cx4, 562), (cx4, 598)])
    draw_arrow(draw, [(cx4, 652), (cx4, 688)])
    draw_arrow(draw, [(cx4, 742), (cx4, 781)])

    # Save
    img.save(output_path, "PNG", dpi=(300, 300))
    print(f"ANSI Flowchart generated successfully at: {output_path}")

build_service_flowchart(r"d:\Project\dealer-toyota\docs\flowcharts\flowchart_pengajuan_servis.png")


def build_vehicle_flowchart(output_path):
    w_img, h_img = 2000, 1150
    img = Image.new("RGB", (w_img, h_img), "#FFFFFF")
    draw = ImageDraw.Draw(img)
    
    # Title like reference image
    f_title = get_font(42, bold=True)
    bbox_t = draw.textbbox((0, 0), "Flowchart", font=f_title)
    tw = bbox_t[2] - bbox_t[0]
    draw.text(((w_img - tw) / 2, 25), "Flowchart", fill="#000000", font=f_title)
    
    f_sub = get_font(14, bold=False)
    sub_text = "Alur Pemesanan Kendaraan (SPK Online) oleh Pengguna & Penanganan Alokasi Unit oleh Admin Sales"
    bbox_s = draw.textbbox((0, 0), sub_text, font=f_sub)
    sw = bbox_s[2] - bbox_s[0]
    draw.text(((w_img - sw) / 2, 78), sub_text, fill="#475569", font=f_sub)

    col_xs = [260, 740, 1240, 1720]
    col_labels = [
        "1. EKSPLORASI & SIMULASI (PELANGGAN)",
        "2. PENGAJUAN SPK & PAYMENT GATEWAY",
        "3. VERIFIKASI & ALOKASI UNIT (ADMIN SALES)",
        "4. PDI, STNK & SERAH TERIMA UNIT"
    ]
    f_badge = get_font(11, bold=True)
    for cx, lbl in zip(col_xs, col_labels):
        bbox_b = draw.textbbox((0, 0), lbl, font=f_badge)
        bw = bbox_b[2] - bbox_b[0] + 20
        draw.rounded_rectangle([cx - bw/2, 112, cx + bw/2, 136], radius=4, fill="#F1F5F9", outline="#CBD5E1", width=1)
        draw.text((cx - (bbox_b[2] - bbox_b[0])/2, 117), lbl, fill="#334155", font=f_badge)

    sz_oval = (160, 48)
    sz_p = (230, 54)
    sz_rect = (230, 54)
    sz_dia = (190, 68)

    # ==================== COLUMN 1 ====================
    cx1 = col_xs[0]
    draw_oval(draw, (cx1, 175), sz_oval, "Start (Mulai)")
    draw_parallelogram(draw, (cx1, 260), sz_p, "Buka Katalog Mobil &\nFilter Model / Varian")
    draw_parallelogram(draw, (cx1, 350), sz_p, "Pilih Model Kendaraan,\nTipe Mesin & Warna")
    draw_process(draw, (cx1, 440), sz_rect, "Buka Simulasi Kredit &\nHitung Uang Muka (DP)")
    draw_parallelogram(draw, (cx1, 530), sz_p, "Tentukan Pilihan Tenor &\nBandingkan Opsi Leasing")
    draw_process(draw, (cx1, 620), sz_rect, "Kalkulasi Estimasi Angsuran\n& Total Premi Asuransi")
    draw_parallelogram(draw, (cx1, 710), sz_p, "Pilih Metode Pembelian:\nTunai atau Kredit Leasing")

    draw_arrow(draw, [(cx1, 199), (cx1, 233)])
    draw_arrow(draw, [(cx1, 287), (cx1, 323)])
    draw_arrow(draw, [(cx1, 377), (cx1, 413)])
    draw_arrow(draw, [(cx1, 467), (cx1, 503)])
    draw_arrow(draw, [(cx1, 557), (cx1, 593)])
    draw_arrow(draw, [(cx1, 647), (cx1, 683)])

    conn1_pos = (500, 175)
    draw_arrow(draw, [
        (cx1, 737),
        (cx1, 800),
        (500, 800),
        (500, 175 + 16)
    ])
    draw_connector(draw, conn1_pos)
    draw_arrow(draw, [(500 + 16, 175), (col_xs[1] - sz_p[0]/2, 175)])

    # ==================== COLUMN 2 ====================
    cx2 = col_xs[1]
    draw_parallelogram(draw, (cx2, 175), sz_p, "Pilih Cabang Dealer &\nIsi Form Pemesanan SPK")
    draw_parallelogram(draw, (cx2, 265), sz_p, "Upload Dokumen Pembeli\n(KTP & Berkas Pembiayaan)")
    draw_process(draw, (cx2, 355), sz_rect, "Sistem Generate Snap Token\nMidtrans Payment Gateway")
    draw_parallelogram(draw, (cx2, 445), sz_p, "Bayar Booking Fee Resmi\n(VA / QRIS / e-Wallet)")
    draw_diamond(draw, (cx2, 550), sz_dia, "Pembayaran\nSukses (< 24 Jam)?")

    # Payment Failed (to the right)
    draw_process(draw, (cx2 + 200, 550), (170, 54), "Update Status Booking:\n'Expired / Batal'")
    draw_parallelogram(draw, (cx2 + 200, 640), (170, 54), "Kirim Notifikasi\nKedaluwarsa ke User")

    # Payment Succeeded
    draw_process(draw, (cx2, 665), sz_rect, "Midtrans Kirim Webhook\nNotifikasi Pembayaran Sukses")
    draw_process(draw, (cx2, 755), sz_rect, "Sistem Validasi Signature SHA-512\n& Update Status 'Confirmed'")
    draw_parallelogram(draw, (cx2, 845), sz_p, "Terima Notifikasi Bukti SPK\nResmi via Email & WhatsApp")

    draw_arrow(draw, [(cx2, 202), (cx2, 238)])
    draw_arrow(draw, [(cx2, 292), (cx2, 328)])
    draw_arrow(draw, [(cx2, 382), (cx2, 418)])
    draw_arrow(draw, [(cx2, 472), (cx2, 516)])

    # Diamond No
    draw_arrow(draw, [(cx2 + sz_dia[0]/2, 550), (cx2 + 200 - 85, 550)], label="No", label_pos=(cx2 + 105, 535))
    draw_arrow(draw, [(cx2 + 200, 577), (cx2 + 200, 613)])

    # Diamond Yes
    draw_arrow(draw, [(cx2, 584), (cx2, 638)], label="Yes", label_pos=(cx2 + 18, 608))
    draw_arrow(draw, [(cx2, 692), (cx2, 728)])
    draw_arrow(draw, [(cx2, 782), (cx2, 818)])

    conn2_pos = (990, 175)
    draw_arrow(draw, [
        (cx2, 872),
        (cx2, 920),
        (990, 920),
        (990, 175 + 16)
    ])
    draw_connector(draw, conn2_pos)
    draw_arrow(draw, [(990 + 16, 175), (col_xs[2] - sz_p[0]/2, 175)])

    # ==================== COLUMN 3 ====================
    cx3 = col_xs[2]
    draw_parallelogram(draw, (cx3, 175), sz_p, "Notifikasi SPK Baru Masuk\ndi Dashboard Admin Sales")
    draw_process(draw, (cx3, 265), sz_rect, "Sales Verifikasi Berkas KTP\n& Konfirmasi Rekanan Leasing")
    draw_diamond(draw, (cx3, 370), sz_dia, "Berkas & Leasing\nDisetujui?")

    # Not Approved
    draw_parallelogram(draw, (cx3 + 195, 370), (170, 54), "Sales Hubungi Pelanggan\nLengkapi Dokumen")

    # Approved
    draw_process(draw, (cx3, 480), sz_rect, "Cek Stok Fisik & Kunci Nomor\nRangka Unit (VIN / Chasis)")
    draw_process(draw, (cx3, 570), sz_rect, "Terbitkan Surat Penawaran Resmi\n& Cetak Faktur SPK Final")
    draw_parallelogram(draw, (cx3, 660), sz_p, "Pelanggan Lakukan Pelunasan\nSisa Tagihan / Akad Kredit")
    draw_process(draw, (cx3, 750), sz_rect, "Finance Validasi Pelunasan &\nAlokasi Unit Siap Kirim")

    draw_arrow(draw, [(cx3, 202), (cx3, 238)])
    draw_arrow(draw, [(cx3, 292), (cx3, 336)])

    # Diamond No (Lengkapi Dokumen -> Loop back)
    draw_arrow(draw, [(cx3 + sz_dia[0]/2, 370), (cx3 + 195 - 85, 370)], label="No", label_pos=(cx3 + 102, 355))
    draw_arrow(draw, [
        (cx3 + 195, 397),
        (cx3 + 195, 430),
        (cx3 + 100, 430),
        (cx3 + 100, 265),
        (cx3 + sz_rect[0]/2, 265)
    ])

    # Diamond Yes
    draw_arrow(draw, [(cx3, 404), (cx3, 453)], label="Yes", label_pos=(cx3 + 18, 428))
    draw_arrow(draw, [(cx3, 507), (cx3, 543)])
    draw_arrow(draw, [(cx3, 597), (cx3, 633)])
    draw_arrow(draw, [(cx3, 687), (cx3, 723)])

    conn3_pos = (1490, 175)
    draw_arrow(draw, [
        (cx3, 777),
        (cx3, 830),
        (1490, 830),
        (1490, 175 + 16)
    ])
    draw_connector(draw, conn3_pos)
    draw_arrow(draw, [(1490 + 16, 175), (col_xs[3] - sz_rect[0]/2, 175)])

    # ==================== COLUMN 4 ====================
    cx4 = col_xs[3]
    draw_process(draw, (cx4, 175), sz_rect, "Pre-Delivery Inspection (PDI)\noleh Teknisi Bengkel")
    draw_process(draw, (cx4, 265), sz_rect, "Pemasangan Aksesoris Resmi &\nPengurusan STNK / Plat")
    draw_parallelogram(draw, (cx4, 355), sz_p, "Koordinasi Penjadwalan Serah\nTerima Unit (Dealer/Rumah)")
    draw_parallelogram(draw, (cx4, 445), sz_p, "Penyerahan Mobil, Kunci Kontak\n& Buku Garansi Servis")
    draw_parallelogram(draw, (cx4, 535), sz_p, "Pelanggan Menandatangani\nDokumen BASTK Resmi")
    draw_process(draw, (cx4, 625), sz_rect, "Update Status Pemesanan:\n'Delivered / Completed'")
    draw_oval(draw, (cx4, 715), sz_oval, "End (Selesai)")

    # Cancelled branch to End
    draw_arrow(draw, [
        (cx2 + 200, 667),
        (cx2 + 200, 1100),
        (cx4, 1100),
        (cx4, 715 + sz_oval[1]/2)
    ])

    draw_arrow(draw, [(cx4, 202), (cx4, 238)])
    draw_arrow(draw, [(cx4, 292), (cx4, 328)])
    draw_arrow(draw, [(cx4, 382), (cx4, 418)])
    draw_arrow(draw, [(cx4, 472), (cx4, 508)])
    draw_arrow(draw, [(cx4, 562), (cx4, 598)])
    draw_arrow(draw, [(cx4, 652), (cx4, 691)])

    img.save(output_path, "PNG", dpi=(300, 300))
    print(f"ANSI Vehicle Flowchart generated successfully at: {output_path}")

build_vehicle_flowchart(r"d:\Project\dealer-toyota\docs\flowcharts\flowchart_pemesanan_kendaraan.png")
