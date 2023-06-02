<?php

namespace App\Filters;

use Illuminate\Http\Request;


class RegistrationsFilter {
    protected $safeParms = [
        'id' => ['eq'],
        'clubId' => ['eq'],
        'competitionId' => ['eq'],
        'position' => ['eq', 'gt'],
        'isPrinted' => ['eq'],
    ];

    protected $columnsMap = [
        'clubId' => 'club_id',
        'competitionId' => 'compatition_id',
        'isPrinted' => 'is_printed',
    ];

    protected $operatorMap = [
        'eq' => '=',
        'gt' => '>'
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