<?php

use yii\helpers\Html;
use humhub\widgets\Button;
use yii\widgets\ActiveForm;

/** 
 * @var $scanResult array
 * @var $scanCount int
 * @var $maliciousCount int
 */

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= Yii::t('AntivirusModule.base', 'Antivirus Manual Scan'); ?></strong>
    </div>
    <div class="panel-body">
        <div class="help-block">
            <?= Yii::t('AntivirusModule.base', 'This tool allows you to scan all uploaded files for malware and viruses.'); ?>
        </div>
        <hr>
        <?php if (Yii::$app->request->isPost && $scanCount > 0): ?>
            <div class="alert alert-success">
                <?= Yii::t('AntivirusModule.base', 'Scan completed successfully! {count} files scanned, {malicious} malicious files found.', [
                    'count' => $scanCount,
                    'malicious' => $maliciousCount
                ]); ?>
            </div>
            <?php if (!empty($scanResult)): ?>
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <?= Yii::t('AntivirusModule.base', 'Malicious Files Detected'); ?>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?= Yii::t('AntivirusModule.base', 'File'); ?></th>
                                        <th><?= Yii::t('AntivirusModule.base', 'Result'); ?></th>
                                        <th><?= Yii::t('AntivirusModule.base', 'Action Taken'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($scanResult as $result): ?>
                                        <tr>
                                            <td><?= Html::encode($result['file']->guid); ?></td>
                                            <td>
                                                <span class="label label-danger">
                                                    <?= Html::encode($result['result']); ?>
                                                </span>
                                            </td>
                                            <td><?= Html::encode($result['action']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="panel-heading">
                    <?= Yii::t('AntivirusModule.base', 'Start Manual Scan'); ?>
                </div>
                <div class="panel-body">
                    <?= Yii::t('AntivirusModule.base', 'Click the button below to start a manual scan of all uploaded files. This may take some time depending on the number of files.'); ?>
                    <?php $form = ActiveForm::begin(['id' => 'antivirus-scan-form']); ?>
                    <hr>
                    <div class="form-group">
                        <?= Button::primary(Yii::t('AntivirusModule.base', 'Start Scan'))
                            ->submit()
                            ->sm()
                            ->icon('fa-search')
                            ->loader(true) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="panel-heading">
                            <?= Yii::t('AntivirusModule.base', 'Information'); ?>
                        </div>
                        <div class="panel-body">
                            <?= Yii::t('AntivirusModule.base', 'This scan will check all uploaded files in the system against the configured virus detection rules. Any files detected as malicious will be deleted automatically.'); ?>
                            <br><br>
                            <?= Yii::t('AntivirusModule.base', 'Scanning will use the current configuration settings. Make sure your anti-virus settings are configured correctly before running a scan.'); ?>
                            <br><br>
                            <?= Yii::t('AntivirusModule.base', 'For more detailed information, check the scan logs after the scan is complete.'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>