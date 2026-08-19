<?php echo json_encode(App\Models\Movie::where('title', 'like', '%La La Land%')->first()->toArray());
