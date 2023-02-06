<?php

declare(strict_types=1);

/*
 * This file is part of the box project.
 *
 * (c) Kevin Herrera <kevin@herrera.io>
 *     Théo Fidry <theo.fidry@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace KevinGH\Box\PhpScoper;

use function func_get_args;
use Humbug\PhpScoper\Scoper\Scoper as PhpScoperScoper;

final class CallRecorderScoper implements PhpScoperScoper
{
    private $calls = [];

    /**
     * {@inheritdoc}
     */
    public function scope(string $filePath, string $contents): string
    {
        $this->calls[] = func_get_args();

        return $contents;
    }

    public function getCalls(): array
    {
        return $this->calls;
    }
}
