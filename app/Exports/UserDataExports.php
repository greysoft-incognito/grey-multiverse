<?php

namespace App\Exports;

use App\Exports\Sheets\UserDataSheets;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Excel;

class UserDataExports implements WithMultipleSheets, WithProperties
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
        $formData = User::query();

        $sheets = [];

        $formData->chunk($this->perPage, function ($data, $page) use (&$sheets) {
            $sheets[] = new UserDataSheets($page + 1, $data);
        });

        return $sheets;
    }

    public function properties(): array
    {
        return [
            'creator' => 'GreyMultiverese',
            'lastModifiedBy' => 'GreyMultiverse',
            'title' => 'Users Submitted Data',
            'description' => 'Users Submitted Data',
            'keywords' => 'submissions,export,spreadsheet,greysoft,greymultiverse,users',
            'category' => 'Submitted Data',
            'company' => 'GreyMultiverse',
        ];
    }
}
