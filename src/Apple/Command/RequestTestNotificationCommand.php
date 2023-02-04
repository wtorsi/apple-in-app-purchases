<?php
declare(strict_types=1);

namespace Apple\Command;

use Apple\Token\Encoder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'apple:store:request-test')]
class RequestTestNotificationCommand extends \AbstractCommand
{
    private const URL = 'https://api.storekit.itunes.apple.com/inApps/v1/notifications/test';
    private const SANDBOX_URL = 'https://api.storekit-sandbox.itunes.apple.com/inApps/v1/notifications/test';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly Encoder $encoder,
        #[Autowire('%env(bool:APPLE_DEBUG)%')]
        private readonly bool $debug,
    )
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->client->request('POST', $this->debug ? self::SANDBOX_URL : self::URL, [
            'headers' => ['Authorization' => \sprintf("Bearer %s", $this->getToken())],
        ])->toArray();

        $this->io->success(['Successfully requested notification', 'Token:', $response['testNotificationToken']]);

        return self::SUCCESS;
    }

    private function getToken(): string
    {
        return $this->encoder->encode();
    }
}