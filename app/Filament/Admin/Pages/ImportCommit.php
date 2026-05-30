<?php

namespace App\Filament\Admin\Pages;

use App\Models\Laporan;
use App\Services\GitCommitService;
use App\Jobs\GenerateCommitLampiranJob;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ImportCommit extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationLabel = 'Import dari Git';

    protected static ?string $title = 'Import Laporan dari Commit';

    protected static string $view = 'filament.admin.pages.import-commit';

    protected static ?string $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 10;

    // Form state
    public ?string $repo_url = '';
    public ?string $token = '';
    public ?string $branch = 'main';
    public ?string $platform = 'auto';
    public ?string $author_email = '';
    public ?string $start_date = '';
    public ?string $end_date = '';
    public ?string $user_id = '';
    public bool $skip_merge = true;
    public bool $import_with_lampiran = false;
    public ?string $project_name = '';

    // Results state
    public array $fetchedCommits = [];
    public array $previewData = [];
    public bool $showPreview = false;
    public int $importedCount = 0;
    public bool $showResult = false;
    public array $selectedCommits = [];
    public bool $selectAll = true;

    public function mount(): void
    {
        $this->user_id = (string) auth()->id();

        // Default date range: current month
        $this->start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Konfigurasi Repository')
                    ->description('Masukkan informasi repository Git untuk mengambil commit')
                    ->icon('heroicon-o-code-bracket')
                    ->schema([
                        TextInput::make('repo_url')
                            ->label('URL Repository')
                            ->placeholder('https://github.com/username/repo')
                            ->required()
                            ->url()
                            ->helperText('URL lengkap repository (GitHub, GitLab, Bitbucket)')
                            ->prefixIcon('heroicon-o-link')
                            ->columnSpanFull(),

                        Select::make('platform')
                            ->label('Platform')
                            ->options([
                                'auto' => 'Auto Detect',
                                'github' => 'GitHub',
                                'gitlab' => 'GitLab',
                                'bitbucket' => 'Bitbucket',
                            ])
                            ->default('auto')
                            ->helperText('Pilih platform atau biarkan auto detect'),

                        TextInput::make('token')
                            ->label('Access Token')
                            ->password()
                            ->revealable()
                            ->required()
                            ->helperText('Personal Access Token / Private Token')
                            ->prefixIcon('heroicon-o-key'),

                        TextInput::make('branch')
                            ->label('Branch')
                            ->default('main')
                            ->required()
                            ->prefixIcon('heroicon-o-arrow-path-rounded-square'),

                        TextInput::make('author_email')
                            ->label('Filter Author Email')
                            ->email()
                            ->placeholder('user@example.com')
                            ->helperText('Opsional: filter commit berdasarkan email author')
                            ->prefixIcon('heroicon-o-envelope'),
                    ])
                    ->columns(2),

                Section::make('Rentang Tanggal')
                    ->description('Pilih periode waktu commit yang ingin diimpor')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(Carbon::now()->startOfMonth())
                            ->prefixIcon('heroicon-o-calendar'),

                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->default(Carbon::now())
                            ->prefixIcon('heroicon-o-calendar'),
                    ])
                    ->columns(2),

                Section::make('Opsi Import')
                    ->description('Konfigurasi tambahan untuk import')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Select::make('user_id')
                            ->label('Import sebagai User')
                            ->options(
                                \App\Models\User::pluck('name', 'id')->toArray()
                            )
                            ->default(auth()->id())
                            ->required()
                            ->searchable()
                            ->prefixIcon('heroicon-o-user'),

                        TextInput::make('project_name')
                            ->label('Nama Project (Opsional)')
                            ->placeholder('contoh: ppdb admin')
                            ->helperText('Akan ditambahkan di akhir pesan commit. Contoh: "update fitur - ppdb admin"')
                            ->prefixIcon('heroicon-o-folder')
                            ->live(onBlur: true),

                        Toggle::make('import_with_lampiran')
                            ->label('Generate Gambar Lampiran')
                            ->default(false)
                            ->live()
                            ->helperText('Otomatis membuat gambar / screenshot detail commit di latar belakang untuk diisi ke kolom gambar (Lampiran).'),

                        Toggle::make('skip_merge')
                            ->label('Lewati Merge Commit')
                            ->default(true)
                            ->helperText('Jangan import commit yang dimulai dengan "Merge"'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Fetch commits and show preview.
     */
    public function fetchAndPreview(): void
    {
        $this->validate([
            'repo_url' => 'required|url',
            'token' => 'required|string',
            'branch' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'required|exists:users,id',
        ]);

        $this->showResult = false;

        try {
            $service = new GitCommitService();

            $this->fetchedCommits = $service->fetchCommits(
                repoUrl: $this->repo_url,
                token: $this->token,
                branch: $this->branch,
                startDate: $this->start_date,
                endDate: $this->end_date,
                platform: $this->platform,
                authorEmail: $this->author_email ?: null,
            );

            if (empty($this->fetchedCommits)) {
                Notification::make()
                    ->title('Tidak ada commit ditemukan')
                    ->body('Tidak ada commit pada rentang tanggal yang dipilih.')
                    ->warning()
                    ->send();
                return;
            }

            $this->previewData = $service->commitsToLaporanData(
                $this->fetchedCommits,
                (int) $this->user_id,
                $this->project_name ?: null
            );

            // If skip merge enabled, the service already handles it
            // but let's also filter here for double safety
            if ($this->skip_merge) {
                $this->previewData = array_values(
                    array_filter($this->previewData, function ($item) {
                        return !str_starts_with(strtolower($item['aktifitas']), 'merge');
                    })
                );
            }

            $this->showPreview = true;
            $this->selectAll = true;
            $this->selectedCommits = array_keys($this->previewData);

            Notification::make()
                ->title('Commit berhasil diambil!')
                ->body(count($this->previewData) . ' commit ditemukan dari ' . count($this->fetchedCommits) . ' total commit.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal mengambil commit')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Handle select all toggle
     */
    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedCommits = array_keys($this->previewData);
        } else {
            $this->selectedCommits = [];
        }
    }

    /**
     * Import the previewed commits as Laporan records.
     */
    public function importCommits(): void
    {
        if (empty($this->previewData)) {
            Notification::make()
                ->title('Tidak ada data untuk diimpor')
                ->warning()
                ->send();
            return;
        }

        $selectedData = array_intersect_key($this->previewData, array_flip($this->selectedCommits));

        if (empty($selectedData)) {
            Notification::make()
                ->title('Tidak ada data yang dipilih')
                ->warning()
                ->send();
            return;
        }

        try {
            $count = 0;
            $updatedCount = 0;

            foreach ($selectedData as $data) {
                // Update final data based on current form state before importing
                $aktifitas = $data['aktifitas'];
                if (!empty($this->project_name)) {
                    // Check if not already appended in preview
                    if (!str_ends_with($aktifitas, ' - ' . $this->project_name)) {
                        $aktifitas .= ' - ' . $this->project_name;
                    }
                }
                
                $data['aktifitas'] = $aktifitas;
                $data['user_id'] = $this->user_id;

                // Check for duplicate based on same activity, date, and user
                $existingLaporan = Laporan::where('aktifitas', $data['aktifitas'])
                    ->where('tanggal', $data['tanggal'])
                    ->where('bulan', $data['bulan'])
                    ->where('tahun', $data['tahun'])
                    ->where('user_id', $data['user_id'])
                    ->first();

                $commitDetails = [
                    'sha' => $data['sha'] ?? 'Unknown',
                    'repo' => parse_url($this->repo_url, PHP_URL_PATH) ?? 'Repo',
                ];

                if (!$existingLaporan) {
                    $laporan = Laporan::create($data);
                    
                    if ($this->import_with_lampiran) {
                        // Dispatch Job untuk assign gambar via background processing
                        GenerateCommitLampiranJob::dispatch($laporan, $commitDetails);
                    }
                    
                    $count++;
                } else {
                    // Jika data sudah ada, tapi belum punya gambar lampiran, buatkan gambar baru
                    if ($this->import_with_lampiran && empty($existingLaporan->gambar)) {
                        GenerateCommitLampiranJob::dispatch($existingLaporan, $commitDetails);
                        $updatedCount++;
                    }
                }
            }

            $skipped = count($selectedData) - $count - $updatedCount;

            $this->importedCount = $count;
            $this->showResult = true;
            $this->showPreview = false;

            $notificationBody = "{$count} laporan baru ditambahkan.";
            if ($updatedCount > 0) $notificationBody .= " {$updatedCount} laporan lama diupdate (gambar lampiran).";
            if ($skipped > 0) $notificationBody .= " {$skipped} data dilewati (sudah ada).";

            Notification::make()
                ->title('Import & Update Selesai! ✅')
                ->body($notificationBody)
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal mengimpor data')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Reset the form and results.
     */
    public function resetForm(): void
    {
        $this->fetchedCommits = [];
        $this->previewData = [];
        $this->showPreview = false;
        $this->showResult = false;
        $this->importedCount = 0;
        $this->selectedCommits = [];
        $this->selectAll = true;
    }
}
