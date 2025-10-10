<x-filament::page>
    {{-- Page-scoped CSS: no Tailwind utilities, so we define our own --}}
    <style>
        .pf-wrap { min-height: 80vh; background:#fff; display:flex; flex-direction:column; }
        .pf-card { max-width: 640px; width:100%; margin: 2rem auto 1rem; padding: 0 1rem; }

        .pf-heading { text-align:center; }
        .pf-icon { width:56px; height:56px; display:grid; place-items:center; margin:0 auto; border-radius:999px; background:#FEF3C7; } /* amber-100 */
        .pf-title { margin-top:.75rem; font-weight:600; font-size:1.125rem; }

        /* Stars */
        .pf-stars { display:flex; gap:16px; justify-content:center; margin-top:.75rem; user-select:none; }
        .pf-star { font-size:56px; line-height:1; color:#D1D5DB; transition: transform .15s ease, text-shadow .15s ease, color .15s ease; } /* gray-300 */
        .pf-star--active { color:#F59E0B; transform:scale(1.1); text-shadow: 0 2px 8px rgba(245,158,11,.45); }   /* amber-500 */
        .pf-star:focus { outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,.35); } /* blue focus */

        /* Textarea + button */
        .pf-textarea { width:100%; min-height:120px; margin-top:.75rem; padding:1rem;
            border:1px solid #D1D5DB; border-radius: 16px; font-size:1rem; }
        .pf-textarea:focus { outline:none; border-color:#2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,.35); }
        .pf-counter { font-size:.75rem; color:#6B7280; text-align:right; margin-top:.25rem; }
        .pf-btn { width:100%; padding:1rem; color:#fff; background:#2563EB; border:none; border-radius:999px;
            font-weight:600; box-shadow:0 6px 14px rgba(37,99,235,.2); cursor:pointer; }
        .pf-btn:hover { background:#1D4ED8; }
        .pf-btn:disabled { opacity:.6; cursor:not-allowed; }
        .pf-success { background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:1rem; border-radius:12px; text-align:center; margin-top:2rem; }
        .pf-label { font-weight:600; color:#1D4ED8; text-align:center; } /* blue-700-ish */
    </style>

    @php
        $serviceName = optional($this->feedback?->transaction?->service)->name;
        $serviceDate = optional($this->feedback?->transaction)->date
            ? \Carbon\Carbon::parse($this->feedback->transaction->date)->format('d M Y')
            : null;
        $employee = optional($this->feedback?->transaction?->employee);
        $empName  = $employee?->name;
        $empR5ole  = $employee?->position ?? $employee?->job_title;
    @endphp

    <div class="pf-wrap">
        <div class="pf-card">
            <div class="pf-heading">
                <div class="pf-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" width="28" height="28" style="color:#F59E0B">
                        <path d="M2 5.75A2.75 2.75 0 0 1 4.75 3h10.5A2.75 2.75 0 0 1 18 5.75v6.5A2.75 2.75 0 0 1 15.25 15H9l-3.5 3.5A1 1 0 0 1 4 17.5V15A2.75 2.75 0 0 1 2 12.25v-6.5Z"/>
                        <path d="M20 8.25v7.5A2.25 2.25 0 0 1 17.75 18H14l-3 3v-3H9.75A2.25 2.25 0 0 1 7.5 15.75v-.7h6.75A4.25 4.25 0 0 0 18.5 10.8V8.25A2.25 2.25 0 0 1 20 8.25Z"/>
                    </svg>
                </div>
                <div class="pf-title">
                    Terima kasih Anda telah menggunakan layanan
                    <span style="white-space:nowrap">
                        {{ $serviceName ?? 'BPS Kota Malang' }}
                        @if($serviceDate)
                            &nbsp;pada tanggal {{ $serviceDate }}
                        @endif
                    </span>

                    @if ($empName)
                        <div style="display:flex;justify-content:center;gap:.5rem;margin-top:.75rem;font-size:.9rem;color:#374151">
                            Dilayani oleh <strong style="color:#111827">&nbsp;{{ $empName }}</strong>
                            {{-- @if ($empRole) · <span>{{ $empRole }}</span>@endif --}}
                        </div>
                    @else
                        <div style="text-align:center;margin-top:.75rem;color:#6B7280">Dilayani oleh: —</div>
                    @endif
                </div>
            </div>

            @if ($this->submitted)
                <div class="pf-success">Feedback sudah diterima. Terima kasih! 🙏</div>
            @else
                <form wire:submit.prevent="save" class="space-y-6" style="margin-top:2rem">
                    <p class="pf-label">Berikan Penilaian</p>

                    {{-- Stars --}}
                    <div class="pf-stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button"
                                wire:click="$set('formState.rate', {{ $i }})"
                                class="pf-star {{ ($formState['rate'] ?? 0) >= $i ? 'pf-star--active' : '' }}">
                                ★
                            </button>
                        @endfor
                    </div>
                    @error('formState.rate') <p style="margin-top:.5rem;color:#DC2626;font-size:.875rem">{{ $message }}</p> @enderror

                    {{-- Comment --}}
                    <p class="pf-label" style="margin-top:1rem">Punya Masukan atau Saran?</p>
                    <textarea wire:model.defer="formState.comment" maxlength="255" class="pf-textarea" placeholder="Ketik di sini..."></textarea>
                    <div class="pf-counter">{{ strlen($formState['comment'] ?? '') }}/255</div>
                    @error('formState.comment') <p style="margin-top:.5rem;color:#DC2626;font-size:.875rem">{{ $message }}</p> @enderror

                    {{-- Submit --}}
                    <button type="submit" class="pf-btn" wire:loading.attr="disabled">
                        <span wire:loading.remove>Kirim</span>
                        <span wire:loading>Memproses…</span>
                    </button>
                </form>
            @endif
        </div>
        <div style="flex:1"></div>
    </div>
</x-filament::page>
