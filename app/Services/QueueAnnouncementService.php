<?php
namespace App\Services;
use App\Models\Queue;
use App\Models\SpeechAudio;
use Illuminate\Support\Facades\Storage;
class QueueAnnouncementService
{
    public function getAnnouncementAudioFiles(Queue $queue): array
    {
        $serviceCode = $queue->transaction->service->code;
        $queueNumber = str_pad($queue->number, 3, '0', STR_PAD_LEFT);
        $serviceAudio = SpeechAudio::where('filename', $serviceCode)->value('audiopath');
        $queueAudio = SpeechAudio::where('filename', $queueNumber)->value('audiopath');
        // return [
        //     asset('storage/opening_speech.mp3'),
        //     asset('storage/' . $serviceAudio),
        //     asset('storage/' . $queueAudio),
        //     asset('storage/closing_speech.mp3'),
        // ];

         // Ensure the audio files exist and are accessible
         $files = [
            'opening' => 'opening_speech.mp3',
            'service' => $serviceAudio,
            'queue' => $queueAudio,
            'closing' => 'closing_speech.mp3'
        ];

        $audioFiles = [];
        foreach ($files as $key => $file) {
            if ($file && Storage::disk('public')->exists($file)) {
                $audioFiles[] = asset('storage/' . $file);
            }
        }

        return $audioFiles;
        // return [
        //     Storage::url('opening_speech.mp3'),
        //     Storage::url($serviceAudio),
        //     Storage::url($queueAudio),
        //     Storage::url('closing_speech.mp3'),
        // ];
    }
}
