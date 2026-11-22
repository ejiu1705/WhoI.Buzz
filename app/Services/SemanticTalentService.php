<?php

namespace App\Services;

use App\Models\StudentProfile;
use Illuminate\Support\Collection;

class SemanticTalentService
{
    /**
     * Domain-specific semantic concept vocabulary (bilingual ID + EN) used to generate
     * normalized dense embeddings compatible with pgvector cosine similarity.
     */
    private const CONCEPT_DIMENSIONS = [
        'cybersecurity' => ['cyber', 'siber', 'keamanan', 'security', 'ctf', 'capture the flag', 'pentest', 'owasp', 'cryptography', 'kriptografi', 'infosec', 'soc', 'network'],
        'cloud_devops' => ['aws', 'cloud', 'docker', 'kubernetes', 'devops', 'frankenphp', 'linux', 'minio', 's3', 'ci/cd', 'terraform', 'server'],
        'backend_web' => ['laravel', 'php', 'backend', 'api', 'postgresql', 'sql', 'web', 'fullstack', 'node', 'microservice', 'rest', 'json:api'],
        'ai_data' => ['ai', 'kecerdasan buatan', 'machine learning', 'ml', 'data', 'python', 'llm', 'nlp', 'pgvector', 'analytics', 'deep learning'],
        'uiux_product' => ['ui', 'ux', 'design', 'desain', 'figma', 'product', 'frontend', 'tailwind', 'accessibility', 'prototype'],
        'leadership' => ['ketua', 'leader', 'kepemimpinan', 'bem', 'hima', 'organisasi', 'koordinator', 'manajemen', 'president', 'lead', 'panitia'],
        'competition_award' => ['juara', 'winner', 'champion', 'medali', 'gemastik', 'hackathon', 'lomba', 'kompetisi', 'finalis', 'award', 'prestasi'],
        'research_academic' => ['riset', 'research', 'jurnal', 'publikasi', 'paper', 'scopus', 'sinta', 'asisten', 'akademik', 'skripsi'],
        'communication' => ['pembicara', 'speaker', 'moderator', 'debat', 'english', 'toefl', 'ielts', 'presentasi', 'komunikasi', 'public speaking'],
        'social_impact' => ['relawan', 'volunteer', 'pengabdian', 'sosial', 'masyarakat', 'edukasi', 'komunitas', 'sdgs', 'kkn'],
        'senior_ready' => ['semester 7', 'semester 8', 'semester akhir', 'akhir', 'tingkat akhir', 'siap kerja', 'yudisium', 'lulus', 'skpi', 'magang', 'intern'],
        'finance_biz' => ['bisnis', 'business', 'startup', 'entrepreneur', 'kewirausahaan', 'produk', 'marketing', 'keuangan', 'fintech'],
    ];

    /**
     * Build a normalized semantic vector from arbitrary Indonesian/English text.
     */
    public function embedText(string $text): array
    {
        $lower = mb_strtolower($text);
        $vector = [];

        foreach (self::CONCEPT_DIMENSIONS as $concept => $terms) {
            $score = 0.0;
            foreach ($terms as $term) {
                $count = substr_count($lower, $term);
                if ($count > 0) {
                    $score += min(3.0, 1.0 + ($count - 1) * 0.45);
                }
            }
            $vector[$concept] = $score;
        }

        // L2 normalize vector
        $norm = sqrt(array_sum(array_map(fn ($v) => $v * $v, $vector)));
        if ($norm > 0) {
            foreach ($vector as $k => $v) {
                $vector[$k] = round($v / $norm, 5);
            }
        }

        return $vector;
    }

    /**
     * Refresh and persist a student's semantic embedding from their profile + approved activities.
     */
    public function refreshStudentEmbedding(StudentProfile $profile): array
    {
        $profile->loadMissing(['user.activities' => fn ($q) => $q->where('status', 'approved')->with('category')]);

        $corpusParts = [
            $profile->headline ?? '',
            $profile->bio ?? '',
            $profile->prodi_name,
            $profile->faculty_name,
            "semester {$profile->semester}",
            $profile->semester >= 7 ? 'semester akhir tingkat akhir siap kerja' : '',
            implode(' ', $profile->skills_json ?? []),
        ];

        foreach ($profile->user->activities as $act) {
            $corpusParts[] = $act->title_id;
            $corpusParts[] = $act->title_en;
            $corpusParts[] = $act->organizer;
            $corpusParts[] = $act->role_in_activity;
            $corpusParts[] = $act->category?->name_id ?? '';
            $corpusParts[] = $act->category?->competency_domain ?? '';
        }

        $embedding = $this->embedText(implode(' ', $corpusParts));
        $profile->forceFill(['embedding' => $embedding])->save();

        return $embedding;
    }

    /**
     * Cosine similarity semantic search across opted-in (Privacy-by-Default) student profiles.
     */
    public function search(string $query, ?string $prodiFilter = null, int $minSemester = 1): Collection
    {
        $profiles = StudentProfile::query()
            ->where('is_public_enabled', true)
            ->when($prodiFilter, fn ($q) => $q->where('prodi_name', $prodiFilter))
            ->where('semester', '>=', $minSemester)
            ->with(['user.activities' => fn ($q) => $q->where('status', 'approved')->where('is_public', true)->with('category')])
            ->get();

        $trimmed = trim($query);
        if ($trimmed === '') {
            return $profiles->map(fn (StudentProfile $p) => [
                'profile' => $p,
                'similarity' => 100,
                'matched_concepts' => array_slice(array_keys(array_filter($p->embedding ?? [], fn ($v) => $v > 0.15)), 0, 4),
                'approved_points' => $p->user->activities->sum('awarded_points'),
            ])->sortByDesc('approved_points')->values();
        }

        $queryVec = $this->embedText($trimmed);
        $queryTokens = preg_split('/\s+/', mb_strtolower($trimmed)) ?: [];

        return $profiles->map(function (StudentProfile $profile) use ($queryVec, $queryTokens) {
            $studentVec = $profile->embedding ?: $this->refreshStudentEmbedding($profile);
            $dot = 0.0;
            $matchedConcepts = [];

            foreach ($queryVec as $concept => $qVal) {
                $sVal = (float) ($studentVec[$concept] ?? 0.0);
                if ($qVal > 0 && $sVal > 0) {
                    $dot += $qVal * $sVal;
                    $matchedConcepts[] = $concept;
                }
            }

            // Hybrid boost for exact keyword hits in skills/titles
            $haystack = mb_strtolower(
                ($profile->user->name ?? '').' '.
                ($profile->headline ?? '').' '.
                implode(' ', $profile->skills_json ?? []).' '.
                $profile->user->activities->pluck('title_id')->implode(' ').' '.
                $profile->user->activities->pluck('title_en')->implode(' ')
            );

            $lexicalHits = 0;
            foreach ($queryTokens as $tok) {
                if (mb_strlen($tok) >= 3 && str_contains($haystack, $tok)) {
                    $lexicalHits++;
                }
            }
            $lexicalBonus = min(0.25, $lexicalHits * 0.06);

            $score = (int) min(99, round(($dot + $lexicalBonus) * 100));

            return [
                'profile' => $profile,
                'similarity' => $score,
                'matched_concepts' => $matchedConcepts,
                'approved_points' => $profile->user->activities->sum('awarded_points'),
            ];
        })
            ->filter(fn (array $row) => $row['similarity'] >= 15)
            ->sortByDesc('similarity')
            ->values();
    }
}
