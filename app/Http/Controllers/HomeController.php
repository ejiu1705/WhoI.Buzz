<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\IssuedCredential;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $stats = [
            'verified_credentials' => IssuedCredential::whereNull('revoked_at')->count(),
            'approved_activities' => Activity::where('status', 'approved')->count(),
            'active_students' => StudentProfile::count(),
            'public_portfolios' => StudentProfile::where('is_public_enabled', true)->count(),
        ];

        $featuredPortfolios = StudentProfile::with(['user.activities' => fn ($q) => $q->where('status', 'approved')])
            ->where('is_public_enabled', true)
            ->take(3)
            ->get();

        $sampleCredential = IssuedCredential::with('student.studentProfile')
            ->whereNull('revoked_at')
            ->where('type', 'skpi')
            ->latest('issued_at')
            ->first();

        $demoUsers = User::with(['roles', 'studentProfile'])
            ->whereIn('email', [
                'nadia.putri@campus.ac.id',
                'dr.hendrawan@campus.ac.id',
                'admin.kemahasiswaan@campus.ac.id',
                'hrd.mitra@techcorp.id',
            ])
            ->get()
            ->keyBy('role_scope');

        return view('home', compact('stats', 'featuredPortfolios', 'sampleCredential', 'demoUsers'));
    }
}
