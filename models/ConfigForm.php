<?php

namespace humhub\modules\antivirus\models;

use Yii;
use yii\base\Model;

/**
 * ConfigForm model for antivirus module
 */
class ConfigForm extends Model
{
    /**
     * @var array File extensions that are considered potentially dangerous
     */
    public $dangerousExtensions;

    /**
     * @var array MIME types that are considered potentially dangerous
     */
    public $dangerousMimeTypes;

    /**
     * @var int Maximum file size to scan in bytes
     */
    public $maxScanSize;

    /**
     * @var bool Enable scanning of uploaded files
     */
    public $enableScanning;

    /**
     * @var bool Enable notifications for deleted files
     */
    public $enableNotifications;

    /**
     * @var bool Enable automatic file deletion
     */
    public $enableAutoDelete;

    /**
     * @var bool Enable logging of scan results
     */
    public $enableLogging;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dangerousExtensions', 'dangerousMimeTypes'], 'required'],
            ['maxScanSize', 'integer', 'min' => 1024, 'max' => 1073741824],
            [['enableScanning', 'enableNotifications', 'enableAutoDelete', 'enableLogging'], 'boolean'],
            ['dangerousExtensions', 'validateExtensions'],
            ['dangerousMimeTypes', 'validateMimeTypes'],
        ];
    }

    /**
     * Validates file extensions
     * 
     * @param string $attribute
     * @param array $params
     */
    public function validateExtensions($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return;
        }

        $extensions = explode(',', $this->$attribute);
        foreach ($extensions as $ext) {
            $ext = trim($ext);
            if (!preg_match('/^[a-z0-9]+$/i', $ext)) {
                $this->addError($attribute, Yii::t('AntivirusModule.base', 'Invalid file extension: {extension}', ['extension' => $ext]));
            }
        }
    }

    /**
     * Validates MIME types
     * 
     * @param string $attribute
     * @param array $params
     */
    public function validateMimeTypes($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return;
        }

        $mimeTypes = explode(',', $this->$attribute);
        foreach ($mimeTypes as $mime) {
            $mime = trim($mime);
            if (!preg_match('/^[a-z0-9\-\/\+\.]+$/i', $mime)) {
                $this->addError($attribute, Yii::t('AntivirusModule.base', 'Invalid MIME type: {mime}', ['mime' => $mime]));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dangerousExtensions' => Yii::t('AntivirusModule.base', 'Dangerous file extensions'),
            'dangerousMimeTypes' => Yii::t('AntivirusModule.base', 'Dangerous MIME types'),
            'maxScanSize' => Yii::t('AntivirusModule.base', 'Maximum file size to scan (bytes)'),
            'enableScanning' => Yii::t('AntivirusModule.base', 'Enable file scanning'),
            'enableNotifications' => Yii::t('AntivirusModule.base', 'Enable notifications'),
            'enableAutoDelete' => Yii::t('AntivirusModule.base', 'Automatically delete malicious files'),
            'enableLogging' => Yii::t('AntivirusModule.base', 'Enable logging'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'dangerousExtensions' => Yii::t('AntivirusModule.base', 'Comma-separated list of file extensions that are considered dangerous (e.g. exe,bat,cmd)'),
            'dangerousMimeTypes' => Yii::t('AntivirusModule.base', 'Comma-separated list of MIME types that are considered dangerous (e.g. application/x-msdownload)'),
            'maxScanSize' => Yii::t('AntivirusModule.base', 'Maximum file size in bytes to scan (files larger than this will be skipped)'),
            'enableScanning' => Yii::t('AntivirusModule.base', 'When enabled, files will be scanned for malicious content upon upload'),
            'enableNotifications' => Yii::t('AntivirusModule.base', 'When enabled, users will be notified when their uploaded files are deleted due to malicious content'),
            'enableAutoDelete' => Yii::t('AntivirusModule.base', 'When enabled, malicious files will be automatically deleted'),
            'enableLogging' => Yii::t('AntivirusModule.base', 'When enabled, all scan results will be logged'),
        ];
    }

    /**
     * Loads the current module settings
     */
    public function loadSettings()
    {
        $module = Yii::$app->getModule('antivirus');

        $this->dangerousExtensions = is_array($module->dangerousExtensions) ? 
            implode(',', $module->dangerousExtensions) : '';

        $this->dangerousMimeTypes = is_array($module->dangerousMimeTypes) ? 
            implode(',', $module->dangerousMimeTypes) : '';

        $this->maxScanSize = $module->maxScanSize;

        $this->enableScanning = $module->settings->get('enableScanning');
        $this->enableNotifications = $module->settings->get('enableNotifications');
        $this->enableAutoDelete = $module->settings->get('enableAutoDelete');
        $this->enableLogging = $module->settings->get('enableLogging');
    }

    /**
     * Saves the module settings
     * 
     * @return boolean
     */
    public function saveSettings()
    {
        $module = Yii::$app->getModule('antivirus');

        $module->dangerousExtensions = $this->dangerousExtensions ? 
            array_map('trim', explode(',', $this->dangerousExtensions)) : [];

        $module->dangerousMimeTypes = $this->dangerousMimeTypes ? 
            array_map('trim', explode(',', $this->dangerousMimeTypes)) : [];

        $module->maxScanSize = $this->maxScanSize;

        $module->settings->set('enableScanning', $this->enableScanning);
        $module->settings->set('enableNotifications', $this->enableNotifications);
        $module->settings->set('enableAutoDelete', $this->enableAutoDelete);
        $module->settings->set('enableLogging', $this->enableLogging);
        
        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $this->sendNotification();
        }
    }

    /**
     * Sends a notification to the file owner about the deleted malicious file.
     * 
     * @param File $file The file that was deleted.
     * @param ContentActiveRecord|null $owner The content owner, if applicable.
     */
    public function sendNotification(File $file, $owner)
    {
        if ($owner && method_exists($owner, 'getCreatedBy')) {
            $user = $owner->getCreatedBy()->one();

            if ($user instanceof \humhub\modules\user\models\User) {
                \humhub\modules\antivirus\services\NotificationService::sendMaliciousFileDeletedNotification($owner, $file);
                $this->logWarning("Notification sent to {$user->username} for deleted file: {$file->file_name}");
            }
        }
    }
}