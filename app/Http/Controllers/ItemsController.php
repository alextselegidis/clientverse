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

use App\Models\Item;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        $q = $request->query('q');

        if ($q) {
            $query->where('description', 'like', "%{$q}%");
        }

        sort_query($query, $request->query('sort', 'description'), $request->query('direction', 'asc'));

        return view('pages.items', [
            'items' => $query->paginate(25)->withQueryString(),
            'q' => $q,
        ]);
    }

    public function create()
    {
        return view('pages.items-edit', ['item' => new Item(['tax_rate' => 0])]);
    }

    public function store(Request $request)
    {
        Item::create($this->validateItem($request));

        return redirect(route('items'))->with('success', __('record_saved_message'));
    }

    public function edit(Item $item)
    {
        return view('pages.items-edit', ['item' => $item]);
    }

    public function update(Request $request, Item $item)
    {
        $item->update($this->validateItem($request));

        return redirect(route('items'))->with('success', __('record_saved_message'));
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect(route('items'))->with('success', __('record_deleted_message'));
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'description' => 'required|string|max:255',
            'unit_price' => 'required|numeric',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);
    }
}
