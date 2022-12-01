<?php

namespace MageSuite\NotificationDashboardSlack\Helper;

class Configuration
{
    const XML_PATH_NOTIFICATION_DASHBOARD_SLACK_WEBHOOK_URL = 'notification_dashboard/slack/webhook_url';
    const XML_PATH_NOTIFICATION_DASHBOARD_SLACK_WEBHOOK_TOKEN = 'notification_dashboard/slack/webhook_token';
    const XML_PATH_NOTIFICATION_DASHBOARD_SLACK_USERNAME = 'notification_dashboard/slack/username';
    const XML_PATH_NOTIFICATION_DASHBOARD_SLACK_ICON_EMOJI = 'notification_dashboard/slack/icon_emoji';
    const XML_PATH_NOTIFICATION_DASHBOARD_SLACK_IS_DEBUG_MODE_ENABLED = 'notification_dashboard/slack/is_debug_mode_enabled';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    protected \Magento\Framework\Encryption\EncryptorInterface $encryptor;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfigInterface;
        $this->encryptor = $encryptor;
    }

    public function getSlackWebhookUrl()
    {
        $webhookUrl = $this->scopeConfig->getValue(self::XML_PATH_NOTIFICATION_DASHBOARD_SLACK_WEBHOOK_URL);
        $webhookToken = $this->scopeConfig->getValue(self::XML_PATH_NOTIFICATION_DASHBOARD_SLACK_WEBHOOK_TOKEN);

        if (!$webhookUrl || !$webhookToken) {
            return null;
        }

        return sprintf(
            '%s/%s',
            rtrim($webhookUrl, '/'),
            $this->encryptor->decrypt($webhookToken)
        );
    }

    public function getSlackUsername()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NOTIFICATION_DASHBOARD_SLACK_USERNAME);
    }

    public function getSlackIconEmoji()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NOTIFICATION_DASHBOARD_SLACK_ICON_EMOJI);
    }

    public function isDebugModeEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NOTIFICATION_DASHBOARD_SLACK_IS_DEBUG_MODE_ENABLED);
    }
}
