<?php

namespace App\Components;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use ReflectionClass;
use ReflectionMethod;

class TextInput implements Htmlable
{
    protected string $label;

    public function __construct(protected string $name)
    {

    }

    public static function make(string $name) : self
    {
        return new self($name);
    }

    public function toHtml() : string
    {
        return $this->render()->render();
    }

    public function label(string $label) : self
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label ?? str($this->name)
            ->title();
    }

    public function extractPublicMethods(): array
    {
         $reflection = new ReflectionClass($this);

         $methods = [];

         foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
         {
             $methods[$method->getName()] = Closure::fromCallable([$this, $method->getName()]);
         }

         return $methods;
    }

    public function render(): view
    {
        return view('components.text-input', $this->extractPublicMethods());
    }
}
