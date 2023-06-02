<?php

namespace App\Filters;

use Illuminate\Http\Request;


class CompatitionsFilter {
    protected $safeParms = [
        'id' => ['eq'],
        'name' => ['eq', 'like'],
        'country' => ['eq', 'like'],
        'city' => ['eq', 'like'],
        'address' => ['eq', 'like'],
        'startTimeDate' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'registrationDeadline' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'status' => ['eq'],
        'hostName' => ['eq', 'like']
    ];

    protected $columnsMap = [
        'startTimeDate' => 'start_time_date',
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
                    $eloQuery[] = [$column, $this->operatorMap[$operator], $this->operatorMap[$operator] == 'like' ? "%$query[$operator]%" :$query[$operator]];
                }
            }
        }
        return $eloQuery;
    }
   

}