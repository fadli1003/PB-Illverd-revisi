<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function showPaymentPage(Booking $booking)
    {
        if ($booking->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this booking.');
        }
        if ($booking->payment_status !== 'unpaid') {
            return redirect()->route('book')->with('info', 'Booking sudah diproses sebelumnya.');
        }

        return view('bookings.payment', compact('booking'));
    }

    public function createPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);
        $booking = Booking::findOrFail($validated['booking_id']);
        if ($booking->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($booking->payment_status !== 'unpaid') {
            return response()->json(['error' => 'Booking sudah diproses sebelumnya.'], 400);
        }

        // Parameter untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => uniqid() . '-' . $booking->id,
                'gross_amount' => (int) $booking->dp_amount,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => auth()->user()->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => $booking->id,
                    'price' => (int) $booking->dp_amount,
                    'quantity' => 1,
                    'name' => $this->getBookingDescription($booking),
                ]
            ],
            'callbacks' => [
                'finish' => route('payment.success'),
                'unfinish' => route('payment.unfinish'),
                'error' => route('payment.error'),
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $booking->update([
                'payment_order_id' => $params['transaction_details']['order_id'],
                'payment_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $params['transaction_details']['order_id'],
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Payment Creation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Payment creation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function paymentNotification(Request $request)
    {      
        $json = $request->getContent();
        $notification = json_decode($json);

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;
        $fraudStatus = $notification->fraud_status;

        $booking = Booking::where('payment_order_id', $orderId)->first();

        if (!$booking) {
            Log::warning('Payment notification for unknown order_id: ' . $orderId);
            return response()->json(['status' => 'error'], 404);
        }

        // Update status berdasarkan notifikasi
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $booking->update(['payment_status' => 'challenge']);
            } else if ($fraudStatus == 'accept') {
                $this->handlePaymentSuccess($booking);
            }
        } else if ($transactionStatus == 'settlement') {
            $this->handlePaymentSuccess($booking);
        } else if ($transactionStatus == 'cancel' ||
                   $transactionStatus == 'deny' ||
                   $transactionStatus == 'expire') {
            $booking->update(['payment_status' => 'failed']);
        } else if ($transactionStatus == 'pending') {
            $booking->update(['payment_status' => 'pending']);
        }
    }

    private function handlePaymentSuccess($booking)
    {
        $booking->update([
            'payment_status' => 'paid',
            'status' => 'approved'
        ]);

        // Kirim notifikasi email jika perlu
        // Mail::to($booking->user->email)->send(new PaymentSuccessMail($booking));
    }

    private function getBookingDescription($booking)
    {
        if ($booking->booking_type === 'regular') {
            return "Booking Lapangan {$booking->field->name} - " .
                   $booking->booking_date . " " .
                   $booking->start_time . "-" . $booking->end_time;
        } else {
            return "Membership Lapangan {$booking->field->name} - " .
                   $booking->total_hours . " Jam";
        }
    }
}