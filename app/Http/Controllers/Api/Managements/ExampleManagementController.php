<?php
namespace App\Http\Controllers\Api\Managements;

use App\Http\Controllers\Controller;
use App\Models\ExampleManagement;
use Illuminate\Http\Request;
use App\Http\Requests\ExampleManagement\StoreExampleManagementRequest;
use App\Http\Requests\ExampleManagement\UpdateExampleManagementRequest;

class ExampleManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = ExampleManagement::query();
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%$search%");
        }
        $examples = $query->orderByDesc('created_at')->paginate($request->input('per_page', 10));
        return response()->json($examples);
    }

    public function store(StoreExampleManagementRequest $request)
    {
        $example = ExampleManagement::create($request->validated());
        return response()->json($example, 201);
    }

    public function show(ExampleManagement $exampleManagement)
    {
        return response()->json($exampleManagement);
    }

    public function update(UpdateExampleManagementRequest $request, ExampleManagement $exampleManagement)
    {
        $exampleManagement->update($request->validated());
        return response()->json($exampleManagement);
    }

    public function destroy(ExampleManagement $exampleManagement)
    {
        $exampleManagement->delete();
        return response()->json(['message' => 'Soft deleted']);
    }
}
