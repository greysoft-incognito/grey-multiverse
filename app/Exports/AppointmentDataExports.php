<?php

namespace App\Exports;

use App\Exports\Sheets\AppointmentDataSheets;
use App\Models\BizMatch\Appointment;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithProperties;

class AppointmentDataExports implements WithMultipleSheets, WithProperties
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
    ) {}

    /**
     * @return array
     */
    public function sheets(): array
    {
        $formData = Appointment::query();

        $sheets = [];

        $formData->chunk($this->perPage, function ($data, $page) use (&$sheets) {
            $sheets[] = new AppointmentDataSheets($page + 1, $data);
        });

        return $sheets;
    }

    public function properties(): array
    {
        return [
            'creator' => 'GreyMultiverese',
            'lastModifiedBy' => 'GreyMultiverse',
            'title' => "Appointment Data",
            'description' => 'Appointment Data',
            'keywords' => "submissions,export,spreadsheet,greysoft,greymultiverse,appointments",
            'category' => 'Appointments Data',
            'company' => 'GreyMultiverse',
        ];
    }
}
