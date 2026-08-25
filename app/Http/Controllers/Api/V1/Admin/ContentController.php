<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    //
}
            'scheduled' => Movie::where('status', 'Post Production')->orWhere('status', 'In Production')->count(),
            'draft' => Movie::where('status', 'Planned')->orWhere('status', 'Canceled')->count(),
        ];

        $response = $paginator->toArray();
        $response['stats'] = $stats;

        return $this->success($response);
    }
}
