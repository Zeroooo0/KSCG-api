<?php

namespace App\Filters;

use Illuminate\Http\Request;


class RegistrationsFilter {
    protected $safeParms = [
        'tatamiNo' => ['eq'],
        'etoStart' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'status' => ['eq'],
    ];

    protected $columnsMap = [
        'tatamiNo' => 'tatami_no',
        'etoStart' => 'eto_start'
    ];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'lorg' => '<>',
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