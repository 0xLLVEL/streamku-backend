<?php \ = App\Models\Movie::where('slug', 'la-la-land')->first(); echo json_encode((new App\Http\Controllers\Api\V1\MovieController())->show(\)->getData());
