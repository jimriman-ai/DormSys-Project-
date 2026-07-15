<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Support;

use App\Modules\Identity\Domain\Exceptions\CannotRemoveOwnSystemAdministratorRoleException;
use App\Modules\Identity\Domain\Exceptions\LastSystemAdministratorException;
use App\Modules\Identity\Domain\Exceptions\ProtectedRoleException;
use App\Modules\Identity\Domain\Exceptions\RoleHasAssignedUsersException;
use App\Modules\Identity\Domain\Exceptions\RoleNotFoundException;
use App\Modules\Identity\Domain\Exceptions\UserNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class IdentityRoleApiExceptionResponse
{
    public static function fromDomainException(\Throwable $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof RoleNotFoundException,
            $exception instanceof UserNotFoundException => self::failure(
                $exception->getMessage(),
                Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof ProtectedRoleException,
            $exception instanceof RoleHasAssignedUsersException,
            $exception instanceof CannotRemoveOwnSystemAdministratorRoleException,
            $exception instanceof LastSystemAdministratorException => self::failure(
                $exception->getMessage(),
                Response::HTTP_CONFLICT,
            ),
            default => self::failure(
                $exception->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
        };
    }

    private static function failure(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
