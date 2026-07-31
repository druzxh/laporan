<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class GitCommitService
{
    /**
     * Detect the platform from the repository URL.
     */
    public function detectPlatform(string $repoUrl): string
    {
        $host = parse_url($repoUrl, PHP_URL_HOST);

        if (str_contains($host, 'github')) {
            return 'github';
        }

        if (str_contains($host, 'gitlab')) {
            return 'gitlab';
        }

        if (str_contains($host, 'bitbucket')) {
            return 'bitbucket';
        }

        // Default: try to detect from self-hosted GitLab or others
        return 'gitlab'; // fallback for self-hosted
    }

    /**
     * Extract owner and repo from the URL.
     */
    public function parseRepoUrl(string $repoUrl): array
    {
        $path = trim(parse_url($repoUrl, PHP_URL_PATH), '/');
        $path = preg_replace('/\.git$/', '', $path);
        $parts = explode('/', $path);

        $host = parse_url($repoUrl, PHP_URL_HOST);
        $scheme = parse_url($repoUrl, PHP_URL_SCHEME) ?? 'https';

        return [
            'owner' => $parts[0] ?? '',
            'repo' => $parts[1] ?? '',
            'full_path' => $path,
            'base_url' => "{$scheme}://{$host}",
        ];
    }

    /**
     * Fetch commits from the repository.
     */
    public function fetchCommits(
        string $repoUrl,
        string $token,
        string $branch,
        string $startDate,
        string $endDate,
        string $platform = 'auto',
        ?string $authorEmail = null
    ): array {
        if ($platform === 'auto') {
            $platform = $this->detectPlatform($repoUrl);
        }

        return match ($platform) {
            'github' => $this->fetchGitHubCommits($repoUrl, $token, $branch, $startDate, $endDate, $authorEmail),
            'gitlab' => $this->fetchGitLabCommits($repoUrl, $token, $branch, $startDate, $endDate, $authorEmail),
            'bitbucket' => $this->fetchBitbucketCommits($repoUrl, $token, $branch, $startDate, $endDate, $authorEmail),
            default => throw new \InvalidArgumentException("Platform tidak dikenal: {$platform}"),
        };
    }

    /**
     * Fetch a single commit's diff/patch.
     */
    public function fetchCommitDiff(string $repoUrl, string $token, string $sha, string $platform = 'auto'): ?string
    {
        if ($platform === 'auto') {
            $platform = $this->detectPlatform($repoUrl);
        }

        try {
            return match ($platform) {
                'github' => $this->fetchGitHubCommitDiff($repoUrl, $token, $sha),
                'gitlab' => $this->fetchGitLabCommitDiff($repoUrl, $token, $sha),
                'bitbucket' => null, // Simplified for now, or implement if needed
                default => null,
            };
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error fetching diff for $sha: " . $e->getMessage());
            return null;
        }
    }

    protected function fetchGitHubCommitDiff(string $repoUrl, string $token, string $sha): ?string
    {
        $parsed = $this->parseRepoUrl($repoUrl);
        $owner = $parsed['owner'];
        $repo = $parsed['repo'];

        $url = "https://api.github.com/repos/{$owner}/{$repo}/commits/{$sha}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/vnd.github.v3+json',
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $diffText = '';
            
            if (isset($data['files']) && is_array($data['files'])) {
                foreach ($data['files'] as $file) {
                    if (isset($file['patch'])) {
                        $diffText .= "File: " . $file['filename'] . "\n";
                        $diffText .= $file['patch'] . "\n\n";
                    }
                }
            }
            
            // Limit to max 15 lines total to avoid massive diffs
            $lines = explode("\n", trim($diffText));
            if (count($lines) > 15) {
                $lines = array_slice($lines, 0, 15);
                $lines[] = "...(diff truncated)...";
            }
            return implode("\n", $lines);
        }

        return null;
    }

    protected function fetchGitLabCommitDiff(string $repoUrl, string $token, string $sha): ?string
    {
        $parsed = $this->parseRepoUrl($repoUrl);
        $projectPath = urlencode($parsed['full_path']);
        $baseUrl = $parsed['base_url'];

        $url = "{$baseUrl}/api/v4/projects/{$projectPath}/repository/commits/{$sha}/diff";

        $response = Http::withHeaders([
            'PRIVATE-TOKEN' => $token,
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $diffText = '';
            
            if (is_array($data)) {
                foreach ($data as $file) {
                    if (isset($file['diff'])) {
                        $diffText .= "File: " . ($file['new_path'] ?? $file['old_path']) . "\n";
                        $diffText .= $file['diff'] . "\n\n";
                    }
                }
            }
            
            // Limit to max 15 lines total to avoid massive diffs
            $lines = explode("\n", trim($diffText));
            if (count($lines) > 15) {
                $lines = array_slice($lines, 0, 15);
                $lines[] = "...(diff truncated)...";
            }
            return implode("\n", $lines);
        }

        return null;
    }

    /**
     * Fetch commits from GitHub API.
     */
    protected function fetchGitHubCommits(
        string $repoUrl,
        string $token,
        string $branch,
        string $startDate,
        string $endDate,
        ?string $authorEmail
    ): array {
        $parsed = $this->parseRepoUrl($repoUrl);
        $owner = $parsed['owner'];
        $repo = $parsed['repo'];

        $url = "https://api.github.com/repos/{$owner}/{$repo}/commits";

        $params = [
            'sha' => $branch,
            'since' => Carbon::parse($startDate)->startOfDay()->toIso8601String(),
            'until' => Carbon::parse($endDate)->endOfDay()->toIso8601String(),
            'per_page' => 100,
        ];

        if ($authorEmail) {
            $params['author'] = $authorEmail;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/vnd.github.v3+json',
        ])->get($url, $params);

        if ($response->failed()) {
            throw new \RuntimeException(
                "GitHub API error: {$response->status()} - {$response->body()}"
            );
        }

        $commits = $response->json();

        return collect($commits)->map(function ($commit) {
            $date = Carbon::parse($commit['commit']['author']['date']);

            return [
                'message' => $commit['commit']['message'],
                'date' => $date,
                'author' => $commit['commit']['author']['name'],
                'email' => $commit['commit']['author']['email'],
                'sha' => substr($commit['sha'], 0, 7),
            ];
        })->toArray();
    }

    /**
     * Fetch commits from GitLab API.
     */
    protected function fetchGitLabCommits(
        string $repoUrl,
        string $token,
        string $branch,
        string $startDate,
        string $endDate,
        ?string $authorEmail
    ): array {
        $parsed = $this->parseRepoUrl($repoUrl);
        $projectPath = urlencode($parsed['full_path']);
        $baseUrl = $parsed['base_url'];

        $url = "{$baseUrl}/api/v4/projects/{$projectPath}/repository/commits";

        $params = [
            'ref_name' => $branch,
            'since' => Carbon::parse($startDate)->startOfDay()->toIso8601String(),
            'until' => Carbon::parse($endDate)->endOfDay()->toIso8601String(),
            'per_page' => 100,
        ];

        $response = Http::withHeaders([
            'PRIVATE-TOKEN' => $token,
        ])->get($url, $params);

        if ($response->failed()) {
            throw new \RuntimeException(
                "GitLab API error: {$response->status()} - {$response->body()}"
            );
        }

        $commits = $response->json();

        return collect($commits)
            ->when($authorEmail, function ($collection) use ($authorEmail) {
                return $collection->filter(function ($commit) use ($authorEmail) {
                    return $commit['author_email'] === $authorEmail;
                });
            })
            ->map(function ($commit) {
                $date = Carbon::parse($commit['committed_date']);

                return [
                    'message' => $commit['message'],
                    'date' => $date,
                    'author' => $commit['author_name'],
                    'email' => $commit['author_email'],
                    'sha' => substr($commit['id'], 0, 7),
                ];
            })->values()->toArray();
    }

    /**
     * Fetch commits from Bitbucket API.
     */
    protected function fetchBitbucketCommits(
        string $repoUrl,
        string $token,
        string $branch,
        string $startDate,
        string $endDate,
        ?string $authorEmail
    ): array {
        $parsed = $this->parseRepoUrl($repoUrl);
        $owner = $parsed['owner'];
        $repo = $parsed['repo'];

        // Bitbucket uses basic auth with app password or OAuth
        $url = "https://api.bitbucket.org/2.0/repositories/{$owner}/{$repo}/commits/{$branch}";

        $allCommits = [];
        $nextUrl = $url;

        while ($nextUrl) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->get($nextUrl);

            if ($response->failed()) {
                throw new \RuntimeException(
                    "Bitbucket API error: {$response->status()} - {$response->body()}"
                );
            }

            $data = $response->json();
            $commits = $data['values'] ?? [];

            foreach ($commits as $commit) {
                $date = Carbon::parse($commit['date']);

                // Stop if commit is before start date
                if ($date->lt(Carbon::parse($startDate)->startOfDay())) {
                    $nextUrl = null;
                    break;
                }

                // Skip if commit is after end date
                if ($date->gt(Carbon::parse($endDate)->endOfDay())) {
                    continue;
                }

                if ($authorEmail && ($commit['author']['raw'] ?? '') !== $authorEmail) {
                    if (!str_contains($commit['author']['raw'] ?? '', $authorEmail)) {
                        continue;
                    }
                }

                $allCommits[] = [
                    'message' => $commit['message'],
                    'date' => $date,
                    'author' => $commit['author']['user']['display_name'] ?? $commit['author']['raw'] ?? 'Unknown',
                    'email' => $authorEmail ?? '',
                    'sha' => substr($commit['hash'], 0, 7),
                ];
            }

            $nextUrl = $data['next'] ?? null;

            if (count($allCommits) >= 100) {
                break;
            }
        }

        return $allCommits;
    }

    /**
     * Convert commits to Laporan records.
     */
    public function commitsToLaporanData(array $commits, int $userId, ?string $projectName = null): array
    {
        $namaHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $laporanData = [];

        foreach ($commits as $commit) {
            $date = $commit['date'] instanceof Carbon
                ? $commit['date']
                : Carbon::parse($commit['date']);

            // Take the full commit message to ensure activity description is complete
            $message = trim($commit['message']);

            // Skip merge commits
            if (str_starts_with(strtolower($message), 'merge')) {
                continue;
            }

            $originalContext = $message . ($projectName ? " - " . $projectName : "");
            $contextForData = $originalContext;

            // Cek apakah sudah ada laporan dengan aktifitas yang sama
            $existingLaporan = \App\Models\Laporan::where('user_id', $userId)
                ->where('tanggal', $date->format('d'))
                ->where('bulan', $date->format('m'))
                ->where('tahun', $date->format('Y'))
                ->where('aktifitas', 'LIKE', $originalContext . '%')
                ->first();

            if (!$existingLaporan) {
                $contextForData = $message . ($projectName ? " - " . $projectName : "");
                if ($projectName) {
                    if (str_contains($message, "\n")) {
                        $message .= "\n\nProject: " . $projectName;
                    } else {
                        $message .= ' - ' . $projectName;
                    }
                }
            } else {
                $message = $existingLaporan->aktifitas;
            }

            $dayEnglish = $date->format('l');

            $laporanData[] = [
                'aktifitas' => $message,
                'original_context' => $contextForData ?? $message,
                'hari' => $namaHari[$dayEnglish] ?? $dayEnglish,
                'tanggal' => $date->format('d'),
                'bulan' => $date->format('m'),
                'tahun' => $date->format('Y'),
                'user_id' => $userId,
                'gambar' => null,
                'sha' => $commit['sha'] ?? 'unknown',
            ];
        }

        return $laporanData;
    }
}
