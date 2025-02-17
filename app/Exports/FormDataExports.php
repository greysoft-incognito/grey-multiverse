<?php

namespace App\Exports;

use App\Exports\Sheets\FormDataSheets;
use App\Models\Form;
use App\Models\GenericFormData;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithProperties;

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
    ) {}

    /**
     * @return array
     */
    public function sheets(): array
    {
        $formData = $this->form->data();

        if ($this->scanned === true) {
            $formData->scanned();
        }

        $sheets = [];

        $formData->chunk($this->perPage, function ($data, $page) use (&$sheets) {
            $sheets[] = new FormDataSheets($page + 1, $data);
        });

        return $sheets;
    }

    public function properties(): array
    {
        $data = GenericFormData::first();

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
            'title' => "{$title} Submited Data",
            'description' => $data->form->title ?? 'Submited Data',
            'keywords' => "submissions,export,spreadsheet,greysoft,greymultiverse,$keywords",
            'category' => 'Submited Data',
            'company' => $data->form->name ?? $data->form->title ?? 'GreyMultiverse',
        ];
    }
}
