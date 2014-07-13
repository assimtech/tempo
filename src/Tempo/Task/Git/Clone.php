<?php

/**
 * @param string $repository Repository name e.g. git@github.com:kralos/tempo.git
 * @param string $destination Destination path e.g. ~/tempo
 */
return function ($repository, $destination = null) {
    $cmd = "git clone $repository";
    if ($destination !== null) {
        $cmd .= " $destination";
    }
    return $cmd;
};
