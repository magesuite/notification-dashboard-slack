<?php

namespace MageSuite\NotificationDashboardSlack\Service\NotificationSender\Channel;

class Slack
{
    const SLACK_HERE_PREFIX = '<!here>';

    protected \GuzzleHttp\ClientFactory $clientFactory;

    protected \MageSuite\NotificationDashboardSlack\Helper\Configuration $configuration;

    protected \Magento\Framework\Serialize\Serializer\Json $serializer;

    protected \MageSuite\NotificationDashboard\Logger\Logger $logger;

    public function __construct(
        \GuzzleHttp\ClientFactory $clientFactory,
        \MageSuite\NotificationDashboardSlack\Helper\Configuration $configuration,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \MageSuite\NotificationDashboard\Logger\Logger $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->configuration = $configuration;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    public function send($notification, $channelsData)
    {
        $webhookUrl = $this->configuration->getSlackWebhookUrl();

        if (empty($webhookUrl)) {
            return;
        }

        $client = $this->clientFactory->create();

        $payload = [
            'username' => $this->configuration->getSlackUsername(),
            'icon_emoji' => $this->configuration->getSlackIconEmoji(),
            'attachments' => [
                'fallback' => (string)$notification->getMessage(),
                'fields' => [
                    'title' => (string)$notification->getTitle(),
                    'text' => (string)$notification->getMessage(),
                    'color' => 'danger'
                ]
            ]
        ];

        foreach ($channelsData as $channelData) {
            $channel = $channelData->getChannel();
            $collectorUser = $channelData->getCollectorUser();

            if (empty($channel)) {
                continue;
            }

            $payload['channel'] = $channel;

            if ($collectorUser['slack_add_here']) {
                $payload['attachments']['fields']['text'] = sprintf('%s %s', self::SLACK_HERE_PREFIX, $payload['attachments']['fields']['text']);
            }

            try {
                $client->post($webhookUrl, ['body' => $this->serializer->serialize(array_filter($payload))]);
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
