<?php

namespace humhub\modules\antivirus\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;

class FileDeletedCategory extends NotificationCategory
{
    /**
     * @inheritdoc
     */
    public $id = 'antivirus';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('AntivirusModule.base', 'Antivirus Notifications');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('AntivirusModule.notifications', 'Receive Notifications for deleted files by antivirus.');
    }
}