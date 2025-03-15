<?php

use yii\helpers\Html;
use humhub\widgets\Button;
use yii\widgets\ActiveForm;

/** @var $model \humhub\modules\antivirus\models\ConfigForm */

?>

<div class="panel panel-default">
    <div class="panel-footer">
        <?= Yii::t('AntivirusModule.base', '<strong>Anti-Virus</strong> Actions'); ?>
        <div class="panel-body">
            <p><?= Yii::t('AntivirusModule.base', 'From here you can manually scan all uploaded files or view scan logs.'); ?></p>
            <div class="row text-center">
                <div class="col-md-6">
                    <?= Button::info(Yii::t('AntivirusModule.base', 'Run Scan'))
                        ->link(\yii\helpers\Url::to(['scan']))
                        ->icon('search'); ?>
                </div>
                <div class="col-md-6">
                    <?= Button::info(Yii::t('AntivirusModule.base', 'View Scan Logs'))
                        ->link(\yii\helpers\Url::to(['logs']))
                        ->icon('list'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-heading">
        <?= Yii::t('AntivirusModule.base', '<strong>Antivirus</strong> Settings'); ?>
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(['id' => 'antivirus-settings-form']); ?>

        <?= $form->field($model, 'enableScanning')->checkbox(); ?>
        <?= $form->field($model, 'enableNotifications')->checkbox(); ?>
        <?= $form->field($model, 'enableAutoDelete')->checkbox(); ?>
        <?= $form->field($model, 'enableLogging')->checkbox(); ?>
        
        <hr>
        
        <?= $form->field($model, 'maxScanSize')->textInput(['type' => 'number']); ?>
        
        <?= $form->field($model, 'dangerousExtensions')->textarea(['rows' => 3]); ?>
        
        <?= $form->field($model, 'dangerousMimeTypes')->textarea(['rows' => 5]); ?>
        
        <hr>
        
        <div class="form-group">
            <?= Button::save()->submit(); ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>