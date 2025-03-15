<?php

namespace humhub\modules\antivirus;

use Yii;
use yii\helpers\Url;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\permissions\ManageModules;

/**
 * Event Handlers for AntiVirus Module
 */
class Events
{
    /**
     * Handles the AdminMenu Init event to add module configuration link
     * 
     * @param $event
     */
    public static function onAdminMenuInit($event)
    {
        /** @var AdminMenu $menu */
        $menu = $event->sender;

        if (Yii::$app->user->can(ManageModules::class)) {
            $menu->addEntry(new MenuLink([
                'label' => Yii::t('AntivirusModule.base', 'Antivirus Settings'),
                'url' => Url::to(['/antivirus/admin/index']),
                'icon' => 'shield',
                'isActive' => (Yii::$app->controller->module && 
                               Yii::$app->controller->module->id === 'antivirus' && 
                               Yii::$app->controller->id === 'admin'),
                'sortOrder' => 650,
            ]));
        }
    }
}