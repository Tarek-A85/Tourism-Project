<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use App\Models\PackageArea;
use App\Models\Region;
use App\Models\TripDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TrackingController extends Controller
{
    private function is_trip_start(TripDetail $trip): bool
    {
        if ($trip->date->date > now()->toDateString())
            return false;
        if ($trip->time > now()->toTimeString())
            return false;
        return true;
    }

    public function tracking(TripDetail $trip)
    {
        if (!$this->is_trip_start($trip))
            return $this->fail('The trip has not started yet');

        $areas = $trip->areas();
        $track = [];
        if ($trip->current_area == -1) {
            return $this->fail('you can track this trip ,it is already finished');
        }

        if ($trip->auto_tracking ?? true) {
            $hours = now()->diff($trip->date->date)->format('%d') * 24 + now()->diff($trip->time)->format('%h.%i');
            $hours += $trip->delay;
            $count = 0;
            foreach ($areas as $i => $area) {
                $count++;
                if ($hours < 0)
                    $track[] = ['name' => $area['name'], 'status' => 0];

                else if (!array_key_exists('period', $area)) {
                    if ($trip->current_area <= $count) {
                        $track[] = ['name' => $area['name'], 'status' => 1];
                        $trip->current_area = $count;
                        $trip->save();
                        $hours = -1;
                    } else {
                        $track[] = ['name' => $area['name'], 'status' => 2];
                    }
                } else if ($hours < $area['period']) {
                    $track[] = ['name' => $area['name'], 'status' => 1];
                    $hours = $hours - $area['period'];
                    $trip->current_area = $count;
                    $trip->save();
                } else {
                    $track[] = ['name' => $area['name'], 'status' => 2];
                    $hours = $hours - $area['period'];
                }
            }
            if ($hours > 0) {
                $trip->current_area = -1;
                $trip->save();
            }
        } else {

            $status = 2;
            $count = 0;
            foreach ($areas as $area) {
                $count++;
                if ($count == $trip->current_area) {
                    $track[] = ['name' => $area['name'], 'status' => 1];
                    $status = 0;
                    continue;
                }
                $track[] = ['name' => $area['name'], 'status' => $status];
            }
        }
        return $this->success('tracking the trip', ['tracking' => $track]);
    }

    public function update(TripDetail $trip, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_area' => ['sometimes', 'required', Rule::exists('regions', 'name')],
            'auto_tracking' => [!$request->current_area ? 'required' : 'nullable', 'boolean'],
            'end_trip' => ['required', 'boolean']
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }
        if (!$this->is_trip_start($trip))
            return $this->fail('The trip has not started yet');
        if ($trip->current_area == -1)
            return $this->fail('you can update tracking for finished trip');


        if ($request->end_trip) {
            $trip->update([
                'current_area' => -1
            ]);
            return $this->success('the tripe finished successfully');
        }

        if ($request->auto_tracking || !$request->current_area) {
            $allPackageTime = 0;
            $realPackageTime = now()->diff($trip->date->date)->format('%d') * 24 + now()->diff($trip->time)->format('%h.%i');
            $count = 0;

            foreach ($trip->areas() as $area) {
                $count++;
                $allPackageTime += $area['period'] ?? 0;
                if ($count == $trip->current_area) {
                    break;
                }
            }

            $trip->update([
                'current_area' => $count,
                'delay' => $allPackageTime - $realPackageTime,
                'auto_tracking' => 1
            ]);

            return $this->success('tracking changed to aout tracking mode');
        }

        $area_id = Region::where('name', $request->current_area)->first()->id;

        if (!PackageArea::where('package_id', $trip->package_id)->where('visitable_type', 'Region')->where('visitable_id', $area_id)->first()) {
            return $this->fail("this is not a region in this trip.");
        }


        $currentAreaIndex = 0;
        foreach ($trip->areas() as $area) {
            $currentAreaIndex++;
            if ($area['name'] == $request->current_area)
                break;
        }

        $trip->update([
            'current_area' => $currentAreaIndex,
            'auto_tracking' => 0
        ]);

        return $this->success('Tracking trip updated successfully');
    }
}
