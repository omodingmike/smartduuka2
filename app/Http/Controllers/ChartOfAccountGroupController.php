<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\StoreChartOfAccountGroupRequest;
    use App\Http\Requests\UpdateChartOfAccountGroupRequest;
    use App\Http\Resources\ChartOfAccountGroupResource;
    use App\Models\ChartOfAccountGroup;
    use Exception;
    use Illuminate\Support\Facades\Log;

    class ChartOfAccountGroupController extends Controller
    {
        public function index(PaginateRequest $request)
        {
            try {
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                return ChartOfAccountGroupResource::collection(ChartOfAccountGroup::whereNull('parent_id')
                                                                                  ->with([ 'childrenRecursive' , 'ledgers' ])
                                                                                  ->$method($methodValue));
            } catch ( \Exception $e ) {
                Log::info($e->getMessage());
                throw new Exception($e->getMessage() , 422);
            }
        }

        public function groups()
        {
            return ChartOfAccountGroupResource::collection(ChartOfAccountGroup::all());
        }


        public function store(StoreChartOfAccountGroupRequest $request)
        {
            ChartofaccountGroup::create($request->validated());
        }

        public function show(ChartOfAccountGroup $ledger_group)
        {
            return new ChartOfAccountGroupResource($ledger_group);
        }

        public function update(UpdateChartOfAccountGroupRequest $request , ChartOfAccountGroup $ledger_group)
        {
            $ledger_group->update($request->validated());
        }

        public function destroy(ChartOfAccountGroup $ledger_group)
        {
            $ledger_group->delete();
        }
    }
