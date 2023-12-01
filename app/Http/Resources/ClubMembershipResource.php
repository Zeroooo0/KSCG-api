<?php

namespace App\Http\Resources;

use App\Models\CompetitorMembership;
use App\Models\Roles;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubMembershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $competitorMemberships = "embeddable";
        if(str_contains($request->embed, 'competitorMemberships')) {
            if($this->competitorMemberships->first() != null) {
                $competitorMemberships = CompetitorMembershipResource::collection($this->competitorMemberships);
            } else {
                $competitorMemberships =  'Nema prijava';
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'membershipPrice' => $this->membership_price,
            'competitorsCount' => (string)$this->competitorMemberships->count(),
            'address' => $this->address,
            'startDate' => $this->start_date,
            'amountToPay' => $this->amount_to_pay,
            'isPaid' => (boolean)$this->is_paid,
            'status' => (boolean)$this->status,
            'isSubmited' => (boolean)$this->is_submited,
            'club' => [
                'name' => $this->club->name,
                'phone' => $this->club->phone_number,
                'pib' => $this->club->pib,
            ],
            'createdAt' => date($this->created_at),
            'competitorsMemberships' => $competitorMemberships,
            'documents' => DocumentsResource::collection($this->document),
            'roles' => RolesResource::collection($this->role)
        ];
    }
}
