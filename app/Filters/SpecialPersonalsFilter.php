<?php

namespace App\Filters;

use Illuminate\Http\Request;


class SpecialPersonalsFilter {
    protected $safeParms = [
        'status' => ['eq'],
        'name' => ['eq', 'like'],
        'lastName' => ['eq', 'like'],
        'email' => ['eq', 'like'],
        'gender' => ['eq'],
        'country' => ['eq', 'like'],
        'role' => ['eq']
    ];

    protected $columnsMap = [
        'lastName' => 'last_name'
    ];

    protected $operatorMap = [
        'eq' => '=',
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
                    $eloQuery[] = [$column, $this->operatorMap[$operator], $this->operatorMap[$operator] == 'like' ? "%$query[$operator]%" :$query[$operator]];
                }
            }
        }
        return $eloQuery;
    }
   

}