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
    ) {
    }

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
        $fields = $submision->form->fields->mapWithKeys(function ($field) use ($submision) {
            $label = $field->label ?? $field->name;
            $value = $submision->data[$field->name] ?? null;

            // Early return for null values to reduce nesting
            if ($value === null) {
                return [$label => ''];
            }

            // Use match for cleaner conditional logic
            $value = match (true) {
                str($label)->lower()->is('primary') => $value ? 'Yes' : '',
                ! $field->options => $value,
                $field->expected_value_type === 'array' => collect((array) $value)
                    ->map(fn ($val) => $field->options[$val]['label'] ?? $val)
                    ->join(', '),
                default => $field->options[$value]['label'] ?? $value,
            };

            return [$label => is_array($value) ? implode(', ', $value) : $value];
        });

        $data = collect([
            'Fullname' => $submision->fullname,
            'Rank' => $submision->rank,
            'Score (%)' => $submision->score,
        ])->merge($fields)->merge([
            'Submission Date' => $submision->created_at->format('Y/m/d'),
        ]);

        // $data->prepend($submision->id, '#');

        return $data->toArray();
    }

    public function title(): string
    {
        return 'Page '.$this->page;
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
