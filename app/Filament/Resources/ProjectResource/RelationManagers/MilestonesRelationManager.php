<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

class MilestonesRelationManager extends ProjectOperationsRelationManager
{
    protected static string $relationship = 'milestones';
    protected string $operationType = 'milestone';
}
