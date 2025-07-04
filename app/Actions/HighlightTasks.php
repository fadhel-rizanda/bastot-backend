<?php

namespace App\Actions;

//    php artisan make:class Actions/HighlightTasks
use App\Jobs\ProcessHighlightUpload;
use App\Models\game\Highlight;

class HighlightTasks
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
