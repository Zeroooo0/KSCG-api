<?php

namespace App\Filters;

use Illuminate\Http\Request;


class CompatitionsFilter {
    protected $safeParms = [
        'name' => ['eq', 'like'],
        'country' => ['eq'],
        'city' => ['eq'],
        'address' => ['eq'],
        'startTime' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'registrationDeadline' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'status' => ['eq'],
        'hostName' => ['eq']
    ];

    protected $columnsMap = [
        'startTime' => 'start_time_date',
        'registrationDeadline' => 'registration_deadline',
        'hostName' => 'host_name'
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
                    $eloQuery[] = [$column, $this->operatorMap[$operator], $query[$operator]];
                }
            }
        }
        return $eloQuery;
    }
   

}