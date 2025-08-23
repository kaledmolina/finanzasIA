<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'amount' => $this->amount,
            'date' => $this->date->format('Y-m-d'),
            'type' => $this->type,
            'expense_type' => $this->expense_type,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
