<?php

namespace MageSuite\NotificationDashboardSlack\Test\Integration\Service\NotificationSender\Channel;

class SlackTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Framework\ObjectManagerInterface $objectManager;

    protected ?\MageSuite\NotificationDashboard\Model\CollectorRepository $collectorRepository;

    protected ?\MageSuite\NotificationDashboard\Model\Command\Notification\AddNotification $addNotification;

    protected ?\MageSuite\NotificationDashboard\Model\CollectorUserRepository $collectorUserRepository;

    /**
     * @var \MageSuite\NotificationDashboardSlack\Service\NotificationSender\Channel\Slack
     */
    protected ?\PHPUnit\Framework\MockObject\MockObject $slackChannelMock;

    protected ?\MageSuite\NotificationDashboard\Service\NotificationSender\Channel\Resolver $notificationChannelResolver;

    /**
     * @var \MageSuite\NotificationDashboard\Service\NotificationSender\Channel\Resolver
     */
    protected ?\PHPUnit\Framework\MockObject\MockObject $notificationChannelResolverMock;

    protected ?\MageSuite\NotificationDashboard\Service\NotificationSender\SenderResolver $notificationSenderResolver;

    protected ?\MageSuite\NotificationDashboard\Model\NotificationRepository $notificationRepository;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->collectorRepository = $this->objectManager->get(\MageSuite\NotificationDashboard\Model\CollectorRepository::class);
        $this->notificationRepository = $this->objectManager->get(\MageSuite\NotificationDashboard\Model\NotificationRepository::class);
        $this->collectorUserRepository = $this->objectManager->get(\MageSuite\NotificationDashboard\Model\CollectorUserRepository::class);

        $this->slackChannelMock = $this->getMockBuilder(\MageSuite\NotificationDashboardSlack\Service\NotificationSender\Channel\Slack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notificationChannelResolver = $this->objectManager->get(\MageSuite\NotificationDashboard\Service\NotificationSender\Channel\Resolver::class);

        $this->notificationChannelResolverMock = $this->getMockBuilder(\MageSuite\NotificationDashboard\Service\NotificationSender\Channel\Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notificationSenderResolver = $this->objectManager->create(
            \MageSuite\NotificationDashboard\Service\NotificationSender\SenderResolver::class,
            ['notificationChannelResolver' => $this->notificationChannelResolverMock]
        );

        $this->addNotification = $this->objectManager->create(
            \MageSuite\NotificationDashboard\Model\Command\Notification\AddNotification::class,
            ['notificationSenderResolver' => $this->notificationSenderResolver]
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_NotificationDashboard::Test/Integration/_files/collector_with_user.php
     */
    public function testItAddsNotificationCorrectly()
    {
        $message = 'Dummy message';
        $title = 'Item name';
        $collector = $this->getCollector();
        $this->prepareCollectorUser($collector->getId());

        $this->notificationChannelResolverMock->method('getChannelDataByCollectorIds')->willReturn(
            $this->notificationChannelResolver->getChannelDataByCollectorIds($collector->getId())
        );

        $this->notificationChannelResolverMock->method('getAllChannels')->willReturn([
            'send_to_slack' => $this->slackChannelMock
        ]);

        $this->slackChannelMock
            ->expects($this->once())
            ->method('send');

        $this->addNotification->execute($message, $collector->getId(), $collector->getSeverity(), $title);

        $notifications = $this->notificationRepository->getList();
        $items = $notifications->getItems();
        $this->assertCount(1, $items);
    }

    protected function getCollector()
    {
        $collectors = $this->collectorRepository->getList();
        $items = $collectors->getItems();

        return array_shift($items);
    }

    protected function prepareCollectorUser($collectorId)
    {
        $collectorUsers = $this->collectorUserRepository->getByCollectorIds([$collectorId]);
        $collectorUser = array_shift($collectorUsers);

        $collectorUser->setSendToSlack(1);
        $this->collectorUserRepository->save($collectorUser);
    }
}
