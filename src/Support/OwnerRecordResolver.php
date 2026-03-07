<?php

namespace Toolborg\ChatField\Support;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Contracts\CanEntangleWithSingularRelationships;
use Illuminate\Database\Eloquent\Model;

class OwnerRecordResolver
{
    public function resolve(Component $component): ?Model
    {
        $directRecord = $component->getRecord(withContainerRecord: false);

        if ($directRecord instanceof Model && $directRecord->exists) {
            return $directRecord;
        }

        $parentComponent = $component->getContainer()->getParentComponent();

        while ($parentComponent !== null) {
            if ($parentComponent instanceof CanEntangleWithSingularRelationships && $parentComponent->hasRelationship()) {
                $record = $parentComponent->getCachedExistingRecord();

                return ($record instanceof Model && $record->exists) ? $record : null;
            }

            $parentComponent = $parentComponent->getContainer()->getParentComponent();
        }

        $record = $component->getRecord();

        return ($record instanceof Model && $record->exists) ? $record : null;
    }
}
