<?php

namespace App\Http\Resources;

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
        $name = '';
        $date = date('Y', strtotime($this->created_at));
        if($this->name == 'yearlyMembership'){
            $name = "Članarina za $date.";
        }
        if($this->name == 'midYearMembership'){
            $name = "Registracija članova";
        }
        if($this->name == 'beltsChange'){
            $name = 'Promjena pojaseva';
        }
        return [
            'id' => $this->id,
            'name' => $name,
            'value' => $this->name,
            'membershipPrice' => $this->membership_price,
            'amountToPay' => $this->amount_to_pay,
            'isPaid' => $this->is_paid,
            'club' => new ClubsResource($this->club),
            'createdAt' => date($this->created_at)
        ];
    }
}
