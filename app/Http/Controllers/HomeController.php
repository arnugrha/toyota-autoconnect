<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Display the public homepage.
     */
    public function index(): View
    {
        $featuredVehicles = Vehicle::published()
            ->featured()
            ->with('images')
            ->latest('published_at')
            ->take(6)
            ->get();

        // Standard categories available in the showroom
        $categoryList = ['MPV', 'SUV', 'Hatchback', 'Sedan', 'Commercial'];
        $categories = collect($categoryList)->map(function (string $cat) {
            $count = Vehicle::published()->where('category', $cat)->count();

            $descriptions = [
                'MPV' => 'Kenyamanan dan kelapangan kabin untuk seluruh keluarga.',
                'SUV' => 'Ketangguhan dan ground clearance tinggi untuk segala medan.',
                'Hatchback' => 'Kelincahan, efisiensi bahan bakar, dan gaya modern urban.',
                'Sedan' => 'Kenyamanan berkendara premium dengan sentuhan kemewahan.',
                'Commercial' => 'Ketangguhan daya angkut untuk mendukung produktivitas bisnis.',
            ];

            return [
                'name' => $cat,
                'description' => $descriptions[$cat] ?? 'Pilihan kendaraan andalan Toyota.',
                'count' => $count,
            ];
        });

        return view('home', [
            'featuredVehicles' => $featuredVehicles,
            'categories' => $categories,
        ]);
    }
}
