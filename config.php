<?php

namespace humhub\modules\antivirus\config;

use yii\helpers\Url;
use humhub\widgets\TopMenu;
use humhub\modules\admin\widgets\AdminMenu;

return [
    'id' => 'antivirus',
    'class' => 'humhub\modules\antivirus\Module',
    'namespace' => 'humhub\modules\antivirus',
    'events' => [
        [
            'class' => AdminMenu::class,
            'event' => AdminMenu::EVENT_INIT,
            'callback' => ['humhub\modules\antivirus\Events', 'onAdminMenuInit']
        ]
    ],
];