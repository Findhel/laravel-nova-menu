<?php

namespace Novius\LaravelNovaMenu\Resources;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Slug;
use Novius\LaravelNovaMenu\Actions\TranslateMenu;
use Novius\LaravelNovaMenu\Filters\Locale;

class Menu extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Novius\LaravelNovaMenu\Models\Menu::class;

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
        'id',
        'name',
    ];

    public static $displayInNavigation = true;

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return trans('laravel-nova-menu::menu.menus_label');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return trans('laravel-nova-menu::menu.menu_singular_label');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(trans('laravel-nova-menu::menu.menu_name'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Slug::make(trans('laravel-nova-menu::menu.slug'), 'slug')
                ->from('name')
                ->rules('required', 'regex:/^[0-9a-z\-_]+$/i'),

            Select::make(trans('laravel-nova-menu::menu.locale'), 'locale')
                ->options(config('laravel-nova-menu.locales', ['en' => 'English']))
                ->rules('in:'.implode(',', array_keys(config('laravel-nova-menu.locales', ['en' => 'English'])))),

            Text::make(trans('laravel-nova-menu::menu.blade_directive'), function () {
                return sprintf('<code class="p-2 bg-30 text-sm text-success">@menu("%s")</code>', $this->slug);
            })
                ->asHtml()
                ->onlyOnIndex(),

            HasMany::make(trans('laravel-nova-menu::menu.menu_items'), 'items', MenuItem::class),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        $locales = config('laravel-nova-menu.locales', ['en' => 'English']);

        return (is_array($locales) && count($locales) > 1) ? [new Locale()] : [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        $locales = config('laravel-nova-menu.locales', ['en' => 'English']);
        if (count($locales) <= 1) {
            return [];
        }

        return [
            (new TranslateMenu())->onlyInline(),
        ];
    }
}
