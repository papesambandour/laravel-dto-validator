<?php
namespace DtoValidator\Middleware;

use DtoValidator\Mapper\RequestDtoResolver;
use Closure;
use Illuminate\Http\Request;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

class InjectDtoMiddleware
{
    /**
     * @throws ReflectionException
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $action = $route->getAction();

        if (!isset($action['uses'])) {
            // Pas d'action détectée, on continue
            return $next($request);
        }

        $callable = $action['uses'];

        // Identifier la reflection selon le type de callable
        if (is_string($callable)) {
            if (str_contains($callable, '@')) {
                // Controller classique: Class@method
                [$class, $method] = explode('@', $callable);
                $ref = new ReflectionMethod($class, $method);
            } else {
                // Controller invokable: Class seule
                $class = $callable;
                $ref = new ReflectionMethod($class, '__invoke');
            }
        } elseif (is_array($callable) && count($callable) === 2) {
            // Callable sous forme [Class, method]
            $ref = new ReflectionMethod($callable[0], $callable[1]);
        } elseif ($callable instanceof Closure) {
            // Closure anonyme
            $ref = new ReflectionFunction($callable);
        } else {
            // Type inconnu, on continue sans modifier
            return $next($request);
        }

        // Parcourir les paramètres de la méthode ou closure
        foreach ($ref->getParameters() as $param) {
            $type = $param->getType()?->getName();

            // Injection automatique si le type est une classe DTO
            if ($type && class_exists($type) && str_ends_with($type, 'DTO')) {
                $dto = RequestDtoResolver::resolve($type, $request);
                // Injecter dans les paramètres de la route pour que Laravel l'injecte dans le callable
                $request->route()->setParameter($param->getName(), $dto);
            }
        }

        return $next($request);
    }
}
