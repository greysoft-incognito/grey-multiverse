<?php

namespace App\Exports;

use App\Exports\Sheets\CompanyDataSheets;
use App\Models\BizMatch\Company;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Excel;

class CompanyDataExports implements WithMultipleSheets, WithProperties
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
        protected int $perPage = 50,
    ) {
    }

    public function sheets(): array
    {
        $formData = Company::query();

        $sheets = [];

        $formData->chunk($this->perPage, function ($data, $page) use (&$sheets) {
            $sheets[] = new CompanyDataSheets($page + 1, $data);
        });

        return $sheets;
    }

    public function properties(): array
    {
        return [
            'creator' => 'GreyMultiverese',
            'lastModifiedBy' => 'GreyMultiverse',
            'title' => 'Companies Submitted Data',
            'description' => 'Companies Submitted Data',
            'keywords' => 'submissions,export,spreadsheet,greysoft,greymultiverse,companies',
            'category' => 'Submitted Data',
            'company' => 'GreyMultiverse',
        ];
    }
}
