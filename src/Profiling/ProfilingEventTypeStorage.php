<?php

namespace Vengine\Libs\DI\Profiling;

class ProfilingEventTypeStorage
{
    public const CREATE_SERVICE = 11;
    public const CREATE_ARGUMENT = 12;
    public const CREATE_DEFINITION = 13;

    public const END_SERVICE_CREATION = 21;
    public const END_ARGUMENT_CREATION = 22;
    public const END_DEFINITION_CREATION = 23;
}
