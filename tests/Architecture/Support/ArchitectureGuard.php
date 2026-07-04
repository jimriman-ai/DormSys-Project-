<?php

declare(strict_types=1);

namespace Tests\Architecture\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ArchitectureGuard
{
    /**
     * @return list<array{path: string, line: int, import: string, owner_module: string, foreign_module: string}>
     */
    public static function findCrossModuleApplicationImports(string $basePath, string $relativePath, string $ownerModule): array
    {
        $violations = [];
        $absolutePath = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (! is_dir($absolutePath)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absolutePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if ($contents === false) {
                continue;
            }

            foreach (explode("\n", $contents) as $index => $line) {
                if (! preg_match('/^\s*use\s+(App\\\\Modules\\\\([A-Za-z]+)\\\\Application\\\\[^;]+);/', $line, $matches)) {
                    continue;
                }

                $foreignModule = $matches[2];

                if ($foreignModule === $ownerModule) {
                    continue;
                }

                $repoRelativePath = self::toRepoRelativePath($basePath, $file->getPathname());

                $violations[] = [
                    'path' => $repoRelativePath,
                    'line' => $index + 1,
                    'import' => $matches[1],
                    'owner_module' => $ownerModule,
                    'foreign_module' => $foreignModule,
                ];
            }
        }

        return $violations;
    }

    /**
     * @return list<array{path: string, line: int, import: string, foreign_module: string}>
     */
    public static function findForeignDomainImports(string $basePath, string $module): array
    {
        $violations = [];
        $applicationPath = "app/Modules/{$module}/Application";
        $absolutePath = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $applicationPath);

        if (! is_dir($absolutePath)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absolutePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if ($contents === false) {
                continue;
            }

            foreach (explode("\n", $contents) as $index => $line) {
                if (! preg_match('/^\s*use\s+(App\\\\Modules\\\\([A-Za-z]+)\\\\Domain\\\\[^;]+);/', $line, $matches)) {
                    continue;
                }

                if ($matches[2] === $module) {
                    continue;
                }

                $violations[] = [
                    'path' => self::toRepoRelativePath($basePath, $file->getPathname()),
                    'line' => $index + 1,
                    'import' => $matches[1],
                    'foreign_module' => $matches[2],
                ];
            }
        }

        return $violations;
    }

    /**
     * @param  list<class-string>  $portClasses
     * @return list<array{port: string, path: string, line: int}>
     */
    public static function findPortBindingsInModuleProviders(string $basePath, array $portClasses): array
    {
        $violations = [];
        $providersPath = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';

        if (! is_dir($providersPath)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($providersPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php' || ! str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if ($contents === false) {
                continue;
            }

            $repoRelativePath = self::toRepoRelativePath($basePath, $file->getPathname());

            foreach ($portClasses as $portClass) {
                $shortName = class_basename($portClass);

                foreach (explode("\n", $contents) as $index => $line) {
                    if (
                        str_contains($line, 'singleton(')
                        && (str_contains($line, $portClass) || str_contains($line, "{$shortName}::class"))
                    ) {
                        $violations[] = [
                            'port' => $portClass,
                            'path' => $repoRelativePath,
                            'line' => $index + 1,
                        ];
                    }
                }
            }
        }

        return $violations;
    }

    /**
     * @return list<array{path: string, line: int, pattern: string, message: string}>
     */
    public static function scanForbiddenImports(string $basePath): array
    {
        $violations = [];
        $rules = [
            [
                'scope' => 'app/Modules/*/Domain',
                'pattern' => '/^\s*use\s+Illuminate\\\\Database\\\\Eloquent\\\\[^;]+;/',
                'message' => 'Domain layer must not import Eloquent',
            ],
            [
                'scope' => 'app/Modules/*/Domain',
                'pattern' => '/^\s*use\s+App\\\\Modules\\\\[^\\\\]+\\\\Infrastructure\\\\[^;]+;/',
                'message' => 'Domain layer must not import module Infrastructure',
            ],
            [
                'scope' => 'app/Modules/*/Domain',
                'pattern' => '/^\s*use\s+Illuminate\\\\Support\\\\Facades\\\\[^;]+;/',
                'message' => 'Domain layer must not import Laravel facades',
            ],
            [
                'scope' => 'app/Modules/*/Application',
                'pattern' => '/^\s*use\s+App\\\\Modules\\\\[^\\\\]+\\\\Infrastructure\\\\[^;]+;/',
                'message' => 'Application layer must not import module Infrastructure',
            ],
            [
                'scope' => 'app/Modules/*/Domain',
                'pattern' => '/@extends\s+State\s*</',
                'message' => 'Domain state must not bind to Infrastructure models in PHPDoc',
            ],
        ];

        $modulesPath = rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';

        if (! is_dir($modulesPath)) {
            return [];
        }

        foreach (scandir($modulesPath) ?: [] as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }

            foreach (['Domain', 'Application'] as $layer) {
                $layerPath = "{$modulesPath}/{$module}/{$layer}";

                if (! is_dir($layerPath)) {
                    continue;
                }

                $layerRules = array_values(array_filter(
                    $rules,
                    static fn (array $rule): bool => str_contains($rule['scope'], $layer === 'Domain' ? '/Domain' : '/Application')
                ));

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($layerPath, RecursiveDirectoryIterator::SKIP_DOTS)
                );

                /** @var SplFileInfo $file */
                foreach ($iterator as $file) {
                    if ($file->getExtension() !== 'php') {
                        continue;
                    }

                    $contents = file_get_contents($file->getPathname());

                    if ($contents === false) {
                        continue;
                    }

                    foreach (explode("\n", $contents) as $index => $line) {
                        foreach ($layerRules as $rule) {
                            if (preg_match($rule['pattern'], $line) !== 1) {
                                continue;
                            }

                            $violations[] = [
                                'path' => self::toRepoRelativePath($basePath, $file->getPathname()),
                                'line' => $index + 1,
                                'pattern' => $rule['message'],
                                'message' => $rule['message'],
                            ];
                        }
                    }
                }
            }
        }

        return $violations;
    }

    /**
     * @param  list<string>  $matrixModules
     * @return list<array{path: string, line: int, import: string, module: string, foreign_module: string}>
     */
    public static function findMatrixApplicationForeignDomainImports(string $basePath, array $matrixModules): array
    {
        $violations = [];

        foreach ($matrixModules as $module) {
            foreach (self::findForeignDomainImports($basePath, $module) as $finding) {
                $violations[] = [
                    'path' => $finding['path'],
                    'line' => $finding['line'],
                    'import' => $finding['import'],
                    'module' => $module,
                    'foreign_module' => $finding['foreign_module'],
                ];
            }
        }

        return $violations;
    }

    private static function toRepoRelativePath(string $basePath, string $absolutePath): string
    {
        $normalizedBase = rtrim(str_replace('\\', '/', $basePath), '/');
        $normalizedPath = str_replace('\\', '/', $absolutePath);

        if (str_starts_with($normalizedPath, $normalizedBase.'/')) {
            return substr($normalizedPath, strlen($normalizedBase) + 1);
        }

        return $normalizedPath;
    }
}
