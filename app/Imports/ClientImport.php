<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;

class ClientImport implements ToModel
{
    private $accountId;

    public function __construct($accountId)
    {
        $this->accountId = $accountId;
    }

    public function model(array $row)
    {

        return new Client([
            'account_id' => $this->accountId,
            'name' => $row[0],
            'ice' => $row[1],
            'if_no' => $row[2],
            'rc_no' => $row[3],
            'cnss_no' => $row[4],
            'address' => $row[5],
            'phone_number' => $row[6],
        ]);
    }
}
