<?php

namespace App\Filters;

use Illuminate\Http\Request;


class RoleFilter {
    protected $safeParms = [
        'role' => ['eq','neq'],
        'status' => ['eq'],
    ];

    protected $columnsMap = [
        //
    ];

    protected $operatorMap = [
        'eq' => '=',
        'neq' => '!=',
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