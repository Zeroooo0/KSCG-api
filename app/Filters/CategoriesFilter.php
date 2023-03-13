<?php

namespace App\Filters;

use Illuminate\Http\Request;


class CategoriesFilter {
    protected $safeParms = [
        'name' => ['eq', 'like'],
        'kataOrKumite' => ['eq'],
        'categoryName' => ['eq', 'like'],
        'gender' => ['eq', 'gt', 'gte', 'lt', 'lte', 'lorg'],
        'dateFrom' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'dateTo' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'weightFrom' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'weightTo' => ['eq', 'gt', 'gte', 'lt', 'lte'],
        'soloOrTeam' => ['eq'],
        'status' => ['eq'],
        'belts' => ['eq'],
    ];

    protected $columnsMap = [
        'kataOrKumite' => 'kata_or_kumite',
        'categoryName' => 'category_name',
        'dateFrom' => 'date_from',
        'dateTo' => 'date_to',
        'weightFrom' => 'weight_from',
        'weightTo' => 'weight_to',
        'soloOrTeam' => 'solo_or_team', 
    ];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'lorg' => '<>',
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