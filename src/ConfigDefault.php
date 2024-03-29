<?php

class Config
{

    public function init()
    {
        return [
            'getRepositoriesPath' => __DIR__ . '/../../',
            'jsonFilePath' => __DIR__ . '/../public/json',
            'enviroments' => ['prod', 'stage', 'local'],
            'projectRename' => ['default' => ['ares'], 'new' => 'Ares Microsites'],
            'projectExclude' => [],
            'projectPrefix' => 'WC ',
            'projectSuffiks' => ' SK',
            'enviromentDescription' => 'Markiza Production<br/>',
            'enviromentId' => function ($env) {
                $enviromentId = 'mkz-<env>-www1';
                return str_replace('<env>', $env, $enviromentId);
            },
            'mamaUrl' => '',
            'mamaEnv' => '',
            'mamaToken' => '',
            'externalData' => [
            ]
        ];
    }

}
