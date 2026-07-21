<?php

declare(strict_types=1);

namespace App\Rules\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Wave 1 T3 — forbid bare auth() and Auth::{user,attempt,logout} outside the allowlist.
 *
 * @see docs/audit/auth-accessor-allowlist.md
 *
 * @implements Rule<Node>
 */
final class NoBareAuthCallRule implements Rule
{
    /**
     * Relative paths from repo root (forward slashes).
     *
     * @var list<string>
     */
    private const ALLOWED_FILES = [
        'app/Infrastructure/Auth/SessionAuthenticator.php',
        'app/Infrastructure/Auth/SessionAuthUserResolver.php',
    ];

    /**
     * @var list<string>
     */
    private const FORBIDDEN_AUTH_FACADE_METHODS = [
        'user',
        'attempt',
        'logout',
    ];

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $relativePath = $this->toRepoRelativePath($scope->getFile());

        if ($relativePath !== null && in_array($relativePath, self::ALLOWED_FILES, true)) {
            return [];
        }

        if ($node instanceof FuncCall) {
            return $this->processAuthHelperCall($node);
        }

        if ($node instanceof StaticCall) {
            return $this->processAuthFacadeCall($node, $scope);
        }

        return [];
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    private function processAuthHelperCall(FuncCall $node): array
    {
        if (! $node->name instanceof Name) {
            return [];
        }

        if ($node->name->toString() !== 'auth') {
            return [];
        }

        if (count($node->getArgs()) >= 1) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Bare auth() is forbidden. Use auth(\'identity\') or auth(\'web\') (Wave 1 T3 / HD-W1-Q1).'
            )->identifier('dormsys.noBareAuth')->build(),
        ];
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    private function processAuthFacadeCall(StaticCall $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $method = $node->name->toString();

        if (! in_array($method, self::FORBIDDEN_AUTH_FACADE_METHODS, true)) {
            return [];
        }

        if (! $this->isAuthFacade($node, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Auth::%s() is forbidden outside the Wave 1 allowlist (SessionAuthenticator / SessionAuthUserResolver). Use auth(\'identity\') or auth(\'web\').',
                    $method
                )
            )->identifier('dormsys.noAuthFacade')->build(),
        ];
    }

    private function isAuthFacade(StaticCall $node, Scope $scope): bool
    {
        if (! $node->class instanceof Name) {
            return false;
        }

        $resolved = $scope->resolveName($node->class);

        return $resolved === 'Illuminate\\Support\\Facades\\Auth'
            || $node->class->toString() === 'Auth';
    }

    private function toRepoRelativePath(string $absolutePath): ?string
    {
        $normalized = str_replace('\\', '/', $absolutePath);
        $marker = '/app/';
        $pos = strpos($normalized, $marker);

        if ($pos === false) {
            return null;
        }

        return ltrim(substr($normalized, $pos), '/');
    }
}
