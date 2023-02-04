<?php
declare(strict_types=1);

namespace Api\Apple\Action;

use Api\Action\PostAction;
use Api\Apple\Form\Data\ProcessReceiptDto;
use Api\Apple\Form\ProcessReceiptForm;
use Apple\Messenger\Message\VerifyReceipt;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use User\Entity\User;

#[Route('/apple/receipt/process', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
class ProcessReceiptAction extends PostAction
{
    use \MessageBusAwareTrait;
    use \EntityManagerAwareTrait;

    public function __invoke(): Response
    {
        return $this->handleApiCall(ProcessReceiptForm::class, function (ProcessReceiptDto $dto) {
            $this->process($this->getUser(), $dto);
        });
    }

    private function process(User $user, ProcessReceiptDto $dto): void
    {
        $subscription = $user->getSubscription();
        $subscription->beginAppleVerification($dto);
        $this->em->persist($subscription);
        $this->em->flush();

        // async
        $this->bus->dispatch(VerifyReceipt::create($user, $dto));
    }

    private function handleApiCall(string $class, \Closure $param): Response
    {
        // process and validate the request with form in your own way
        \call_user_func($param);
        return new Response();
    }
}