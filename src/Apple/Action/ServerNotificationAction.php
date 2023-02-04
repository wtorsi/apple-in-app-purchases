<?php
declare(strict_types=1);

namespace Apple\Action;

use Api\Action\GetAction;
use Apple\Enum\NotificationType;
use Apple\Event\DidChangeRenewalStatus;
use Apple\Event\DidRenew;
use Apple\Event\Expired;
use Apple\Event\Subscribed;
use Apple\Token\Decoder;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/apple/server-notification', methods: ['POST'])]
class ServerNotificationAction extends GetAction
{
    use \EventDispatcherAwareTrait;

    public function __construct(
        private readonly Decoder $decoder,
        #[Autowire(service: 'monolog.logger.apple')]
        private readonly LoggerInterface $logger
    )
    {
    }

    public function __invoke(Request $request): Response
    {
        $this->logger->info('Start processing notification', ['notification' => $request->getContent()]);

        try {
            $payload = $this->decoder->decode($this->getPayload($request));
        } catch (\Throwable $e) {
            $this->logger->error(\sprintf('Could not process the request, error occurred: %s', $e->getMessage()), ['error' => $e]);
            throw new BadRequestHttpException('Could not process the request', $e);
        }

        $this->logger->info('Parsed the request, ready to process', ['type' => $payload['notificationType'], 'payload' => $payload]);

        try {
            $this->process($payload);
        } catch (\Throwable $e) {
            $this->logger->error('Could not process the request', [
                'type' => $payload['notificationType'],
                'payload' => $payload,
                'error' => $e,
            ]);
            throw new BadRequestHttpException('Could not process the request', $e);
        }

        return new Response();
    }

    private function getPayload(Request $request): string
    {
        try {
            $payload = $request->toArray();
        } catch (\Throwable $e) {
            $this->logger->error('Could not decode request body', ['error' => $e]);
            throw $e;
        }

        if (!isset($payload['signedPayload']) || !$payload['signedPayload'] || !\is_string($payload['signedPayload'])) {
            $this->logger->error('Payload is empty');
            throw new BadRequestException('Request is empty');
        }

        return $payload['signedPayload'];
    }

    private function process(array $payload): void
    {
        $event = match (NotificationType::from($payload['notificationType'])) {
            NotificationType::DID_CHANGE_RENEWAL_STATUS => new DidChangeRenewalStatus($payload),
            NotificationType::DID_RENEW => new DidRenew($payload),
            NotificationType::EXPIRED => new Expired($payload),
            NotificationType::SUBSCRIBED => new Subscribed($payload),
            default => null,
        };

        if (null === $event) {
            $this->logger->warning('Unsupported notification type', ['type' => $payload['notificationType'], 'payload' => $payload]);
            return;
        }

        $this->dispatcher->dispatch($event);
    }
}