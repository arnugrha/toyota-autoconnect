import os
from PIL import Image, ImageDraw, ImageFont

def get_font(size=18, bold=False):
    font_name = "segoeuib.ttf" if bold else "segoeui.ttf"
    fallback_name = "arialbd.ttf" if bold else "arial.ttf"
    paths = [
        os.path.join(r"C:\Windows\Fonts", font_name),
        os.path.join(r"C:\Windows\Fonts", fallback_name),
    ]
    for p in paths:
        if os.path.exists(p):
            try:
                return ImageFont.truetype(p, size)
            except Exception:
                pass
    return ImageFont.load_default()

def draw_header(draw, title, subtitle):
    # Top banner background
    draw.rectangle([(0, 0), (1920, 115)], fill="#0F172A")
    draw.rectangle([(0, 112), (1920, 115)], fill="#EB0A1E")
    
    # Toyota Red Badge
    draw.rounded_rectangle([(40, 24), (250, 52)], radius=6, fill="#EB0A1E")
    f_badge = get_font(14, bold=True)
    draw.text((54, 29), "TOYOTA AUTOCONNECT", fill="#FFFFFF", font=f_badge)
    
    # Title & Subtitle
    f_title = get_font(24, bold=True)
    f_sub = get_font(14, bold=False)
    draw.text((270, 24), title, fill="#FFFFFF", font=f_title)
    draw.text((270, 62), subtitle, fill="#94A3B8", font=f_sub)
    
    # Tech Tags on the right
    tags = ["Laravel 13", "PHP 8.4+", "Midtrans Snap", "Spatie RBAC"]
    tx = 1880
    f_tag = get_font(12, bold=True)
    for tag in reversed(tags):
        bbox = draw.textbbox((0, 0), tag, font=f_tag)
        tw = bbox[2] - bbox[0] + 20
        tx -= tw
        draw.rounded_rectangle([(tx, 40), (tx + tw - 8, 72)], radius=6, fill="#1E293B", outline="#334155", width=1)
        draw.text((tx + 10, 47), tag, fill="#38BDF8", font=f_tag)

def draw_step_card(draw, box, step_num, title, lines, role="customer", is_highlight=False):
    x1, y1, x2, y2 = box
    
    # Background and border
    if role == "customer":
        bg_color = "#FFFFFF"
        border_color = "#FCA5A5" if not is_highlight else "#DC2626"
        badge_bg = "#EF4444"
        badge_text = "#FFFFFF"
        header_color = "#991B1B"
    elif role == "admin":
        bg_color = "#FFFFFF"
        border_color = "#93C5FD" if not is_highlight else "#2563EB"
        badge_bg = "#2563EB"
        badge_text = "#FFFFFF"
        header_color = "#1E3A8A"
    else: # system / gateway
        bg_color = "#FEF3C7"
        border_color = "#F59E0B"
        badge_bg = "#D97706"
        badge_text = "#FFFFFF"
        header_color = "#92400E"
        
    border_w = 2 if is_highlight else 1
    
    # Card shadow
    draw.rounded_rectangle([(x1+2, y1+2), (x2+2, y2+2)], radius=12, fill="#E2E8F0")
    # Card body
    draw.rounded_rectangle([(x1, y1), (x2, y2)], radius=12, fill=bg_color, outline=border_color, width=border_w)
    
    # Step Circle Badge
    draw.rounded_rectangle([(x1+14, y1+14), (x1+48, y1+48)], radius=17, fill=badge_bg)
    f_num = get_font(15, bold=True)
    n_bbox = draw.textbbox((0, 0), str(step_num), font=f_num)
    nw = n_bbox[2] - n_bbox[0]
    nh = n_bbox[3] - n_bbox[1]
    draw.text((x1 + 31 - nw/2, y1 + 31 - nh/2 - 1), str(step_num), fill=badge_text, font=f_num)
    
    # Title
    f_head = get_font(15, bold=True)
    draw.text((x1+58, y1+18), title, fill=header_color, font=f_head)
    
    # Divider line
    draw.line([(x1+14, y1+56), (x2-14, y1+56)], fill="#F1F5F9", width=1)
    
    # Body lines
    f_body = get_font(12, bold=False)
    ly = y1 + 66
    for line in lines:
        draw.text((x1+16, ly), line, fill="#475569", font=f_body)
        ly += 19

def draw_decision_card(draw, center, size, title, lines):
    cx, cy = center
    w, h = size
    hw, hh = w/2, h/2
    
    # Shadow
    draw.rounded_rectangle([(cx-hw+2, cy-hh+2), (cx+hw+2, cy+hh+2)], radius=12, fill="#E2E8F0")
    # Body
    draw.rounded_rectangle([(cx-hw, cy-hh), (cx+hw, cy+hh)], radius=12, fill="#FFFBEB", outline="#F59E0B", width=2)
    
    # Header tag
    draw.rounded_rectangle([(cx-65, cy-hh+8), (cx+65, cy-hh+28)], radius=10, fill="#D97706")
    f_tag = get_font(11, bold=True)
    draw.text((cx-50, cy-hh+12), "DECISION / GATE", fill="#FFFFFF", font=f_tag)
    
    # Title
    f_title = get_font(14, bold=True)
    t_bbox = draw.textbbox((0,0), title, font=f_title)
    tw = t_bbox[2] - t_bbox[0]
    draw.text((cx - tw/2, cy-hh+35), title, fill="#92400E", font=f_title)
    
    # Lines
    f_body = get_font(11, bold=False)
    ly = cy - hh + 60
    for line in lines:
        b_bbox = draw.textbbox((0,0), line, font=f_body)
        bw = b_bbox[2] - b_bbox[0]
        draw.text((cx - bw/2, ly), line, fill="#78350F", font=f_body)
        ly += 16

def draw_arrow_connector(draw, p1, p2, color="#64748B", width=2, label="", label_pos=None, label_color="#0F172A"):
    x1, y1 = p1
    x2, y2 = p2
    draw.line([(x1, y1), (x2, y2)], fill=color, width=width)
    
    # Arrow head
    sz = 7
    if x1 == x2: # Vertical
        if y2 > y1:
            draw.polygon([(x2, y2), (x2-sz, y2-sz*1.5), (x2+sz, y2-sz*1.5)], fill=color)
        else:
            draw.polygon([(x2, y2), (x2-sz, y2+sz*1.5), (x2+sz, y2+sz*1.5)], fill=color)
    elif y1 == y2: # Horizontal
        if x2 > x1:
            draw.polygon([(x2, y2), (x2-sz*1.5, y2-sz), (x2-sz*1.5, y2+sz)], fill=color)
        else:
            draw.polygon([(x2, y2), (x2+sz*1.5, y2-sz), (x2+sz*1.5, y2+sz)], fill=color)
            
    if label:
        f_lbl = get_font(11, bold=True)
        lx, ly = label_pos if label_pos else ((x1+x2)/2, (y1+y2)/2 - 10)
        l_bbox = draw.textbbox((0,0), label, font=f_lbl)
        lw, lh = l_bbox[2]-l_bbox[0], l_bbox[3]-l_bbox[1]
        draw.rounded_rectangle([(lx-lw/2-4, ly-lh/2-2), (lx+lw/2+4, ly+lh/2+4)], radius=4, fill="#FFFFFF", outline="#CBD5E1", width=1)
        draw.text((lx-lw/2, ly-lh/2), label, fill=label_color, font=f_lbl)

def create_vehicle_booking_flowchart(output_path):
    img = Image.new("RGB", (1920, 1080), "#F8FAFC")
    draw = ImageDraw.Draw(img)
    
    # Header
    draw_header(draw, "FLOWCHART PROSES PEMESANAN KENDARAAN (SPK & PAYMENT GATEWAY)", 
                "Alur Kolaborasi Dua Sisi: Peran Pelanggan (Customer Experience) vs Tim Dealer (Sales, Finance & Admin)")
    
    # Swimlane 1: Pelanggan
    draw.rounded_rectangle([(40, 130), (1880, 560)], radius=16, fill="#FFF5F5", outline="#FCA5A5", width=2)
    draw.rounded_rectangle([(40, 130), (320, 175)], radius=12, fill="#DC2626")
    f_lane = get_font(15, bold=True)
    draw.text((58, 142), "ROLE: PELANGGAN (CUSTOMER)", fill="#FFFFFF", font=f_lane)
    
    # Swimlane 2: Admin Dealer
    draw.rounded_rectangle([(40, 590), (1880, 1020)], radius=16, fill="#F0F9FF", outline="#BAE6FD", width=2)
    draw.rounded_rectangle([(40, 590), (380, 635)], radius=12, fill="#0F172A")
    draw.text((58, 602), "ROLE: ADMIN / SALES / FINANCE DEALER", fill="#FFFFFF", font=f_lane)
    
    # ------------------ PELANGGAN CARDS (TOP ROW) ------------------
    # Step 1: Eksplorasi
    draw_step_card(draw, (70, 210, 310, 470), "1", "Eksplorasi Katalog", [
        "• Buka website Toyota AutoConnect",
        "• Lihat model (SUV, MPV, Hybrid, GR)",
        "• Filter spesifikasi, fitur & warna",
        "• Cek harga OTR resmi per cabang"
    ], role="customer")
    
    # Step 2: Simulasi
    draw_step_card(draw, (360, 210, 600, 470), "2", "Simulasi Finansial", [
        "• Pilih skema: Tunai atau Kredit",
        "• Tentukan Down Payment (DP)",
        "• Pilih opsi tenor kredit (1 - 5 thn)",
        "• Bandingkan tawaran leasing rekanan",
        "• Hitung estimasi angsuran per bulan"
    ], role="customer")
    
    # Step 3: Isi Form SPK
    draw_step_card(draw, (650, 210, 890, 470), "3", "Pengajuan SPK", [
        "• Pilih varian & warna mobil final",
        "• Pilih cabang dealer pemenuhan unit",
        "• Masukkan data pemesan & domisili",
        "• Upload dokumen pendukung (KTP)",
        "• Setujui syarat & ketentuan SPK"
    ], role="customer")
    
    # Step 4: Midtrans Snap Payment
    draw_step_card(draw, (940, 210, 1180, 470), "4", "Bayar Booking Fee", [
        "• Pembayaran tanda jadi (Rp 5 Juta)",
        "• Integrasi Midtrans Snap Gateway",
        "• Virtual Account (BCA, Mandiri, BRI)",
        "• Instant e-Wallet (QRIS & GoPay)",
        "• Batas waktu pembayaran 24 jam"
    ], role="gateway", is_highlight=True)
    
    # Decision Gate: Midtrans Callback
    draw_decision_card(draw, (1330, 340), (220, 140), "Pembayaran Sukses?", [
        "Validasi Webhook Midtrans",
        "Signature Hash SHA-512",
        "Update status SPK otomatis"
    ])
    
    # Step 5 (Pelanggan): Pantau SPK & Pelunasan
    draw_step_card(draw, (1590, 210, 1840, 470), "5", "Pelunasan & Akad", [
        "• Terima konfirmasi SPK resmi",
        "• Pantau progres unit di portal",
        "• Pelunasan tunai / akad leasing",
        "• Koordinasi jadwal serah terima",
        "• Tanda tangan BASTK resmi"
    ], role="customer")
    
    # ------------------ ADMIN CARDS (BOTTOM ROW) ------------------
    # Step A: Notifikasi Masuk
    draw_step_card(draw, (940, 670, 1180, 930), "A", "Notifikasi & Verifikasi", [
        "• Sistem terima webhook Midtrans",
        "• SPK tercatat di dashboard staff",
        "• Notifikasi masuk (Email & WA)",
        "• Sales verifikasi berkas pelanggan",
        "• Konfirmasi ke rekanan leasing"
    ], role="admin", is_highlight=True)
    
    # Step B: Alokasi Stok Unit
    draw_step_card(draw, (1230, 670, 1480, 930), "B", "Alokasi Stok Fisik", [
        "• Cek ketersediaan di inventaris",
        "• Kunci nomor rangka (VIN / Chasis)",
        "• Mutasi stok fisik ke cabang tujuan",
        "• Terbitkan Surat Penawaran Resmi",
        "• Cetak faktur SPK bermeterai"
    ], role="admin")
    
    # Step C: PDI, STNK & Serah Terima
    draw_step_card(draw, (1550, 670, 1840, 930), "C", "PDI & Delivery", [
        "• Pre-Delivery Inspection (PDI)",
        "• Pemasangan aksesoris & plat resmi",
        "• Pengurusan berkas STNK / BPKB",
        "• Serah terima unit di dealer / rumah",
        "• Penyerahan buku servis & garansi"
    ], role="admin")
    
    # Master Data Card on bottom left
    draw_step_card(draw, (70, 670, 380, 930), "M1", "Kelola Katalog & Stok", [
        "• Admin input model & varian mobil",
        "• Setting harga OTR per wilayah",
        "• Monitor level stok & minimum alert",
        "• Input mutasi unit masuk/keluar"
    ], role="admin")
    
    draw_step_card(draw, (430, 670, 740, 930), "M2", "Atur Skema Pembiayaan", [
        "• Setting leasing rekanan (ACC, TAF)",
        "• Konfigurasi suku bunga & tenor",
        "• Monitoring pipeline prospek CRM",
        "• Follow-up calon pembeli potensial"
    ], role="admin")
    
    # ------------------ CONNECTING ARROWS ------------------
    # 1 -> 2
    draw_arrow_connector(draw, (310, 340), (360, 340), color="#EF4444", width=3)
    # 2 -> 3
    draw_arrow_connector(draw, (600, 340), (650, 340), color="#EF4444", width=3)
    # 3 -> 4
    draw_arrow_connector(draw, (890, 340), (940, 340), color="#EF4444", width=3)
    # 4 -> Decision
    draw_arrow_connector(draw, (1180, 340), (1220, 340), color="#D97706", width=3)
    
    # Decision -> Yes -> Step 5 (Pelanggan)
    draw_arrow_connector(draw, (1440, 340), (1590, 340), color="#10B981", width=3, label="YA (Lunas)", label_pos=(1515, 320), label_color="#047857")
    
    # Decision -> Yes -> Down to Step A (Admin)
    draw.line([(1330, 410), (1330, 520), (1060, 520), (1060, 670)], fill="#2563EB", width=3)
    draw.polygon([(1060, 670), (1053, 656), (1067, 656)], fill="#2563EB")
    f_cross = get_font(11, bold=True)
    draw.rounded_rectangle([(1110, 508), (1275, 532)], radius=4, fill="#FFFFFF", outline="#2563EB", width=1)
    draw.text((1118, 513), "Webhook & SPK Masuk", fill="#2563EB", font=f_cross)
    
    # Step A -> Step B
    draw_arrow_connector(draw, (1180, 800), (1230, 800), color="#2563EB", width=3)
    # Step B -> Step C
    draw_arrow_connector(draw, (1480, 800), (1550, 800), color="#2563EB", width=3)
    
    # Step C up to Step 5 (Serah Terima Final)
    draw.line([(1715, 670), (1715, 470)], fill="#059669", width=3)
    draw.polygon([(1715, 470), (1708, 484), (1722, 484)], fill="#059669")
    draw.rounded_rectangle([(1630, 555), (1800, 579)], radius=4, fill="#ECFDF5", outline="#059669", width=1)
    draw.text((1640, 560), "Serah Terima Unit", fill="#047857", font=f_cross)
    
    # Save image
    img.save(output_path, "PNG", dpi=(300, 300))
    print(f"Flowchart Pemesanan Kendaraan created successfully at: {output_path}")

create_vehicle_booking_flowchart(r"d:\Project\dealer-toyota\docs\flowcharts\flowchart_pemesanan_kendaraan.png")

def create_service_booking_flowchart(output_path):
    img = Image.new("RGB", (1920, 1080), "#F8FAFC")
    draw = ImageDraw.Draw(img)
    
    # Header
    draw_header(draw, "FLOWCHART PENGAJUAN & PENGERJAAN SERVIS BENGKEL (AFTER-SALES & WORK ORDER)", 
                "Alur Kolaborasi Dua Sisi: Peran Pelanggan (Customer Experience) vs Tim Bengkel (Service Advisor, Mekanik & Kasir)")
    
    # Swimlane 1: Pelanggan
    draw.rounded_rectangle([(40, 130), (1880, 560)], radius=16, fill="#FFF5F5", outline="#FCA5A5", width=2)
    draw.rounded_rectangle([(40, 130), (320, 175)], radius=12, fill="#DC2626")
    f_lane = get_font(15, bold=True)
    draw.text((58, 142), "ROLE: PELANGGAN (CUSTOMER)", fill="#FFFFFF", font=f_lane)
    
    # Swimlane 2: Bengkel Resmi
    draw.rounded_rectangle([(40, 590), (1880, 1020)], radius=16, fill="#F0F9FF", outline="#BAE6FD", width=2)
    draw.rounded_rectangle([(40, 590), (410, 635)], radius=12, fill="#0F172A")
    draw.text((58, 602), "ROLE: SERVICE ADVISOR (SA) & MEKANIK", fill="#FFFFFF", font=f_lane)
    
    # ------------------ PELANGGAN CARDS (TOP ROW) ------------------
    # Step 1: Pilih Mobil
    draw_step_card(draw, (70, 210, 320, 470), "1", "Pilih Kendaraan", [
        "• Login ke Customer Portal",
        "• Buka menu 'Booking Servis'",
        "• Pilih mobil dari garasi terdaftar",
        "• Input odometer / KM terkini",
        "• Cek rekomendasi perawatan berkala"
    ], role="customer")
    
    # Step 2: Pilih Bengkel & Layanan
    draw_step_card(draw, (370, 210, 620, 470), "2", "Layanan & Cabang", [
        "• Pilih cabang bengkel Toyota resmi",
        "• Pilih jenis servis:",
        "  - Servis Berkala (10k, 20k, dll)",
        "  - Ganti Oli TMO Synthetic",
        "  - Rem, AC, Mesin & Keluhan Lain",
        "• Estimasi biaya & durasi transparan"
    ], role="customer")
    
    # Step 3: Tentukan Jadwal
    draw_step_card(draw, (670, 210, 920, 470), "3", "Jadwal & Keluhan", [
        "• Pilih tanggal servis (Senin - Sabtu)",
        "• Pilih slot jam (08:00 - 15:00 WIB)",
        "• Validasi kuota per jam dinamis",
        "• Input catatan keluhan kendaraan",
        "• Ajukan permohonan booking"
    ], role="customer", is_highlight=True)
    
    # Step 4: Booking Terdaftar & Notifikasi
    draw_step_card(draw, (970, 210, 1220, 470), "4", "Konfirmasi Booking", [
        "• Terima kode booking (SB-xxx)",
        "• Notifikasi email & WhatsApp",
        "• Pengingat otomatis H-1 kedatangan",
        "• Status booking terpantau di portal",
        "• Fasilitas batal/reschedule jika perlu"
    ], role="customer")
    
    # Step 5: Ambil Mobil & Review
    draw_step_card(draw, (1580, 210, 1840, 470), "5", "Serah Terima & Ulasan", [
        "• Terima penjelasan hasil servis & suku cadang",
        "• Cek kondisi fisik kendaraan bersama SA",
        "• Terima kunci mobil & garansi pengerjaan",
        "• Status di portal: 'Delivered / Selesai'",
        "• Berikan rating & ulasan kepuasan servis"
    ], role="customer", is_highlight=True)
    
    # ------------------ BENGKEL CARDS (BOTTOM ROW) ------------------
    # Step A: SA Terima & Verifikasi
    draw_step_card(draw, (670, 670, 920, 930), "A", "Validasi & Stall", [
        "• Booking masuk ke Feed Dashboard",
        "• SA periksa jenis servis & suku cadang",
        "• Alokasi stall kerja & teknisi",
        "• Konfirmasi status: 'Dikonfirmasi'",
        "• Notifikasi otomatis terkirim ke customer"
    ], role="admin", is_highlight=True)
    
    # Step B: Kedatangan & Buka WO
    draw_step_card(draw, (970, 670, 1220, 930), "B", "Inspeksi & Buka WO", [
        "• Pelanggan tiba di bengkel resmi",
        "• SA lakukan walk-around inspection",
        "• Catat kondisi awal & barang bawaan",
        "• Terbitkan Work Order (WO) resmi",
        "• Mobil masuk ke stall teknisi"
    ], role="admin")
    
    # Step C: Pengerjaan Mekanik
    draw_step_card(draw, (1270, 670, 1520, 930), "C", "Pengerjaan & Parts", [
        "• Mekanik jalankan SOP servis Toyota",
        "• Penggantian oli TMO & filter",
        "• Penggantian sparepart asli Toyota",
        "• Mekanik update 9 tahapan pengerjaan:",
        "  (Inspeksi -> Pengerjaan -> QC)"
    ], role="admin")
    
    # Decision Gate: Kerusakan Tambahan
    draw_decision_card(draw, (1395, 340), (220, 140), "Ada Temuan Baru?", [
        "Komponen kritis aus / rusak",
        "SA konfirmasi ke pelanggan",
        "Persetujuan estimasi biaya"
    ])
    
    # Step D: QC & Pembayaran
    draw_step_card(draw, (1580, 670, 1840, 930), "D", "QC, Faktur & Kasir", [
        "• Final Quality Control oleh Foreman",
        "• Cuci mobil / pembersihan unit",
        "• Kasir cetak faktur rincian jasa & part",
        "• Pembayaran biaya di kasir bengkel",
        "• SA serahkan unit & kunci ke pelanggan"
    ], role="admin")
    
    # Master Bengkel Setup Cards (Bottom Left)
    draw_step_card(draw, (70, 670, 320, 930), "M1", "Master Servis & Biaya", [
        "• Kelola master jenis perawatan",
        "• Setting estimasi biaya & suku cadang",
        "• Atur standar estimasi durasi servis",
        "• Integrasi katalog part resmi TMO"
    ], role="admin")
    
    draw_step_card(draw, (370, 670, 620, 930), "M2", "Kapasitas & Jadwal", [
        "• Atur jam operasional cabang",
        "• Tentukan kuota slot kendaraan per jam",
        "• Atur hari libur operasional bengkel",
        "• Monitor utilisasi stall & mekanik"
    ], role="admin")
    
    # ------------------ CONNECTING ARROWS ------------------
    # 1 -> 2
    draw_arrow_connector(draw, (320, 340), (370, 340), color="#EF4444", width=3)
    # 2 -> 3
    draw_arrow_connector(draw, (620, 340), (670, 340), color="#EF4444", width=3)
    # 3 -> 4
    draw_arrow_connector(draw, (920, 340), (970, 340), color="#EF4444", width=3)
    
    # 3 down to A (Booking masuk ke admin)
    draw_arrow_connector(draw, (795, 470), (795, 670), color="#2563EB", width=3, label="Notifikasi Masuk", label_pos=(795, 570), label_color="#1E3A8A")
    
    # A -> B
    draw_arrow_connector(draw, (920, 800), (970, 800), color="#2563EB", width=3)
    # B -> C
    draw_arrow_connector(draw, (1220, 800), (1270, 800), color="#2563EB", width=3)
    
    # C up to Decision Gate
    draw.line([(1395, 670), (1395, 410)], fill="#D97706", width=2)
    draw.polygon([(1395, 410), (1388, 424), (1402, 424)], fill="#D97706")
    
    # Decision -> Yes (Hubungi customer & konfirmasi)
    f_cross = get_font(11, bold=True)
    draw.line([(1285, 340), (1220, 340)], fill="#D97706", width=2)
    draw.polygon([(1220, 340), (1234, 333), (1234, 347)], fill="#D97706")
    draw.rounded_rectangle([(1225, 318), (1280, 338)], radius=4, fill="#FEF3C7", outline="#D97706", width=1)
    draw.text((1233, 322), "ADA", fill="#92400E", font=f_cross)
    
    # Decision -> No (Lanjut ke QC)
    draw.line([(1505, 340), (1550, 340), (1550, 800), (1580, 800)], fill="#059669", width=2)
    draw.polygon([(1580, 800), (1566, 793), (1566, 807)], fill="#059669")
    draw.rounded_rectangle([(1510, 318), (1565, 338)], radius=4, fill="#ECFDF5", outline="#059669", width=1)
    draw.text((1518, 322), "TIDAK", fill="#047857", font=f_cross)
    
    # Step D up to Step 5 (Penyerahan Unit)
    draw.line([(1710, 670), (1710, 470)], fill="#059669", width=3)
    draw.polygon([(1710, 470), (1703, 484), (1717, 484)], fill="#059669")
    draw.rounded_rectangle([(1630, 555), (1790, 579)], radius=4, fill="#ECFDF5", outline="#059669", width=1)
    draw.text((1642, 560), "Mobil Selesai Diservis", fill="#047857", font=f_cross)
    
    # Save image
    img.save(output_path, "PNG", dpi=(300, 300))
    print(f"Flowchart Pengajuan Servis created successfully at: {output_path}")

create_service_booking_flowchart(r"d:\Project\dealer-toyota\docs\flowcharts\flowchart_pengajuan_servis.png")
