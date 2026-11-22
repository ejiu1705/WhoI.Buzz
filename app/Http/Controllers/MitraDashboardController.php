<?php

namespace App\Http\Controllers;

use App\Models\IssuedCredential;
use App\Models\StudentProfile;
use App\Services\SemanticTalentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MitraDashboardController extends Controller
{
    public function index(Request $request, SemanticTalentService $semantic): View
    {
        $query = (string) $request->query('q', '');
        $prodi = $request->query('prodi') ?: null;

        $results = $semantic->search($query, $prodi, 1);
        $prodiList = StudentProfile::where('is_public_enabled', true)->distinct()->pluck('prodi_name')->sort()->values();

        $stats = [
            'public_talents' => StudentProfile::where('is_public_enabled', true)->count(),
            'skpi_ready' => StudentProfile::where('is_public_enabled', true)->where('yudisium_status', 'eligible')->count(),
            'verified_credentials' => IssuedCredential::whereNull('revoked_at')->count(),
            'revoked_alerts' => IssuedCredential::whereNotNull('revoked_at')->count(),
        ];

        $recentCredentials = IssuedCredential::with('student.studentProfile')
            ->latest('issued_at')
            ->take(5)
            ->get();

        return view('mitra.dashboard', compact('query', 'prodi', 'results', 'prodiList', 'stats', 'recentCredentials'));
    }
}
