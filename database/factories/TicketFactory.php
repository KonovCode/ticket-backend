<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Ticket title',
            'description' => 'Ticket default description',
            'date' => now(),
            'time' => '14:50',
            'price' => 100,
            'city' => 'Dnepr',
            'total_tickets' => 100,
            'available_tickets' => 100,
            'address' => 'Dovatora 88',
        ];
    }
}
