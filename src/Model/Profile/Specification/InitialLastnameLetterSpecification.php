<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Profile\Specification;

use Contao\Model;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Netzmacht\Contao\Toolkit\Data\Model\Specification;

use function is_numeric;
use function range;
use function stripos;

final class InitialLastnameLetterSpecification implements Specification
{
    private string $letter;

    public function __construct(string $letter)
    {
        $this->letter = $letter;
    }

    public function isSatisfiedBy(Model $model): bool
    {
        if (! $model instanceof Profile) {
            return false;
        }

        if ($this->letter === '') {
            return true;
        }

        if ($this->letter === 'numeric') {
            return is_numeric($model->lastname[0] ?? null);
        }

        return stripos($model->lastname, $this->letter) === 0;
    }

    /** {@inheritDoc} */
    public function buildQuery(array &$columns, array &$values): void
    {
        if ($this->letter === '') {
            return;
        }

        if ($this->letter === 'numeric') {
            $letters = range('a', 'z');
            foreach ($letters as $letter) {
                $columns[] = '.lastname NOT LIKE ?';
                $values[]  = $letter . '%';
            }

            return;
        }

        $columns[] = '.lastname LIKE ?';
        $values[]  = $this->letter . '%';
    }
}
