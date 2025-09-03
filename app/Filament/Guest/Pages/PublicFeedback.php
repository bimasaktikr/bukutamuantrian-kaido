<?php
namespace App\Filament\Guest\Pages;

use App\Models\Feedback;
use App\Services\FeedbackService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class PublicFeedback extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;


    protected static string $view = 'filament.guest.pages.public-feedback';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'f/{uuid}';          // short link

    protected static ?string $routeName = 'feedback.public'; // becomes filament.guest.feedback.public

    public ?Feedback $feedback = null;
     /** Holds rate & comment bound from the Blade via @entangle */
       // ⬇️ rename this
    public array $formState = [
        'rate' => null,
        'comment' => null,
    ];

    public bool $submitted = false;

    public function getTitle(): string
    {
        return ' ';
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public function mount(string $uuid): void
    {
        // $uuid = request()->query('uuid');

        $this->feedback = Feedback::where('uuid', $uuid)->firstOrFail();

        $this->submitted = (bool) $this->feedback->submited;

        $this->formState = [
            'rate'    => $this->submitted ? $this->feedback->rate : null,
            'comment' => $this->submitted ? $this->feedback->comment : null,
        ];

        // hydrate Filament form
        $this->form->fill($this->formState);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('rate')
                    ->label('Rating')
                    ->options([1, 2, 3, 4, 5])
                    ->required(),
                Forms\Components\Textarea::make('comment')
                    ->label('Komentar')
                    ->maxLength(255),
            ])
            ->statePath('formState'); // maps to $this->formState
    }

    public function save(): void
    {
        // Validate using Filament form schema
        $this->form->validate();
        $state = $this->form->getState(); // ['rate' => ..., 'comment' => ...]

        // dd($state);
        // dd('MASUKKK PAK EKOOO');
        // Guard double-submit
        if ($this->feedback->submited) {
            $this->submitted = true;
            Notification::make()->title('Feedback sudah dikirim.')->warning()->send();
            return;
        }

        // Use service
        $service = app(FeedbackService::class);
        $service->markSubmitted(
            $this->feedback,
            (int) $state['rate'],
            $state['comment'] ?: null,
        );

        $this->submitted = true;

        Notification::make()
            ->title('Terima kasih atas feedback Anda!')
            ->success()
            ->send();
    }
}
