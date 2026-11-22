@props(['scores' => []])

@php
    $labels = ['Leadership', 'Technical', 'Communication', 'Social', 'Academic'];
    $cx = 150;
    $cy = 140;
    $maxR = 92;
    $count = count($labels);

    $pointAt = function (int $i, float $ratio) use ($cx, $cy, $maxR, $count): array {
        $angle = deg2rad(-90 + ($i * (360 / $count)));
        return [
            round($cx + cos($angle) * $maxR * $ratio, 2),
            round($cy + sin($angle) * $maxR * $ratio, 2),
        ];
    };

    $dataPoints = [];
    foreach ($labels as $i => $label) {
        $val = max(12, min(100, (int) ($scores[$label] ?? 0)));
        [$x, $y] = $pointAt($i, $val / 100);
        $dataPoints[] = "{$x},{$y}";
    }
    $polygonPoints = implode(' ', $dataPoints);
@endphp

<div class="w-full flex flex-col items-center">
    <svg viewBox="0 0 300 275" class="w-full max-w-[290px] h-auto" role="img" aria-label="Skill Radar Chart">
        {{-- Concentric Pentagon Grid --}}
        @foreach([0.25, 0.5, 0.75, 1.0] as $ring)
            @php
                $ringPts = [];
                for ($i = 0; $i < $count; $i++) {
                    [$rx, $ry] = $pointAt($i, $ring);
                    $ringPts[] = "{$rx},{$ry}";
                }
            @endphp
            <polygon points="{{ implode(' ', $ringPts) }}"
                     fill="{{ $ring === 1.0 ? 'rgba(249, 231, 169, 0.35)' : 'none' }}"
                     stroke="rgba(76, 37, 0, 0.18)"
                     stroke-width="1"/>
        @endforeach

        {{-- Axis Lines & Labels --}}
        @foreach($labels as $i => $label)
            @php
                [$ax, $ay] = $pointAt($i, 1.0);
                [$lx, $ly] = $pointAt($i, 1.24);
                $rawScore = (int) ($scores[$label] ?? 0);
            @endphp
            <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $ax }}" y2="{{ $ay }}" stroke="rgba(76, 37, 0, 0.18)" stroke-width="1"/>
            <text x="{{ $lx }}" y="{{ $ly }}" text-anchor="middle" dominant-baseline="middle"
                  fill="#4C2500" font-size="10.5" font-weight="700">
                {{ $label }}
            </text>
            <text x="{{ $lx }}" y="{{ $ly + 12 }}" text-anchor="middle" dominant-baseline="middle"
                  fill="#CD7000" font-size="9.5" font-weight="700">
                {{ $rawScore }}%
            </text>
        @endforeach

        {{-- Competency Polygon --}}
        <polygon points="{{ $polygonPoints }}"
                 fill="rgba(242, 170, 0, 0.38)"
                 stroke="#CD7000"
                 stroke-width="2.5"
                 stroke-linejoin="round"/>

        {{-- Vertex Dots --}}
        @foreach($labels as $i => $label)
            @php
                $val = max(12, min(100, (int) ($scores[$label] ?? 0)));
                [$dx, $dy] = $pointAt($i, $val / 100);
            @endphp
            <circle cx="{{ $dx }}" cy="{{ $dy }}" r="4.5" fill="#4C2500" stroke="#F9E7A9" stroke-width="2"/>
        @endforeach
    </svg>
</div>
