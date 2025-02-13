<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Resources\CompanyCollection;
use App\Http\Resources\CompanyResource;
use App\Models\BizMatch\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Company::query();

        $data = $query->paginate($request->input('limit', 30));

        return (new CompanyCollection($data))->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $valid = $this->validate($request, [
            'image' => 'nullable|image|mimes:png,jpg,jpeg',
            'name' => 'required|string',
            'description' => 'required|string',
            'industry_category' => 'required|string',
            'country' => 'required|string',
            'location' => 'required|string',
            'services' => 'nullable|array',
            'services.*' => 'required|string',
            'conference_objectives' => 'required|string',
        ]);

        /** @var \App\Models\BizMatch\Company $company */
        $company = $user->company()->firstOrCreate($valid);
        $user->reg_status = 'ongoing';
        $user->saveQuietly();

        return (new CompanyResource($company))->additional([
            'status' => 'success',
            'message' => __('Your company ":0" has been created successfully.', [$company->name]),
            'statusCode' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return (new CompanyResource($company))->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var \App\Models\BizMatch\Company $company */
        $company = $user->company()->where('id', $id)->firstOrFail();

        $valid = $this->validate($request, [
            'image' => 'nullable|image|mimes:png,jpg,jpeg',
            'name' => 'required|string',
            'description' => 'required|string',
            'industry_category' => 'required|string',
            'country' => 'required|string',
            'location' => 'required|string',
            'services' => 'nullable|array',
            'services.*' => 'required|string',
            'conference_objectives' => 'required|string',
        ]);

        $company->name = $valid['name'];
        $company->description = $valid['description'];
        $company->industry_category = $valid['industry_category'];
        $company->country = $valid['country'];
        $company->location = $valid['location'];
        $company->services = $valid['services'] ?? [];
        $company->conference_objectives = $valid['conference_objectives'];
        $company->save();

        $action = $company->wasRecentlyCreated ? 'created' : 'updated';
        $statusCode = $company->wasRecentlyCreated ? HttpStatus::CREATED : HttpStatus::ACCEPTED;

        return (new CompanyResource($company))->additional([
            'status' => 'success',
            'message' => __('Your company ":0" has been :1 successfully.', [$company->name, $action]),
            'statusCode' => $statusCode,
        ])->response()->setStatusCode($statusCode->value);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Company $company)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($user->isNot($company->user), Providers::response()->error([
            'data' => [],
            'message' => 'You do not have permission to delete this company.',
        ], HttpStatus::FORBIDDEN));

        $company->delete();

        return (new CompanyResource($company))->additional([
            'status' => 'success',
            'message' => __('Your company ":0" has been deleted successfully.', [$company->name]),
            'statusCode' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }
}
