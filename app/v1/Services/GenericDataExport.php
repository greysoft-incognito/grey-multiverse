<?php

namespace V1\Services;

use App\Models\Form;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericDataExport implements ShouldAutoSize, WithMultipleSheets, WithProperties, WithStyles
{
    use Exportable;

    public function __construct(array $data, Form $form = null, $title = null)
    {
        $this->data = $data;
        $this->form = $form;
        $this->title = $title;
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->data as $key => $data) {
            $sheets[] = new GenericDataExportSheet($key + 1, $data);
        }

        return $sheets;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function properties(): array
    {
        return [
            'creator' => 'GreyMultiverese',
            'lastModifiedBy' => $this->form->name ?? 'GreyMultiverse',
            'title' => ($this->title ? $this->title : ($this->form->name ?? 'GreyMultiverse')).' Submited Data',
            'description' => $this->form->title ?? $this->title ?? 'Submited Data',
            'keywords' => 'submissions,export,spreadsheet,greysoft,greymultiverse,'.($this->form->name ?? 'good'),
            'category' => 'Submited Data',
            'company' => $this->form->name ?? 'GreyMultiverse',
        ];
    }
}
