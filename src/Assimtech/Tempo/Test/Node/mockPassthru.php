<?php

namespace Assimtech\Tempo\Node;

function passthru($cmd, &$retVal)
{
    global $passthruCallback;
    $retVal = call_user_func($passthruCallback, $cmd);
}
