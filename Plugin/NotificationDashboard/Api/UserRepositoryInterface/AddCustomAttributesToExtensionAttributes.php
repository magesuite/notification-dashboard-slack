<?php
namespace MageSuite\NotificationDashboardSlack\Plugin\NotificationDashboard\Api\UserRepositoryInterface;

class AddCustomAttributesToExtensionAttributes
{
    public function afterGetList(\MageSuite\NotificationDashboard\Api\UserRepositoryInterface $subject, $result)
    {
        $items = [];
        foreach ($result->getItems() as $item) {
            $extensionAttributes = $item->getExtensionAttributes();

            $extensionAttributes->setData('slack', $item->getExternalSkuAttribute());

            $item->setExtensionAttributes($extensionAttributes);
            $items[] = $item;
        }

        $result->setItems($items);
        return $result;
    }
}
