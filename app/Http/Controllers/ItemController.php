<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $items = Item::all();
        return response()->json($items);
    }

    public function show($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    public function store(Request $request) {
        $this->validate($request, [
            'name' => 'required|unique:items',
        ]);

        $item = Item::create($request->all());

        return response()->json(['message' => 'Item created', 'id' => $item->id], 201);
    }

    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $this->validate($request, [
            'name' => 'required|unique:items',
        ]);

        $item->update($request->all());

        return response()->json(['message' => 'Item updated']);
    }

    public function destroy($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }
}
