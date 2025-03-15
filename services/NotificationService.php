<?php

namespace humhub\modules\antivirus\services;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\antivirus\notifications\MaliciousFileDeleted;

class NotificationService
{
    /**
     * Sends a notification to the user about a deleted malicious file.
     *
     * @param ContentActiveRecord $owner The content owner, if applicable.
     * @param \humhub\modules\antivirus\models\File $file The file that was deleted.
     */
    public static function sendMaliciousFileDeletedNotification(ContentActiveRecord $owner, $file)
    {
        if ($owner && method_exists($owner, 'getCreatedBy')) {
            $user = $owner->getCreatedBy()->one();

        if ($user instanceof User) {
                $notification = new MaliciousFileDeleted([
                    'source' => $file,
                    'fileName' => $file->file_name,
                    'originator' => Yii::$app->user->identity,
                ]);

                $notification->send($user);
            }
        }
    }
}