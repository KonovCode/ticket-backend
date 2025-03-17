<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketStoreRequest;
use App\Http\Requests\TicketUpdateRequest;
use App\Models\Ticket;
use App\Services\TicketService;

class TicketController extends Controller
{
    public function index()
    {
        return response()->json([
            'tickets' => Ticket::all(),
        ]);
    }

    public function show(int $ticket_id)
    {
        $ticket = Ticket::query()->find($ticket_id);

        if(!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.'
            ], 404);
        }

        return response()->json([
            'ticket' => $ticket
        ]);
    }

    public function store(TicketStoreRequest $request)
    {
        $user = auth()->user();

        $data = $request->validated();

        $data['available_tickets'] = $data['total_tickets'];

        $ticket = $user->tickets()->create($data);

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket,
        ]);
    }

    public function update(TicketUpdateRequest $request, int $ticket_id, TicketService $ticketService)
    {
        $user = auth()->user();

        $ticket = $user->tickets()->findOrFail($ticket_id);

        $data = $request->validated();

        if(isset($data['total_tickets'])) {
            $ticketService->updateTicketQuantity($ticket, $data['total_tickets']);
        }

        $ticket->update($data);

        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket,
        ]);
    }

    public function delete(int $ticket_id)
    {
        $user = auth()->user();

        $ticket = $user->tickets()->find($ticket_id);

        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.',
            ], 404);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully',
        ]);
    }
}
