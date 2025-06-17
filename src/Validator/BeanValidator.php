<?php
namespace DtoValidator\Validator;

use DtoValidator\Attributes\Rules;
use Illuminate\Support\Facades\Validator;
use ReflectionClass;
use ReflectionProperty;

class BeanValidator
{
    public static function validate(object $dto): void
    {
        list($data, $rules) = self::extractedDataAndRuleFromDto($dto);
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $request = request();
            if ($request->expectsJson()  || $request->isJson()) {
                abort(response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422));
            } else {
                redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->send();

                exit;
            }
        }

    }

    private static function isPropertyInitialized(object $dto, ReflectionProperty $property): bool
    {
        // PHP 8.2+ : méthode native
        if (method_exists($property, 'isInitialized')) {
            return $property->isInitialized($dto);
        }

        // PHP < 8.2 fallback : tentative d’accès avec try/catch
        try {
            $property->getValue($dto);
            return true;
        } catch (\Error $e) {
            return false;
        }
    }

    /**
     * @param object $dto
     * @return array[]
     */
    public static function extractedDataAndRuleFromDto(object $dto): array
    {
        $refClass = new ReflectionClass($dto);
        $data = [];
        $rules = [];

        foreach ($refClass->getProperties() as $property) {
            $name = $property->getName();
            $property->setAccessible(true);

            if (self::isPropertyInitialized($dto, $property)) {
                $data[$name] = $property->getValue($dto);
            } else {
                $data[$name] = null;
            }

            foreach ($property->getAttributes(Rules::class) as $attr) {
                /** @var Rules $rule */
                $rule = $attr->newInstance();
                $rules[$name] = $rule->rules;
            }
        }
        return array($data, $rules);
    }
}
