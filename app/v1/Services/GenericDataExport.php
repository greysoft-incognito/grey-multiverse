<?php

namespace V1\Services;

use App\Models\BizMatch\Appointment;
use App\Models\BizMatch\Company;
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

    public function __construct(
        protected array $data,
        protected Form|Company|Appointment $dataset,
        protected $title = null
    ) {
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
        $title = $this->title ?: $this->dataset->name ?? 'GreyMultiverse';
        $keywords = str($this->dataset->name ?? $this->dataset->slug)->append(",$title")->lower();

        return [
            'creator' => 'GreyMultiverese',
            'lastModifiedBy' => 'GreyMultiverse',
            'title' => "{$title} Submited Data",
            'description' => $this->dataset->title ?? $this->title ?? 'Submited Data',
            'keywords' => "submissions,export,spreadsheet,greysoft,greymultiverse,$keywords",
            'category' => 'Submited Data',
            'company' => $this->dataset->name ?? $this->dataset->title ?? 'GreyMultiverse',
        ];
    }
}
