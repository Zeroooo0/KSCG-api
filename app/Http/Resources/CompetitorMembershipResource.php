<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompetitorMembershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $name = $this->competitor->name;
        $lastName = $this->competitor->last_name;
        return [
            'id' => $this->id,
            'membershipType' => $this->clubMembership->type,
            'membershipPrice' => $this->membership_price == null ? 0 : $this->membership_price,
            'membershipBelt' => new BeltResource($this->belt),
            'firstMembership' => $this->first_membership,
            'competitor' => [
                'fullName' => "$name $lastName",
                'firstMembership' => (boolean)$this->competitor->first_membership,
                'kscgId' => $this->competitor->kscg_compatitor_id
            ]
        ];
    }
}
