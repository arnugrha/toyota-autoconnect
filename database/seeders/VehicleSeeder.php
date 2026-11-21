<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehicleImage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imageDir = public_path('images/vehicles');
        if (! File::exists($imageDir)) {
            File::makeDirectory($imageDir, 0755, true);
        }

        $vehiclesData = [
            [
                'name' => 'All New Kijang Innova Zenix',
                'slug' => 'all-new-kijang-innova-zenix',
                'category' => 'MPV',
                'variant' => '2.0 V CVT HEV (Hybrid)',
                'price' => 545000000,
                'status' => 'available',
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(10),
                'transmission' => 'e-CVT Automatic',
                'fuel_type' => 'Hybrid (Bensin + Motor Listrik)',
                'engine_capacity' => '1.987 cc (M20A-FXS)',
                'passenger_capacity' => 7,
                'dimensions' => '4.755 x 1.850 x 1.795 mm',
                'color' => '#1e293b',
                'accent' => '#0284c7',
                'description' => 'Generasi terbaru Innova yang mengadopsi platform TNGA: GA-C dengan sistem penggerak roda depan (FWD) dan opsi Toyota Hybrid System (THS) Generasi ke-5. Menghadirkan efisiensi bahan bakar superior, kabin hening, panoramic sunroof mewah, serta Toyota Safety Sense (TSS) 3.0.',
            ],
            [
                'name' => 'All New Avanza',
                'slug' => 'all-new-avanza',
                'category' => 'MPV',
                'variant' => '1.5 G CVT TSS',
                'price' => 278800000,
                'status' => 'available',
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(15),
                'transmission' => 'CVT Automatic',
                'fuel_type' => 'Bensin Tanpa Timbal',
                'engine_capacity' => '1.496 cc (2NR-VE)',
                'passenger_capacity' => 7,
                'dimensions' => '4.395 x 1.730 x 1.700 mm',
                'color' => '#334155',
                'accent' => '#dc2626',
                'description' => 'Pilihan keluarga Indonesia dengan platform modern berteknologi FWD dan transmisi CVT yang halus. Dilengkapi fitur keselamatan komprehensif Toyota Safety Sense (TSS) untuk rasa aman maksimal di setiap perjalanan.',
            ],
            [
                'name' => 'All New Veloz',
                'slug' => 'all-new-veloz',
                'category' => 'MPV',
                'variant' => '1.5 Q CVT TSS',
                'price' => 315300000,
                'status' => 'available',
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(12),
                'transmission' => 'CVT Automatic',
                'fuel_type' => 'Bensin Tanpa Timbal',
                'engine_capacity' => '1.496 cc (2NR-VE)',
                'passenger_capacity' => 7,
                'dimensions' => '4.475 x 1.750 x 1.700 mm',
                'color' => '#0f172a',
                'accent' => '#f59e0b',
                'description' => 'Desain crossover-look yang premium dan ekspresif. Dilengkapi fitur Electric Parking Brake (EPB) dengan Auto Hold, wireless charger, serta dashboard modern dengan ambient lighting.',
            ],
            [
                'name' => 'New Fortuner GR Sport',
                'slug' => 'new-fortuner-gr-sport',
                'category' => 'SUV',
                'variant' => '2.8 GR Sport 4x4 A/T',
                'price' => 745350000,
                'status' => 'available',
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(8),
                'transmission' => '6-Speed Automatic Sport Sequential',
                'fuel_type' => 'Diesel Common Rail',
                'engine_capacity' => '2.755 cc (1GD-FTV Turbo)',
                'passenger_capacity' => 7,
                'dimensions' => '4.795 x 1.855 x 1.835 mm',
                'color' => '#111827',
                'accent' => '#EB0A1E',
                'description' => 'SUV ladder-frame tangguh bermesin 2.800 cc bertenaga 203.9 PS dengan torsi masif 500 Nm. Dilengkapi suspensi racikan Gazoo Racing (GR) dan sentuhan bodi agresif untuk performa tak tertandingi di aspal maupun off-road.',
            ],
            [
                'name' => 'All New Yaris Cross HEV',
                'slug' => 'all-new-yaris-cross-hev',
                'category' => 'SUV',
                'variant' => '1.5 S HV CVT TSS GR Parts',
                'price' => 449950000,
                'status' => 'available',
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(5),
                'transmission' => 'e-CVT Automatic',
                'fuel_type' => 'Hybrid EV',
                'engine_capacity' => '1.496 cc (2NR-VEX)',
                'passenger_capacity' => 5,
                'dimensions' => '4.310 x 1.770 x 1.615 mm',
                'color' => '#1e3a8a',
                'accent' => '#38bdf8',
                'description' => 'Compact SUV ramah lingkungan dengan akselerasi instan dari motor listrik dan konsumsi BBM luar biasa hemat hingga di atas 25 km/liter. Dilengkapi panoramic glass roof, power back door kick sensor, dan TSS terlengkap.',
            ],
            [
                'name' => 'New Rush GR Sport',
                'slug' => 'new-rush-gr-sport',
                'category' => 'SUV',
                'variant' => '1.5 S A/T GR Sport',
                'price' => 306000000,
                'status' => 'available',
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(20),
                'transmission' => '4-Speed Automatic',
                'fuel_type' => 'Bensin Tanpa Timbal',
                'engine_capacity' => '1.496 cc (2NR-VE)',
                'passenger_capacity' => 7,
                'dimensions' => '4.435 x 1.695 x 1.705 mm',
                'color' => '#374151',
                'accent' => '#ef4444',
                'description' => 'SUV tangguh penggerak roda belakang (RWD) dengan ground clearance tinggi 220 mm. Sangat andal melewati tanjakan dan kontur jalan menantang di berbagai wilayah tanah air.',
            ],
            [
                'name' => 'All New Agya GR Sport',
                'slug' => 'all-new-agya-gr-sport',
                'category' => 'Hatchback',
                'variant' => '1.2 GR Sport CVT Two Tone',
                'price' => 256000000,
                'status' => 'available',
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(7),
                'transmission' => 'CVT Automatic',
                'fuel_type' => 'Bensin Tanpa Timbal',
                'engine_capacity' => '1.198 cc (WA-VE Dual VVT-i)',
                'passenger_capacity' => 5,
                'dimensions' => '3.830 x 1.665 x 1.505 mm',
                'color' => '#b91c1c',
                'accent' => '#0f172a',
                'description' => 'Sporty hatchback lincah dengan platform DNGA baru dan tuning suspensi GR eksklusif. Handling presisi, responsif, dan sangat menyenangkan untuk kebutuhan mobilitas urban sehari-hari.',
            ],
            [
                'name' => 'New Yaris GR Sport',
                'slug' => 'new-yaris-gr-sport',
                'category' => 'Hatchback',
                'variant' => '1.5 S CVT 7 Airbags',
                'price' => 345000000,
                'status' => 'available',
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(18),
                'transmission' => 'CVT 7-Speed Sequential Shiftmatic',
                'fuel_type' => 'Bensin Tanpa Timbal',
                'engine_capacity' => '1.496 cc (2NR-FE)',
                'passenger_capacity' => 5,
                'dimensions' => '4.145 x 1.730 x 1.500 mm',
                'color' => '#047857',
                'accent' => '#f59e0b',
                'description' => 'Hatchback aerodinamis dengan paddle shift dan radius putar minim 5.1 m. Dilengkapi 7 airbags untuk perlindungan menyeluruh penumpang.',
            ],
            [
                'name' => 'All New Camry Hybrid',
                'slug' => 'all-new-camry-hybrid',
                'category' => 'Sedan',
                'variant' => '2.5 HV Platinum',
                'price' => 937400000,
                'status' => 'available',
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(6),
                'transmission' => 'e-CVT Automatic',
                'fuel_type' => 'Hybrid EV',
                'engine_capacity' => '2.487 cc (A25A-FXS)',
                'passenger_capacity' => 5,
                'dimensions' => '4.885 x 1.840 x 1.445 mm',
                'color' => '#030712',
                'accent' => '#0284c7',
                'description' => 'Simbol kemewahan dan prestise eksekutif. Menghadirkan kesenyapan kabin level tertinggi, rear seat control panel, audio premium JBL, serta sistem hibrida generasi teranyar.',
            ],
            [
                'name' => 'All New Corolla Altis Hybrid',
                'slug' => 'all-new-corolla-altis-hybrid',
                'category' => 'Sedan',
                'variant' => '1.8 Hybrid AT',
                'price' => 617000000,
                'status' => 'available',
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(14),
                'transmission' => 'e-CVT Automatic',
                'fuel_type' => 'Hybrid EV',
                'engine_capacity' => '1.798 cc (2ZR-FXE)',
                'passenger_capacity' => 5,
                'dimensions' => '4.630 x 1.780 x 1.435 mm',
                'color' => '#475569',
                'accent' => '#06b6d4',
                'description' => 'Sedan kompak legendaris yang nyaman dan presisi. Efisiensi bahan bakar kelas atas dengan platform TNGA dan fitur keselamatan Toyota Safety Sense (TSS).',
            ],
            [
                'name' => 'Hilux Double Cabin 4x4',
                'slug' => 'hilux-double-cabin-4x4',
                'category' => 'Commercial',
                'variant' => '2.4 V 4x4 DSL A/T',
                'price' => 532700000,
                'status' => 'available',
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(22),
                'transmission' => '6-Speed Automatic 4WD Shift-on-the-fly',
                'fuel_type' => 'Diesel Common Rail Turbo',
                'engine_capacity' => '2.393 cc (2GD-FTV)',
                'passenger_capacity' => 5,
                'dimensions' => '5.325 x 1.855 x 1.815 mm',
                'color' => '#1f2937',
                'accent' => '#e11d48',
                'description' => 'Pekerja keras sejati di sektor perkebunan, pertambangan, maupun hobi ekspedisi off-road. Rangka heavy-duty dengan daya angkut bak belakang besar dan diff lock elektronik.',
            ],
            [
                'name' => 'New Hiace Commuter',
                'slug' => 'new-hiace-commuter',
                'category' => 'Commercial',
                'variant' => '2.8 M/T High Roof',
                'price' => 564300000,
                'status' => 'available',
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(30),
                'transmission' => '5-Speed Manual',
                'fuel_type' => 'Diesel Turbo',
                'engine_capacity' => '2.755 cc (1GD-FTV)',
                'passenger_capacity' => 16,
                'dimensions' => '5.380 x 1.880 x 2.285 mm',
                'color' => '#e2e8f0',
                'accent' => '#0284c7',
                'description' => 'Kendaraan komersial penumpang nomor satu di Indonesia untuk armada travel, pariwisata, dan shuttle korporasi. Ruang kabin luas berkapasitas 16 penumpang dengan AC dual-zone.',
            ],
            [
                'name' => 'All New bZ4X EV',
                'slug' => 'all-new-bz4x-ev',
                'category' => 'SUV',
                'variant' => 'Panoramic Roof FWD EV',
                'price' => 1190000000, // OTR Resmi Toyota Indonesia
                'status' => 'available',
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(2),
                'transmission' => 'Single Speed Electric Drive',
                'fuel_type' => 'Full Electric Battery (BEV)',
                'engine_capacity' => '71.4 kWh Battery Pack (Lithium-ion)',
                'passenger_capacity' => 5,
                'dimensions' => '4.690 x 1.860 x 1.650 mm',
                'color' => '#0b1329',
                'accent' => '#06b6d4',
                'description' => 'Langkah berani Toyota menuju elektrifikasi masa depan. Menggunakan platform khusus e-TNGA dengan jangkauan baterai hingga 500 km per pengecasan penuh. Unit segera tiba di dealer kami.',
            ],
            [
                'name' => 'All New Land Cruiser 300',
                'slug' => 'all-new-land-cruiser-300',
                'category' => 'SUV',
                'variant' => 'GR Sport 3.3L Twin Turbo Diesel 4WD',
                'price' => 2588000000,
                'status' => 'reserved', // Reserved to test "Terpesan"
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(25),
                'transmission' => '10-Speed Direct Shift Automatic',
                'fuel_type' => 'Diesel Twin Turbo',
                'engine_capacity' => '3.346 cc (F33A-FTV V6)',
                'passenger_capacity' => 7,
                'dimensions' => '4.965 x 1.980 x 1.945 mm',
                'color' => '#18181b',
                'accent' => '#dc2626',
                'description' => 'The King of 4WD. Legenda ketangguhan dunia dengan teknologi E-KDSS (Electronic Kinetic Dynamic Suspension System), Multi-Terrain Monitor, dan interior super premium.',
            ],
            [
                'name' => 'Toyota GR Supra',
                'slug' => 'toyota-gr-supra',
                'category' => 'Sedan',
                'variant' => '3.0L A/T 6-Cylinder Turbo',
                'price' => 2237600000,
                'status' => 'sold', // Sold to test "Terjual"
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(40),
                'transmission' => '8-Speed Sport Automatic with Paddle Shift',
                'fuel_type' => 'Bensin Oktan Tinggi',
                'engine_capacity' => '2.998 cc (Inline 6 Twin Scroll Turbo)',
                'passenger_capacity' => 2,
                'dimensions' => '4.380 x 1.865 x 1.295 mm',
                'color' => '#7f1d1d',
                'accent' => '#ffffff',
                'description' => 'Pure sports car sejati berdistribusi bobot sempurna 50:50. Menghasilkan tenaga 387 PS dan torsi 500 Nm dengan akselerasi 0-100 km/jam hanya dalam 4.1 detik. Unit batch ini telah terjual.',
            ],
            [
                'name' => 'Toyota Compact EV Concept (Unreleased)',
                'slug' => 'toyota-compact-ev-concept',
                'category' => 'Hatchback',
                'variant' => 'Prototype Draft',
                'price' => null,
                'status' => 'incoming',
                'is_featured' => false,
                'published_at' => null, // Unpublished draft - MUST NOT appear publicly!
                'transmission' => 'Electric',
                'fuel_type' => 'Battery EV',
                'engine_capacity' => 'N/A',
                'passenger_capacity' => 4,
                'dimensions' => '3.700 x 1.650 x 1.500 mm',
                'color' => '#334155',
                'accent' => '#10b981',
                'description' => 'Draft kendaraan uji internal yang belum dipublikasikan.',
            ],
        ];

        foreach ($vehiclesData as $data) {
            $vehicle = Vehicle::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'category' => $data['category'],
                    'variant' => $data['variant'],
                    'price' => $data['price'],
                    'status' => $data['status'],
                    'is_featured' => $data['is_featured'],
                    'published_at' => $data['published_at'],
                    'transmission' => $data['transmission'],
                    'fuel_type' => $data['fuel_type'],
                    'engine_capacity' => $data['engine_capacity'],
                    'passenger_capacity' => $data['passenger_capacity'],
                    'dimensions' => $data['dimensions'],
                    'description' => $data['description'],
                ]
            );

            // Generate SVG files
            $primaryFilename = "{$data['slug']}-exterior.svg";
            $interiorFilename = "{$data['slug']}-cockpit.svg";

            $this->generateVehicleSvg(
                filePath: $imageDir.'/'.$primaryFilename,
                title: $data['name'],
                variant: $data['variant'],
                category: $data['category'],
                bodyColor: $data['color'],
                accentColor: $data['accent'],
                viewType: 'exterior'
            );

            $this->generateVehicleSvg(
                filePath: $imageDir.'/'.$interiorFilename,
                title: $data['name'],
                variant: 'Interior Cockpit & Comfort',
                category: $data['category'],
                bodyColor: '#0f172a',
                accentColor: $data['accent'],
                viewType: 'interior'
            );

            // Attach images
            VehicleImage::updateOrCreate(
                ['vehicle_id' => $vehicle->id, 'sort_order' => 1],
                [
                    'image_path' => 'images/vehicles/'.$primaryFilename,
                    'alt_text' => $data['name'].' '.$data['variant'].' - Tampilan Eksterior',
                    'is_primary' => true,
                ]
            );

            VehicleImage::updateOrCreate(
                ['vehicle_id' => $vehicle->id, 'sort_order' => 2],
                [
                    'image_path' => 'images/vehicles/'.$interiorFilename,
                    'alt_text' => $data['name'].' - Tampilan Interior & Kabin Kemudi',
                    'is_primary' => false,
                ]
            );
        }
    }

    /**
     * Generate an automotive studio SVG image.
     */
    private function generateVehicleSvg(
        string $filePath,
        string $title,
        string $variant,
        string $category,
        string $bodyColor,
        string $accentColor,
        string $viewType
    ): void {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES);
        $safeVariant = htmlspecialchars($variant, ENT_QUOTES);
        $safeCat = htmlspecialchars($category, ENT_QUOTES);

        if ($viewType === 'interior') {
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 560" width="900" height="560" fill="none">
  <defs>
    <linearGradient id="intBg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0b0f19"/>
      <stop offset="60%" stop-color="#111827"/>
      <stop offset="100%" stop-color="#030712"/>
    </linearGradient>
    <linearGradient id="dashGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#374151"/>
      <stop offset="100%" stop-color="#1f2937"/>
    </linearGradient>
    <linearGradient id="screenGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1e293b"/>
      <stop offset="100%" stop-color="#0284c7"/>
    </linearGradient>
    <radialGradient id="intGlow" cx="50%" cy="30%" r="50%">
      <stop offset="0%" stop-color="{$accentColor}" stop-opacity="0.2"/>
      <stop offset="100%" stop-color="#000000" stop-opacity="0"/>
    </radialGradient>
  </defs>

  <!-- Background -->
  <rect width="900" height="560" fill="url(#intBg)"/>
  <circle cx="450" cy="220" r="350" fill="url(#intGlow)"/>

  <!-- Cockpit Windshield Horizon -->
  <path d="M 120 180 L 780 180 L 860 380 L 40 380 Z" fill="#030712" fill-opacity="0.9"/>
  <line x1="80" y1="280" x2="820" y2="280" stroke="#334155" stroke-dasharray="8 8" stroke-width="1.5" stroke-opacity="0.4"/>

  <!-- Dashboard Surface -->
  <path d="M 60 340 C 200 320, 700 320, 840 340 L 890 560 L 10 560 Z" fill="url(#dashGrad)" stroke="#4b5563" stroke-width="2"/>

  <!-- Center Floating Infotainment Display -->
  <g transform="translate(360, 240)">
    <rect x="0" y="0" width="180" height="105" rx="8" fill="#090d16" stroke="#64748b" stroke-width="2.5"/>
    <rect x="6" y="6" width="168" height="93" rx="5" fill="url(#screenGrad)" fill-opacity="0.9"/>
    <!-- UI Elements on screen -->
    <text x="90" y="38" text-anchor="middle" font-family="system-ui, sans-serif" font-size="11" font-weight="700" fill="#f8fafc">TOYOTA MULTIMEDIA</text>
    <text x="90" y="58" text-anchor="middle" font-family="system-ui, sans-serif" font-size="9" fill="#94a3b8">{$safeTitle}</text>
    <line x1="20" y1="70" x2="160" y2="70" stroke="#38bdf8" stroke-width="2"/>
    <circle cx="90" cy="84" r="5" fill="#22c55e"/>
  </g>

  <!-- Steering Wheel (Right Hand Drive - RHD for Indonesia) -->
  <g transform="translate(640, 420)">
    <!-- Outer Rim -->
    <circle cx="0" cy="0" r="110" fill="none" stroke="#111827" stroke-width="24"/>
    <circle cx="0" cy="0" r="110" fill="none" stroke="#4b5563" stroke-width="4"/>
    <!-- Center Hub -->
    <circle cx="0" cy="0" r="45" fill="#1f2937" stroke="#9ca3af" stroke-width="2"/>
    <text x="0" y="5" text-anchor="middle" font-family="system-ui, sans-serif" font-size="14" font-weight="800" fill="#f1f5f9">TOYOTA</text>
    <!-- Spokes -->
    <path d="M -45 0 L -100 -20 L -100 20 Z" fill="#374151"/>
    <path d="M 45 0 L 100 -20 L 100 20 Z" fill="#374151"/>
    <path d="M 0 45 L -20 100 L 20 100 Z" fill="#374151"/>
    <!-- Ambient Red Line -->
    <circle cx="0" cy="-110" r="4" fill="{$accentColor}"/>
  </g>

  <!-- Digital Instrument Cluster behind wheel -->
  <g transform="translate(560, 275)">
    <rect x="0" y="0" width="160" height="75" rx="6" fill="#050811" stroke="#475569" stroke-width="2"/>
    <text x="80" y="32" text-anchor="middle" font-family="system-ui, sans-serif" font-size="12" font-weight="700" fill="#38bdf8">0 km/h</text>
    <text x="80" y="52" text-anchor="middle" font-family="system-ui, sans-serif" font-size="9" fill="#22c55e">READY • ECO MODE</text>
  </g>

  <!-- Title Badge -->
  <rect x="40" y="40" width="340" height="64" rx="8" fill="#000000" fill-opacity="0.6" stroke="#334155" stroke-width="1"/>
  <text x="60" y="68" font-family="system-ui, sans-serif" font-size="15" font-weight="700" fill="#f8fafc">{$safeTitle}</text>
  <text x="60" y="90" font-family="system-ui, sans-serif" font-size="11" font-weight="500" fill="{$accentColor}">PREMIUM COCKPIT &amp; INTERIOR</text>
</svg>
SVG;
        } else {
            // Exterior Showroom Side Silhouette
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 560" width="900" height="560" fill="none">
  <defs>
    <linearGradient id="extBg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0a0f1d"/>
      <stop offset="50%" stop-color="#141c2e"/>
      <stop offset="100%" stop-color="#0a0f1d"/>
    </linearGradient>
    <linearGradient id="carPaint" x1="0%" y1="0%" x2="100%" y2="40%">
      <stop offset="0%" stop-color="{$bodyColor}"/>
      <stop offset="50%" stop-color="#475569"/>
      <stop offset="100%" stop-color="{$bodyColor}"/>
    </linearGradient>
    <radialGradient id="spotlight" cx="50%" cy="45%" r="55%">
      <stop offset="0%" stop-color="{$accentColor}" stop-opacity="0.18"/>
      <stop offset="100%" stop-color="#000000" stop-opacity="0"/>
    </radialGradient>
    <linearGradient id="glassGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1e293b"/>
      <stop offset="100%" stop-color="#090d16"/>
    </linearGradient>
  </defs>

  <!-- Showroom Ambient -->
  <rect width="900" height="560" fill="url(#extBg)"/>
  <circle cx="450" cy="270" r="380" fill="url(#spotlight)"/>

  <!-- Floor Grid & Stage Reflection -->
  <ellipse cx="450" cy="460" rx="360" ry="18" fill="#000000" fill-opacity="0.7" filter="blur(8px)"/>
  <line x1="80" y1="458" x2="820" y2="458" stroke="#334155" stroke-width="1.5" stroke-opacity="0.7"/>

  <!-- Car Silhouette Group -->
  <g transform="translate(140, 180)">
    <!-- Cabin Windows -->
    <path d="M 150 140 L 250 55 L 430 55 L 510 140 Z" fill="url(#glassGrad)" stroke="#64748b" stroke-width="2"/>
    <line x1="330" y1="56" x2="330" y2="138" stroke="#334155" stroke-width="3"/>

    <!-- Main Aerodynamic Body Silhouette -->
    <path d="M 50 180 
             C 55 155, 80 145, 120 140 
             L 160 135 
             L 255 50 
             C 270 45, 300 45, 435 45 
             L 515 135 
             L 590 145 
             C 625 150, 645 165, 635 185 
             L 620 220 
             C 600 225, 575 225, 550 225 
             C 545 195, 505 170, 470 170 
             C 435 170, 395 195, 390 225 
             L 260 225 
             C 255 195, 215 170, 180 170 
             C 145 170, 105 195, 100 225 
             L 55 225 
             Z" fill="url(#carPaint)" stroke="#94a3b8" stroke-width="2.5"/>

    <!-- Dynamic Character Line -->
    <path d="M 130 152 Q 350 148 600 160" stroke="{$accentColor}" stroke-width="3.5" stroke-linecap="round"/>

    <!-- Front LED Headlight glow -->
    <path d="M 615 170 L 635 178 L 625 188 Z" fill="#38bdf8" filter="drop-shadow(0 0 10px #38bdf8)"/>
    <!-- Rear Taillight signature -->
    <path d="M 55 170 L 45 178 L 55 186 Z" fill="#ef4444" filter="drop-shadow(0 0 8px #ef4444)"/>

    <!-- Front & Rear Alloy Wheels -->
    <g transform="translate(180, 225)">
      <circle cx="0" cy="0" r="46" fill="#090d16" stroke="#475569" stroke-width="6"/>
      <circle cx="0" cy="0" r="30" fill="#1e293b" stroke="#cbd5e1" stroke-width="3.5"/>
      <circle cx="0" cy="0" r="10" fill="{$accentColor}"/>
      <line x1="0" y1="-28" x2="0" y2="28" stroke="#94a3b8" stroke-width="3"/>
      <line x1="-28" y1="0" x2="28" y2="0" stroke="#94a3b8" stroke-width="3"/>
      <line x1="-20" y1="-20" x2="20" y2="20" stroke="#94a3b8" stroke-width="2.5"/>
      <line x1="20" y1="-20" x2="-20" y2="20" stroke="#94a3b8" stroke-width="2.5"/>
    </g>

    <g transform="translate(470, 225)">
      <circle cx="0" cy="0" r="46" fill="#090d16" stroke="#475569" stroke-width="6"/>
      <circle cx="0" cy="0" r="30" fill="#1e293b" stroke="#cbd5e1" stroke-width="3.5"/>
      <circle cx="0" cy="0" r="10" fill="{$accentColor}"/>
      <line x1="0" y1="-28" x2="0" y2="28" stroke="#94a3b8" stroke-width="3"/>
      <line x1="-28" y1="0" x2="28" y2="0" stroke="#94a3b8" stroke-width="3"/>
      <line x1="-20" y1="-20" x2="20" y2="20" stroke="#94a3b8" stroke-width="2.5"/>
      <line x1="20" y1="-20" x2="-20" y2="20" stroke="#94a3b8" stroke-width="2.5"/>
    </g>
  </g>

  <!-- Overlay Watermark & Category Tag -->
  <rect x="40" y="40" width="380" height="70" rx="8" fill="#000000" fill-opacity="0.6" stroke="#334155" stroke-width="1"/>
  <text x="60" y="68" font-family="system-ui, sans-serif" font-size="16" font-weight="700" fill="#f8fafc">{$safeTitle}</text>
  <text x="60" y="92" font-family="system-ui, sans-serif" font-size="12" font-weight="600" fill="{$accentColor}">{$safeCat} • {$safeVariant}</text>
</svg>
SVG;
        }

        File::put($filePath, $svg);
    }
}
