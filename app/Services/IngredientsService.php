<?php

namespace App\Services;


use App\Http\Requests\IngredientRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\Ingredient;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IngredientsService
{
    public $ingredient;
    protected $itemFilter = [
        'name',
    ];
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Ingredient::orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function store(IngredientRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->ingredient = Ingredient::create($request->validated());
                activityLog('Created Raw material: ' . $this->ingredient->name);
            });
            return $this->ingredient;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }
    public function update(IngredientRequest $request, Ingredient $ingredient): Ingredient
    {
        try {
            DB::transaction(function () use ($request, $ingredient) {
                $ingredient->update($request->validated());
                activityLog('Updated Raw material: ' . $ingredient->name);
            });
            return Ingredient::find($ingredient->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function destroy(Ingredient $ingredient)
    {
        try {
            DB::transaction(function () use ($ingredient) {
                $ingredient->delete();
                activityLog('Deleted Raw material: ' . $ingredient->name);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function show(Ingredient $ingredient): Ingredient
    {
        try {
            return $ingredient;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

}
