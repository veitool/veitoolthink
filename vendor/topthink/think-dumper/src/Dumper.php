<?php

namespace think\dumper;

use Exception;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use Symfony\Component\VarDumper\Dumper\ContextualizedDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use think\Request;

class Dumper
{
    private static $handlers;

    public static function dump($var, ?string $label = null)
    {
        $token = env('DUMPER_TOKEN');

        $format = self::isCli() ? 'cli' : 'html';

        if (!empty($token)) {
            $format = 'server';
        }

        return self::getHandler($format)($var, $label);
    }

    private static function getHandler($format): callable
    {
        if (empty(self::$handlers[$format])) {
            self::$handlers[$format] = self::createHandler($format);
        }

        return self::$handlers[$format];
    }

    private static function createHandler($format)
    {
        $cloner = new VarCloner();
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

        switch ($format) {
            case 'html' :
                $dumper = new HtmlDumper();
                break;
            case 'cli':
                $dumper = new CliDumper();
                break;
            case 'server' :
                $dumper = self::isCli() ? new CliDumper() : new HtmlDumper();
                $dumper = new ServerDumper($dumper, self::getDefaultContextProviders());
                break;
            default:
                throw new Exception('Invalid dump format.');
        }

        if (!$dumper instanceof ServerDumper) {
            $dumper = new ContextualizedDumper($dumper, [new SourceContextProvider()]);
        }

        return function ($var, ?string $label = null) use ($cloner, $dumper) {
            $var = $cloner->cloneVar($var);

            if (null !== $label) {
                $var = $var->withContext(['label' => $label]);
            }

            $dumper->dump($var);
        };
    }

    private static function getDefaultContextProviders(): array
    {
        $contextProviders = [];

        if (self::isCli()) {
            $contextProviders['cli'] = new class implements ContextProviderInterface {
                public function getContext(): ?array
                {
                    return [
                        'command_line' => $commandLine = implode(' ', $_SERVER['argv'] ?? []),
                        'identifier'   => hash('crc32b', $commandLine . $_SERVER['REQUEST_TIME_FLOAT']),
                    ];
                }
            };
        } else {
            $contextProviders['request'] = new class implements ContextProviderInterface {
                public function getContext(): ?array
                {
                    $request = app(Request::class);
                    return [
                        'uri'        => $request->url(),
                        'method'     => $request->method(),
                        'identifier' => spl_object_hash($request),
                    ];
                }
            };
        }

        $contextProviders['source'] = new SourceContextProvider();

        return $contextProviders;
    }

    private static function isCli()
    {
        return app()->runningInConsole();
    }
}
