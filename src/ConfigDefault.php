<?php

class Config
{

    public function init()
    {
        return [
            'getRepositoriesPath' => __DIR__ . '/../../',
            'enviroments' => ['prod', 'stage', 'local'],
            'projectRename' => ['default' => ['ares'], 'new' => 'Ares Microsites'],
            'projectExclude' => [],
            'projectPrefix' => 'WC ',
            'projectSuffiks' => ' SK',
            'enviromentId' => function ($env) {
                $enviromentId = 'mkz-<env>-www1';
                return str_replace('<env>', $env, $enviromentId);
            },
        ];
    }

}
