<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use App\Models\{
    Date,
    Package,
    TripDetail
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TripController extends Controller
{
    public function index(Package $package)
    {
        $trips = $package->trip_detail;

        if (!auth()->user()->is_admin) {
            $trips = $trips->where('date.date', '>', now());
            $trips->makeHidden(['auto_tracking']);
        }

        return $this->success("All {$package->name} package trips", $trips);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => ['required', Rule::exists('packages', 'id')],
            'num_of_tickets' => ['required', 'gte:5', 'lte:100', 'integer'],
            'date' => ['required', 'date', 'after:today', Rule::exists('dates', 'date')],
            'time' => ['required', 'date_format:H:i:s']
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        if (Package::onlyTrashed()->where('id', $request->package_id)->first())
            return $this->fail('You can add a trip, the package is archives');

        $date_id = Date::where('date', $request->date)->first()->id;

        if (
            TripDetail::where('package_id', $request->package_id)
            ->where('date_id', $date_id)
            ->where('time', $request->time)->first()
        ) {
            return $this->fail('there is a trip at the same time for this package');
        }

        TripDetail::create([
            'package_id' => $request->package_id,
            'date_id' => $date_id,
            'time' => $request->time,
            'num_of_tickets' => $request->num_of_tickets,
            'available_tickets' => $request->num_of_tickets,
        ]);

        return $this->success('The trip added successfully');
    }

    public function update(TripDetail $trip, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'num_of_tickets' => ['required', 'gte:5', 'lte:100', 'integer'],
            'date' => ['required', 'date', 'after:today', Rule::exists('dates', 'date')],
            'time' => ['required', 'date_format:H:i:s']
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        if ($trip->date->date <= now()) {
            return $this->fail('You can\'t update a trip already started');
        }


        $date_id = Date::where('date', $request->date)->first()->id;

        if (
            TripDetail::where('package_id', $trip->package_id)
            ->where('date_id', $date_id)
            ->where('time', $request->time)->first()
        ) {
            return $this->fail('there is a trip at the same time for this package');
        }

        $reservedTickets = ($trip->num_of_tickets - $trip->available_tickets);
        $available_tickets = $request->num_of_tickets - $reservedTickets;

        if ($request->num_of_tickets <= $reservedTickets) {
            //cancel some tickets
            $available_tickets = 0;
        }

        $trip->update([
            'date_id' => $date_id,
            'time' => $request->time,
            'num_of_tickets' => $request->num_of_tickets,
            'available_tickets' => $available_tickets,
        ]);

        return $this->success('The trip update successfully');
    }

    //update delete condition
    public function destroy($id)
    {
        $trip = TripDetail::where('id', $id)->firstOrFail();
        if ($trip->date->date < now() /*&& there is a transaction*/) {
            return $this->fail('You can\'t delete a trip already started');
        }
        //cancel all transaction then delete
        $trip->delete();
        return $this->success('The trip deleted successfully');
    }
}
