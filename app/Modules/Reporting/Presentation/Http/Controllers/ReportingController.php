<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Application\Contracts\ReportingReadContract;
use App\Modules\Reporting\Presentation\Http\Requests\AuditWindowSummaryRequest;
use App\Modules\Reporting\Presentation\Http\Requests\ComplianceExportRequest;
use App\Modules\Reporting\Presentation\Http\Requests\CorrelationBundleRequest;
use App\Modules\Reporting\Presentation\Http\Requests\DrillDownRequest;
use App\Modules\Reporting\Presentation\Http\Requests\EntityTimelineRequest;
use App\Modules\Reporting\Presentation\Http\Requests\SecurityActorActivityRequest;
use App\Modules\Reporting\Presentation\Http\Responses\ReportingApiResponseFactory;
use Illuminate\Http\JsonResponse;

final class ReportingController extends Controller
{
    public function __construct(
        private readonly ReportingReadContract $reporting,
    ) {}

    public function entityTimeline(EntityTimelineRequest $request): JsonResponse
    {
        return ReportingApiResponseFactory::success(
            'RU-01',
            $this->reporting->entityTimeline($request->toQuery()),
        );
    }

    public function correlationBundle(CorrelationBundleRequest $request): JsonResponse
    {
        return ReportingApiResponseFactory::success(
            'RU-02',
            $this->reporting->correlationBundle($request->toQuery()),
        );
    }

    public function auditWindowSummary(AuditWindowSummaryRequest $request): JsonResponse
    {
        return ReportingApiResponseFactory::success(
            'RU-03',
            $this->reporting->auditWindowSummary($request->toQuery()),
        );
    }

    public function complianceExport(ComplianceExportRequest $request): JsonResponse
    {
        return ReportingApiResponseFactory::success(
            'RU-04',
            $this->reporting->complianceExport($request->toQuery()),
        );
    }

    public function securityActorActivity(SecurityActorActivityRequest $request): JsonResponse
    {
        return ReportingApiResponseFactory::success(
            'RU-05',
            $this->reporting->securityActorActivity($request->toQuery()),
        );
    }

    public function drillDown(DrillDownRequest $request): JsonResponse
    {
        return ReportingApiResponseFactory::success(
            'RU-06',
            $this->reporting->drillDown($request->toQuery()),
        );
    }
}
