<?php
namespace DtoValidator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Rules
{
    public array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }
}
