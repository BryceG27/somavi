<?php

return [
    'apartment_image_max_kb' => (int) env('APARTMENT_IMAGE_MAX_KB', 20480),
    'apartment_image_webp_quality' => (int) env('APARTMENT_IMAGE_WEBP_QUALITY', 82),
    'apartment_image_max_width' => (int) env('APARTMENT_IMAGE_MAX_WIDTH', 2560),
    'apartment_image_max_height' => (int) env('APARTMENT_IMAGE_MAX_HEIGHT', 2560),
];
