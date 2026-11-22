<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressReportController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();

        $profiles = StudentProfile::query()
            ->with([
                'user.activities' => fn ($q) => $q->with('category'),
                'advisor',
            ])
            ->when($viewer->hasRole('dosen') && ! $viewer->hasRole('admin'), fn ($q) => $q->where('advisor_id', $viewer->id))
            ->orderBy('nim')
            ->get()
            ->map(function (StudentProfile $p) {
                $approved = $p->user->activities->where('status', 'approved');
                $submitted = $p->user->activities->where('status', 'submitted');

                return [
                    'profile' => $p,
                    'approved_points' => (int) $approved->sum('awarded_points'),
                    'approved_count' => $approved->count(),
                    'pending_count' => $submitted->count(),
                    'ready_skpi' => $approved->sum('awarded_points') >= 100,
                ];
            });

        return view('reports.progres', compact('profiles', 'viewer'));
    }
}
