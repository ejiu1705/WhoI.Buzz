<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Kode Kategori</label>
            <input type="text" name="code" value="{{ old('code', $category->code) }}" required placeholder="SKPI-TECH-09"
                   class="w-full rounded-xl bg-[#FDF6DC] border @error('code') border-red-600 @else border-[#4C2500]/25 @enderror px-3 py-2 text-xs font-mono">
            @error('code') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Versi Kurikulum</label>
            <input type="text" name="curriculum_version" value="{{ old('curriculum_version', $category->curriculum_version ?? '2026-V1') }}" required
                   class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-mono">
        </div>
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Poin Default (5–60)</label>
            <input type="number" name="default_points" value="{{ old('default_points', $category->default_points) }}" min="5" max="60" required
                   class="w-full rounded-xl bg-[#FDF6DC] border @error('default_points') border-red-600 @else border-[#4C2500]/25 @enderror px-3 py-2 text-xs font-bold">
            @error('default_points') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Nama Kategori (Bahasa Indonesia)</label>
            <input type="text" name="name_id" value="{{ old('name_id', $category->name_id) }}" required
                   class="w-full rounded-xl bg-[#FDF6DC] border @error('name_id') border-red-600 @else border-[#4C2500]/25 @enderror px-3 py-2 text-xs">
            @error('name_id') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Category Name (English — SKPI)</label>
            <input type="text" name="name_en" value="{{ old('name_en', $category->name_en) }}" required
                   class="w-full rounded-xl bg-[#FDF6DC] border @error('name_en') border-red-600 @else border-[#4C2500]/25 @enderror px-3 py-2 text-xs">
            @error('name_en') <p class="mt-1 text-xs text-red-700 font-semibold">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Domain Skill Radar</label>
            <select name="competency_domain" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-bold">
                @foreach(['Leadership', 'Technical', 'Communication', 'Social', 'Academic'] as $dom)
                    <option value="{{ $dom }}" @selected(old('competency_domain', $category->competency_domain) === $dom)>{{ $dom }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-[#4C2500] mb-1">Tingkat</label>
            <select name="level" class="w-full rounded-xl bg-[#FDF6DC] border border-[#4C2500]/25 px-3 py-2 text-xs font-bold">
                @foreach(['Lokal', 'Nasional', 'Internasional'] as $lvl)
                    <option value="{{ $lvl }}" @selected(old('level', $category->level) === $lvl)>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="pt-4">
            <label class="flex items-center gap-2 text-xs font-bold text-[#4C2500] cursor-pointer">
                <input type="checkbox" name="is_mandatory" value="1" @checked(old('is_mandatory', $category->is_mandatory))>
                <span>Kategori Wajib Syarat SKPI</span>
            </label>
        </div>
    </div>
</div>
