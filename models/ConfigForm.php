<?php

namespace humhub\modules\antivirus\models;

use Yii;
use yii\base\Model;
use humhub\modules\user\models\User;
use humhub\modules\antivirus\services\NotificationService;

/**
 * ConfigForm model for antivirus module
 */
class ConfigForm extends Model
{
    /**
     * @var string Comma-separated list of file extensions that are considered potentially dangerous
     */
    public $dangerousExtensions;

    /**
     * @var string Comma-separated list of MIME types that are considered potentially dangerous
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
        $this->loadSettings();
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
        $settings = $module->settings;

        $this->dangerousExtensions = $settings->get('dangerousExtensions');
        if (empty($this->dangerousExtensions)) {
            $this->dangerousExtensions = implode(',', $module->dangerousExtensions);
        } else {
            $decoded = json_decode($this->dangerousExtensions, true);
            if (is_array($decoded)) {
                $this->dangerousExtensions = implode(',', $decoded);
            }
        }

        $this->dangerousMimeTypes = $settings->get('dangerousMimeTypes');
        if (empty($this->dangerousMimeTypes)) {
            $this->dangerousMimeTypes = implode(',', $module->dangerousMimeTypes);
        } else {
            $decoded = json_decode($this->dangerousMimeTypes, true);
            if (is_array($decoded)) {
                $this->dangerousMimeTypes = implode(',', $decoded);
            }
        }

        $this->maxScanSize = $settings->get('maxScanSize', $module->maxScanSize);
        $this->enableScanning = $settings->get('enableScanning', true);
        $this->enableNotifications = $settings->get('enableNotifications', true);
        $this->enableAutoDelete = $settings->get('enableAutoDelete', true);
        $this->enableLogging = $settings->get('enableLogging', true);
    }

    /**
     * Saves the module settings
     * 
     * @return boolean
     */
    public function saveSettings()
    {
        if (!$this->validate()) {
            return false;
        }

        $module = Yii::$app->getModule('antivirus');
        $settings = $module->settings;

        $dangerousExtensions = array_map('trim', explode(',', $this->dangerousExtensions));
        $dangerousMimeTypes = array_map('trim', explode(',', $this->dangerousMimeTypes));

        $settings->set('dangerousExtensions', json_encode($dangerousExtensions));
        $settings->set('dangerousMimeTypes', json_encode($dangerousMimeTypes));
        $settings->set('maxScanSize', $this->maxScanSize);
        $settings->set('enableScanning', $this->enableScanning);
        $settings->set('enableNotifications', $this->enableNotifications);
        $settings->set('enableAutoDelete', $this->enableAutoDelete);
        $settings->set('enableLogging', $this->enableLogging);

        $module->dangerousExtensions = $dangerousExtensions;
        $module->dangerousMimeTypes = $dangerousMimeTypes;
        $module->maxScanSize = $this->maxScanSize;
        
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

            if ($user instanceof User) {
                NotificationService::sendMaliciousFileDeletedNotification($owner, $file);
            }
        }
    }
}
