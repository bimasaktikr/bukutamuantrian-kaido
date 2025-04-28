<?php

namespace App\Filament\Guest\Pages;

use App\Models\Customer;
use App\Models\Education;
use App\Models\Institution;
use App\Models\Purpose;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Submethod;
use App\Models\Transaction;
use App\Models\University;
use App\Models\Work;
use App\Services\QueueService;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;


/**
 * Service that we use
 */

use App\Services\CustomerService;
use App\Services\TransactionService;
use Livewire\Attributes\On;

class PublicTransaction extends Page implements HasForms
{
    // use CreateRecord\Concerns\HasWizard;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.guest.pages.public-transaction';

    protected static ?string $navigationLabel = 'Pendaftaran Layanan';

    protected static ?string $slug = 'public';

    public function getTitle(): string
    {
        return ' ';
    }
    // protected static string $heading = 'filament.guest.pages.public-transaction';

    /**
     * implement service
     *
     * @var [type]
     */
    protected $customerService;
    protected $queueService;

    public $name;
    public $phone;
    public $email;
    public $age;
    public $gender;
    public $work_id;
    public $service_id;
    public $education_id;
    public $university_id;
    public $institution_id;
    public $submethod_id;
    public $purpose_id;

    public $customer;
    public $transaction;
    public $queue;

    public $services;
    public $selectedService;

    public $showQueueModal  = false;

    // protected bool $showTransactionModal = false;

    // public function __construct(QueueService $queueService)
    // {
    //     $this->queueService = $queueService; // Inject the service in the constructor
    // }


    public function mount()
    {
        // Ensure the form fields are initialized
        $this->form->fill([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'age' => $this->age,
            'gender' => $this->gender,
            'work_id' => $this->work_id,
            'service_id' => $this->service_id,
            'education_id' => $this->education_id,
            'university_id' => $this->university_id ?? null,
            'institution_id' => $this->institution_id ?? null,
            'submethod_id' => $this->submethod_id,
            'service_id' => $this->service_id,
            'purpose_id' => $this->purpose_id,
        ]);
        $this->selectedService = null;

        $this->services = Service::all()->toArray();
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Data Pribadi')
                        ->schema([
                            TextInput::make('email')
                                ->required()
                                ->email()
                                ->afterStateUpdated(function (callable $set, $state) {
                                    $this->autofillCustomerData($state);
                                })
                                ->reactive() // Ensure the field is reactive
                                ->extraAttributes([
                                    'onkeypress' => 'if(event.key === "Tab") { this.dispatchEvent(new Event("change")); }',
                                ]),
                            TextInput::make('name')
                                ->label('Masukkan Nama Anda')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->required()
                                ->label('Masukkan Nomor Handphone')
                                ->tel()
                                ->telRegex('/^(?:\+62|62|0)8[1-9][0-9]{6,10}$/'),
                            TextInput::make('age')
                                ->label('Masukkan Usia Anda')
                                ->numeric()
                                ->maxLength(2)              // Maximum length of 2 characters
                                ->required(),
                            Select::make('gender')
                                ->label('Pilih Jenis Kelamin')
                                ->options([
                                    'male' => 'Laki-Laki',
                                    'female' => 'Perempuan',
                                ])
                                ->required(),
                        ]),
                    Wizard\Step::make('Pendidikan & Pekerjaan')
                        ->schema([
                            Select::make('education_id')
                                ->label('Pendidikan terakhir')
                                ->options(Education::all()->pluck('name', 'id'))
                                ->required(),
                            Select::make('work_id')
                                ->label('Pilih Pekerjaan Anda')
                                ->options(Work::all()->pluck('name', 'id'))
                                ->required()
                                ->reactive() // Make this field reactive to allow dynamic form updates
                                ->afterStateUpdated(function (callable $set, $state) {
                                    $set('university_id', null); // Reset 'university_id' when 'work_id' is updated
                                    $set('institution_id', null); // Reset 'institution_id' when 'work_id' is updated
                                }),
                            Select::make('university_id')
                                ->label('Pilih Universitas')
                                ->options(University::all()->pluck('name', 'id')) // Make sure to populate this with your actual university data
                                ->required(fn(Get $get) => $get('work_id') === '1')
                                ->searchable()
                                ->hidden(fn(Get $get) => $get('work_id') !== '1') // Show only when work_id is 1
                                ->reactive()
                                ->live()
                                ->createOptionForm([ // Allow adding a new institution if not found
                                    TextInput::make('name')
                                        ->label('Masukkan Nama Universitas')
                                        ->required(),
                                ])
                                ->createOptionUsing(function ($data) {
                                    return University::create([
                                        'name' => $data['name'],
                                    ])->id;
                                }),
                            Select::make('institution_id')
                                ->label('Pilih Institusi')
                                ->options(Institution::all()->pluck('name', 'id')) // Populated with current institutions
                                // ->relationship('institution', 'name')
                                ->required(fn(Get $get) => $get('work_id') && $get('work_id') !== '1') // Required only if work_id is NOT '1'
                                ->hidden(fn(Get $get) => !$get('work_id') || $get('work_id') == '1') // Show only when work_id is not '1'
                                ->reactive()
                                ->createOptionForm([ // Allow adding a new institution if not found
                                    TextInput::make('name')
                                        ->label('Masukkan Nama Institusi')
                                        ->required(),
                                ])
                                ->createOptionUsing(function ($data) {
                                    return Institution::create([
                                        'name' => $data['name'],
                                    ])->id;
                                })
                                ->searchable(), // Allows searching through the institution list
                        ]),
                    Wizard\Step::make('Layanan')
                        ->schema([
                            Select::make('submethod_id')
                                ->label('Pilih Media Layanan')
                                ->options(Submethod::all()->pluck('name', 'id'))
                                ->required(),
                            Select::make('purpose_id')
                                ->label('Tujuan Penggunaan Layanan')
                                ->options(Purpose::all()->pluck('name', 'id'))
                                ->reactive()
                                ->required(),
                            Select::make('service_id')
                                ->label('Pilih Layananan yang dibutuhkan')
                                ->options(Service::all()->pluck('name', 'id'))

                                ->required(),
                        ]),
                ])->submitAction(new HtmlString('<button class="bg-yellow-200" type="submit">Submit</button>')),
            ]);
    }

    protected function autofillCustomerData(string $email): void
    {
        // Check if a customer exists with the provided email
        $customer = Customer::where('email', $email)->first();

        if ($customer) {
            // Autofill the form with the customer's data
            $this->form->fill([
                'name' => $customer->name,
                'phone' => $customer->phone,
                'age' => $customer->age,
                'gender' => $customer->gender,
                'work_id' => $customer->work_id,
                'education_id' => $customer->education_id,
                'university_id' => $customer->university_id,
                'institution_id' => $customer->institution_id,
            ]);
        }
    }

    public function submit()
    {
        // Start a database transaction to ensure all operations succeed or none
        DB::beginTransaction();

        try {
            // Save customer data inside a try-catch block
            try {
                // Get the data from the form
                // $email = $this->form->getState()['email'];
                // $phone =$this->form->getState()['phone'];

                $data = [
                    'email' => $this->form->getState()['email'],
                    'phone' => $this->form->getState()['phone'],
                    'name' => $this->form->getState()['name'],
                    'age' => $this->form->getState()['age'],
                    'gender' => $this->form->getState()['gender'],
                    'work_id' => $this->form->getState()['work_id'],
                    'education_id' => $this->form->getState()['education_id'],
                    'university_id' => $this->form->getState()['university_id'] ?? null,
                    'institution_id' => $this->form->getState()['institution_id'] ?? null,
                ];

                Log::info('Customer debug info', [
                    'data' => $data,
                ]);


                // Using the CustomerService to create or update the customer
                $customerService = app(CustomerService::class);

                // Call the service method to handle customer creation
                $this->customer = $customerService->createOrGet($data);
            } catch (\Exception $e) {
                // Rollback the transaction
                DB::rollBack();
                // Log error and return user-friendly message
                Log::error('Error saving customer: ' . $e->getMessage());
                return $this->notifyError('An error occurred while saving customer data.');
            }

            // Save transaction data
            try {
                $transactionService = app(TransactionService::class);
                // Collect form data
                $data = [
                    'date' => Carbon::today(),
                    'customer_id' => $this->customer->id,
                    'service_id' => $this->form->getState()['service_id'],
                    'purpose_id' => $this->form->getState()['purpose_id'],
                    'submethod_id' => $this->form->getState()['submethod_id'],
                ];

                Log::info('Transaction debug info', [
                    'data' => $data,
                ]);

                $this->transaction = $transactionService->createTransaction($data);
                // Handle queue creation for specific services

            } catch (\Exception $e) {
                // Rollback the transaction
                DB::rollBack();

                // Log error and return user-friendly message
                Log::error('Error saving transaction: ' . $e->getMessage());

                // Notify the user with an error message
                Notification::make()
                    ->danger()
                    ->title('Error')
                    ->body('An error occurred while saving transaction data :' . $e->getMessage())
                    ->send();

                return;
            }

            // Commit the transaction since everything is successful
            DB::commit();

            Notification::make()
                ->success()
                ->title('Success')
                ->body('Data saved successfully')
                ->send();

            // $this->showQueueModal = true;
            if ($this->transaction->submethod->method_id == 2) {
                // Show the queue modal
                $this->showQueueModal = true;
                $this->dispatch('showQueueModal', $this->transaction->id);
            } else {
                // Redirect to the public page
                return redirect()->route('filament.guest.pages.public');
            }
            $this->dispatch('showQueueModal', $this->transaction->id);


            // return redirect()->route('filament.guest.pages.public');
        } catch (\Exception $e) {
            // Rollback in case of any unforeseen errors
            DB::rollBack();

            // Log the error
            Log::error('Unexpected error: ' . $e->getMessage());

            // Display a notification to the user
            Notification::make()
                ->danger()
                ->title('Unexpected Error')
                ->body('An unexpected error occurred. Please try again.')
                ->send();

            return;
        }
    }

}
