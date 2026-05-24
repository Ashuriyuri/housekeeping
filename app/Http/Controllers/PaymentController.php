<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('appointment');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        // Filter by date (mm/dd/yyyy)
        if ($request->filled('payment_date')) {
            try {
                $date = Carbon::createFromFormat('m/d/Y', $request->payment_date);
                $query->whereDate('created_at', $date);
            } catch (\Exception $e) {
                // Invalid date format, ignore
            }
        }

        // Filter by date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            try {
                $fromDate = Carbon::createFromFormat('m/d/Y', $request->from_date);
                $toDate = Carbon::createFromFormat('m/d/Y', $request->to_date);
                $query->whereBetween('created_at', [$fromDate, $toDate->endOfDay()]);
            } catch (\Exception $e) {
                // Invalid date format, ignore
            }
        }

        // Filter by payment method
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate totals
        $totals = [
            'pending_amount' => Payment::where('payment_status', 'Pending')->sum('amount'),
            'paid_amount' => Payment::where('payment_status', 'Paid')->sum('amount'),
            'total_amount' => Payment::sum('amount'),
        ];

        return view('payments.index', [
            'payments' => $payments,
            'totals' => $totals,
            'status' => $request->status,
            'paymentDate' => $request->payment_date,
        ]);
    }

    public function create(Appointment $appointment)
    {
        if ($appointment->status !== 'Completed') {
            return redirect()->route('appointments.show', $appointment)->with('error', 'Payment can only be created for completed appointments.');
        }

        $totalPrice = $appointment->total_price;
        return view('payments.create', compact('appointment', 'totalPrice'));
    }

    public function store(Request $request, Appointment $appointment)
    {
        if ($appointment->status !== 'Completed') {
            return redirect()->route('appointments.show', $appointment)->with('error', 'Payment can only be created for completed appointments.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Cash,GCash,Bank Transfer',
            'payment_status' => 'required|in:Pending,Paid',
        ]);

        $validated['appointment_id'] = $appointment->id;

        $payment = Payment::updateOrCreate(
            ['appointment_id' => $appointment->id],
            $validated
        );

        return redirect()->route('payments.show', $payment)->with('success', 'Receipt created successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load('appointment.services', 'appointment.employees');

        return view('payments.receipt', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Cash,GCash,Bank Transfer',
            'payment_status' => 'required|in:Pending,Paid',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.show', $payment)->with('success', 'Receipt updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Payment deleted successfully.');
    }

    /**
     * Get payment summary by date range
     */
    public function paymentSummary(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date_format:m/d/Y',
            'to_date' => 'required|date_format:m/d/Y|after_or_equal:from_date',
        ]);

        try {
            $fromDate = Carbon::createFromFormat('m/d/Y', $validated['from_date']);
            $toDate = Carbon::createFromFormat('m/d/Y', $validated['to_date']);

            $payments = Payment::whereBetween('created_at', [$fromDate, $toDate->endOfDay()])
                ->get()
                ->groupBy('payment_status');

            return response()->json([
                'period' => "{$validated['from_date']} to {$validated['to_date']}",
                'paid' => [
                    'count' => $payments->get('Paid', collect())->count(),
                    'amount' => $payments->get('Paid', collect())->sum('amount'),
                ],
                'pending' => [
                    'count' => $payments->get('Pending', collect())->count(),
                    'amount' => $payments->get('Pending', collect())->sum('amount'),
                ],
                'summary' => [
                    'total_transactions' => $payments->flatten()->count(),
                    'total_amount' => $payments->flatten()->sum('amount'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }
    }
}
