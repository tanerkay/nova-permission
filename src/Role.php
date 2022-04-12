<?php

namespace Vyuldashev\NovaPermission;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Spatie\Permission\PermissionRegistrar;

class Role extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string
     */
    public static $model = \Spatie\Permission\Models\Role::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    public static function getModel(): \Spatie\Permission\Contracts\Role
    {
        return app(PermissionRegistrar::class)->getRoleClass();
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group(): string
    {
        return __('nova-permission-tool::navigation.sidebar-label');
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return Gate::allows('viewAny', app(PermissionRegistrar::class)->getRoleClass());
    }

    public static function label(): string
    {
        return __('nova-permission-tool::resources.Roles');
    }

    public static function singularLabel(): string
    {
        return __('nova-permission-tool::resources.Role');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        $guardOptions = collect(config('auth.guards'))->mapWithKeys(function ($value, $key) {
            return [$key => $key];
        });

        $userResource = Nova::resourceForModel(getModelForGuard($this->guard_name));

        return [
            ID::make()->sortable(),

            Text::make(__('nova-permission-tool::roles.name'), 'name')
                ->rules(['required', 'string', 'max:255'])
                ->creationRules('unique:'.config('permission.table_names.roles'))
                ->updateRules('unique:'.config('permission.table_names.roles').',name,{{resourceId}}'),

            Select::make(__('nova-permission-tool::roles.guard_name'), 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)]),

            DateTime::make(__('nova-permission-tool::roles.created_at'), 'created_at')->exceptOnForms(),
            DateTime::make(__('nova-permission-tool::roles.updated_at'), 'updated_at')->exceptOnForms(),

            PermissionBooleanGroup::make(__('nova-permission-tool::roles.permissions'), 'permissions'),

            MorphToMany::make($userResource::label(), 'users', $userResource)
                ->searchable()
                ->singularLabel($userResource::singularLabel()),
        ];
    }
}
