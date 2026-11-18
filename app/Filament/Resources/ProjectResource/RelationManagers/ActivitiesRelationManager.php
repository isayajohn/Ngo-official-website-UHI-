<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

class ActivitiesRelationManager extends ProjectOperationsRelationManager
{
    protected static string $relationship = 'activities';
    protected string $operationType = 'activity';
}
