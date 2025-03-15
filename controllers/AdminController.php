<?php

namespace humhub\modules\antivirus\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\antivirus\models\ConfigForm;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\permissions\ManageModules;

/**
 * Admin controller for the antivirus module
 */
class AdminController extends Controller
{
    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => ManageModules::class]
        ];
    }

    /**
     * Configure the antivirus module
     */
    public function actionIndex()
    {
        $model = new ConfigForm();
        $model->loadSettings();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->saveSettings()) {
            $this->view->success(Yii::t('AntivirusModule.base', 'Settings saved successfully'));
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model
        ]);
    }

    /**
     * View scan logs
     */
    public function actionLogs()
    {
        $logEntries = $this->getLogEntries();

        return $this->render('logs', [
            'logEntries' => $logEntries
        ]);
    }
    
    /**
     * Get antivirus log entries
     * 
     * @param int $limit
     * @return array
     */
    public function getLogEntries($limit = 100)
    {
        $logFile = Yii::getAlias('@runtime/logs/antivirus.log');
        $entries = [];

        if (file_exists($logFile)) {
            $lines = file($logFile);
            $lines = array_reverse($lines);
            $count = 0;

            foreach ($lines as $line) {
                if ($count >= $limit) {
                    break;
                }

                if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) \[(.*?)\] (.*?)$/i', $line, $matches)) {
                    $entries[] = [
                        'timestamp' => $matches[1],
                        'level' => $matches[2],
                        'message' => $matches[3]
                    ];
                    $count++;
                }
            }
        }

        return $entries;
    }

    /**
     * Run manual scan of uploaded files
     */
    public function actionScan()
    {
        $scanResult = [];
        $scanCount = 0;
        $maliciousCount = 0;

        $module = Yii::$app->getModule('antivirus');

        if (Yii::$app->request->isPost) {
            $files = $this->getAllFiles();
            $scanCount = count($files);

            foreach ($files as $file) {
                $result = $module->isMaliciousFile($file);
                if ($result) {
                    $maliciousCount++;
                    $scanResult[] = [
                        'file' => $file,
                        'result' => 'malicious',
                        'action' => 'deleted'
                    ];

                    $module->deleteFile($file);
                }
            }

            $this->view->success(Yii::t('AntivirusModule.base', 'Scan completed. {count} files scanned, {malicious} malicious files found and deleted.', [
                'count' => $scanCount,
                'malicious' => $maliciousCount
            ]));
        }

        return $this->render('scan', [
            'scanResult' => $scanResult,
            'scanCount' => $scanCount,
            'maliciousCount' => $maliciousCount
        ]);
    }

    /**
     * Get all files in the system
     * 
     * @return array
     */
    public function getAllFiles()
    {
        return \humhub\modules\file\models\File::find()->all();
    }
}