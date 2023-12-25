<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AllUsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{

    use Importable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = User::query()
            ->select('name', 'surname', 'login', 'city', 'created_at')
            ->where('type', null)
            ->get();

        return $data;
    }

    /**
     * @param $data
     * @return array
     */
    public function map($data): array
    {
        return [
            $data->name,
            $data->surname,
            $data->login,
            $data->city,
            $data->created_at,
        ];
    }
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
        ];
    }
    public function headings(): array
    {
        return [
            'Имя',
            'Фамилия',
            'Номер телефона',
            'Город',
            'Дата регистрации',
        ];
    }
}
