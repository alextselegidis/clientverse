<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * ponytail: every report groups in PHP so it runs the same on MySQL and SQLite.
 * Fine for years of a small team's data; move the grouping into SQL if a page gets slow.
 */
class ReportsController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        $invoices = Invoice::where('status', 'sent')->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])->get();
        $payments = Payment::with('invoice')->whereHas('invoice')->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])->get();

        // month => currency => [invoiced, collected]
        $monthly = [];

        foreach ($invoices as $invoice) {
            $monthly[$invoice->issue_date->format('Y-m')][$invoice->currency]['invoiced'] ??= 0;
            $monthly[$invoice->issue_date->format('Y-m')][$invoice->currency]['invoiced'] += (float) $invoice->total;
        }

        foreach ($payments as $payment) {
            $currency = $payment->invoice->currency;
            $monthly[$payment->paid_on->format('Y-m')][$currency]['collected'] ??= 0;
            $monthly[$payment->paid_on->format('Y-m')][$currency]['collected'] += (float) $payment->amount;
        }

        krsort($monthly);

        // Not bound by the date range, so it is summed in SQL rather than loading every unpaid invoice.
        $outstanding = Invoice::query()
            ->selectRaw('customer_id, currency, SUM(total - amount_paid) AS outstanding, COUNT(*) AS invoice_count')
            ->where('status', 'sent')
            ->whereColumn('amount_paid', '<', 'total')
            ->groupBy('customer_id', 'currency')
            ->orderByDesc('outstanding')
            ->limit(10)
            ->with('customer')
            ->get()
            ->map(fn (Invoice $row) => [
                'customer' => $row->customer,
                'currency' => $row->currency,
                'balance' => round((float) $row->outstanding, 2),
                'count' => (int) $row->invoice_count,
            ]);

        $time = TimeEntry::with('user')
            ->whereNotNull('ended_at')
            ->whereBetween('started_at', [$from, $to->copy()->endOfDay()])
            ->get()
            ->groupBy('user_id')
            ->map(fn ($entries) => ['user' => $entries->first()->user, 'minutes' => $entries->sum->minutes])
            ->sortByDesc('minutes');

        return view('pages.reports', [
            'from' => $from,
            'to' => $to,
            'monthly' => $monthly,
            'outstanding' => $outstanding,
            'time' => $time,
            'tickets' => [
                'opened' => Ticket::whereBetween('created_at', [$from, $to->copy()->endOfDay()])->count(),
                'closed' => Ticket::where('status', 'closed')->whereBetween('updated_at', [$from, $to->copy()->endOfDay()])->count(),
                'open_now' => Ticket::where('status', '!=', 'closed')->count(),
            ],
        ]);
    }

    public function paymentsCsv(Request $request)
    {
        [$from, $to] = $this->range($request);

        $payments = Payment::with('invoice.customer')->whereHas('invoice')->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])->orderBy('paid_on')->get();

        return response()->streamDownload(function () use ($payments) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['date', 'invoice', 'customer', 'amount', 'currency', 'method', 'reference']);

            foreach ($payments as $payment) {
                fputcsv($out, array_map([$this, 'csvCell'], [
                    $payment->paid_on->format('Y-m-d'),
                    $payment->invoice?->number,
                    $payment->invoice?->customer?->name,
                    $payment->amount,
                    $payment->invoice?->currency,
                    $payment->method,
                    $payment->reference,
                ]));
            }

            fclose($out);
        }, 'payments-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * A spreadsheet runs a cell that starts with =, +, - or @ as a formula, so a
     * customer named "=HYPERLINK(...)" would execute on open. A leading quote
     * keeps such text as text.
     */
    private function csvCell(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }

    /**
     * The requested date range, defaulting to the year so far.
     */
    private function range(Request $request): array
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        return [
            Carbon::parse($request->query('from', today()->startOfYear()))->startOfDay(),
            Carbon::parse($request->query('to', today()))->startOfDay(),
        ];
    }
}
