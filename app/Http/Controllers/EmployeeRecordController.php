<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Auth\EmployeeRecordsPolicyEnforcementPoint;
use App\Http\Requests\EditEmployeeRecordRequest;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * HTTP stubs for the employee_records production surface (D-L6-4-C2).
 * Authorization is enforced via EmployeeRecordsPolicyEnforcementPoint / Form Requests —
 * no Gate:: / can() shortcuts.
 */
final class EmployeeRecordController extends Controller
{
    public function __construct(
        private readonly EmployeeRecordsPolicyEnforcementPoint $enforcementPoint,
    ) {}

    public function index(): Response
    {
        $this->assertCanRead();

        return response()->noContent();
    }

    public function show(string $employeeRecord): JsonResponse
    {
        $this->assertCanRead();

        return response()->json([
            'id' => $employeeRecord,
        ]);
    }

    public function create(): Response
    {
        $this->assertCanEdit();

        return response()->noContent();
    }

    public function store(EditEmployeeRecordRequest $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $request->validated(),
        ], Response::HTTP_CREATED);
    }

    public function edit(string $employeeRecord): JsonResponse
    {
        $this->assertCanEdit();

        return response()->json([
            'id' => $employeeRecord,
        ]);
    }

    public function update(EditEmployeeRecordRequest $request, string $employeeRecord): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'id' => $employeeRecord,
            'data' => $request->validated(),
        ]);
    }

    public function destroy(string $employeeRecord): Response
    {
        $this->assertCanEdit();

        return response()->noContent();
    }

    private function assertCanRead(): void
    {
        abort_unless($this->enforcementPoint->canRead($this->apiUser()), Response::HTTP_FORBIDDEN);
    }

    private function assertCanEdit(): void
    {
        abort_unless($this->enforcementPoint->canEdit($this->apiUser()), Response::HTTP_FORBIDDEN);
    }

    private function apiUser(): ?UserModel
    {
        $user = auth('api')->user();

        return $user instanceof UserModel ? $user : null;
    }
}
