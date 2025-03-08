<?php

namespace App\Exports\Sheets;

use App\Models\FormData;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FormDataSheets implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    /**
     * The sheets constructor
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, FormData>  $submisions
     */
    public function __construct(
        protected int $page,
        protected \Illuminate\Database\Eloquent\Collection $submisions
    ) {}

    public function headings(): array
    {
        $submision = $this->submisions->first();

        return array_keys($this->map($submision));
    }

    public function collection()
    {
        return $this->submisions;
    }

    /**
     * Undocumented function
     *
     * @param  FormData  $submision
     */
    public function map($submision): array
    {
        $fields = $submision->form->fields
            ->mapWithKeys(function ($field, $sn) use ($submision) {
                $label = $field->label ?? $field->name;
                $value = $submision->data[$field->name] ?? null;

                if (str($label)->lower()->is('primary')) {
                    $value = $value ? 'Yes' : '';
                }

                if ($field->options) {
                    $value = collect($field->options)
                        ->where('value', $value)
                        ->first()['label'] ?? $value;
                }

                return [$label => is_array($value) ? implode(', ', $value) : $value];
            });

        $data = collect([
            'Fullname' => $submision->fullname,
        ])->merge($fields)->merge([
            'Submission Date' => $submision->created_at->format('Y/m/d'),
        ]);

        // $data->prepend($submision->id, '#');

        return $data->toArray();
    }

    public function title(): string
    {
        return 'Page ' . $this->page;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            'A' => ['font' => ['bold' => true]],
        ];
    }
}
