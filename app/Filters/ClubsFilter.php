<?php

namespace App\Filters;

use Illuminate\Http\Request;


class ClubsFilter {
    protected $safeParms = [
        'name' => ['eq', 'like'],
        'shortName' => ['eq', 'like'],
        'country' => ['eq', 'like', 'neq'],
        'city' => ['eq', 'like'],
        'address' => ['eq', 'like'],
        'pib' => ['eq'],
        'email' => ['eq', 'like'],
        'phoneNumber' => ['eq', 'like'],
        'userId' => ['eq']
    ];

    protected $columnsMap = [
        'shortName' => 'short_name',
        'phoneNumber' => 'phone_number',
        'userId' => 'user_id',
    ];

    protected $operatorMap = [
        'eq' => '=',
        'neq' => '!=',
        'like' => 'like'
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
                    $eloQuery[] = [$column, $this->operatorMap[$operator], $this->operatorMap[$operator] == 'like' ? "%$query[$operator]%" : $query[$operator]];
                }
            }
        }
        return $eloQuery;
    }
   

}