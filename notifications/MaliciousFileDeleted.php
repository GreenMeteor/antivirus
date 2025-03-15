<?php

namespace humhub\modules\antivirus\notifications;

use Yii;
use humhub\modules\notification\components\BaseNotification;

class MaliciousFileDeleted extends BaseNotification
{
    /**
     * @var string Name of the uploaded file
     */
    public $fileName;
    
    /**
     * @inheritdoc
     */
    public $moduleId = 'antivirus';

    public function category()
    {
        return new FileDeletedCategory();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('AntivirusModule.base', 'Security Alert');
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return Yii::t(
            'AntivirusModule.base',
            'A potentially malicious file "<strong>{fileName}</strong>" that you uploaded has been removed for security reasons.',
            ['fileName' => $this->fileName]
        );
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        return Yii::t('AntivirusModule.base', 'Security Alert: Malicious file removed');
    }
}