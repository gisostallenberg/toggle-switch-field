<?php
namespace Naif\ToggleSwitchField\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class ToggleController
{
    public function update(NovaRequest $request)
    {
        $resourceClass = Nova::resourceForKey($request->resource_name);
        /** @var Model $resource */
        $resource = $resourceClass::newModel()->findOrFail($request->resource_id);
        $attribute = $request->input('attribute');
        $value = $request->input('new_value');

        $isFillable = in_array($attribute, $resource->getFillable());
        if (Str::contains($attribute, '->')) {
            $isFillable = in_array(Str::before($attribute, '->'), $resource->getFillable());
        }

        if (!$isFillable) {
            return response()->json(['error' => 'Invalid attribute. Make sure column is a fillable field.'], 400);
        }

        $wasTimeStamped = $resource->timestamps;
        $resource->setAttribute($attribute, $value);
        try {
            $resource->timestamps = false;
            $resource->save();
        } finally {
            $resource->timestamps = $wasTimeStamped;
        }
    }
}
