<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSkpiCategoryRequest;
use App\Http\Requests\UpdateSkpiCategoryRequest;
use App\Models\SkpiCategory;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SkpiCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = SkpiCategory::query()
            ->with('creator')
            ->withCount('activities')
            ->when($request->filled('cari'), function ($query) use ($request) {
                $q = trim((string) $request->query('cari'));
                $query->where(function ($w) use ($q) {
                    $w->where('name_id', 'like', "%{$q}%")
                        ->orWhere('name_en', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('domain'), fn ($q) => $q->byDomain((string) $request->query('domain')))
            ->when($request->boolean('hanya_wajib'), fn ($q) => $q->mandatory())
            ->orderByDesc('is_mandatory')
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        // Modul 3 Aggregation & GroupBy Query Builder recap
        $domainSummary = DB::table('skpi_categories')
            ->select(
                'competency_domain',
                DB::raw('COUNT(*) as jumlah_kategori'),
                DB::raw('SUM(default_points) as total_bobot_poin')
            )
            ->groupBy('competency_domain')
            ->orderBy('competency_domain')
            ->get();

        return view('kurikulum.index', compact('categories', 'domainSummary'));
    }

    public function create(): View
    {
        return view('kurikulum.create', [
            'category' => new SkpiCategory(['curriculum_version' => '2026-V1', 'default_points' => 20]),
        ]);
    }

    public function store(StoreSkpiCategoryRequest $request, AuditService $audit): RedirectResponse
    {
        // Modul 6: created_by filled strictly from auth user, never from mass assignment
        $category = new SkpiCategory($request->validated());
        $category->created_by = $request->user()->id;
        $category->save();

        $audit->log('skpi_category.created', $category, ['code' => $category->code]);

        return redirect()->route('kurikulum-skpi.index')
            ->with('success', "Kategori SKPI {$category->code} berhasil ditambahkan.");
    }

    public function show(SkpiCategory $kurikulum_skpi): View
    {
        $kurikulum_skpi->load(['creator', 'activities.student']);

        return view('kurikulum.show', ['category' => $kurikulum_skpi]);
    }

    public function edit(SkpiCategory $kurikulum_skpi): View
    {
        Gate::authorize('update', $kurikulum_skpi);

        return view('kurikulum.edit', ['category' => $kurikulum_skpi]);
    }

    public function update(UpdateSkpiCategoryRequest $request, SkpiCategory $kurikulum_skpi, AuditService $audit): RedirectResponse
    {
        Gate::authorize('update', $kurikulum_skpi);

        $kurikulum_skpi->update($request->validated());
        $audit->log('skpi_category.updated', $kurikulum_skpi, ['code' => $kurikulum_skpi->code]);

        return redirect()->route('kurikulum-skpi.index')
            ->with('success', "Kategori SKPI {$kurikulum_skpi->code} berhasil diperbarui.");
    }

    public function destroy(SkpiCategory $kurikulum_skpi, AuditService $audit): RedirectResponse
    {
        Gate::authorize('delete', $kurikulum_skpi);

        if ($kurikulum_skpi->activities()->exists()) {
            return back()->with('error', 'Kategori ini masih digunakan oleh data capaian mahasiswa dan tidak dapat dihapus.');
        }

        $code = $kurikulum_skpi->code;
        $kurikulum_skpi->delete();
        $audit->log('skpi_category.deleted', null, ['code' => $code]);

        return redirect()->route('kurikulum-skpi.index')
            ->with('success', "Kategori SKPI {$code} berhasil dihapus.");
    }
}
