<?php

namespace App\Filters;

use Illuminate\Http\Request;


class ClubsFilter {
    protected $safeParms = [
        'name' => ['eq'],
        'shortName' => ['eq'],
        'country' => ['eq'],
        'town' => ['eq'],
        'address' => ['eq'],
        'pib' => ['eq'],
        'email' => ['eq'],
        'phoneNumber' => ['eq'],
        'userId' => ['eq']
    ];

    protected $columnsMap = [
        'shortName' => 'short_name',
        'phoneNumber' => 'phone_number',
        'userId' => 'user_id',
    ];

    protected $operatorMap = [
        'eq' => '=',
        'like' => 'LIKE'
    ];

    public function transform(Request $request) {
        $eloQuery = [];
        foreach ($this->safeParms as $parm => $operators) {
            $query = $request->query($parm);

            if(!isset($query)) {
                continue;
            }

            $column = $this->columnsMap[$parm] ?? $parm;

            foreach ($operators as $operator) {
                if (isset($query[$operator])) {
                    $eloQuery[] = [$column, $this->operatorMap[$operator], $query[$operator]];
                }
            }
        }
        return $eloQuery;
    }
   

}