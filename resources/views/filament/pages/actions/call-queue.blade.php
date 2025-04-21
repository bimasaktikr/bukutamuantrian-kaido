<div
    x-data="{
        speak() {
            const message = `Nomor Antrian {{ $record->transaction->service->code }}-{{ str_pad($record->number, 3, '0', STR_PAD_LEFT) }} atas nama {{ $record->transaction->customer->name }} silahkan ke petugas`;
            console.log('🔊 Speaking:', message);

            if ('speechSynthesis' in window) {
                const synth = window.speechSynthesis;
                const utterance = new SpeechSynthesisUtterance(message);
                utterance.lang = 'id-ID';

                const setVoice = () => {
                    const voices = synth.getVoices();
                    const indonesianVoice = voices.find(v => v.lang === 'id-ID');

                    if (indonesianVoice) {
                        utterance.voice = indonesianVoice;
                        console.log('✅ Voice found:', indonesianVoice.name);
                    } else {
                        console.warn('⚠️ No id-ID voice found. Using default.');
                    }

                    synth.speak(utterance);
                };

                if (synth.getVoices().length === 0) {
                    synth.addEventListener('voiceschanged', setVoice);
                } else {
                    setVoice();
                }

            } else {
                alert('Speech synthesis not supported in this browser.');
            }
        }
    }"
    x-init="speak()"
>
    <p>Nomor: <strong>{{ $record->transaction->service->code }}-{{ str_pad($record->number, 3, '0', STR_PAD_LEFT) }}</strong></p>
    <p>Nama: <strong>{{ $record->transaction->customer->name }}</strong></p>
</div>
