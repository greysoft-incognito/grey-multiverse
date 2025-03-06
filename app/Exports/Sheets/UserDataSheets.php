<?php

namespace App\Exports\Sheets;

use App\Models\User;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserDataSheets implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    /**
     * The sheets constructor
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, User>  $submisions
     */
    public function __construct(
        protected int $page,
        protected \Illuminate\Database\Eloquent\Collection $submisions
    ) {
    }

    public function headings(): array
    {
        $submision = $this->submisions->first();

        return collect(array_keys($this->map($submision)))->map(
            fn ($e) => str($e)
                ->replace('_', ' ')
                ->title()
                ->replace(['Id'], ['ID'])
                ->toString()
        )->toArray();
    }

    public function collection()
    {
        return $this->submisions;
    }

    /**
     * Undocumented function
     *
     * @param  User  $submision
     */
    public function map($submision): array
    {
        $allowed = [
            // 'id',
            'firstname',
            'lastname',
            'email',
            'phone',
            'city',
            'state',
            'country',
            'created_at',
        ];

        $data = collect($allowed)->mapWithKeys(function ($key) use ($submision) {
            $value = $submision[$key] ?? '';

            return [$key => match (true) {
                in_array($key, ['created_at', 'date']) => Carbon::parse($value)->isoFormat('MMM DD, YYYY'),
                default => (string) $value
            }];
        });

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
            'B' => ['font' => ['bold' => true]],
        ];
    }
}
