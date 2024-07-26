<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use App\Models\TypeOfPackage;
use Illuminate\Http\Request;

class PackageTypeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $types = TypeOfPackage::all()->select('id','name');
        return $this->success('all package\'s types',$types);
    }
}
