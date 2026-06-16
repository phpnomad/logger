<?php

namespace PHPNomad\Logger\Traits;

use Exception;
use PHPNomad\Logger\Enums\LoggerLevel;
use PHPNomad\Logger\Interfaces\LoggerStrategy;
use Throwable;

trait CanLogException
{
    /**
     * Hard cap on how many levels of the exception chain are folded into the
     * logged message, guarding against pathologically deep chains.
     */
    protected int $maxLoggedExceptionDepth = 10;

    public function logException(Exception $e, string $message = '', array $context = [], $level = null)
    {
        if (!$level) {
            $level = LoggerLevel::Critical;
        }

        $context['exception'] = $e;
        $this->$level(implode(' - ', $this->buildExceptionMessageParts($e, $message)), $context);
    }

    /**
     * Flattens the prefix message and the exception chain into the message
     * parts that get logged.
     *
     * Datastore and other infrastructure exceptions deliberately surface a
     * stable, client-safe message and carry the real cause (e.g. the SQL
     * driver error) as a chained previous exception. Logging only the outer
     * message would discard that cause, so the chain is walked here.
     *
     * A chain message is skipped when it is already contained in a part we
     * have collected — some wrappers embed the cause's message in their own
     * text *and* chain the cause, which would otherwise log it twice. The
     * walk is also bounded by {@see $maxLoggedExceptionDepth} and a cycle
     * guard so the message can never grow without limit.
     *
     * @return list<string>
     */
    protected function buildExceptionMessageParts(Throwable $e, string $message): array
    {
        $parts = [];

        if ($message !== '') {
            $parts[] = $message;
        }

        $seen = [];
        $current = $e;
        $depth = 0;

        while ($current instanceof Throwable && $depth < $this->maxLoggedExceptionDepth && !in_array($current, $seen, true)) {
            $seen[] = $current;
            $depth++;

            $currentMessage = $current->getMessage();
            if ($currentMessage !== '' && !$this->messageAlreadyCollected($currentMessage, $parts)) {
                $parts[] = $currentMessage;
            }

            $current = $current->getPrevious();
        }

        return $parts;
    }

    /**
     * Whether a chain message is already represented in the collected parts,
     * so wrappers that embed their cause's message aren't logged twice.
     *
     * @param list<string> $parts
     */
    protected function messageAlreadyCollected(string $message, array $parts): bool
    {
        foreach ($parts as $part) {
            if (strpos($part, $message) !== false) {
                return true;
            }
        }

        return false;
    }
}
