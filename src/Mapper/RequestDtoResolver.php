<?php
namespace DtoValidator\Mapper;
use Illuminate\Http\Request;
use ReflectionClass;

class RequestDtoResolver
{
    public static function resolve(string $dtoClass, Request $request): object
    {
        $refClass = new ReflectionClass($dtoClass);
        $dto = $refClass->newInstanceWithoutConstructor();

        foreach ($refClass->getProperties() as $property) {
            $name = $property->getName();
            $value = $request->input($name) ?? $request->file($name);
            $property->setAccessible(true);
            $property->setValue($dto, $value);
        }

        return $dto;
    }

}
