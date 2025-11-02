<?php

    namespace App\Http\Controllers;

    use App\Enums\Ask;
    use App\Http\Resources\IngredientResource;
    use App\Http\Resources\ProductionItemResource;
    use App\Models\Ingredient;
    use App\Models\Item;
    use App\Models\Product;
    use App\Models\ProductionProcess;
    use App\Models\ProductionSetup;

    class ProductionController extends Controller
    {
        public function items()
        {
            return ProductionItemResource::collection(Product::all());
        }

        public function processing()
        {
            return ProductionItemResource::collection(ProductionSetup::all());
        }

        public function ingredients()
        {
            return IngredientResource::collection(Ingredient::all());
        }
    }
