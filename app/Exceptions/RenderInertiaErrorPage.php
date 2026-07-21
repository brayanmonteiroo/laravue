<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Middleware\HandleInertiaRequests;
use App\Support\ErrorPageNavigation;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Inertia\ExceptionResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RenderInertiaErrorPage
{
    /**
     * Em visitas Inertia, troca HTML de erro pelo componente Vue integrado ao shell.
     */
    public function __invoke(Response $response, Throwable $e, Request $request): Response
    {
        if (! $request->inertia()) {
            return $response;
        }

        $status = $response->getStatusCode();

        if (! in_array($status, [403, 404, 500, 503], true)) {
            return $response;
        }

        $exceptionMessage = $e->getMessage();
        $navigation = ErrorPageNavigation::for($request->user());

        return (new ExceptionResponse(
            $e,
            $request,
            $response,
            app(Router::class),
            app(Kernel::class),
        ))
            ->render('errors/Error', [
                'status' => $status,
                'message' => __($exceptionMessage !== '' ? $exceptionMessage : ErrorPageNavigation::defaultMessage($status)),
                'ctaUrl' => $navigation['url'],
                'ctaLabel' => $navigation['label'],
                'crumbTitle' => $navigation['crumbTitle'],
            ])
            ->usingMiddleware(HandleInertiaRequests::class)
            ->withSharedData()
            ->toResponse($request);
    }
}
