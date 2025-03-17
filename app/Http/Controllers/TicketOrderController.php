<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Ticket;
use App\Services\PaymentTicketService;
use Illuminate\Http\Request;

class TicketOrderController extends Controller
{
    public function purchase(int $ticket_id)
    {
        $ticket = Ticket::query()->find($ticket_id);

        if(!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if(!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.'
            ], 404);
        }

        $order = $ticket->orders()->create([
            'user_id' => auth()->id(),
            'status' => OrderStatus::PENDING
        ]);

        return response()->json([
            'message' => 'Ticket successfully purchased.',
            'script' => "Происходит редирект на платежный шлюз. Что бы симетировать что вы совершили платеж сделайте post запрос на: http:/localhost:8088/api/confirm-payment/ticket-order/{$order->id}
            и укажите в теле запроса status = paid если вы совершили платеж и status = failed если вы не совершили платеж.",
            'order_id' => $order->id
        ], 200);
    }

    public function paymentCallback(Request $request, int $order_id, PaymentTicketService $paymentTicketService)
    {
        $order = auth()->user()->ticketOrders()->find($order_id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.'
            ], 404);
        }

        $status = $request->input('status');
        if (!$status || !in_array($status, ['paid', 'failed'])) {
            return response()->json([
                'message' => 'Invalid payment status.'
            ], 400);
        }

        if ($order->status !== OrderStatus::PENDING) {
            return response()->json([
                'message' => 'Order is not pending or already processed.'
            ], 409);
        }

        if ($status === 'paid') {
            $result = $paymentTicketService->paymentHandler($order);

            return response()->json([
                'message' => $result ? 'Payment successful.' : 'Payment processing failed.'
            ], $result ? 200 : 500);
        }

        $order->update(['status' => OrderStatus::FAILED]);

        return response()->json([
            'message' => 'Payment failed.'
        ], 200);
    }
}
