<?php

namespace App\Actions;

//    php artisan make:class Actions/HighlightTasks
use App\Jobs\ProcessHighlightUpload;
use App\Models\game\Highlight;

class HighlightTasks // This class handles the actions related to highlights, such as uploading and updating highlights. TAPI BELUM DIGUNAKAN
{
    public static function upload(int $statId, ?int $highlightId, string $tempPath, ?string $notes): void
    {
        ProcessHighlightUpload::dispatch($statId, $highlightId, $tempPath, $notes);
    }

    public static function update(int $highlightId, ?string $notes): void
    {
        Highlight::where('id', $highlightId)->update(['notes' => $notes]);
    }
}
