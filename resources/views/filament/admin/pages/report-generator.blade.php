<x-filament::page>
    <div style="padding: 24px; background-color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <form method="POST" action="{{ route('filament.admin.report-generator.generate-pdf') }}" target="_blank" style="display: flex; flex-direction: column; gap: 24px;">
            @csrf
            <!-- 3 columns for User, Month, and Year -->
            <div style="display: flex; gap: 16px;">
                <!-- Select User -->
                <div style="flex: 1;">
                    <label for="user_id" style="display: block; font-size: 14px; font-weight: medium; color: #4a4a4a; margin-bottom: 8px;">Disusun Oleh:</label>
                    <select name="user_id" id="user_id" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;" required>
                        <option value="" disabled selected>Pilih User</option>
                        @foreach (\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Year -->
                <div style="flex: 1;">
                    <label for="tahun" style="display: block; font-size: 14px; font-weight: medium; color: #4a4a4a; margin-bottom: 8px;">Tahun:</label>
                    <select name="tahun" id="tahun" onchange="updateBulanOptions()" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;" required>
                        <option value="" disabled selected>Pilih Tahun</option>
                        @for ($i = now()->year; $i >= now()->year - 4; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Select Month -->
                <div style="flex: 1;">
                    <label for="bulan" style="display: block; font-size: 14px; font-weight: medium; color: #4a4a4a; margin-bottom: 8px;">Bulan:</label>
                    <select name="bulan" id="bulan" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;" required>
                        <option value="" disabled selected>Pilih Bulan</option>
                        @php
                            $currentMonth = now()->month;
                            $currentYear = now()->year;
                        @endphp
                        @for ($month = 1; $month <= 12; $month++)
                            <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}" 
                                @if ($month > $currentMonth && now()->year == old('tahun', $currentYear)) 
                                    style="display: none;" 
                                @endif>
                                {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 16px;">
                <!-- Select User -->
                <div style="flex: 1;">
                    <label for="verifikator_id" style="display: block; font-size: 14px; font-weight: medium; color: #4a4a4a; margin-bottom: 8px;">Diverifikasi Oleh:</label>
                    <select name="verifikator_id" id="verifikator_id" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;" required>
                        <option value="" disabled selected>Pilih Verifikator</option>
                        @foreach (\App\Models\TandaTangan::where('type', 'verifikator')->get() as $verifikator)
                            <option value="{{ $verifikator->id }}">{{ $verifikator->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Select User -->
                <div style="flex: 1;">
                    <label for="persetujuan_id" style="display: block; font-size: 14px; font-weight: medium; color: #4a4a4a; margin-bottom: 8px;">Disetujui Oleh:</label>
                    <select name="persetujuan_id" id="persetujuan_id" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;" required>
                        <option value="" disabled selected>Pilih Persetujuan</option>
                        @foreach (\App\Models\TandaTangan::where('type', 'persetujuan')->get() as $persetujuan)
                            <option value="{{ $persetujuan->id }}">{{ $persetujuan->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Template -->
                <!-- <div style="flex: 1;">
                    <label for="tahun" style="display: block; font-size: 14px; font-weight: medium; color: #4a4a4a; margin-bottom: 8px;">Template:</label>
                    <select name="tahun" id="tahun" onchange="updateBulanOptions()" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;" required>
                        <option value="" disabled selected>Pilih Template</option>
                            <option value="Standar">Standar</option>
                    </select>
                </div> -->
            </div>

            <!-- Buttons for Preview and Download -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" name="preview" value="1" style="flex: 1; padding: 8px 16px; background-color: #2563eb; color: white; font-weight: bold; border-radius: 6px; border: none; cursor: pointer;" 
                        onmouseover="this.style.backgroundColor='#1d4ed8';" 
                        onmouseout="this.style.backgroundColor='#2563eb';">
                    Preview
                </button>
                <button type="submit" style="flex: 1; padding: 8px 16px; background-color: #16a34a; color: white; font-weight: bold; border-radius: 6px; border: none; cursor: pointer;" 
                        onmouseover="this.style.backgroundColor='#15803d';" 
                        onmouseout="this.style.backgroundColor='#16a34a';">
                    Download
                </button>
            </div>
        </form>
    </div>

    <script>
        function updateBulanOptions() {
            const tahunSelect = document.getElementById('tahun');
            const bulanSelect = document.getElementById('bulan');
            const currentYear = {{ now()->year }};
            const currentMonth = {{ now()->month }};

            const selectedYear = parseInt(tahunSelect.value);

            Array.from(bulanSelect.options).forEach((option) => {
                const bulanValue = parseInt(option.value);
                if (selectedYear === currentYear && bulanValue > currentMonth) {
                    option.style.display = 'none';
                } else {
                    option.style.display = '';
                }
            });
        }
    </script>
</x-filament::page>
