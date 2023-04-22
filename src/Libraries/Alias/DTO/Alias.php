<?php

namespace Loader\Libraries\Alias\DTO;

use Loader\Libraries\Alias\Interfaces\AliasInterface;
use Loader\System\Helpers\Reflection;

class Alias implements AliasInterface
{
    /**
     * Основной алиас класса
     * 
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $className;

    /**
     * Используется для множественных алисалов для одного класса
     * 
     * @var array
     */
    protected $aliasList;
    
    public function __construct(string $name)
    {
        $this->name = $name;
        
        if (Reflection::has($name)) {
            $this->className = $name;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $class): AliasInterface
    {
        $this->className = $class;
        
        return $this;
    }

    public function add(string $alias): AliasInterface
    {
        $this->aliasList[md5($alias)] = $alias;
        
        return $this;
    }

    public function remove(string $alias): AliasInterface
    {
        if (!$this->hasAlias($alias)) {
            return $this;
        }
        
        unset($this->aliasList[md5($alias)]);
        
        return $this;
    }

    public function setList(array $aliasList): AliasInterface
    {
        $this->aliasList = $aliasList;

        return $this;
    }

    public function has(string $name): bool
    {
        return in_array($name, $this->aliasList, true);
    }

    public static function getNew(string $name): Alias
    {
        return new static($name);
    }
    
    public function get(string $name): string
    {
        return $this->aliasList[md5($name)] ?? '';
    }
}
