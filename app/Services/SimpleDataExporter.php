<?php

namespace App\Services;

use App\Exports\AppointmentDataExports;
use App\Exports\CompanyDataExports;
use App\Exports\FormDataExports;
use App\Exports\UserDataExports;
use App\Jobs\DispatchExportsNotifications;
use App\Mail\ReportGenerated;
use App\Models\BizMatch\Appointment;
use App\Models\BizMatch\Company;
use App\Models\Form;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class SimpleDataExporter
{
    /**
     * Default collection of email addresses to share exported items with
     *
     * @var \Illuminate\Support\Collection<int, \Illuminate\Support\Stringable>
     */
    protected \Illuminate\Support\Collection $data_emails;

    /**
     * Undocumented function
     *
     * @param integer $perPage
     * @param boolean $scanned
     * @param boolean $draft
     * @param array<int,string> $dataset
     * @param array<int,string> $emails
     * @param array<int,string|int> $formIds
     * @param boolean $queue
     */
    public function __construct(
        protected int $perPage = 50,
        protected bool $scanned = false,
        protected bool $draft = false,
        protected array $dataset = [],
        protected array $emails = [],
        protected array $formIds = [],
        protected ?string $rank = null,
        protected bool $queue = false,
    ) {
        $this->data_emails = collect(
            ! empty($emails) ? $emails : dbconfig('notifiable_emails', collect([]))
        )->map(fn($e) => str($e));
    }

    /**
     * Dispatch the exported data to the data_emails
     */
    private function dispatchMails(
        Form|Company|Appointment|User $dataset,
        string $title,
        int $batch = 1,
    ): void {
        $this->data_emails
            ->unique()
            ->filter(fn($e) => $e->isNotEmpty() && ! $e->is('[]'))
            ->each(function ($email) use ($dataset, $batch, $title) {
                RateLimiter::attempt(
                'send-report:' . $email . $batch,
                    5,
                fn() => Mail::to($email->toString())->send(new ReportGenerated($dataset, $batch, $title))
                );
            });
    }

    private function exportCompanies()
    {
        if (Company::count() > 0) {
            $path = 'exports/companies-dataset/data-batch-0.xlsx';
            (new CompanyDataExports($this->perPage))->store($path);
            if ($this->queue) {
                (new CompanyDataExports($this->perPage))->queue($path)->chain([
                    new DispatchExportsNotifications(
                        Company::first(),
                        $this->data_emails,
                        'Companies Data',
                        0
                    )
                ]);
            } else {
                (new CompanyDataExports($this->perPage))->store($path);

                DispatchExportsNotifications::dispatch(
                    Company::first(),
                    $this->data_emails,
                    'Companies Data',
                    0
                );
            }
        }
    }

    private function exportUsers()
    {
        if (User::count() > 0) {
            $path = 'exports/users-dataset/data-batch-0.xlsx';

            if ($this->queue) {
                (new UserDataExports($this->perPage))->queue($path)->chain([
                    new DispatchExportsNotifications(
                        User::first(),
                        $this->data_emails,
                        'User Data',
                        0
                    )
                ]);
            } else {
                (new UserDataExports($this->perPage))->store($path);

                DispatchExportsNotifications::dispatch(
                    User::first(),
                    $this->data_emails,
                    'User Data',
                    0
                );
            }
        }
    }

    private function exportAppointment()
    {
        if (Appointment::count() > 0) {
            $path = 'exports/appointments-dataset/data-batch-0.xlsx';

            if ($this->queue) {
                (new AppointmentDataExports($this->perPage))->queue($path)->chain([
                    new DispatchExportsNotifications(
                        Appointment::first(),
                        $this->data_emails,
                        'Appointment Data',
                        0
                    )
                ]);
            } else {
                (new AppointmentDataExports($this->perPage))->store($path);

                DispatchExportsNotifications::dispatch(
                    Appointment::first(),
                    $this->data_emails,
                    'Appointment Data',
                    0
                );
            }
        }
    }

    private function exportForms()
    {
        $query = Form::query()->whereHas(
            $this->draft === true ? 'drafts' : 'data'
        )->where('data_emails', '!=', null);

        $query->when($this->scanned === true, fn($q) => $q->whereHas('data.scans'));
        $query->when(!empty($this->formIds), fn($q) => $q->whereIn('id', $this->formIds));

        foreach ($query->cursor() as $batch => $form) {
            $name = str('forms')->append('-')->append($form->id)->slug();

            $path = "exports/{$name}/data-batch-{$batch}.xlsx";

            $this->data_emails = ! empty($this->emails)
                ? collect($this->emails)->map(fn($e) => str($e))
                : $form->data_emails;

            $this->dispatchMails($form, $form->title ?? $form->name, 0);


            if ($this->queue) {
                (new FormDataExports(
                    form: $form,
                    scanned: $this->scanned,
                    perPage: $this->perPage,
                    draft: $this->draft,
                    rank: $this->rank,
                ))->queue($path)->chain([
                    new DispatchExportsNotifications(
                        $form,
                        $this->data_emails,
                        $form->name . ' Data',
                        0
                    )
                ]);
            } else {
                (new FormDataExports(
                    form: $form,
                    scanned: $this->scanned,
                    perPage: $this->perPage,
                    draft: $this->draft,
                    rank: $this->rank,
                ))->store($path);

                DispatchExportsNotifications::dispatch(
                    $form,
                    $this->data_emails,
                    $form->name . ' Data',
                    0
                );
            }
        }
    }

    public function __destruct()
    {
        if (! empty($this->dataset)) {
            $validDataset = [];
            foreach ($this->dataset as $dataset) {
                $method = str("export-$dataset")->camel()->toString();

                if (! method_exists($this, $method)) {
                    throw new \Exception("$dataset is not a valid dataset", 1);
                }

                $validDataset[] = $method;
            }

            foreach ($validDataset as $method) {
                $this->{$method}();
            }

            return;
        }

        if ($this->scanned || $this->draft || !empty($this->formIds)) {
            $this->exportForms();

            return;
        }

        $this->exportUsers();
        $this->exportAppointment();
        $this->exportCompanies();
        $this->exportForms();
    }
}
