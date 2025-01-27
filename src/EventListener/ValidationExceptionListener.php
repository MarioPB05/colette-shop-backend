<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof UnprocessableEntityHttpException && $exception->getPrevious() instanceof ValidationFailedException) {
            /** @var ValidationFailedException $validationException */
            $validationException = $exception->getPrevious();
            $violations = $validationException->getViolations();

            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            $response = new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => $errors,
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );

            $event->setResponse($response);
        }
    }
}
