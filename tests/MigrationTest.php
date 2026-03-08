<?php

use Illuminate\Support\Facades\DB;

it('creates an index for thread timeline pagination', function () {
    $indexes = collect(DB::select("PRAGMA index_list('chat_field_messages')"))
        ->map(fn (object $index): string => $index->name)
        ->values()
        ->all();

    expect($indexes)->toContain('chat_field_messages_thread_created_at_index');
});
