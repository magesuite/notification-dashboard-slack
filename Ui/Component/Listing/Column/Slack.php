<?php

namespace MageSuite\NotificationDashboardSlack\Ui\Component\Listing\Column;

class Slack extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected \MageSuite\NotificationDashboard\Api\UserRepositoryInterface $userRepository;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \MageSuite\NotificationDashboard\Api\UserRepositoryInterface $userRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->userRepository = $userRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['id'])) {
                $item[$fieldName] = '';
                continue;
            }

            $user = $this->userRepository->getById($item['id']);
            $item[$fieldName] = $user->getSlack();
        }

        return $dataSource;
    }
}
