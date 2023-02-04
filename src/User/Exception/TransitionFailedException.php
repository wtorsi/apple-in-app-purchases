<?php
declare(strict_types=1);

namespace User\Exception;

class TransitionFailedException extends RuntimeException
{
    public static function create(\BackedEnum|null $from, \BackedEnum|null $to, array $allowed = []): self
    {
        $format = static fn(\BackedEnum|null $v) => $v ? \sprintf('"%s" /%s/', $v->name, $v->value) : '"null"';

        return new self(\sprintf('Transition value %s from %s to %s is not allowed, allowed transitions "%s".',
            $from ? \get_class($from) : ($to ? \get_class($to) : 'null class'),
            $format($from),
            $format($to),
            \implode(',', \array_map(fn(\BackedEnum|null $v) => $v ? $v->name : 'null', $allowed)),
        ));
    }
}