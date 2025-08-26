<?php
// app/Services/FeedbackService.php
namespace App\Services;

use App\Filament\Guest\Pages\PublicFeedback;
use App\Models\Feedback;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class FeedbackService
{
    /** Create feedback skeleton for a transaction (if missing). */
    public function createForTransaction(Transaction $transaction): Feedback
    {
        return Feedback::firstOrCreate(
            ['transaction_id' => $transaction->id],
            ['rate' => null, 'comment' => null, 'submited' => false]
        );
    }

    public function publicUrl(\App\Models\Feedback $fb): string
    {
        // return PublicFeedback::getUrl(['uuid' => $fb->uuid], panel: 'guest');
        return route('filament.guest.feedback.public', ['uuid' => $fb->uuid]);

        // return PublicFeedback::getUrl(['uuid' => $fb->uuid]);
        // return route('feedback.public', ['uuid' => $fb->uuid]);

    }

    public function markSubmitted(Feedback $fb, int $rate, ?string $comment): Feedback
    {
        return DB::transaction(function () use ($fb, $rate, $comment) {
            $fb->update([
                'rate'     => $rate,
                'comment'  => $comment ?: null,
                'submited' => true,
            ]);

            return $fb->refresh();
        });
    }

    /** Load feedback by uuid or fail. */
    public function findByUuid(string $uuid): Feedback
    {
        return Feedback::where('uuid', $uuid)->firstOrFail();
    }

    /** Submit/Update feedback via public page. */
    public function submit(string $uuid, ?int $rate, ?string $comment): Feedback
    {
        $fb = $this->findByUuid($uuid);
        $fb->update([
            'rate'     => $rate,
            'comment'  => $comment,
            'submited' => true,
        ]);
        return $fb;
    }
}
