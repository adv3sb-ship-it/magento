<?php

namespace Stagem\KeyCrm\Api;

interface ConfigManagerInterface
{
    const URL_PATH = 'keycrm/general/api_url';
    const KEY_PATH = 'keycrm/general/api_key';

    public function getConfigValue($path);
}
