<?php

namespace App\Filters;

use Illuminate\Http\Request;


class UsersFilter {
    protected $safeParms = [
        'name' => ['eq', 'like'],
        'lastName' => ['eq', 'like'],
        'status' => ['eq'],
        'email' => ['eq', 'like'],
        'userType' => ['eq']
    ];

    protected $columnsMap = [
        'lastName' => 'last_name',
        'userType' => 'user_type'
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