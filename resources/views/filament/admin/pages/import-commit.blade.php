<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form Section --}}
        <form wire:submit.prevent="fetchAndPreview">
            {{ $this->form }}

            <div class="mt-6 flex gap-3">
                <x-filament::button
                    type="submit"
                    icon="heroicon-o-magnifying-glass"
                    wire:loading.attr="disabled"
                    wire:target="fetchAndPreview"
                >
                    <span wire:loading.remove wire:target="fetchAndPreview">
                        Ambil & Preview Commit
                    </span>
                    <span wire:loading wire:target="fetchAndPreview">
                        Mengambil commit...
                    </span>
                </x-filament::button>

                @if($showPreview || $showResult)
                    <x-filament::button
                        color="gray"
                        wire:click="resetForm"
                        icon="heroicon-o-arrow-path"
                    >
                        Reset
                    </x-filament::button>
                @endif
            </div>
        </form>

        {{-- Loading Indicator --}}
        <div wire:loading wire:target="fetchAndPreview" class="mt-4">
            <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-950/30 dark:border-blue-800 p-6">
                <div class="flex items-center gap-3">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-blue-700 dark:text-blue-300 font-medium">Sedang mengambil commit dari repository...</span>
                </div>
            </div>
        </div>

        {{-- Preview Section --}}
        @if($showPreview && count($previewData) > 0)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                {{-- Preview Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">Preview Data Import</h3>
                            <p class="text-blue-100 text-sm mt-1">{{ count($previewData) }} commit siap diimpor</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-sm font-medium text-white backdrop-blur">
                                {{ count($previewData) }} items
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Preview Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                                <th class="px-4 py-3 text-left w-10">
                                    <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 w-12">#</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Aktivitas (Commit Message)</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Hari</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Bulan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Tahun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($previewData as $index => $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-3 text-left w-10">
                                        <input type="checkbox" wire:model.live="selectedCommits" value="{{ $index }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-start gap-2">
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                            </span>
                                            <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $item['aktifitas'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $item['hari'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono">{{ $item['tanggal'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono">{{ $item['bulan'] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono">{{ $item['tahun'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Import Actions --}}
                <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            ⚠️ Data duplikat (aktivitas + tanggal + user sama) akan otomatis dilewati.
                        </p>
                        <div class="flex gap-3">
                            <x-filament::button
                                color="gray"
                                wire:click="resetForm"
                                icon="heroicon-o-x-mark"
                                size="sm"
                            >
                                Batal
                            </x-filament::button>
                            <x-filament::button
                                color="success"
                                wire:click="importCommits"
                                wire:loading.attr="disabled"
                                wire:target="importCommits"
                                icon="heroicon-o-arrow-down-tray"
                                size="sm"
                                :disabled="count($selectedCommits) === 0"
                            >
                                <span wire:loading.remove wire:target="importCommits">
                                    ✅ Import {{ count($selectedCommits) }} Laporan
                                </span>
                                <span wire:loading wire:target="importCommits">
                                    Mengimpor...
                                </span>
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Import Result --}}
        @if($showResult)
            <div class="rounded-xl border border-green-200 dark:border-green-800 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-green-800 dark:text-green-200">Import Berhasil!</h3>
                        <p class="text-green-600 dark:text-green-400 mt-1">
                            <strong>{{ $importedCount }}</strong> laporan berhasil diimpor ke database.
                        </p>
                        <div class="mt-4 flex gap-3">
                            <x-filament::button
                                color="gray"
                                wire:click="resetForm"
                                icon="heroicon-o-arrow-path"
                                size="sm"
                            >
                                Import Lagi
                            </x-filament::button>
                            <a href="{{ \App\Filament\Admin\Resources\LaporanResource::getUrl('index') }}">
                                <x-filament::button
                                    color="primary"
                                    icon="heroicon-o-eye"
                                    size="sm"
                                    tag="a"
                                >
                                    Lihat Data Laporan
                                </x-filament::button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
