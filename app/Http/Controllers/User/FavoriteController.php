<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\Region;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function index()
    {
        //return all favaorite list with name and id
        $user = auth()->user();
        $lists = $user->lists->select('id', 'name');
        return $this->success('All favorite lists', ['lists' => $lists]);
    }

    public function show($id)
    {
        //return list info
        $list = FavoriteList::with('favorites')->where('id', $id)->firstOrFail();
        if ($list->user_id !== auth()->user()->id)
            return $this->fail('You are not authorized');
        $list->favorites->load('favorable');
        foreach ($list->favorites as $item) {
            switch ($item->favorable_type) {
                case 'Hotel': {
                        $item->image = Hotel::find($item->favorable_id)->images[0];
                        break;
                    }
                case 'Package': {
                        $images = Package::find($item->favorable_id)->images;
                        if (empty($images))
                            $item->image = null;
                        else
                            $item->image = $images[0];
                        break;
                    }
                case 'Compane': {
                        $item->image = Company::find($item->favorable_id)->images[0];
                        break;
                    }
                case 'Region': {
                        $region = Region::where('id', $item->favorable_id)->first();
                        if ($region->region_id)
                            $item->image = $region->images[0];
                        else
                            $item->image = null;
                        break;
                    }
            }
        }

        return $this->success('favorite list items', ['lists' => $list]);
    }

    public function store(Request $request)
    {
        //create new list for user
        //validator
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('lists', 'name')->where(fn ($query) => $query->where('user_id', auth()->user()->id))]
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }
        //create list for this user
        $list = FavoriteList::create([
            'name' => $request->name,
            'user_id' => auth()->id()
        ]);

        //return message
        return $this->success("your favortie list $request->name created successfully");
    }

    public function update($id, Request $request)
    {
        //update name favorite list
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('lists', 'name')->where(fn ($query) => $query->where('user_id', auth()->user()->id))]
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $list = FavoriteList::findOrfail($id);

        if ($list->name == 'default' || $list->user_id !== auth()->user()->id) {
            return $this->fail('you are un authorized to update list name');
        }

        $oldname = $list->name;

        $list->update([
            'name' => $request->name
        ]);

        return $this->success("list name changed from $oldname to $request->name");
    }

    public function add_to_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'list_id' => ['required', Rule::exists('lists', 'id')->where('user_id', auth()->user()->id)],
            'item_type' => ['required', Rule::in(['packages', 'hotels', 'regions', 'companies'])],
            'item_id' => ['required', Rule::exists($request->item_type, 'id')]
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $list = FavoriteList::findOrFail($request->list_id);

        $morphTypeMape = [
            'packages' => 'Package',
            'hotels' => 'Hotel',
            'regions' => 'Region',
            'companies' => 'Company'
        ];

        if (Favorite::where('list_id', $list->id)
            ->where('favorable_id', $request->item_id)
            ->where('favorable_type', $morphTypeMape[$request->item_type])->first()
        ) {
            return $this->fail('this item already in this favorate list');
        }

        Favorite::create([
            'list_id' => $list->id,
            'favorable_type' => $morphTypeMape[$request->item_type],
            'favorable_id' => $request->item_id
        ]);

        return $this->success("The {$morphTypeMape[$request->item_type]} added to your favorite list {$list->name}");
    }

    public function remove_from_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deleted' => ['array', 'required'],
            'deleted.*' => [
                'required', Rule::exists('Favorites', 'id'),
                function (string $attribute, mixed $value, Closure $fail) {
                    $favorite = Favorite::findOrFail($value);
                    if ($favorite->list->user_id !== auth()->user()->id) {
                        $fail("you are un authorized to delete these items.");
                    }
                }
            ]
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        foreach ($request->deleted as $id) {
            Favorite::where('id', $id)->first()->delete();
        }

        return $this->success('the items removed from favorite successfully');
    }

    public function destroy(Request $request)
    {
        $message = [
            'deleted_lists.*' => 'the selected list id is invaled'
        ];

        $validator = Validator::make($request->all(), [
            'deleted_lists' => ['required', 'array'],
            'deleted_lists.*' => [
                'required',
                'numeric',
                Rule::exists('lists','id')
                    ->where(fn ($query) => $query->where('user_id',auth()->user()->id)->where('name','!=','default'))
            ]
        ], $message);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        foreach ($request->deleted_lists as $id) {
            FavoriteList::where('id', $id)->delete();
        }

        return $this->success('the favorite lists deleted successfully');
    }
}
