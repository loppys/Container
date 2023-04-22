<?php


namespace Loader\System\Interfaces;


interface ContainerFastCall
{
    /**
     * @param string $name
     * @param array $arguments
     * 
     * @return mixed
     */
    public function get(string $name, array $arguments);
}
