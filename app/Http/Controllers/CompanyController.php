<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
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
        $valid = $this->validate($request, [
            'image' => 'nullable|image|mimes:png,jpg,jpeg',
            'name' => 'required|string',
            'description' => 'required|string',
            'industry_category' => 'required|string',
            'location' => 'required|string',
            'services' => 'nullable|array',
            'services.*' => 'required|string',
            'conference_objectives' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var \App\Models\BizMatch\Company $company */
        $company = $user->company()->firstOrNew();

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
    public function update(Request $request, Company $company)
    {
        $valid = $this->validate($request, [
            'image' => 'nullable|image|mimes:png,jpg,jpeg',
            'name' => 'required|string',
            'description' => 'required|string',
            'industry_category' => 'required|string',
            'location' => 'required|string',
            'services' => 'nullable|array',
            'services.*' => 'required|string',
            'conference_objectives' => 'required|string',
        ]);

        return (new CompanyResource($company))->additional([
            'status' => 'success',
            'message' => __('Your company ":0" has been updated successfully.', [$company->name]),
            'statusCode' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return (new CompanyResource($company))->additional([
            'status' => 'success',
            'message' => __('Your company ":0" has been deleted successfully.', [$company->name]),
            'statusCode' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }
}
