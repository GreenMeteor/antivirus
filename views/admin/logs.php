<?php

use yii\helpers\Html;
use humhub\widgets\Button;

/** @var $logEntries array */

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AntivirusModule.base', '<strong>Anti-Virus</strong> Scan Logs'); ?>
    </div>
    <div class="panel-body">
        <div class="help-block">
            <?= Yii::t('AntivirusModule.base', 'Displaying the last 100 scan log entries.'); ?>
        </div>

        <div class="text-right">
            <?= Button::defaultType(Yii::t('AntivirusModule.base', 'Back to Settings'))
                ->link(\yii\helpers\Url::to(['index']))
                ->icon('fa-arrow-left'); ?>
        </div>

        <hr>

        <?php if (empty($logEntries)): ?>
            <div class="alert alert-info">
                <?= Yii::t('AntivirusModule.base', 'No log entries found.'); ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th><?= Yii::t('AntivirusModule.base', 'Date/Time'); ?></th>
                            <th><?= Yii::t('AntivirusModule.base', 'Level'); ?></th>
                            <th><?= Yii::t('AntivirusModule.base', 'Message'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logEntries as $entry): ?>
                            <tr class="<?= strpos($entry['message'], 'deleted') !== false ? 'danger' : (strpos($entry['message'], 'malicious') !== false ? 'warning' : ''); ?>">
                                <td><?= Html::encode($entry['timestamp']); ?></td>
                                <td>
                                    <?php
                                    $levelClass = 'label-info';
                                    if ($entry['level'] === 'ERROR') {
                                        $levelClass = 'label-danger';
                                    } elseif ($entry['level'] === 'WARNING') {
                                        $levelClass = 'label-warning';
                                    } elseif ($entry['level'] === 'INFO') {
                                        $levelClass = 'label-info';
                                    }
                                    ?>
                                    <span class="label <?= $levelClass; ?>">
                                        <?= Html::encode($entry['level']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?= Html::encode($entry['message']); ?>
                                    <?php if (strpos($entry['message'], 'deleted') !== false): ?>
                                        <span class="text-danger"><strong>(File Deleted)</strong></span>
                                    <?php elseif (strpos($entry['message'], 'malicious') !== false): ?>
                                        <span class="text-warning"><strong>(Malicious File Detected)</strong></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>