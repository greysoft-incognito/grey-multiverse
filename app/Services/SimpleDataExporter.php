<?php

namespace App\Services;

use App\Exports\AppointmentDataExports;
use App\Exports\CompanyDataExports;
use App\Exports\FormDataExports;
use App\Exports\UserDataExports;
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

    public function __construct(
        protected int $perPage = 50,
        protected bool $scanned = false,
    ) {
        $this->data_emails = dbconfig('notifiable_emails', collect([]))->map(fn ($e) => str($e));
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
            ->filter(fn ($e) => $e->isNotEmpty())
            ->each(function ($email) use ($dataset, $batch, $title) {
                RateLimiter::attempt(
                    'send-report:'.$email.$batch,
                    5,
                    fn () => Mail::to($email->toString())->send(new ReportGenerated($dataset, $batch, $title))
                );
            });
    }

    private function exportCompanies()
    {
        if (Company::count() > 0) {
            $path = 'exports/companies-dataset/data-batch-0.xlsx';
            (new CompanyDataExports($this->perPage))->store($path);

            $this->dispatchMails(new Company(), 'Companies Data', 0);
        }
    }

    private function exportUsers()
    {
        if (User::count() > 0) {
            $path = 'exports/users-dataset/data-batch-0.xlsx';
            (new UserDataExports($this->perPage))->store($path);

            $this->dispatchMails(new User(), 'User Data', 0);
        }
    }

    private function exportAppointment()
    {
        if (Appointment::count() > 0) {
            $path = 'exports/appointments-dataset/data-batch-0.xlsx';
            (new AppointmentDataExports($this->perPage))->store($path);

            $this->dispatchMails(new Appointment(), 'Appointment Data', 0);
        }
    }

    private function exportForms()
    {
        $query = Form::query()->whereHas('data')->where('data_emails', '!=', null);

        $query->when($this->scanned === true, fn ($q) => $q->whereHas('data.scans'));

        foreach ($query->cursor() as $batch => $form) {
            $name = str('forms')->append('-')->append($form->id)->slug();

            $path = "exports/{$name}/data-batch-{$batch}.xlsx";

            (new FormDataExports($form, $this->scanned, $this->perPage))->store($path);

            $this->data_emails = $form->data_emails;
            $this->dispatchMails($form, $form->title ?? $form->name, 0);
        }
    }

    public function __destruct()
    {
        if ($this->scanned) {
            $this->exportForms();

            return;
        }

        $this->exportUsers();
        $this->exportAppointment();
        $this->exportCompanies();
        $this->exportForms();
    }
}
