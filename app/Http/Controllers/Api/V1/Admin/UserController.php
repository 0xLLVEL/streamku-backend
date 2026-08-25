<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
}
            'trial' => floor(User::count() * 0.1),
            'churn' => '2.3%',
        ];

        $response = $users->toArray();
        $response['stats'] = $stats;

        return response()->json($response);
    }
}
