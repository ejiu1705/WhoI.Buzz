<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSkpiCategoryRequest;
use App\Http\Requests\UpdateSkpiCategoryRequest;
use App\Http\Resources\V1\SkpiCategoryResource;
use App\Models\SkpiCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class SkpiCategoryApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = SkpiCategory::query()
            ->with('creator')
            ->withCount('activities')
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name_id', 'like', '%'.$request->cari.'%')
                ->orWhere('code', 'like', '%'.$request->cari.'%')))
            ->when($request->filled('domain'), fn ($q) => $q->byDomain((string) $request->domain))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return SkpiCategoryResource::collection($data);
    }

    #[Middleware('auth:sanctum')]
    public function store(StoreSkpiCategoryRequest $request): JsonResponse
    {
        Gate::authorize('kelola-kurikulum-skpi');

        $cat = new SkpiCategory($request->validated());
        $cat->created_by = $request->user()->id;
        $cat->save();

        return (new SkpiCategoryResource($cat->load('creator')->loadCount('activities')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(SkpiCategory $skpi_category): SkpiCategoryResource
    {
        return new SkpiCategoryResource($skpi_category->load('creator')->loadCount('activities'));
    }

    #[Middleware('auth:sanctum')]
    public function update(UpdateSkpiCategoryRequest $request, SkpiCategory $skpi_category): SkpiCategoryResource
    {
        Gate::authorize('update', $skpi_category);

        $skpi_category->update($request->validated());

        return new SkpiCategoryResource($skpi_category->load('creator')->loadCount('activities'));
    }

    #[Middleware('auth:sanctum')]
    public function destroy(SkpiCategory $skpi_category): Response
    {
        Gate::authorize('delete', $skpi_category);

        $skpi_category->delete();

        return response()->noContent();
    }
}
