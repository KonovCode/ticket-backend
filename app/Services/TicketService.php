<?php

namespace App\Services;

use App\Models\Ticket;

class TicketService
{
    public function updateTicketQuantity(Ticket $ticket, int $quantity): void
    {
        $purchasedTickets = $ticket->total_tickets - $ticket->available_tickets;

        $ticket->total_tickets = $quantity;
        $ticket->available_tickets = max(0, $quantity - $purchasedTickets);

        $ticket->save();
    }
}
