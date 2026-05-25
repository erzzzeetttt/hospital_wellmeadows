<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        $bills = DB::select("
            SELECT
                b.bill_id,
                b.bill_date,
                b.total_amount,
                b.status,
                CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
                p.patient_no,
                COALESCE(SUM(py.amount_paid), 0) AS amount_paid
            FROM bills b
            JOIN patients p ON p.patient_no = b.patient_no
            LEFT JOIN payments py ON py.bill_id = b.bill_id
            GROUP BY b.bill_id, b.bill_date, b.total_amount, b.status,
                     p.first_name, p.last_name, p.patient_no
            ORDER BY b.bill_date DESC, b.bill_id DESC
        ");

        $summary = DB::select("
            SELECT
                COUNT(*)                                                             AS total_bills,
                COUNT(CASE WHEN status = 'Paid'       THEN 1 END)                   AS paid_count,
                COUNT(CASE WHEN status = 'Unpaid'     THEN 1 END)                   AS unpaid_count,
                COUNT(CASE WHEN status = 'Partial'    THEN 1 END)                   AS partial_count,
                COUNT(CASE WHEN status = 'Cancelled'  THEN 1 END)                   AS cancelled_count
            FROM bills
        ")[0];

        return view('module5.index', compact('bills', 'summary'));
    }

    public function create()
    {
        $patients = DB::table('patients')
            ->select('patient_no', 'first_name', 'last_name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('module5.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_no'              => 'required',
            'bill_date'               => 'required|date',
            'items'                   => 'required|array|min:1',
            'items.*.description'     => 'required|string|max:255',
            'items.*.quantity'        => 'required|integer|min:1',
            'items.*.unit_price'      => 'required|numeric|min:0',
        ]);

        try {
            $result = DB::select('SELECT fn_generate_bill(?, ?) AS bill_id', [
                $request->patient_no,
                $request->bill_date,
            ]);

            $billId = (int) $result[0]->bill_id;

            foreach ($request->items as $item) {
                DB::select('SELECT fn_add_bill_item(?, ?, ?, ?) AS message', [
                    $billId,
                    $item['description'],
                    (int) $item['quantity'],
                    (float) $item['unit_price'],
                ]);
            }

            return redirect()->route('billing.show', $billId)
                ->with('success', 'Bill generated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not generate bill: ' . $e->getMessage());
        }
    }

    public function show($bill_id)
    {
        $billRows = DB::select("
            SELECT b.bill_id, b.bill_date, b.total_amount, b.status,
                   CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
                   p.patient_no
            FROM bills b
            JOIN patients p ON p.patient_no = b.patient_no
            WHERE b.bill_id = ?
        ", [(int) $bill_id]);

        if (empty($billRows)) {
            abort(404);
        }
        $bill = $billRows[0];

        $items = DB::select("
            SELECT item_id, item_description, quantity, unit_price,
                   (quantity * unit_price) AS subtotal
            FROM bill_items
            WHERE bill_id = ?
            ORDER BY item_id ASC
        ", [(int) $bill_id]);

        $payments = DB::select("
            SELECT payment_id, payment_date, amount_paid, payment_method
            FROM payments
            WHERE bill_id = ?
            ORDER BY payment_date ASC, payment_id ASC
        ", [(int) $bill_id]);

        $amountPaid = collect($payments)->sum('amount_paid');
        $balance    = $bill->total_amount - $amountPaid;

        return view('module5.show', compact('bill', 'items', 'payments', 'amountPaid', 'balance'));
    }

    public function recordPayment($bill_id)
    {
        $billRows = DB::select("
            SELECT b.bill_id, b.bill_date, b.total_amount, b.status,
                   CONCAT(p.first_name, ' ', p.last_name) AS patient_name
            FROM bills b
            JOIN patients p ON p.patient_no = b.patient_no
            WHERE b.bill_id = ?
        ", [(int) $bill_id]);

        if (empty($billRows)) {
            abort(404);
        }
        $bill = $billRows[0];

        $amountPaid = DB::select("
            SELECT COALESCE(SUM(amount_paid), 0) AS total_paid
            FROM payments WHERE bill_id = ?
        ", [(int) $bill_id])[0]->total_paid;

        $balance = $bill->total_amount - $amountPaid;

        return view('module5.payment', compact('bill', 'amountPaid', 'balance'));
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'bill_id'        => 'required|integer',
            'payment_date'   => 'required|date',
            'amount_paid'    => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:Cash,Credit Card,Insurance,Bank Transfer',
        ]);

        try {
            $result = DB::select('SELECT fn_record_payment(?, ?, ?, ?) AS message', [
                (int) $request->bill_id,
                $request->payment_date,
                (float) $request->amount_paid,
                $request->payment_method,
            ]);

            return redirect()->route('billing.show', $request->bill_id)
                ->with('success', $result[0]->message);

        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not record payment: ' . $e->getMessage());
        }
    }

    public function cancel($bill_id)
    {
        try {
            $result = DB::select('SELECT fn_cancel_bill(?) AS message', [(int) $bill_id]);

            return redirect()->route('billing.show', $bill_id)
                ->with('success', $result[0]->message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Could not cancel bill: ' . $e->getMessage());
        }
    }

    public function reports()
    {
        $totals = DB::select("
            SELECT
                COUNT(*)                                                              AS total_bills,
                COALESCE(SUM(total_amount), 0)                                        AS total_revenue,
                COALESCE(SUM(CASE WHEN status = 'Paid' THEN total_amount ELSE 0 END), 0) AS paid_revenue,
                COUNT(CASE WHEN status = 'Paid'      THEN 1 END)                      AS paid_count,
                COUNT(CASE WHEN status = 'Unpaid'    THEN 1 END)                      AS unpaid_count,
                COUNT(CASE WHEN status = 'Partial'   THEN 1 END)                      AS partial_count
            FROM bills
        ")[0];

        $statusSummary = DB::select("
            SELECT status, COUNT(*) AS count, COALESCE(SUM(total_amount), 0) AS total
            FROM bills
            GROUP BY status
            ORDER BY status
        ");

        $recentBills = DB::select("
            SELECT b.bill_id, b.bill_date, b.total_amount, b.status,
                   CONCAT(p.first_name, ' ', p.last_name) AS patient_name
            FROM bills b
            JOIN patients p ON p.patient_no = b.patient_no
            ORDER BY b.bill_date DESC, b.bill_id DESC
            LIMIT 10
        ");

        return view('module5.reports', compact('totals', 'statusSummary', 'recentBills'));
    }
}
