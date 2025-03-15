<?php

namespace humhub\modules\antivirus;

use Yii;
use yii\base\Component;
use humhub\components\Event;
use humhub\modules\file\models\File;
use humhub\modules\content\components\ContentActiveRecord;

/**
 * Class Module
 * 
 * HumHub Antivirus Module to scan and remove potentially malicious files.
 */
class Module extends \humhub\components\Module
{
    /**
     * @inheritdoc
     */
    public $resourcesPath = 'resources';

    /**
     * @var array List of dangerous file extensions.
     */
    public $dangerousExtensions = ['exe', 'bat', 'cmd', 'scr', 'js', 'vbs', 'dll', 'ps1', 'reg', 'msi', 'com'];

    /**
     * @var array List of dangerous MIME types.
     */
    public $dangerousMimeTypes = ['application/x-msdownload', 'application/x-ms-installer', 'application/javascript'];

    /**
     * @var int Maximum file size (in bytes) that can be scanned.
     */
    public $maxScanSize = 52428800; // 50MB

    /**
     * @var array List of virus signatures in hexadecimal format.
     */
    public $virusSignatures = [
        'EICAR' => '58354F2150254041505B345C505A58353428505E2937434329377D24454943415' . 
                   '22D5354414E444152442D414E544956495255532D544553542D46494C452124' . 
                   '48202B',
    ];

    /**
     * Initializes the module and registers event handlers.
     */
    public function init()
    {
        parent::init();
        Event::on(File::class, File::EVENT_AFTER_INSERT, [$this, 'onFileUpload']);
    }

    /**
     * Event handler for file upload.
     * 
     * @param Event $event The file upload event.
     */
    public function onFileUpload($event)
    {
        /** @var File $file */
        $file = $event->sender;

        if ($file->size > $this->maxScanSize) {
            $this->logWarning("File exceeds max scan size: {$file->file_name}");
            return;
        }

        if ($this->isMaliciousFile($file)) {
            $this->deleteFile($file);
        }
    }

    /**
     * Checks if the uploaded file is potentially malicious.
     * 
     * @param File $file The uploaded file.
     * @return bool True if the file is malicious, false otherwise.
     */
    public function isMaliciousFile(File $file): bool
    {
        $extension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
        if (in_array($extension, $this->dangerousExtensions)) {
            $this->logWarning("Dangerous extension detected: {$file->file_name}");
            return true;
        }

        if (in_array($file->mime_type, $this->dangerousMimeTypes)) {
            $this->logWarning("Dangerous MIME type detected: {$file->file_name} ({$file->mime_type})");
            return true;
        }

        return $this->scanFileForViruses($file);
    }

    /**
     * Scans a file for known virus signatures.
     * 
     * @param File $file The file to scan.
     * @return bool True if a virus signature is found, false otherwise.
     */
    protected function scanFileForViruses(File $file): bool
    {
        try {
            $filePath = $file->getStore()->get();

            if (!file_exists($filePath)) {
                $this->logError("File not found for scanning: {$filePath}");
                return false;
            }

            $handle = fopen($filePath, 'rb');
            if (!$handle) {
                $this->logError("Cannot open file for scanning: {$filePath}");
                return false;
            }

            $fileContent = '';
            while (!feof($handle)) {
                $fileContent .= fread($handle, 8192);

                foreach ($this->virusSignatures as $virusName => $signature) {
                    if (stripos(bin2hex($fileContent), $signature) !== false) {
                        $this->logWarning("Virus signature '{$virusName}' detected in: {$file->file_name}");
                        fclose($handle);
                        return true;
                    }
                }

                $fileContent = substr($fileContent, -1024);
            }

            fclose($handle);
            return false;
        } catch (\Throwable $e) {
            $this->logError("Error scanning file: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Deletes a malicious file and notifies the user.
     * 
     * @param File $file The malicious file to delete.
     */
    public function deleteFile(File $file)
    {
        try {
            $owner = $file->getPolymorphicRelation();
            $this->logWarning("Deleting malicious file: {$file->file_name} (ID: {$file->id})");

            if ($file->delete()) {
                $this->sendNotification($file, $owner);
            } else {
                $this->logError("Failed to delete file: {$file->file_name}");
            }
        } catch (\Throwable $e) {
            $this->logError("Error deleting file: {$e->getMessage()}");
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
                services\NotificationService::sendMaliciousFileDeletedNotification($owner, $file);
                $this->logWarning("Notification sent to {$user->username} for deleted file: {$file->file_name}");
            }
        }
    }

    /**
     * Logs a warning message.
     * 
     * @param string $message The warning message.
     */
    public function logWarning(string $message)
    {
        Yii::warning($message, 'antivirus');
    }

    /**
     * Logs an error message.
     * 
     * @param string $message The error message.
     */
    public function logError(string $message)
    {
        Yii::error($message, 'antivirus');
    }
}