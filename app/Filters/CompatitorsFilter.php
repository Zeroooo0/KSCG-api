<?php

namespace App\Filters;

use Illuminate\Http\Request;


class CompatitorsFilter {
    protected $safeParms = [
        'name' => ['eq', 'like'],
        'lastName' => ['eq', 'like'],
        'status' => ['eq'],
        'beltId' => ['eq'],
        'birthDay' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'weight' => ['eq', 'gt', 'gte', 'lt', 'lte']
    ];

    protected $columnsMap = [
        'lastName' => 'last_name',
        'birthDay' => 'date_of_birth',
        'beltId' => 'belt_id'
    ];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
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