<?php

namespace Vyuldashev\NovaPermission;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class AttachToRole extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $role = Role::getModel()->find($fields['role']);
        foreach ($models as $model) {
            $role->givePermissionTo($model);
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Role')
                ->options(Role::getModel()->get()->pluck('name', 'id')->toArray())
                ->displayUsingLabels(),
        ];
    }
}
