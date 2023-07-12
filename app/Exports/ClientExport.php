<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromArray;

class ClientExport implements FromArray
{
    public function array(): array
    {
        $clients = Client::all();
        $data = [];
        $data[] = [
            'Name',
            'ICE',
            'IF Number',
            'RC Number',
            'CNSS Number',
            'Address',
            'Phone Number',
            'Created At'
        ];

        foreach ($clients as $client) {
            $data[] = [
                $client->name,
                $client->ice,
                $client->if_no,
                $client->rc_no,
                $client->cnss_no,
                $client->address,
                $client->phone_number,
                $client->created_at
            ];
        }

        return $data;
    }
}

