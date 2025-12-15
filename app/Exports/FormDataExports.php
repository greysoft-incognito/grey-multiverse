<?php

namespace App\Exports;

use App\Exports\Sheets\FormDataSheets;
use App\Models\Form;
use App\Models\FormData;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Excel;

class FormDataExports implements WithMultipleSheets, WithProperties
{
    use Exportable;

    /**
     * Optional Writer Type
     */
    private $writerType = Excel::XLSX;

    /**
     * Optional Disk
     */
    private $disk = 'protected';

    /**
     * Optional Disk OPtions
     */
    private $diskOptions = [
        'visibility' => 'public',
    ];

    public function __construct(
        protected Form $form,
        protected bool $scanned = false,
        protected int $perPage = 50,
        protected bool $pending = false,
        protected bool $draft = false,
        protected ?string $rank = null,
    ) {
    }

    public function sheets(): array
    {
        $formData = $this->draft
            ? $this->form->drafts()
            : $this->form->data()->when($this->rank, fn($q) => $q->ranked($this->rank));

        $formData->orderBy('rank', 'DESC');

        if ($this->scanned === true) {
            $formData->scanned();
        }

        if ($this->pending === true) {
            $formData->where('status', 'submitted');
        }

        $sheets = [];

        $formData->chunk($this->perPage, function ($data, $page) use (&$sheets) {
            $sheets[] = new FormDataSheets($page + 1, $data);
        });

        return $sheets;
    }

    public function properties(): array
    {
        $data = FormData::first();

        $title = $data->form->title;
        $keywords = str($data->form->name ?? $data->form->slug)
            ->append(",$title")
            ->lower()
            ->replace(' ', ',')
            ->explode(',')
            ->unique()
            ->join(',');

        return [
            'creator' => 'GreyMultiverese',
            'lastModifiedBy' => 'GreyMultiverse',
            'title' => "{$title} Submitted Data",
            'description' => $data->form->title ?? 'Submitted Data',
            'keywords' => "submissions,export,spreadsheet,greysoft,greymultiverse,$keywords",
            'category' => 'Submitted Data',
            'company' => $data->form->name ?? $data->form->title ?? 'GreyMultiverse',
        ];
    }
}
