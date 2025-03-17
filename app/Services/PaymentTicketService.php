<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Ticket;
use App\Models\TicketOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentTicketService
{
    public function paymentHandler(TicketOrder $ticketOrder): string
    {
        try {
            DB::transaction(function () use ($ticketOrder) {

                $ticket = Ticket::query()->where('id', $ticketOrder->ticket_id)
                    ->where('available_tickets', '>', 0)
                    ->lockForUpdate()
                    ->first();

                if (!$ticket) {
                    Log::error("Failed ticket purchase attempt. Ticket ID: {$ticketOrder->ticket_id}, User ID: {$ticketOrder->user_id}");
                    throw new \Exception("Not enough tickets available.");
                }

                $ticketOrder->update(['status' => OrderStatus::PAID]);
                $ticket->decrement('available_tickets');
            });

            return true;

        } catch (\Exception $e) {
            Log::error("Payment processing failed: " . $e->getMessage());
            return false;
        }
    }
}
