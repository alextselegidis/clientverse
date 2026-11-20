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

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('customer');

        $q = $request->query('q');

        if ($q) {
            $query->where(function ($query) use ($q) {
                $query->where('description', 'like', "%{$q}%")->orWhere('category', 'like', "%{$q}%");
            });
        }

        $category = $request->query('category');

        if ($category) {
            $query->where('category', $category);
        }

        sort_query($query, $request->query('sort', 'expense_date'), $request->query('direction', 'desc'));

        return view('pages.expenses', [
            'expenses' => $query->paginate(25)->withQueryString(),
            'q' => $q,
            'category' => $category,
            'categories' => Expense::categories(),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.expenses-edit', [
            'expense' => new Expense([
                'expense_date' => today(),
                'customer_id' => $request->query('customer_id'),
                'project_id' => $request->query('project_id'),
            ]),
        ] + $this->options());
    }

    public function store(Request $request)
    {
        $expense = Expense::create($this->validateExpense($request));

        return redirect(route('expenses.edit', $expense->id))->with('success', __('record_saved_message'));
    }

    public function edit(Expense $expense)
    {
        return view('pages.expenses-edit', ['expense' => $expense->load('invoice')] + $this->options());
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validateExpense($request));

        return redirect(route('expenses.edit', $expense->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect(route('expenses'))->with('success', __('record_deleted_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:expenses,id',
        ]);

        Expense::whereIn('id', $request->input('ids'))->delete();

        return redirect(route('expenses'))->with('success', __('records_deleted_message'));
    }

    public function bill(Expense $expense)
    {
        if (!$expense->billable || !$expense->customer_id) {
            return back()->with('error', __('expense_not_billable_message'));
        }

        return redirect(route('invoices.edit', $expense->bill()->id))->with('success', __('record_saved_message'));
    }

    private function options(): array
    {
        return [
            'customers' => Customer::toOptions(),
            'projects' => Project::toOptions(),
            'categories' => Expense::categories(),
        ];
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billable' => 'boolean',
            'customer_id' => 'nullable|required_if_accepted:billable|exists:customers,id',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string',
        ]) + ['billable' => false];
    }
}
