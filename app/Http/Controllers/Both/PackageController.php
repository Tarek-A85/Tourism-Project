<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\PackageArea;
use App\Models\Region;
use App\Models\TypeOfPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    private function add_to_package_area($packageId, $visitable, $type)
    {
        $period = null;
        if ($visitable['period'] != 0)
            $period = $visitable['period'];

        PackageArea::create([
            'package_id' => $packageId,
            'visitable_id' => $visitable['id'],
            'visitable_type' => $type,
            'period' => $period
        ]);
    }

    private function update_package_area($packageId, $visitable, $type)
    {
        $period = null;
        if ($visitable['period'] != 0)
            $period = $visitable['period'];

        PackageArea::where([
            ['package_id', '=', $packageId],
            ['visitable_id', '=', $visitable['id']],
            ['visitable_type', '=', $type]
        ])->first()->update([
            'period' => $period
        ]);
    }

    public function index()
    {
        $packages = Package::latest()->filter(request(['search','type']))->OrderBy('id', 'DESC')->get();
        $packages->append(['countries', 'image']);
        $packages->setHidden(['package_areas', 'deleted_at', 'updated_at', 'created_at', 'description']);
        return $this->success('All packages', ['packages' => $packages]);
    }

    public function index_archived()
    {
        $packages = Package::onlyTrashed()->latest()->filter(request(['search','type']))->select('id', 'name')->get();
        $packages->setHidden(['package_areas']);
        return $this->success('All archived packages', ['packages' => $packages]);
    }

    public function show($id)
    {
        if (auth()->user()->is_admin)
            $package = Package::withTrashed()->with(['types:id,name', 'companies','package_areas'])->findOrFail($id);
        else
            $package = Package::with(['types:id,name', 'companies','package_areas'])->findOrFail($id);

        $package->makeVisible('package_areas');
        $package->images = $package->images;

        return $this->success('Package informations', ['package' => $package]);
    }

    public function store(Request $request)
    {
        $messages = [
            "hotels.*.id" => "The selected hotels id is invalid",
            "region.*.id" => "The selected regions id is invalid",
            'flight_companies.*' => 'The selected company id is invalid',
            "photos.*.image" => "The inserted files must all be images"
        ];
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('packages')],
            'description' => ['required'],
            'adult_price' => ['required', 'decimal:2'],
            'child_price' => ['required', 'decimal:2'],
            'period' => ['required', 'numeric', 'gte:0'],
            'hotels' => ['array', 'nullable'],
            'hotels.*.id' => ['required', 'numeric', Rule::exists('hotels', 'id')],
            'hotels.*.period' => ['required', 'numeric'],
            'regions' => ['array', 'required'],
            'regions.*.id' => ['required', 'numeric', Rule::exists('regions', 'id')],
            'regions.*.period' => ['required', 'numeric'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['required', 'image'],
            'types' => ['required', 'array'],
            'types.*' => ['required', 'string'],
            'flight_companies' => ['nullable', 'array'],
            'flight_companies.*' => ['required', 'numeric', Rule::exists('companies', 'id')]
        ], $messages);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $package = Package::create([
            'name' => $request->name,
            'description' => $request->description,
            'adult_price' => $request->adult_price,
            'child_price' => $request->child_price,
            'period' => $request->period,
        ]);

        $package->companies()->syncWithoutDetaching($request->flight_companies);

        foreach ($request->hotels as $hotel) {
            $this->add_to_package_area($package->id, $hotel, 'Hotel');
        }

        foreach ($request->regions as $region) {
            $this->add_to_package_area($package->id, $region, 'Region');
        }

        foreach ($request->types as $type) {
            $find = TypeOfPackage::firstOrCreate(['name' => $type]);
            $package->types()->syncWithoutDetaching($find->id);
        }

        if ($request->photos) {
            $folder = Folder::firstOrCreate([
                "name" => $package->name,
                "folder_id" => 2,
            ]);
            foreach ($request->photos as $photo) {
                $this->save_image($photo, 'Packages', $package->name, $folder->id);
            }
        }

        return $this->success('packege is added successfully');
    }

    public function update($id, Request $request)
    {
        $package = Package::withTrashed()->findOrFail($id);
        $messages = [
            'added_hotels.*.id' => 'The selected added hotels id is invalid',
            'modify_hotels.*.id' => 'The selected modify hotels id is not in this package',
            'deleted_hotels.*'  => 'The selected delete hotels id is not in this package',
            'added_regions.*.id' => 'The selected added regions id is invalid',
            'modify_regions.*.id' => 'The selected modify regions id is not in this package',
            'deleted_regions.*'  => 'The selected delete regions id is not in this package',
            'added_photos.*' => 'The inserted files must all be images',
            'added_companies.*' => 'The selected  companies id are not invalid',
            'deleted_companies.*' => 'The selected delete company id is not in this package'
        ];

        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('packages', 'name')->where(fn ($query) => $query->where('id', '!=', $package->id))],
            'description' => ['required'],
            'period' => ['required', 'numeric', 'gte:0'],
            'adult_price' => ['required', 'decimal:2'],
            'child_price' => ['required', 'decimal:2'],
            'deleted_hotels' => ['array', 'nullable'],
            'deleted_hotels.*' => [
                'required', 'numeric',
                Rule::exists('package_areas', 'visitable_id')
                    ->where(fn ($query) => $query->where('package_id', $package->id)
                        ->where('visitable_type', 'Hotel'))
            ],
            'modify_hotels' => ['array', 'nullable'],
            'modify_hotels.*.id' => [
                'required', 'numeric',
                Rule::exists('package_areas', 'visitable_id')
                    ->where(fn ($query) => $query->where('package_id', $package->id)
                        ->where('visitable_type', 'Hotel'))
            ],
            'modify_hotels.*.period' => ['required', 'numeric'],
            'added_hotels' => ['array', 'nullable'],
            'added_hotels.*.id' => [
                'required', Rule::exists('hotels', 'id'),
                Rule::unique('package_areas', 'visitable_id')
                    ->where(fn ($query) => $query->where('package_id', $package->id)
                        ->where('visitable_type', 'Hotel'))
            ],
            'added_hotels.*.period' => ['required', 'numeric'],
            'deleted_regions' => ['array', 'nullable'],
            'deleted_regions.*' => [
                'required', 'numeric',
                Rule::exists('package_areas', 'visitable_id')
                    ->where(fn ($query) => $query->where('package_id', $package->id)
                        ->where('visitable_type', 'Region'))
            ],
            'modify_regions' => ['array', 'nullable'],
            'modify_regions.*.id' => [
                'required', 'numeric',
                Rule::exists('package_areas', 'visitable_id')
                    ->where(fn ($query) => $query->where('package_id', $package->id)
                        ->where('visitable_type', 'Region'))
            ],
            'modify_regions.*.period' => ['required', 'numeric'],
            'added_regions' => ['array', 'nullable'],
            'added_regions.*.id' => [
                'required', Rule::exists('regions', 'id'),
                Rule::unique('package_areas', 'visitable_id')
                    ->where(fn ($query) => $query->where('package_id', $package->id)
                        ->where('visitable_type', 'Region'))
            ],
            'added_regions.*.period' => ['required', 'numeric'],
            'deleted_photos' => ['nullable', 'array'],
            'deleted_photos.*' => ['required', Rule::exists('photos', 'id')],
            'added_photos' => ['nullable', 'array'],
            'added_photos.*' => ['required', 'image'],
            'deleted_types' => ['nullable', 'array'],
            'deleted_types.*' => [
                'required',
                Rule::exists('package_type', 'type_id')
                    ->where(fn ($query) => $query->where('package_id', $package->id))
            ],
            'added_types' => ['nullable', 'array'],
            'added_types.*' => ['required', 'string', 'regex:/^[a-zA-Z ]+$/'],
            'deleted_companies' => ['nullable', 'array'],
            'deleted_companies.*' => [
                'required',
                Rule::exists('company_package', 'company_id')
                    ->where(fn ($query) => $query->where('package_id', $package->id))
            ],
            'added_companies' => ['nullable', 'array'],
            'added_companies.*' => ['required', 'numeric', Rule::exists('companies', 'id')]
        ], $messages);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        if ($package->name !== $request->name && File::exists(storage_path('app/Packages/' . $package->name))) {
            $oldName = $package->name;
            $newName = $request->name;
            Folder::where('name', $oldName)->update([
                'name' => $newName
            ]);
            File::move(storage_path('app/Packages/' . $oldName), storage_path('app/Packages/' . $newName));
        }

        $package->update([
            'name' => $request->name,
            'description' => $request->description,
            'adult_price' => $request->adult_price,
            'child_price' => $request->child_price,
        ]);

        if ($request->deleted_hotels) {
            foreach ($request->deleted_hotels as $hotelId) {
                Hotel::where('id', $hotelId)->first()->package_areas()->delete();
            }
        }

        if ($request->modify_hotels) {
            foreach ($request->modify_hotels as $hotel) {
                $this->update_package_area($package->id, $hotel, 'Hotel');
            }
        }

        if ($request->added_hotels) {
            foreach ($request->added_hotels as $hotel) {
                $this->add_to_package_area($package->id, $hotel, 'Hotel');
            }
        }

        if ($request->deleted_regions) {
            foreach ($request->deleted_regions as $regionId) {
                Region::where('id', $regionId)->first()->package_areas()->delete();
            }
        }

        if ($request->modify_regions) {
            foreach ($request->modify_regions as $region) {
                $this->update_package_area($package->id, $region, 'Region');
            }
        }

        if ($request->added_regions) {
            foreach ($request->added_regions as $region) {
                $this->add_to_package_area($package->id, $region, 'Region');
            }
        }

        if ($request->deleted_photos) {
            foreach ($request->deleted_photos as $photoId) {
                $this->delete_image($photoId);
            }
        }

        if ($request->added_photos) {
            $folder = Folder::firstOrCreate([
                "name" => $package->name,
                "folder_id" => 2,
            ]);
            foreach ($request->added_photos as $photo) {
                $this->save_image($photo, 'Packages', $package->name, $folder->id);
            }
        }

        if ($request->deleted_types)
            $package->types()->detach($request->deleted_types);

        if ($request->added_types) {
            foreach ($request->added_types as $type) {
                $find = TypeOfPackage::firstOrCreate([
                    "name" => $type,
                ]);
                $package->types()->syncWithoutDetaching($find->id);
            }
        }

        if ($request->deleted_companies)
            $package->companies()->detach($request->deleted_companies);

        if ($request->added_companies) {
            $package->companies()->syncWithoutDetaching($request->added_companies);
        }

        return $this->success('Package is updated successfully');
    }

    public function archive(Package $package)
    {
        // delete every tipe
        $trips = $package->trip_detail;
        if (!empty($trips))
            foreach ($trips as $trip) {
                if ($trip->date->date > now()) {
                    $trip->delete();
                }
            }

        $package->delete();
        return $this->success('package is temporariy deleted successfully and all its tips is canclled');
    }

    public function restore_archived($id)
    {
        $package = Package::onlyTrashed()->findOrFail($id);

        $package->restore();

        return $this->success("Package $package->name restored");
    }

    public function destroy($id)
    {
        $package = Package::withTrashed()->findOrFail($id);

        $trips = $package->trip_detail;
        if (!empty($trips)) {
            foreach ($trips as $trip) {
                if ($trip->date->date < now() && sizeof($trip->packageTransaction))
                    return $this->fail("You can't permenentaly delete this Package, it's used at some places");
            }
        }

        if (File::exists(storage_path("app/Packages/$package->name"))) {
            Folder::where([['name', '=', $package->name], ['folder_id', '=', 2]])->first()->delete();
            Storage::deleteDirectory("Packages/$package->name");
        }
        $package->forceDelete();

        return $this->success('Package is permanently deleted successfully');
    }
}
