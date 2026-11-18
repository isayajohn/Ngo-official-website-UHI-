<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

class RisksRelationManager extends ProjectOperationsRelationManager
{
    protected static string $relationship = 'risks';
    protected string $operationType = 'risk';
}
