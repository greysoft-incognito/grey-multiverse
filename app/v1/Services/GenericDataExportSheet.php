<?php

namespace V1\Services;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class GenericDataExportSheet implements FromCollection, WithTitle
{
    private $sheet;

    private $data;

    public function __construct(int $sheet, array $data)
    {
        $this->sheet = $sheet;
        $this->data = $data;
    }

    public function collection()
    {
        return new Collection($this->data);
    }

    public function title(): string
    {
        return 'Page '.$this->sheet;
    }
}
